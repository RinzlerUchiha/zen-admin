<?php

namespace App\Services\Recruitment;

use App\Models\Applicant\ApplicantApplication;
use App\Models\Applicant\ApplicantDocumentProcess;
use App\Models\Applicant\ApplicantDocumentRequest;
use App\Models\Applicant\ApplicantPersonal;
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
