<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();

        // Keeps auto-composed public job ads in step with HireFlow jobspec
        // edits. Hand-edited ads are skipped by the command itself.
        $schedule->command('recruitment:sync-job-ads')
            ->dailyAt('05:30')
            ->withoutOverlapping();

        // Closes document-completion runs whose deadline passed with documents
        // still outstanding (HireFlow 2.5 · M3). Attempt exhaustion is not
        // handled here — that is decided the moment HR records the rejection.
        $schedule->command('recruitment:expire-document-processes')
            ->dailyAt('01:15')
            ->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
