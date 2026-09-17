<?php

namespace App\Console\Commands;

use App\Models\Applicant\ApplicantDocumentProcess;
use App\Services\Recruitment\DocumentCompletion;
use Illuminate\Console\Command;

/**
 * Closes document processes whose deadline has passed with requests still
 * outstanding. Each one's OWN application becomes Non-Responsive; the
 * applicant's other applications are not touched.
 *
 * The deadline is the only thing this command acts on. A process that has
 * already ended is never touched, and each candidate is re-read under the
 * applicant lock before anything is written, so a process finished or extended
 * between the query and the write is left alone. An overdue process with
 * nothing outstanding is not a failure to respond and is left for HR.
 *
 * WRITES BY DEFAULT. Use --dry-run to see what would close.
 */
class ExpireDocumentProcesses extends Command
{
    protected $signature = 'recruitment:expire-document-processes
                            {--dry-run : Report what would close without saving}';

    protected $description = 'Close overdue applicant document processes and mark their applications Non-Responsive';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $overdue = ApplicantDocumentProcess::overdue()->orderBy('id')->get();

        if ($overdue->isEmpty()) {
            $this->info('No overdue document processes.');

            return self::SUCCESS;
        }

        $closed = 0;
        $skipped = 0;

        foreach ($overdue as $process) {
            $label = sprintf(
                'process %d · applicant %d · application %s · deadline %s',
                $process->id,
                $process->app_id,
                $process->application_id ?? 'none',
                $process->deadline_at->format('Y-m-d')
            );

            if (!DocumentCompletion::hasUnresolved($process)) {
                $skipped++;
                $this->line("  skip          $label · nothing outstanding");

                continue;
            }

            if ($dryRun) {
                $closed++;
                $this->line("  would close   $label");

                continue;
            }

            if (DocumentCompletion::expire($process)) {
                $closed++;
                $this->line("  Non-Responsive $label");
            } else {
                $skipped++;
                $this->line("  skip          $label · changed before it could be closed");
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
