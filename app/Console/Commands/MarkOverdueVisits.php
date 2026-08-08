<?php

namespace App\Console\Commands;

use App\Models\ScheduledVisit;
use Illuminate\Console\Command;

/**
 * Cierra la noche: lo que se programo y nadie ejecuto queda como no_realizada.
 *
 * Sin esto el cronograma nunca refleja el incumplimiento — las visitas viejas se
 * quedarian en "programada" para siempre y el panel de cumplimiento mentiria.
 */
class MarkOverdueVisits extends Command
{
    protected $signature = 'visits:mark-overdue';

    protected $description = 'Marca como no realizadas las visitas programadas que ya pasaron';

    public function handle(): int
    {
        // en_curso queda fuera a proposito: alguien estuvo en sitio y solo falto
        // cerrar los reportes. Eso es un pendiente de firma, no un incumplimiento.
        $marked = ScheduledVisit::query()
            ->whereIn('status', ['programada', 'reprogramacion_solicitada'])
            ->where('scheduled_end', '<', now())
            ->update(['status' => 'no_realizada']);

        $this->info("Visitas marcadas como no realizadas: {$marked}");

        return self::SUCCESS;
    }
}
