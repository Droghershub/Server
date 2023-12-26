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
        $schedule->call(function () {
            Verification::where('status', 'ACTIVE')
                ->where('created_at', '<=', now()->subMinutes(2))
                ->update(['status' => 'INACTIVE']);
                
            Verification::where('status', 'INACTIVE')
                ->where('created_at', '<=', now()->subMinutes(2))
                ->delete();
        })->everyMinute();
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
