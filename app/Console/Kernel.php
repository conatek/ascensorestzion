<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('demo:reset --force')->daily()->at('00:05');

        // Antes del cambio de dia: lo programado que nadie ejecuto pasa a
        // no_realizada. Depende del cron `* * * * * php artisan schedule:run`.
        $schedule->command('visits:mark-overdue')->dailyAt('23:30');

        // Recordatorios de visita. Cada cinco minutos, que es la precision con la
        // que se respeta la hora que configuro cada usuario. withoutOverlapping
        // porque una pasada lenta no puede solaparse con la siguiente y mandar
        // dos veces el mismo aviso.
        $schedule->command('visits:send-reminders')->everyFiveMinutes()->withoutOverlapping();
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
