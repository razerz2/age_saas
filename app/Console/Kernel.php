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
        $schedule->command('subscriptions:process')->dailyAt('01:00');
        $schedule->command('invoices:check-overdue')->dailyAt('02:00');
        $schedule->command('recurring-appointments:process')->dailyAt('03:00');
        // Renova eventos recorrentes no Google Calendar mensalmente (para recorrências sem data fim)
        $schedule->command('google-calendar:renew-recurring-events')->monthlyOn(1, '04:00');

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
