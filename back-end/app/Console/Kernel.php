<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule)
    {
        // run daily at configured time (env SALES_DAILY_SUMMARY_TIME) or default 00:05
        $time = env('SALES_DAILY_SUMMARY_TIME', '00:05');
        $schedule->command('sales:send-daily-summaries')->dailyAt($time);
    }

    protected function commands()
    {
        // load commands automatically
        $this->load(__DIR__ . '/Commands');
    }
}
