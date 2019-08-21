<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Cache;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [];

    /**
     * Define the application's command schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('lyaudio:update')
            ->dailyAt('0:05')
            ->after(function () {
                Cache::tags(['lyaudio'])->flush();
            });
        $schedule->command('lymw:get')
            ->dailyAt('0:55');
        // $schedule->command('lylts:update')->weekly();
        $schedule->command('lyccc:get')
            ->weekly(); //每周更新每周辅导教室
        $schedule->command('subscribe:notify')
            ->hourly();
        //诗篇导读 每日更新 //todo delete on 2010!!
        $schedule->command('ly:psalm')
            ->dailyAt('0:10');

        $schedule->command('gamp-clean')
            ->monthly();
        // ->everyMinute();
        // $schedule->command('horizon:snapshot')->daily();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
