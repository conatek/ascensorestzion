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
        // SIN withoutOverlapping a proposito. Contra los envios dobles el comando
        // reclama cada fila con un UPDATE condicional: es atomico en la base, no
        // depende de nada mas y aguantaria varios servidores. El mutex del
        // scheduler vive en la cache, y anadir esa dependencia no compensa —
        // cuando la cache de ficheros estaba rota, su excepcion abortaba el
        // schedule:run ENTERO, tambien mark-overdue.
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
