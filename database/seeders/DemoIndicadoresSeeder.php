<?php

namespace Database\Seeders;

use App\Models\Catalog;
use App\Models\Equipment;
use App\Models\ReportSequence;
use App\Models\RescheduleRequest;
use App\Models\ScheduledVisit;
use App\Models\ServiceReport;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;

/**
 * Completa la informacion de ejemplo que necesitan los manuales de coordinacion,
 * gerencia y portal del cliente.
 *
 * Los datos que habia servian para el manual del tecnico (una visita, un informe,
 * una firma) pero dejaban vacias las pantallas que ven los demas roles:
 *
 *   - el tablero de gerencia mostraba «Equipos con mas Fallas» con UNA barra,
 *     porque solo habia un RSTC en los ultimos 90 dias;
 *   - el cumplimiento de mantenimiento salia al 12,5 %, con dos RSTP en el mes;
 *   - el cronograma de la semana en curso estaba practicamente vacio y todas las
 *     visitas eran preventivas, asi que la leyenda de colores no ilustraba nada;
 *   - trece visitas de julio se habian quedado en «no realizada» y la operacion
 *     parecia abandonada.
 *
 * Es idempotente: todo lo que crea queda marcado con MARCA en client_uuid y se
 * borra al principio de cada pasada. La marca va ahi y no en customer_order_ref
 * porque ese campo SI se ve, como «Orden de compra», en el detalle del informe y
 * en el PDF que recibe el cliente.
 *
 *   php artisan db:seed --class=DemoIndicadoresSeeder
 */
class DemoIndicadoresSeeder extends Seeder
{
    /**
     * Marca de los informes creados aqui, para poder rehacerlos sin duplicar.
     * Va en client_uuid, que es el identificador que usa la app offline para no
     * duplicar envios: tiene forma de UUID y no se enseña en ninguna pantalla.
     */
    private const MARCA = 'de300000-1d00-4000-8000-';

    private int $secuencia = 0;

    private const UBICACIONES = ['sistema_puertas', 'control_maniobras', 'maquinaria', 'recorrido'];

    private const CAUSAS = ['energia_externa', 'inundacion_humedad', 'tercero', 'tecnica_equipo'];

    private const SOLUCIONES = ['control_maniobras', 'perifericos', 'puertas_piso', 'operador_puertas_cabina'];

    private CarbonImmutable $hoy;

    public function run(): void
    {
        $this->hoy = CarbonImmutable::parse('2026-08-08');

        $this->limpiar();
        $this->sembrarPreventivosDelMes();
        $this->sembrarCorrectivosRecientes();
        $this->sembrarEspeciales();
        $this->sembrarCorrectivosAbiertos();
        $this->ordenarCronograma();
        $this->sembrarSolicitudDeReprogramacion();

        $this->command->info('  Datos de operacion completados.');
    }

    /**
     * Borra lo de la pasada anterior. Solo toca lo que lleva la marca: nunca un
     * where por estado, que es como se perdieron nueve borradores legitimos.
     */
    private function limpiar(): void
    {
        $ids = ServiceReport::where('client_uuid', 'like', self::MARCA.'%')->pluck('id');

        if ($ids->isEmpty()) {
            return;
        }

        ServiceReport::whereIn('id', $ids)->forceDelete();
        $this->command->info("  {$ids->count()} informes de la pasada anterior eliminados.");
    }

    /**
     * Preventivos del mes en curso. El indicador de cumplimiento es
     * «RSTP firmados este mes / equipos activos con contrato», y con dos informes
     * sobre dieciseis equipos daba 12,5 %.
     */
    private function sembrarPreventivosDelMes(): void
    {
        $condiciones = Catalog::where('scope', 'RSTP')->where('category', 'initial_condition')->pluck('key');
        $actividades = Catalog::where('scope', 'RSTP')->where('category', 'rstp_activity')->get();

        // Equipo, dia de agosto, tecnico y hora de entrada. Repartido entre los
        // dos tecnicos y los tres clientes reales, como una semana de verdad.
        $plan = [
            [1, 3, 3, '08:10', '09:40'], [2, 3, 3, '10:00', '11:20'], [6, 3, 4, '08:30', '10:05'],
            [7, 3, 4, '10:30', '12:00'], [4, 4, 3, '08:00', '09:30'], [10, 4, 4, '08:20', '10:00'],
            [11, 4, 4, '10:30', '12:10'], [13, 5, 3, '08:15', '09:50'], [14, 5, 3, '10:15', '11:45'],
            [12, 6, 4, '10:00', '11:30'], [15, 7, 3, '08:30', '10:00'],
        ];
        // Ojo con cuantos preventivos lleva cada cliente: el panel divide los
        // firmados del mes entre los equipos con contrato, y un tercero en Torre
        // Norte dejaba la barra de cumplimiento en un 150 % que no se sostiene.

        foreach ($plan as [$equipoId, $dia, $tecnicoId, $entrada, $salida]) {
            $equipo = Equipment::with('site')->find($equipoId);

            if (! $equipo) {
                continue;
            }

            $fecha = $this->hoy->setDay($dia);

            $informe = $this->crearInforme('RSTP', $equipo, $tecnicoId, $fecha, $entrada, $salida, [
                'equipment_functional' => true,
                'generates_quotation' => $equipoId % 5 === 0,
                'requires_parts_change' => false,
                'conclusion_notes' => 'Se ejecuta la rutina preventiva completa: lubricación de guías, '
                    .'ajuste del operador de puertas, verificación de frenos y limpieza del cuarto de '
                    .'máquinas. El equipo queda operando con normalidad.',
                'status' => 'cerrado',
            ]);

            foreach ($condiciones as $clave) {
                $informe->initialConditions()->create(['condition_key' => $clave, 'value' => 'si']);
            }

            foreach ($actividades as $actividad) {
                $informe->rstpActivities()->create([
                    'group_key' => $actividad->group_key,
                    'activity_key' => $actividad->key,
                    'is_ok' => true,
                ]);
            }

            $informe->rstpMonth()->create(['year' => $fecha->year, 'month' => $fecha->month]);
        }

        $this->command->info('  '.count($plan).' preventivos del mes.');
    }

    /**
     * Correctivos de los ultimos noventa dias, que es la ventana del grafico de
     * equipos con mas fallas. Se concentran a proposito en unos pocos equipos:
     * un grafico donde todos empatan a uno no dice nada.
     */
    private function sembrarCorrectivosRecientes(): void
    {
        $condiciones = Catalog::where('scope', 'RSTC')->where('category', 'initial_condition')->pluck('key');

        // Equipo, dias atras, tecnico, minutos de respuesta, codigos de falla.
        $plan = [
            [3, 74, 3, 45, ['C4', 'C7']],
            [3, 41, 4, 38, ['C4']],
            [3, 12, 3, 52, ['C4', 'C11']],
            [7, 66, 4, 95, ['C2', 'C9']],
            [7, 23, 3, 40, ['C2']],
            [11, 58, 3, 70, ['C13']],
            [11, 9, 4, 33, ['C13', 'C6']],
            [13, 35, 4, 120, ['C8', 'C15', 'C3']],
            [5, 18, 3, 55, ['C1']],
            [9, 5, 3, 28, ['C5']],
        ];

        foreach ($plan as $i => [$equipoId, $atras, $tecnicoId, $respuesta, $codigos]) {
            $equipo = Equipment::with('site')->find($equipoId);

            if (! $equipo) {
                continue;
            }

            $fecha = $this->hoy->subDays($atras);
            $llamada = $fecha->setTime(7 + ($i % 6), ($i * 7) % 60);
            $entrada = $llamada->addMinutes($respuesta);
            $salida = $entrada->addMinutes(60 + ($i % 4) * 25);

            $informe = $this->crearInforme('RSTC', $equipo, $tecnicoId, $fecha,
                $entrada->format('H:i'), $salida->format('H:i'), [
                    'equipment_functional' => true,
                    'generates_quotation' => $i % 4 === 0,
                    'requires_parts_change' => $i % 3 === 0,
                    'conclusion_notes' => 'Se atiende el llamado, se localiza la falla y se restablece el '
                        .'servicio. El equipo queda operativo y se deja constancia fotográfica.',
                    'status' => 'cerrado',
                ]);

            foreach ($condiciones as $clave) {
                $informe->initialConditions()->create(['condition_key' => $clave, 'value' => 'si']);
            }

            $informe->rstcDetails()->create([
                'call_time' => $llamada,
                'entry_time' => $entrada,
                'exit_time' => $salida,
                'response_time_hh' => intdiv($respuesta, 60),
                'response_time_mm' => $respuesta % 60,
                'fault_location' => self::UBICACIONES[$i % count(self::UBICACIONES)],
                'fault_cause' => self::CAUSAS[$i % count(self::CAUSAS)],
                'fault_solution_area' => self::SOLUCIONES[$i % count(self::SOLUCIONES)],
                'analysis_notes' => 'Se verifica el circuito de seguridad y se identifica el componente '
                    .'que origina la parada.',
                'solution_notes' => 'Se sustituye el componente y se prueba el equipo en todos los pisos.',
            ]);

            foreach ($codigos as $codigo) {
                $informe->faultCodes()->create([
                    'code' => $codigo,
                    'severity' => ['baja', 'media', 'alta'][$i % 3],
                ]);
            }
        }

        $this->command->info('  '.count($plan).' correctivos recientes.');
    }

    /** Dos especiales, para que el grafico mensual tenga las tres series. */
    private function sembrarEspeciales(): void
    {
        $condiciones = Catalog::where('scope', 'RSTC')->where('category', 'initial_condition')->pluck('key');
        $trabajos = Catalog::where('scope', 'RSTE')->where('category', 'rste_work')->get();

        foreach ([[4, 29, 3], [10, 47, 4]] as [$equipoId, $atras, $tecnicoId]) {
            $equipo = Equipment::with('site')->find($equipoId);

            if (! $equipo) {
                continue;
            }

            $informe = $this->crearInforme('RSTE', $equipo, $tecnicoId, $this->hoy->subDays($atras),
                '08:00', '16:30', [
                    'equipment_functional' => true,
                    'generates_quotation' => true,
                    'requires_parts_change' => true,
                    'conclusion_notes' => 'Se ejecuta la modernización del operador de puertas según la '
                        .'cotización aprobada por el cliente. Se entrega el equipo probado y en servicio.',
                    'status' => 'cerrado',
                ]);

            foreach ($condiciones as $clave) {
                $informe->initialConditions()->create(['condition_key' => $clave, 'value' => 'si']);
            }

            foreach ($trabajos as $trabajo) {
                $informe->rsteWorks()->create([
                    'group_key' => $trabajo->group_key,
                    'work_key' => $trabajo->key,
                    'is_ok' => true,
                ]);
            }
        }

        $this->command->info('  2 informes especiales.');
    }

    /**
     * Dos correctivos todavia en borrador. El indicador «RSTC abiertos / cerrados»
     * cuenta los borradores, y con cero a la izquierda no se entendia que medía.
     */
    private function sembrarCorrectivosAbiertos(): void
    {
        $condiciones = Catalog::where('scope', 'RSTC')->where('category', 'initial_condition')->pluck('key');

        foreach ([[8, 2, 4, 65], [15, 1, 3, 40]] as [$equipoId, $atras, $tecnicoId, $respuesta]) {
            $equipo = Equipment::with('site')->find($equipoId);

            if (! $equipo) {
                continue;
            }

            $fecha = $this->hoy->subDays($atras);
            $llamada = $fecha->setTime(14, 20);
            $entrada = $llamada->addMinutes($respuesta);

            $informe = $this->crearInforme('RSTC', $equipo, $tecnicoId, $fecha,
                $entrada->format('H:i'), null, [
                    'equipment_functional' => false,
                    'generates_quotation' => true,
                    'requires_parts_change' => true,
                    'conclusion_notes' => 'Se deja el equipo fuera de servicio a la espera del repuesto. '
                        .'Pendiente de cerrar cuando llegue la pieza.',
                    'status' => 'borrador',
                ]);

            foreach ($condiciones as $clave) {
                $informe->initialConditions()->create(['condition_key' => $clave, 'value' => 'si']);
            }

            $informe->rstcDetails()->create([
                'call_time' => $llamada,
                'entry_time' => $entrada,
                'response_time_hh' => intdiv($respuesta, 60),
                'response_time_mm' => $respuesta % 60,
                'fault_location' => 'maquinaria',
                'fault_cause' => 'tecnica_equipo',
                'analysis_notes' => 'Se detecta desgaste en el conjunto y se solicita el repuesto.',
            ]);

            $informe->faultCodes()->create(['code' => 'C12', 'severity' => 'alta']);
        }

        $this->command->info('  2 correctivos abiertos.');
    }

    /**
     * Deja el cronograma con cara de operacion real.
     *
     * Julio se cierra (eran trece «no realizada» seguidas, que leidas de golpe
     * parecen un servicio abandonado; se dejan dos, que es lo que ilustra el
     * estado), y a la semana en curso y a la siguiente se les da variedad de tipo
     * para que la leyenda de colores del calendario signifique algo.
     */
    private function ordenarCronograma(): void
    {
        $julio = ScheduledVisit::where('status', 'no_realizada')
            ->whereBetween('scheduled_start', ['2026-07-01', '2026-07-31 23:59:59'])
            ->orderBy('scheduled_start')
            ->get();

        foreach ($julio->slice(0, max(0, $julio->count() - 2)) as $visita) {
            $visita->update(['status' => 'completada']);
        }

        // Tipos: hasta ahora casi todas eran preventivas y el calendario salia
        // entero verde.
        $tipos = [
            27 => 'correctivo',   // lun 10, Torre A
            31 => 'especial',     // mie 12, Torre B
            32 => 'correctivo',   // jue 13
            24 => 'correctivo',   // vie 7, ya pasada
        ];

        foreach ($tipos as $id => $tipo) {
            ScheduledVisit::where('id', $id)->update(['visit_type' => $tipo]);
        }

        // La semana en curso (3 al 7 de agosto) tenia una sola visita. Se rellena
        // con las que ya se ejecutaron: son las que sostienen los preventivos del
        // mes que acabamos de sembrar.
        $nuevas = [
            [1, 3, 3, '08:10', '09:40', 'preventivo', 'completada'],
            [2, 3, 3, '10:00', '11:20', 'preventivo', 'completada'],
            [6, 3, 4, '08:30', '10:05', 'preventivo', 'completada'],
            [7, 3, 4, '10:30', '12:00', 'preventivo', 'completada'],
            [4, 4, 3, '08:00', '09:30', 'preventivo', 'completada'],
            [10, 4, 4, '08:20', '10:00', 'preventivo', 'completada'],
            [11, 4, 4, '10:30', '12:10', 'preventivo', 'completada'],
            [13, 5, 3, '08:15', '09:50', 'preventivo', 'completada'],
            [14, 5, 3, '10:15', '11:45', 'correctivo', 'completada'],
            [9, 6, 4, '08:00', '09:35', 'preventivo', 'completada'],
            [12, 6, 4, '10:00', '11:30', 'especial', 'completada'],
            [15, 7, 3, '08:30', '10:00', 'preventivo', 'completada'],
        ];

        // La semana siguiente es la que se fotografia para explicar el calendario:
        // es la unica con visitas por venir, y estaba a medio llenar. Con estas se
        // completa y quedan los tres colores de la leyenda a la vista.
        $nuevas = array_merge($nuevas, [
            [10, 11, 4, '10:30', '12:00', 'preventivo', 'programada'],
            [12, 11, 3, '14:00', '16:00', 'especial', 'programada'],
            [13, 12, 4, '14:30', '16:00', 'preventivo', 'programada'],
            [14, 13, 4, '08:00', '09:30', 'preventivo', 'programada'],
            [3, 13, 3, '10:30', '11:45', 'correctivo', 'programada'],
            [15, 14, 4, '10:00', '11:30', 'preventivo', 'programada'],
        ]);

        // Visita duplicada de la semilla original: la misma maquina y el mismo
        // tecnico que la de las 08:10, con lo que el calendario pintaba dos
        // eventos encabalgados en la misma columna. Se borra por id.
        ScheduledVisit::where('id', 20)->where('scheduled_start', '2026-08-03 08:52:00')->delete();

        $coordinador = User::where('email', 'coordinador@ascensorestzion.com')->value('id');
        $creadas = 0;

        foreach ($nuevas as [$equipoId, $dia, $tecnicoId, $inicio, $fin, $tipo, $estado]) {
            $equipo = Equipment::with('site')->find($equipoId);

            if (! $equipo) {
                continue;
            }

            $arranque = $this->hoy->setDay($dia)->setTimeFromTimeString($inicio);

            $existe = ScheduledVisit::where('equipment_id', $equipoId)
                ->where('scheduled_start', $arranque->toDateTimeString())
                ->exists();

            if ($existe) {
                continue;
            }

            ScheduledVisit::create([
                'equipment_id' => $equipoId,
                'site_id' => $equipo->site_id,
                'client_id' => $equipo->site->client_id,
                'technician_id' => $tecnicoId,
                'scheduled_start' => $arranque,
                'scheduled_end' => $this->hoy->setDay($dia)->setTimeFromTimeString($fin),
                'visit_type' => $tipo,
                'status' => $estado,
                'created_by' => $coordinador,
            ]);
            $creadas++;
        }

        $this->command->info("  cronograma: {$julio->count()} visitas de julio revisadas, {$creadas} nuevas en la semana.");
    }

    /**
     * Una solicitud de reprogramacion viva.
     *
     * Habia una visita en «reprogramacion solicitada» pero sin la fila de
     * solicitud detras, asi que la bandeja de coordinacion salia con el mensaje
     * «No hay solicitudes pendientes» sobre una visita en ambar: justo lo que el
     * manual tiene que explicar, y no se veia.
     */
    private function sembrarSolicitudDeReprogramacion(): void
    {
        $visita = ScheduledVisit::where('status', 'reprogramacion_solicitada')
            ->orderBy('scheduled_start')
            ->first();

        if (! $visita) {
            return;
        }

        if (RescheduleRequest::where('scheduled_visit_id', $visita->id)->pending()->exists()) {
            return;
        }

        $solicitante = User::where('client_id', $visita->client_id)->first()
            ?? User::where('email', 'admin@ccoviedo.com')->first();

        if (! $solicitante) {
            return;
        }

        $duracion = $visita->scheduled_start->diffInMinutes($visita->scheduled_end);
        $propuesta = CarbonImmutable::parse($visita->scheduled_start)->addDays(2)->setTime(14, 0);

        RescheduleRequest::create([
            'scheduled_visit_id' => $visita->id,
            'requested_by' => $solicitante->id,
            'original_start' => $visita->scheduled_start,
            'proposed_start' => $propuesta,
            'proposed_end' => $propuesta->addMinutes($duracion),
            'reason' => 'Ese miércoles tenemos asamblea de copropietarios en el edificio y el ascensor '
                .'va a estar en uso continuo toda la mañana. El viernes por la tarde nos viene mucho mejor.',
            'status' => RescheduleRequest::PENDIENTE,
        ]);

        $this->command->info('  1 solicitud de reprogramacion pendiente.');
    }

    /**
     * Crea el informe con lo comun a los tres tipos.
     *
     * Si el informe nace cerrado se le ponen tambien las dos firmas y la
     * confirmacion de recepcion. Sin eso, el detalle salia con el estado
     * «Cerrado» arriba y «Pendiente de firma» en los dos recuadros de abajo, que
     * es justo lo contrario de lo que el manual esta explicando.
     */
    private function crearInforme(string $tipo, Equipment $equipo, int $tecnicoId,
        CarbonImmutable $fecha, string $entrada, ?string $salida, array $extra): ServiceReport
    {
        if (($extra['status'] ?? null) === 'cerrado' && $salida) {
            $firma = $fecha->setTimeFromTimeString($salida);
            $firmante = $equipo->site->contact_name_onsite
                ?: ($equipo->site->client->contact_name ?? 'Administración de la copropiedad');

            $extra += [
                'technician_signed_at' => $firma,
                'customer_signed_at' => $firma->addMinutes(4),
                'customer_signer_name' => $firmante,
                'customer_signer_document' => '43.'.(100 + $equipo->id).'.'.(500 + $equipo->id * 7),
                'reception_confirmed_at' => $firma->addDay()->setTime(9, 12),
            ];
        }

        return ServiceReport::create(array_merge([
            'report_number' => ReportSequence::nextNumber($tipo),
            'report_type' => $tipo,
            'equipment_id' => $equipo->id,
            'client_id' => $equipo->site->client_id,
            'site_id' => $equipo->site_id,
            'client_uuid' => self::MARCA.sprintf('%012d', ++$this->secuencia),
            'service_date' => $fecha->toDateString(),
            'time_in' => $entrada,
            'time_out' => $salida,
            'technician_id' => $tecnicoId,
            'created_by' => $tecnicoId,
        ], $extra));
    }
}
