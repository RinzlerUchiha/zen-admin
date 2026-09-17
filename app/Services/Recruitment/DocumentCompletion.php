<?php

namespace App\Services\Recruitment;

use App\Models\Applicant\ApplicantApplication;
use App\Models\Applicant\ApplicantDocumentProcess;
use App\Models\Applicant\ApplicantDocumentRequest;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

/**
 * The document process: ONE application's document gate.
 *
 * The applicant owns one reusable set of documents, and HR's requests for them
 * belong to the applicant too. A process is what a single application needs
 * from that set before it can move on: the requests HR attaches to it, and one
 * deadline for all of them.
 *
 *   - One active process per application (also enforced by the database). An
 *     applicant with several applications can have several, one each.
 *   - A request belongs to at most one process, and HR chooses which. Nothing
 *     attaches a request to an application's process by itself.
 *   - There is no attempt counter. A rejection reopens the request and costs
 *     nothing; it never moves the deadline.
 *   - Adding a request never moves the deadline either. Only HR changing it
 *     does.
 *
 * A process ends in one of these ways, and the first reached is kept:
 *
 *   complete        every attached request accepted → the application becomes
 *                   "Documents Complete" and stays open
 *   non_responsive  the deadline passed with requests unresolved → the
 *                   application closes as "Non-Responsive"
 *   withdrawn /     the application was closed by that decision
 *   not_selected    (ApplicationDecision)
 *
 * Everything here runs under the applicant-row lock that the document review
 * and the applicant's uploads also take.
 */
class DocumentCompletion
{
    /* =====================================================================
     * Reading
     * ===================================================================== */

    public static function activeForApplication(int $applicationId): ?ApplicantDocumentProcess
    {
        return ApplicantDocumentProcess::where('application_id', $applicationId)->active()->first();
    }

    /** Every active process the applicant has — at most one per application. */
    public static function activeForApplicant(int $appId): Collection
    {
        return ApplicantDocumentProcess::with('application')
            ->where('app_id', $appId)
            ->active()
            ->orderBy('deadline_at')
            ->get();
    }

    public static function defaultDeadlineDays(): int
    {
        return (int) config('applicant_documents.completion.deadline_days', 7);
    }

    /** Is anything attached to this process still waiting on the applicant? */
    public static function hasUnresolved(ApplicantDocumentProcess $process): bool
    {
        return ApplicantDocumentRequest::where('process_id', $process->id)->active()->exists();
    }

    /* =====================================================================
     * HR actions
     * ===================================================================== */

    /**
     * Starts the document process for one open application and fixes its
     * deadline.
     */
    public static function start(int $appId, int $applicationId, ?int $deadlineDays, string $empno): ApplicantDocumentProcess
    {
        $days = $deadlineDays ?: self::defaultDeadlineDays();
        self::assertDeadlineDays($days);

        return ApplicationDecision::inLock($appId, function () use ($appId, $applicationId, $days, $empno) {
            $application = ApplicantApplication::where('id', $applicationId)->where('app_id', $appId)->first();

            if (!$application) {
                throw ValidationException::withMessages([
                    'application_id' => 'That application does not belong to this applicant.',
                ]);
            }

            if ($application->is_closed || $application->closed_at !== null) {
                throw ValidationException::withMessages([
                    'application_id' => 'That application has already been closed (' . $application->status . ').',
                ]);
            }

            if (self::activeForApplication($application->id)) {
                throw self::alreadyRunning();
            }

            $otherActive = ApplicantDocumentProcess::where('app_id', $appId)->active()->exists();

            try {
                $process = ApplicantDocumentProcess::create([
                    'app_id' => $appId,
                    'application_id' => $application->id,
                    'status' => ApplicantDocumentProcess::ACTIVE,
                    'deadline_days' => $days,
                    'deadline_at' => HolidayAwareDeadline::from(now(), $days),
                    'started_by' => $empno,
                    'started_at' => now(),
                ]);
            } catch (QueryException $e) {
                // The database's one-active-process-per-application guard: a
                // concurrent start won. Same outcome as the check above.
                if (($e->errorInfo[1] ?? null) === 1062) {
                    throw self::alreadyRunning();
                }
                throw $e;
            }

            // Outstanding requests HR already made for THIS application join its
            // process. A request made for no particular application joins only
            // when this is the applicant's only running process — with more than
            // one, which it belongs to is HR's choice, never assumed.
            ApplicantDocumentRequest::where('app_id', $appId)
                ->whereNull('process_id')
                ->active()
                ->where(function ($query) use ($application, $otherActive) {
                    $query->where('application_id', $application->id);

                    if (!$otherActive) {
                        $query->orWhereNull('application_id');
                    }
                })
                ->update(['process_id' => $process->id, 'application_id' => $application->id]);

            return $process;
        });
    }

    /**
     * Changes the deadline. Always a deliberate act by HR, counted again from
     * today.
     */
    public static function changeDeadline(ApplicantDocumentProcess $process, int $days, string $empno): void
    {
        self::assertDeadlineDays($days);

        ApplicationDecision::inLock($process->app_id, function () use ($process, $days) {
            $current = ApplicantDocumentProcess::whereKey($process->id)->first();

            if (!$current || !$current->is_active) {
                throw ValidationException::withMessages([
                    'process' => 'This document process has already ended.',
                ]);
            }

            $current->update([
                'deadline_days' => $days,
                'deadline_at' => HolidayAwareDeadline::from(now(), $days),
            ]);
        });
    }

    /**
     * Which process a new request belongs to, from HR's choice.
     *
     *   no active process            → none; the choice is not needed
     *   $choice === 'none'           → none; HR chose "No document deadline"
     *   $choice is a process id      → that process, if it is this applicant's
     *                                  and still running
     *   no choice, one active        → that one
     *   no choice, several active    → refused: HR must choose
     *
     * Must run inside the applicant's lock.
     */
    public static function resolveChoice(int $appId, ?string $choice): ?ApplicantDocumentProcess
    {
        $active = ApplicantDocumentProcess::where('app_id', $appId)->active()->get()->keyBy('id');

        if ($choice === 'none') {
            return null;
        }

        if ($choice !== null && $choice !== '') {
            $process = ctype_digit($choice) ? $active->get((int) $choice) : null;

            if (!$process) {
                throw ValidationException::withMessages([
                    'process_id' => 'That document deadline is not running for this applicant.',
                ]);
            }

            return $process;
        }

        if ($active->isEmpty()) {
            return null;
        }

        if ($active->count() === 1) {
            return $active->first();
        }

        throw ValidationException::withMessages([
            'process_id' => 'This applicant has more than one application with a document deadline. Choose which one this request is for.',
        ]);
    }

    /* =====================================================================
     * Reacting to Milestone 2 review decisions (inside the review's lock)
     * ===================================================================== */

    /**
     * After requests have been accepted: any process they belonged to that now
     * has nothing outstanding is complete, and its application moves on.
     *
     * @param  array<int>  $processIds
     */
    public static function settle(array $processIds): void
    {
        foreach (array_unique(array_filter($processIds)) as $processId) {
            $process = ApplicantDocumentProcess::whereKey($processId)->active()->first();

            if (!$process) {
                continue;
            }

            // A process that was never asked for anything has nothing to complete.
            $everAsked = ApplicantDocumentRequest::where('process_id', $process->id)->exists();

            if (!$everAsked || self::hasUnresolved($process)) {
                continue;
            }

            $process->update([
                'status' => ApplicantDocumentProcess::COMPLETE,
                'outcome_at' => now(),
            ]);

            $application = $process->application_id ? ApplicantApplication::find($process->application_id) : null;

            if ($application && !$application->is_closed) {
                $application->update(['status' => ApplicantApplication::DOCUMENTS_COMPLETE]);
            }
        }
    }

    /* =====================================================================
     * The deadline
     * ===================================================================== */

    /**
     * The deadline passed. Called by the nightly job for one process at a
     * time; re-reads under the lock, so a process that ended or was extended in
     * the meantime is left alone. Only its own application is closed.
     */
    public static function expire(ApplicantDocumentProcess $process): bool
    {
        return (bool) ApplicationDecision::inLock($process->app_id, function () use ($process) {
            $current = ApplicantDocumentProcess::whereKey($process->id)->first();

            if (!$current || !$current->is_active || $current->deadline_at->isFuture() || !self::hasUnresolved($current)) {
                return false;
            }

            $note = 'Document deadline passed with requests still outstanding.';
            $application = $current->application_id ? ApplicantApplication::find($current->application_id) : null;

            if ($application && !$application->is_closed && $application->closed_at === null) {
                // Closes the application and ends this, its active process.
                ApplicationDecision::close(
                    $application,
                    ApplicantApplication::NON_RESPONSIVE,
                    ApplicantDocumentProcess::NON_RESPONSIVE,
                    null,
                    $note
                );
            } else {
                ApplicationDecision::endProcess($current, ApplicantDocumentProcess::NON_RESPONSIVE, null, $note);
            }

            return true;
        });
    }

    /* =====================================================================
     * Helpers
     * ===================================================================== */

    private static function alreadyRunning(): ValidationException
    {
        return ValidationException::withMessages([
            'application_id' => 'A document process is already running for this application.',
        ]);
    }

    private static function assertDeadlineDays(int $days): void
    {
        $max = (int) config('applicant_documents.completion.deadline_days_max', 60);

        if ($days < 1 || $days > $max) {
            throw ValidationException::withMessages([
                'deadline_days' => "Give the applicant between 1 and $max days.",
            ]);
        }
    }
}
