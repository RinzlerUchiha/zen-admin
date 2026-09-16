<?php

namespace App\Services\Recruitment;

use App\Models\Applicant\ApplicantDocumentProcess;
use App\Models\Applicant\ApplicantDocumentRequest;
use App\Models\Applicant\ApplicantPersonal;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * The document-completion process: one run at getting an applicant's
 * outstanding documents in.
 *
 * Milestone 2 asks for documents one at a time, each with its own request. This
 * puts one clock and one allowance around the whole set:
 *
 *   - ONE deadline for the run. Adding another request does not move it; only
 *     HR deciding to change it does.
 *   - ONE attempt counter for the run. A rejection costs an attempt whichever
 *     document it was for; an acceptance costs nothing.
 *
 * A run ends in exactly one of four ways, and the first one reached wins:
 *
 *   complete              every request accepted        → next screening stage
 *   requirements_not_met  attempts ran out              → they answered, but the
 *                                                         documents never passed
 *   non_responsive        deadline passed               → they did not answer
 *   withdrawn             the applicant pulled out      → at any time
 *
 * Nothing reopens a finished run. Every write happens under the applicant-row
 * lock that ApplicantDocumentReview already takes, so a rejection and an upload
 * cannot interleave and spend the same attempt twice.
 */
class DocumentCompletion
{
    /* =====================================================================
     * Reading
     * ===================================================================== */

    public static function activeFor(int $appId): ?ApplicantDocumentProcess
    {
        return ApplicantDocumentProcess::where('app_id', $appId)->active()->latest('id')->first();
    }

    /** The run to show HR: the active one, else the most recent outcome. */
    public static function currentFor(int $appId): ?ApplicantDocumentProcess
    {
        return ApplicantDocumentProcess::where('app_id', $appId)
            ->orderByRaw("status = '" . ApplicantDocumentProcess::ACTIVE . "' DESC")
            ->latest('id')
            ->first();
    }

    public static function defaults(): array
    {
        return [
            'deadline_days' => (int) config('applicant_documents.completion.deadline_days', 7),
            'max_attempts' => (int) config('applicant_documents.completion.max_attempts', 3),
        ];
    }

    /* =====================================================================
     * HR actions
     * ===================================================================== */

    /**
     * Starts the run and fixes its deadline. HR may override the period and the
     * attempt limit; whatever is chosen is stored on the run, so a later change
     * to the defaults cannot move a deadline already given.
     */
    public static function start(
        int $appId,
        ?int $applicationId,
        ?int $deadlineDays,
        ?int $maxAttempts,
        string $empno
    ): ApplicantDocumentProcess {
        $defaults = self::defaults();
        $days = $deadlineDays ?: $defaults['deadline_days'];
        $attempts = $maxAttempts ?: $defaults['max_attempts'];

        self::assertWithinLimits($days, $attempts);

        return self::inLock($appId, function () use ($appId, $applicationId, $days, $attempts, $empno) {
            if (self::activeFor($appId)) {
                throw ValidationException::withMessages([
                    'process' => 'A document completion process is already running for this applicant.',
                ]);
            }

            self::assertApplicationBelongs($applicationId, $appId);

            $process = ApplicantDocumentProcess::create([
                'app_id' => $appId,
                'application_id' => $applicationId,
                'status' => ApplicantDocumentProcess::ACTIVE,
                'deadline_days' => $days,
                'deadline_at' => HolidayAwareDeadline::from(now(), $days),
                'max_attempts' => $attempts,
                'attempts_used' => 0,
                'started_by' => $empno,
                'started_at' => now(),
            ]);

            // Requests HR already made and is still waiting on join the run, so
            // the applicant is not asked to satisfy two different clocks.
            ApplicantDocumentRequest::where('app_id', $appId)
                ->whereNull('process_id')
                ->active()
                ->update(['process_id' => $process->id]);

            return $process;
        });
    }

    /**
     * Changes the deadline. Always a deliberate act by HR — adding a request
     * never does this, and neither does a rejection.
     */
    public static function changeDeadline(ApplicantDocumentProcess $process, int $days, string $empno): void
    {
        self::assertWithinLimits($days, $process->max_attempts);

        self::inLock($process->app_id, function () use ($process, $days) {
            $current = self::lockedOrFail($process);

            // The clock restarts from today, not from the original start: HR is
            // giving the applicant this many days from now.
            $current->update([
                'deadline_days' => $days,
                'deadline_at' => HolidayAwareDeadline::from(now(), $days),
            ]);
        });
    }

    /** Changes how many rejections the run allows. */
    public static function changeMaxAttempts(ApplicantDocumentProcess $process, int $attempts, string $empno): void
    {
        self::assertWithinLimits($process->deadline_days, $attempts);

        self::inLock($process->app_id, function () use ($process, $attempts, $empno) {
            $current = self::lockedOrFail($process);

            if ($attempts < $current->attempts_used) {
                throw ValidationException::withMessages([
                    'max_attempts' => 'The applicant has already used ' . $current->attempts_used
                        . ' attempts. Set the limit to at least that.',
                ]);
            }

            $current->update(['max_attempts' => $attempts]);

            // Lowering the limit to what has already been spent ends the run
            // now, on the same rule as any other exhausted allowance.
            if ($current->attempts_used >= $attempts && self::hasUnresolved($current)) {
                self::close($current, ApplicantDocumentProcess::REQUIREMENTS_NOT_MET, $empno,
                    'Attempt limit reached.');
            }
        });
    }

    /* =====================================================================
     * Reacting to the Milestone 2 review decisions
     *
     * These run INSIDE ApplicantDocumentReview's lock, so they never take it
     * again themselves.
     * ===================================================================== */

    /**
     * A rejection costs one attempt, whichever document it was for. Running out
     * ends the run immediately — HR does not wait for a nightly job to find out
     * that the applicant has no attempts left.
     */
    public static function countRejection(int $appId, string $empno): void
    {
        $process = self::activeFor($appId);

        if (!$process) {
            return;
        }

        $process->increment('attempts_used');
        $process->refresh();

        if ($process->attempts_used >= $process->max_attempts && self::hasUnresolved($process)) {
            self::close($process, ApplicantDocumentProcess::REQUIREMENTS_NOT_MET, $empno,
                'Attempt limit reached with documents still outstanding.');
        }
    }

    /**
     * An acceptance costs nothing. It can finish the run: once nothing under it
     * is still waiting, the applicant has done what was asked.
     */
    public static function settleAcceptance(int $appId, string $empno): void
    {
        $process = self::activeFor($appId);

        if (!$process) {
            return;
        }

        // A run that never carried a request is not "complete" — there was
        // nothing to complete. It stays open for HR to ask for something.
        $everAsked = ApplicantDocumentRequest::where('process_id', $process->id)->exists();

        if ($everAsked && !self::hasUnresolved($process)) {
            self::close($process, ApplicantDocumentProcess::COMPLETE, $empno, null);
        }
    }

    /** Puts a newly created request under the active run, if there is one. */
    public static function attach(ApplicantDocumentRequest $request): void
    {
        $process = self::activeFor($request->app_id);

        if ($process && !$request->process_id) {
            // Deliberately does NOT touch the deadline. Asking for one more
            // document does not buy the applicant more time, and does not take
            // any away either.
            $request->update(['process_id' => $process->id]);
        }
    }

    /* =====================================================================
     * Ending a run
     * ===================================================================== */

    /**
     * The applicant pulls out. Independent of the deadline and the attempts,
     * and allowed at any point before the run has already ended.
     */
    public static function withdraw(int $appId, ?string $note = null): bool
    {
        return (bool) self::inLock($appId, function () use ($appId, $note) {
            $process = self::activeFor($appId);

            if (!$process) {
                return false;
            }

            self::close($process, ApplicantDocumentProcess::WITHDRAWN, null, $note);

            return true;
        });
    }

    /**
     * The deadline passed. Called by the nightly sweep, one applicant at a
     * time. Re-reads under the lock, so a run that finished in the meantime is
     * left exactly as it is.
     */
    public static function expire(ApplicantDocumentProcess $process): bool
    {
        return (bool) self::inLock($process->app_id, function () use ($process) {
            $current = ApplicantDocumentProcess::whereKey($process->id)->first();

            // Already finished, or the deadline moved: not this job's business.
            if (!$current || !$current->is_active || $current->deadline_at->isFuture()) {
                return false;
            }

            if (!self::hasUnresolved($current)) {
                return false;
            }

            self::close($current, ApplicantDocumentProcess::NON_RESPONSIVE, null,
                'Deadline passed with documents still outstanding.');

            return true;
        });
    }

    /**
     * Writes the outcome. The one place a run becomes terminal.
     */
    private static function close(
        ApplicantDocumentProcess $process,
        string $status,
        ?string $empno,
        ?string $note
    ): void {
        $process->update([
            'status' => $status,
            'outcome_at' => now(),
            'closed_by' => $empno,
            'closed_note' => $note,
        ]);

        // Nothing is still being waited on once the run is over. Requests are
        // cancelled rather than deleted — what was asked for is still on record.
        ApplicantDocumentRequest::where('process_id', $process->id)
            ->active()
            ->update(['status' => 'cancelled', 'closed_by' => $empno, 'closed_at' => now()]);

        self::mirrorToApplication($process, $status);
    }

    /**
     * Shows the outcome where the applicant already looks: the status of the
     * application it belongs to. A run started without an application has
     * nowhere to mirror to, and does not need one.
     */
    private static function mirrorToApplication(ApplicantDocumentProcess $process, string $status): void
    {
        if (!$process->application_id) {
            return;
        }

        DB::connection('applicant')->table('tblapp_applications')
            ->where('id', $process->application_id)
            ->update([
                'status' => config('applicant_documents.completion.application_status.' . $status, $status),
                'updated_at' => now(),
            ]);
    }

    /* =====================================================================
     * Helpers
     * ===================================================================== */

    /** Is the applicant still being waited on for anything in this run? */
    public static function hasUnresolved(ApplicantDocumentProcess $process): bool
    {
        return ApplicantDocumentRequest::where('process_id', $process->id)->active()->exists();
    }

    private static function assertWithinLimits(int $days, int $attempts): void
    {
        $maxDays = (int) config('applicant_documents.completion.deadline_days_max', 60);
        $maxAttempts = (int) config('applicant_documents.completion.max_attempts_max', 20);

        if ($days < 1 || $days > $maxDays) {
            throw ValidationException::withMessages([
                'deadline_days' => "Give the applicant between 1 and $maxDays days.",
            ]);
        }

        if ($attempts < 1 || $attempts > $maxAttempts) {
            throw ValidationException::withMessages([
                'max_attempts' => "Allow between 1 and $maxAttempts attempts.",
            ]);
        }
    }

    private static function lockedOrFail(ApplicantDocumentProcess $process): ApplicantDocumentProcess
    {
        $current = ApplicantDocumentProcess::whereKey($process->id)->first();

        if (!$current || !$current->is_active) {
            throw ValidationException::withMessages([
                'process' => 'This document completion process has already ended.',
            ]);
        }

        return $current;
    }

    private static function inLock(int $appId, callable $callback)
    {
        return DB::connection('applicant')->transaction(function () use ($appId, $callback) {
            ApplicantPersonal::whereKey($appId)->lockForUpdate()->first();

            return $callback();
        });
    }

    private static function assertApplicationBelongs(?int $applicationId, int $appId): void
    {
        if ($applicationId === null) {
            return;
        }

        $belongs = DB::connection('applicant')->table('tblapp_applications')
            ->where('id', $applicationId)
            ->where('app_id', $appId)
            ->exists();

        if (!$belongs) {
            throw ValidationException::withMessages([
                'application_id' => 'That application does not belong to this applicant.',
            ]);
        }
    }
}
