<?php

namespace App\Console\Commands;

use App\Models\Applicant\ApplicantDocumentProcess;
use App\Services\Recruitment\DocumentCompletion;
use Illuminate\Console\Command;

/**
 * Closes document-completion runs whose deadline has passed with documents
 * still outstanding, as Non-Responsive.
 *
 * The deadline is the only thing this command acts on. Running out of attempts
 * is decided the moment HR records the rejection that spends the last one —
 * nobody waits until the next night to learn that.
 *
 * A run that has already ended is never touched: the outcome it reached first
 * is the one it keeps. Each candidate is re-read under the applicant lock
 * before anything is written, so a run finished between the sweep's query and
 * its write is left alone.
 *
 * WRITES BY DEFAULT. Use --dry-run to see what would close.
 */
class ExpireDocumentProcesses extends Command
{
    protected $signature = 'recruitment:expire-document-processes
                            {--dry-run : Report what would close without saving}';

    protected $description = 'Close overdue applicant document-completion processes as Non-Responsive';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $overdue = ApplicantDocumentProcess::overdue()->orderBy('id')->get();

        if ($overdue->isEmpty()) {
            $this->info('No overdue document completion processes.');

            return self::SUCCESS;
        }

        $closed = 0;
        $skipped = 0;

        foreach ($overdue as $process) {
            // An overdue run with nothing outstanding is not a failure to
            // respond. It is left active for HR to finish.
            if (!DocumentCompletion::hasUnresolved($process)) {
                $skipped++;
                $this->line(sprintf(
                    '  skip  applicant %d · process %d · overdue but nothing outstanding',
                    $process->app_id,
                    $process->id
                ));

                continue;
            }

            if ($dryRun) {
                $closed++;
                $this->line(sprintf(
                    '  would close  applicant %d · process %d · deadline %s',
                    $process->app_id,
                    $process->id,
                    $process->deadline_at->format('Y-m-d')
                ));

                continue;
            }

            if (DocumentCompletion::expire($process)) {
                $closed++;
                $this->line(sprintf(
                    '  Non-Responsive  applicant %d · process %d · deadline %s',
                    $process->app_id,
                    $process->id,
                    $process->deadline_at->format('Y-m-d')
                ));
            } else {
                // Finished or extended between the query and the write.
                $skipped++;
            }
        }

        $this->info(sprintf(
            '%s %d process%s, skipped %d.',
            $dryRun ? 'Would close' : 'Closed',
            $closed,
            $closed === 1 ? '' : 'es',
            $skipped
        ));

        return self::SUCCESS;
    }
}
