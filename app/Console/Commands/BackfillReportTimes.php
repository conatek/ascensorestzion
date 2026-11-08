<?php

namespace App\Console\Commands;

use App\Models\ServiceReport;
use App\Models\TechnicianCheckin;
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * Rellena la hora de entrada y de salida de los reportes hechos desde la app del
 * tecnico antes del arreglo del 2026-11-08: la app las mandaba con otro nombre
 * (entry_time / exit_time) y el servidor las descartaba.
 *
 *  - Entrada: el check-in del tecnico en ese equipo ese dia (el enlazado al
 *    reporte o, si no, el ultimo antes de crear el reporte).
 *  - Salida: la firma del cliente o, si no hay, la del tecnico.
 *  - Correctivos: la hora de la llamada del check-in, si la tiene.
 *
 * Ademas corrige los check-ins hechos sin conexion antes de ese arreglo: el
 * telefono mandaba la hora en UTC y se guardaba como si fuera local (5 h de mas).
 * Se reconocen porque vienen de la cola (client_uuid) y no tienen access_status,
 * que todo check-in posterior al arreglo si tiene.
 *
 * Solo toca lo que esta vacio. Sin --apply solo muestra lo que haria.
 */
class BackfillReportTimes extends Command
{
    protected $signature = 'reports:backfill-times {--apply : Guardar los cambios (sin esto solo se listan)}';

    protected $description = 'Rellena la hora de entrada/salida vacía de los reportes a partir del check-in y las firmas';

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');

        $this->fixOfflineCheckins($apply);

        $rows = [];

        ServiceReport::query()
            ->with('rstcDetails')
            ->where(fn ($q) => $q->whereNull('time_in')->orWhereNull('time_out'))
            ->orderBy('id')
            ->chunkById(200, function ($reports) use (&$rows, $apply) {
                foreach ($reports as $report) {
                    $changes = [];
                    $checkin = $this->checkinFor($report);

                    if ($report->time_in === null && $checkin) {
                        $changes['time_in'] = $this->realTime($checkin)->format('H:i');
                    }

                    if ($report->time_out === null) {
                        $signed = $report->customer_signed_at ?? $report->technician_signed_at;
                        if ($signed) {
                            $changes['time_out'] = $signed->format('H:i');
                        }
                    }

                    $callTime = null;
                    if ($report->report_type === 'RSTC' && $checkin?->call_received_at
                        && ! $report->rstcDetails?->call_time) {
                        $callTime = $checkin->call_received_at;
                    }

                    if ($changes === [] && ! $callTime) {
                        continue;
                    }

                    $rows[] = [
                        $report->report_number,
                        $changes['time_in'] ?? '—',
                        $changes['time_out'] ?? '—',
                        $callTime?->format('d/m/Y H:i') ?? '—',
                    ];

                    if (! $apply) {
                        continue;
                    }

                    if ($changes !== []) {
                        $report->timestamps = false;
                        $report->update($changes);
                    }
                    if ($callTime) {
                        $report->rstcDetails
                            ? $report->rstcDetails->update(['call_time' => $callTime])
                            : $report->rstcDetails()->create(['call_time' => $callTime, 'entry_time' => $checkin->checked_in_at]);
                    }
                    if ($checkin && ! $checkin->service_report_id) {
                        $checkin->update(['service_report_id' => $report->id]);
                    }
                }
            });

        if ($rows === []) {
            $this->info('No hay reportes que rellenar.');

            return self::SUCCESS;
        }

        $this->table(['Reporte', 'Entrada', 'Salida', 'Llamada'], $rows);
        $this->info(count($rows).' reportes '.($apply ? 'actualizados.' : 'por actualizar. Ejecuta con --apply para guardar.'));

        return self::SUCCESS;
    }

    private function fixOfflineCheckins(bool $apply): void
    {
        $tz = config('app.timezone');
        $rows = [];

        TechnicianCheckin::query()
            ->whereNotNull('client_uuid')
            ->whereNull('access_status')
            ->orderBy('id')
            ->each(function (TechnicianCheckin $checkin) use ($tz, $apply, &$rows) {
                $raw = $checkin->getRawOriginal('checked_in_at');
                $fixed = Carbon::parse($raw, 'UTC')->setTimezone($tz);
                $rows[] = [$checkin->id, $raw, $fixed->format('Y-m-d H:i:s')];

                if ($apply) {
                    // Sin access_status no se volveria a corregir; 'a_tiempo' no
                    // aplica a registros anteriores al bloqueo, se marca aparte.
                    $checkin->timestamps = false;
                    $checkin->update(['checked_in_at' => $fixed, 'access_status' => 'previo_bloqueo']);
                }
            });

        if ($rows === []) {
            return;
        }

        $this->table(['Check-in sin conexión', 'Guardado', 'Hora real'], $rows);
        $this->info(count($rows).' check-ins sin conexión '.($apply ? 'corregidos.' : 'por corregir.'));
    }

    /** Hora real del check-in: corrige en memoria los sin conexion aun no corregidos (vista previa). */
    private function realTime(TechnicianCheckin $checkin): Carbon
    {
        if ($checkin->client_uuid && $checkin->access_status === null) {
            return Carbon::parse($checkin->getRawOriginal('checked_in_at'), 'UTC')->setTimezone(config('app.timezone'));
        }

        return $checkin->checked_in_at;
    }

    private function checkinFor(ServiceReport $report): ?TechnicianCheckin
    {
        $linked = TechnicianCheckin::where('service_report_id', $report->id)->first();
        if ($linked) {
            return $linked;
        }

        return TechnicianCheckin::query()
            ->whereNull('service_report_id')
            ->where('technician_id', $report->technician_id)
            ->where('equipment_id', $report->equipment_id)
            ->whereDate('checked_in_at', $report->service_date->toDateString())
            ->where('checked_in_at', '<=', $report->created_at)
            ->orderByDesc('checked_in_at')
            ->first();
    }
}
