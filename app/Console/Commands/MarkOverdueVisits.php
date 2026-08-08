<?php

namespace App\Console\Commands;

use App\Models\RescheduleRequest;
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
        // Los ids se capturan antes del update para poder cerrar despues las
        // solicitudes de esas mismas visitas; si no, quedarian pendientes en la
        // bandeja apuntando a una visita que ya vencio.
        $ids = ScheduledVisit::query()
            ->whereIn('status', ['programada', 'reprogramacion_solicitada'])
            ->where('scheduled_end', '<', now())
            ->pluck('id');

        if ($ids->isEmpty()) {
            $this->info('Visitas marcadas como no realizadas: 0');

            return self::SUCCESS;
        }

        $marked = ScheduledVisit::query()->whereIn('id', $ids)->update(['status' => 'no_realizada']);

        $closed = RescheduleRequest::query()
            ->whereIn('scheduled_visit_id', $ids)
            ->pending()
            ->update([
                'status' => RescheduleRequest::RECHAZADA,
                'resolved_at' => now(),
                'resolution_notes' => 'La visita vencio sin que se resolviera la solicitud.',
            ]);

        $this->info("Visitas marcadas como no realizadas: {$marked}");
        $this->info("Solicitudes de reprogramacion cerradas: {$closed}");

        return self::SUCCESS;
    }
}
