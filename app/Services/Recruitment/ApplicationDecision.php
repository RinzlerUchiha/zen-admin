<?php

namespace App\Services\Recruitment;

use App\Models\Applicant\ApplicantApplication;
use App\Models\Applicant\ApplicantDocumentProcess;
use App\Models\Applicant\ApplicantDocumentRequest;
use App\Models\Applicant\ApplicantPersonal;
use App\Models\Applicant\Offer;
use App\Models\Applicant\StageHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Decisions that end ONE application.
 *
 * Every way an application closes — HR withdrawing it, HR marking it Not
 * Selected, its document deadline passing — goes through close(), so each is
 * recorded the same way:
 *
 *   - the application gets its status, closed_at, closed_by and closed_note;
 *   - its own active document process ends with the matching outcome;
 *   - that process's unresolved requests are cancelled (kept, not deleted).
 *
 * Nothing else is touched: the applicant's other applications, their profile,
 * their documents, and any request that is not part of this application's
 * process all carry on. Candidate Pool membership follows from the status
 * (config/applications.php) and is therefore per application too.
 *
 * The applicant withdrawing their own application is the same rule, applied in
 * the applicant portal (zen-applicants App\Services\ApplicationWithdrawal).
 */
class ApplicationDecision
{
    /** HR withdraws a specific application on the applicant's behalf. */
    public static function withdraw(int $applicationId, string $empno, ?string $note): ApplicantApplication
    {
        return self::decide($applicationId, ApplicantApplication::WITHDRAWN, ApplicantDocumentProcess::WITHDRAWN, $empno, $note);
    }

    /** HR decides not to proceed with a specific application. */
    public static function notSelected(int $applicationId, string $empno, ?string $note): ApplicantApplication
    {
        return self::decide($applicationId, ApplicantApplication::NOT_SELECTED, ApplicantDocumentProcess::NOT_SELECTED, $empno, $note);
    }

    /**
     * HR decides not to shortlist this application.
     *
     * Screening or matching found them unsuitable for THIS posting, before any
     * interview. Carries no cooldown: the Candidate Pool exists to surface
     * exactly these people for the next posting.
     */
    public static function notShortlisted(int $applicationId, string $empno, ?string $note): ApplicantApplication
    {
        return self::decide($applicationId, ApplicantApplication::NOT_SHORTLISTED, ApplicantDocumentProcess::NOT_SHORTLISTED, $empno, $note);
    }

    /**
     * The applicant declined the offer, and HR recorded it.
     *
     * Their decision rather than HR's, which is why this is not notSelected():
     * that status records a judgement HR made about them. Carries no cooldown.
     */
    public static function offerDeclined(int $applicationId, string $empno, ?string $note): ApplicantApplication
    {
        return self::decide($applicationId, ApplicantApplication::OFFER_DECLINED, ApplicantDocumentProcess::OFFER_DECLINED, $empno, $note);
    }

    /**
     * HR records that this applicant was actually hired.
     *
     * WHY THIS IS NOT close()
     *
     * 'Hired' is configured OPEN (config/applications.php), deliberately:
     * requirements may still be outstanding, an exam may be unsat, and the 201
     * handoff has not happened. close() refuses a non-closing status and would
     * stamp closed_at, which is why this has its own path and why closed_at,
     * closed_by and closed_note are left alone.
     *
     * WHY IT IS MANUAL
     *
     * Signing the contract is a milestone; being hired is a different one.
     * For headcount purposes somebody counts as hired once they have been
     * ORIENTED, which no record in this system observes -- so a person records
     * it. Nothing here is triggered by an offer being accepted or signed, and
     * nothing in the offer workflow calls this.
     *
     * WHAT IT DOES NOT DO
     *
     * It creates no 201 employee record, touches no HRIS table, and does not
     * change tbl_manpower_request_position.filled. Those are separate acts.
     *
     * The note is required because this is the one disposition with no
     * supporting record of its own: the only evidence that orientation
     * happened is what HR writes here.
     */
    public static function hired(int $applicationId, string $empno, string $note): ApplicantApplication
    {
        $note = trim($note);

        if ($note === '') {
            throw ValidationException::withMessages([
                'note' => 'Say what happened — when they were oriented, or how you know. '
                    . 'It is the only record of it.',
            ]);
        }

        $application = ApplicantApplication::find($applicationId);

        if (!$application) {
            throw ValidationException::withMessages(['application' => 'That application could not be found.']);
        }

        return self::inLock($application->app_id, function () use ($applicationId, $empno, $note) {
            $current = ApplicantApplication::whereKey($applicationId)->first();

            /* A closed application is over. Reopening one is a different act
               with different rules, and this is not it. */
            if ($current->is_closed || $current->closed_at !== null) {
                throw ValidationException::withMessages([
                    'application' => 'This application is closed (' . $current->status . ').',
                ]);
            }

            if ($current->status === ApplicantApplication::HIRED) {
                throw ValidationException::withMessages([
                    'application' => 'This application is already recorded as Hired.',
                ]);
            }

            /*
            | An approved offer, accepted, with the signed contract recorded.
            |
            | Of the eleven offer statuses exactly one means all three of those
            | things, and it is CONTRACT_RECEIVED -- reachable only along
            | draft -> pending_approval -> approved -> sent -> accepted ->
            | contract_received. So requiring it enforces the whole chain
            | without this method knowing anything about approval, and without
            | inventing a status or touching the offer workflow.
            |
            | Accepted is deliberately NOT enough: the contract steps are a
            | milestone of their own, which is why they have their own status.
            */
            $qualifying = Offer::where('application_id', $current->id)
                ->where('status', Offer::CONTRACT_RECEIVED)
                ->exists();

            if (!$qualifying) {
                throw ValidationException::withMessages([
                    'application' => self::whyNotHireable($current->id),
                ]);
            }

            $was = (string) $current->status;

            $current->update(['status' => ApplicantApplication::HIRED]);

            /*
            | Who and when, in the application's own history.
            |
            | The stage is NOT moved: being hired is a disposition, not a round,
            | so from and to are the same and the record says what changed.
            | This is the same append-only trail the interview outcomes use.
            */
            self::recordDisposition($current, $was, ApplicantApplication::HIRED, $note, $empno);

            return $current->refresh();
        });
    }

    /**
     * Undo a Hired that was recorded by mistake.
     *
     * Offered because the alternative is an application stuck in a disposition
     * nobody can correct. The previous status is DERIVED rather than
     * remembered -- documents complete, or back to applied -- so this cannot
     * restore a stale value, and both are open statuses so closed_at stays
     * untouched either way.
     */
    public static function unmarkHired(int $applicationId, string $empno, string $reason): ApplicantApplication
    {
        $reason = trim($reason);

        if ($reason === '') {
            throw ValidationException::withMessages([
                'reason' => 'Say why this is being taken back.',
            ]);
        }

        $application = ApplicantApplication::find($applicationId);

        if (!$application) {
            throw ValidationException::withMessages(['application' => 'That application could not be found.']);
        }

        return self::inLock($application->app_id, function () use ($applicationId, $empno, $reason) {
            $current = ApplicantApplication::whereKey($applicationId)->first();

            if ($current->status !== ApplicantApplication::HIRED) {
                throw ValidationException::withMessages([
                    'application' => 'This application is not recorded as Hired.',
                ]);
            }

            $documents = ApplicantDocumentReview::summary((int) $current->app_id);

            $to = ($documents['complete'] ?? false)
                ? ApplicantApplication::DOCUMENTS_COMPLETE
                : ApplicantApplication::APPLIED;

            $current->update(['status' => $to]);

            self::recordDisposition($current, ApplicantApplication::HIRED, $to, $reason, $empno);

            return $current->refresh();
        });
    }

    /**
     * Why this application cannot be recorded as Hired yet.
     *
     * Names the step that is actually outstanding rather than restating the
     * rule, because "not eligible" sends HR looking for the problem. Reads the
     * offer's own status — no new state is derived and nothing is written.
     */
    private static function whyNotHireable(int $applicationId): string
    {
        $offer = Offer::where('application_id', $applicationId)->orderByDesc('id')->first();

        $lead = 'An applicant is recorded as Hired after an approved offer has been accepted and '
            . 'the signed contract recorded. ';

        if (!$offer) {
            return $lead . 'This application has no offer.';
        }

        return $lead . match ($offer->status) {
            Offer::DRAFT => 'Its offer is still a draft — it has not been submitted for approval.',
            Offer::PENDING_APPROVAL => 'Its offer is waiting on the HR Head.',
            Offer::REVISION_REQUESTED => 'The HR Head sent its offer back for revision.',
            Offer::APPROVED => 'Its offer is approved but has not been sent to the applicant.',
            Offer::SENT => 'Its offer is with the applicant, who has not answered yet.',
            Offer::ACCEPTED => 'The applicant accepted, but the signed contract has not been '
                . 'recorded yet.',
            Offer::REJECTED => 'The HR Head declined its offer.',
            Offer::DECLINED => 'The applicant declined its offer.',
            Offer::WITHDRAWN => 'Its offer was withdrawn.',
            Offer::EXPIRED => 'Its offer ended without an answer.',
            default => 'Its offer is ' . $offer->status . '.',
        };
    }

    /**
     * One append-only line for a disposition that does not close anything.
     *
     * from and to are the same stage on purpose: this records a decision ABOUT
     * the application, not a move between stages, so it must not disturb where
     * the application actually is.
     */
    private static function recordDisposition(
        ApplicantApplication $application,
        string $was,
        string $now,
        string $note,
        ?string $empno
    ): void {
        StageHistory::create([
            'application_id' => $application->id,
            'from_stage' => $application->stage_key,
            'to_stage' => $application->stage_key,
            'reason' => 'Status changed from ' . $was . ' to ' . $now . ' — ' . $note,
            'actor_empno' => $empno,
            'occurred_at' => now(),
        ]);
    }

    /**
     * HR closes an application because the applicant was recommended onward.
     *
     * Called only after a recommendation has successfully produced the other
     * application, and only when HR chooses to — a recommendation does not
     * close the original on its own, because HR normally discusses the options
     * with the applicant first and they may still prefer this one.
     *
     * Not a rejection: the status carries no cooldown, so the applicant may
     * apply to this posting again.
     */
    public static function recommended(int $applicationId, string $empno, ?string $note): ApplicantApplication
    {
        return self::decide($applicationId, ApplicantApplication::RECOMMENDED, ApplicantDocumentProcess::RECOMMENDED, $empno, $note);
    }

    private static function decide(
        int $applicationId,
        string $status,
        string $processStatus,
        string $empno,
        ?string $note
    ): ApplicantApplication {
        $application = ApplicantApplication::find($applicationId);

        if (!$application) {
            throw ValidationException::withMessages(['application' => 'That application could not be found.']);
        }

        return self::inLock($application->app_id, function () use ($applicationId, $status, $processStatus, $empno, $note) {
            $current = ApplicantApplication::whereKey($applicationId)->first();

            return self::close($current, $status, $processStatus, $empno, $note);
        });
    }

    /**
     * Ends an application. The one place an application becomes closed.
     *
     * Must run inside the applicant's lock (inLock). Refuses an application
     * that has already closed, so the first outcome reached is the one kept.
     *
     * @param  string|null  $empno  HR's Emp_No; null when no person decided (the deadline job).
     */
    public static function close(
        ApplicantApplication $application,
        string $status,
        string $processStatus,
        ?string $empno,
        ?string $note
    ): ApplicantApplication {
        $definition = ApplicantApplication::statusDefinition($status);

        if (empty($definition['closed'])) {
            throw new \InvalidArgumentException("'$status' is not a closing status in config/applications.php.");
        }

        if ($application->is_closed || $application->closed_at !== null) {
            throw ValidationException::withMessages([
                'application' => 'This application has already been closed (' . $application->status . ').',
            ]);
        }

        $now = now();

        $application->update([
            'status' => $status,
            'closed_at' => $now,
            'closed_by' => $empno,
            'closed_note' => $note,
        ]);

        $process = ApplicantDocumentProcess::where('application_id', $application->id)->active()->first();

        if ($process) {
            self::endProcess($process, $processStatus, $empno, $note);
        }

        return $application->refresh();
    }

    /**
     * Ends a process and cancels what it was still waiting for. Only this
     * process's own requests: a request that belongs to no process, or to
     * another application's process, is not affected.
     */
    public static function endProcess(
        ApplicantDocumentProcess $process,
        string $processStatus,
        ?string $empno,
        ?string $note
    ): void {
        $process->update([
            'status' => $processStatus,
            'outcome_at' => now(),
            'closed_by' => $empno,
            'closed_note' => $note,
        ]);

        ApplicantDocumentRequest::where('process_id', $process->id)
            ->active()
            ->update(['status' => 'cancelled', 'closed_by' => $empno, 'closed_at' => now()]);
    }

    /**
     * The same applicant-row lock every document write takes, so a decision
     * cannot interleave with an upload, a review or the deadline job. Safe to
     * nest inside a caller that already holds it.
     */
    public static function inLock(int $appId, callable $callback)
    {
        return DB::connection('applicant')->transaction(function () use ($appId, $callback) {
            ApplicantPersonal::whereKey($appId)->lockForUpdate()->first();

            return $callback();
        });
    }
}
