<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Jobs\UpdateRemainingDays;
use App\Console\Commands\RebuildAiTables;

class Kernel extends ConsoleKernel
{
protected $commands = [
    \App\Console\Commands\RebuildAiTables::class,
 \App\Console\Commands\BuildLstmFeatures::class,
];

    protected function schedule(Schedule $schedule)
    {
        // اجرای روزانه Job برای به‌روزرسانی روزهای باقیمانده
        $schedule->job(new UpdateRemainingDays())->daily();
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }


}