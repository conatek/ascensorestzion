<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Equipment;
use App\Models\ScheduledVisit;
use App\Models\ServiceReport;
use App\Models\Site;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Cronograma de demostracion para el portal del cliente.
 *
 * DemoSeeder deja 92 reportes de servicio pero ni una sola visita programada, asi que
 * /portal/cronograma sale con las dos listas vacias ("No hay visitas programadas por
 * ahora" / "Todavia no hay visitas realizadas") y el panel se queda sin la tarjeta de
 * proxima visita. Es la unica de las cuatro vistas del portal que no se puede ensenar.
 *
 * Este seeder la llena por los dos lados:
 *
 *  - HISTORIAL: una visita `completada` por cada reporte de los ultimos meses, con su
 *    mismo equipo, tecnico y fecha. Ademas les pone `visit_uuid` a los reportes, que
 *    DemoSeeder deja en NULL, para que el historial pueda enlazar el PDF firmado sin
 *    una segunda consulta (es lo que carga PortalController::schedule con
 *    `serviceReports`).
 *  - PROXIMAS: la ronda preventiva del mes que viene mas una revision especial, en
 *    dias habiles y sin solapar la agenda de un mismo tecnico.
 *
 * Se ejecuta DESPUES de DemoSeeder y es idempotente: borra las visitas del cliente
 * demo antes de crearlas.
 *
 *     php artisan db:seed --class=DemoSeeder
 *     php artisan db:seed --class=DemoCronogramaSeeder
 */
class DemoCronogramaSeeder extends Seeder
{
    private const DEMO_NIT = '900.123.456-7';

    /** Meses de reportes que se convierten en historial de visitas. */
    private const MESES_HISTORIAL = 6;

    public function run(): void
    {
        $client = Client::where('nit', self::DEMO_NIT)->first();
        if (! $client) {
            $this->command?->error('No existe el cliente demo. Corre primero DemoSeeder.');

            return;
        }

        $siteIds = Site::where('client_id', $client->id)->pluck('id');
        $equipos = Equipment::whereIn('site_id', $siteIds)->orderBy('id')->get();
        if ($equipos->isEmpty()) {
            $this->command?->error('El cliente demo no tiene equipos.');

            return;
        }

        $creador = User::whereHas('roles', fn ($q) => $q->whereIn('name', ['master', 'coordinator']))->first()
            ?? User::first();

        $this->limpiar($equipos->pluck('id'));

        $historial = $this->sembrarHistorial($client, $equipos, $creador);
        $proximas = $this->sembrarProximas($client, $equipos, $creador);

        $this->command?->info("Cronograma demo: {$historial} visitas en historial, {$proximas} programadas.");
    }

    private function limpiar($equipmentIds): void
    {
        $visitas = DB::table('scheduled_visits')->whereIn('equipment_id', $equipmentIds)->pluck('id');
        if ($visitas->isNotEmpty()) {
            DB::table('visit_reminders')->whereIn('scheduled_visit_id', $visitas)->delete();
            DB::table('reschedule_requests')->whereIn('scheduled_visit_id', $visitas)->delete();
            DB::table('scheduled_visits')->whereIn('id', $visitas)->delete();
        }
    }

    /**
     * Una visita completada por cada reporte reciente, con su mismo visit_uuid.
     *
     * Los reportes del mismo equipo y dia comparten visita: es exactamente lo que
     * significa visit_uuid en el sistema (el agrupador de la firma diferida).
     */
    private function sembrarHistorial(Client $client, $equipos, User $creador): int
    {
        $desde = now()->subMonths(self::MESES_HISTORIAL)->startOfMonth();

        $reportes = ServiceReport::where('client_id', $client->id)
            ->where('service_date', '>=', $desde)
            ->where('service_date', '<=', now())
            ->orderBy('service_date')
            ->get();

        $porVisita = $reportes->groupBy(fn ($r) => $r->equipment_id.'|'.substr((string) $r->service_date, 0, 10));

        $creadas = 0;
        foreach ($porVisita as $grupo) {
            $primero = $grupo->first();
            $equipo = $equipos->firstWhere('id', $primero->equipment_id);
            if (! $equipo) {
                continue;
            }

            $uuid = (string) Str::uuid();
            $inicio = \Carbon\Carbon::parse($primero->service_date)->setTime(8 + ($creadas % 3) * 2, 0);

            ScheduledVisit::create([
                'uuid' => (string) Str::uuid(),
                'equipment_id' => $equipo->id,
                'site_id' => $equipo->site_id,
                'client_id' => $client->id,
                'technician_id' => $primero->technician_id,
                'scheduled_start' => $inicio,
                'scheduled_end' => $inicio->copy()->addMinutes(90),
                'visit_type' => $this->tipoDesdeReporte($primero->report_type),
                'status' => 'completada',
                'visit_uuid' => $uuid,
                'notes' => null,
                'created_by' => $creador->id,
            ]);

            // El reporte queda enlazado a su visita: es lo que deja ver el PDF firmado
            // desde el historial del cronograma.
            ServiceReport::whereIn('id', $grupo->pluck('id'))->update(['visit_uuid' => $uuid]);
            $creadas++;
        }

        return $creadas;
    }

    /**
     * La ronda que viene.
     *
     * Se colocan en dias habiles y escalonadas: la primera a pocos dias para que el
     * panel muestre la tarjeta de proxima visita con una cuenta atras corta, y la
     * ronda completa del mes siguiente detras.
     */
    private function sembrarProximas(Client $client, $equipos, User $creador): int
    {
        $tecnicos = User::whereHas('roles', fn ($q) => $q->where('name', 'technician'))
            ->orderBy('id')->pluck('id')->all();
        if (empty($tecnicos)) {
            $tecnicos = [$creador->id];
        }

        // [dias desde hoy, indice de equipo, hora, tipo, nota]
        $agenda = [
            [6, 0, 8, 'preventivo', 'Mantenimiento preventivo mensual. Incluye revision de puertas y nivelacion.'],
            [6, 1, 11, 'preventivo', 'Mantenimiento preventivo mensual.'],
            [7, 2, 8, 'preventivo', 'Mantenimiento preventivo mensual de escalera. Revision de peines y pasamanos.'],
            [19, 0, 9, 'especial', 'Revision de cables de traccion y amarres segun programa anual.'],
            [34, 0, 8, 'preventivo', 'Mantenimiento preventivo mensual.'],
            [34, 1, 11, 'preventivo', 'Mantenimiento preventivo mensual.'],
            [35, 2, 8, 'preventivo', 'Mantenimiento preventivo mensual de escalera.'],
        ];

        $creadas = 0;
        foreach ($agenda as $i => [$dias, $idxEquipo, $hora, $tipo, $nota]) {
            $equipo = $equipos->get($idxEquipo % $equipos->count());
            if (! $equipo) {
                continue;
            }

            $inicio = now()->addDays($dias)->setTime($hora, 0)->startOfMinute();
            // Ni sabados ni domingos: una visita programada en fin de semana delata
            // que las fechas son sinteticas.
            while (in_array($inicio->dayOfWeek, [0, 6], true)) {
                $inicio->addDay();
            }

            ScheduledVisit::create([
                'uuid' => (string) Str::uuid(),
                'equipment_id' => $equipo->id,
                'site_id' => $equipo->site_id,
                'client_id' => $client->id,
                'technician_id' => $tecnicos[$i % count($tecnicos)],
                'scheduled_start' => $inicio,
                'scheduled_end' => $inicio->copy()->addMinutes($tipo === 'especial' ? 180 : 90),
                'visit_type' => $tipo,
                'status' => 'programada',
                'notes' => $nota,
                'created_by' => $creador->id,
            ]);
            $creadas++;
        }

        return $creadas;
    }

    private function tipoDesdeReporte(string $tipo): string
    {
        return match (strtoupper($tipo)) {
            'RSTC' => 'correctivo',
            'RSTE' => 'especial',
            default => 'preventivo',
        };
    }
}
