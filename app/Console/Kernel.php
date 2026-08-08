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
        // que se respeta la hora que configuro cada usuario.
        //
        // SIN withoutOverlapping a proposito: su mutex vive en la cache, y en el
        // servidor la cache de ficheros la escriben dos usuarios distintos
        // (www-data desde la web, ubuntu desde el cron), asi que reventaba con un
        // TypeError que abortaba el schedule:run ENTERO — tambien mark-overdue.
        // Contra los envios dobles, el comando reclama cada fila con un UPDATE
        // condicional, que ademas aguanta varios servidores.
        $schedule->command('visits:send-reminders')->everyFiveMinutes();
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
