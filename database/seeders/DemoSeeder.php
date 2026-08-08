<?php

namespace Database\Seeders;

use App\Models\Catalog;
use App\Models\Client;
use App\Models\Equipment;
use App\Models\ServiceReport;
use App\Models\ServiceReportAuditLog;
use App\Models\Site;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    private const DEMO_NIT = '900.123.456-7';

    private const DEMO_EMAIL = 'demo@ascensorestzion.com';

    private int $rstpSeq = 0;

    private int $rstcSeq = 0;

    private int $rsteSeq = 0;

    private array $technicians = [];

    private array $rstpConditionKeys = [];

    private array $rstcConditionKeys = [];

    private array $rstpActivities = [];

    private array $rsteWorks = [];

    public function run(): void
    {
        $this->command?->info('Limpiando datos demo anteriores...');
        $this->cleanup();

        $this->command?->info('Cargando catálogos...');
        $this->loadCatalogs();

        $this->command?->info('Creando cliente y equipos demo...');
        [$client, $site, $equipments] = $this->createClientAndEquipment();

        $this->command?->info('Creando usuario demo...');
        $this->createDemoUser($client);

        $this->command?->info('Generando reportes RSTP (preventivos)...');
        $rstpCount = $this->generateRSTP($equipments, $client, $site);

        $this->command?->info('Generando reportes RSTC (correctivos)...');
        $rstcCount = $this->generateRSTC($equipments, $client, $site);

        $this->command?->info('Generando reportes RSTE (especiales)...');
        $rsteCount = $this->generateRSTE($equipments, $client, $site);

        $total = $rstpCount + $rstcCount + $rsteCount;
        $this->command?->info("Demo completado: {$total} reportes ({$rstpCount} RSTP, {$rstcCount} RSTC, {$rsteCount} RSTE)");
    }

    private function cleanup(): void
    {
        $client = Client::withTrashed()->where('nit', self::DEMO_NIT)->first();
        if (! $client) {
            return;
        }

        // Borrar reportes (cascade borra hijos)
        ServiceReport::where('client_id', $client->id)->forceDelete();

        // Borrar equipos y site
        $sites = Site::where('client_id', $client->id)->get();
        foreach ($sites as $s) {
            Equipment::where('site_id', $s->id)->forceDelete();
        }
        Site::where('client_id', $client->id)->forceDelete();

        // Borrar usuario demo
        User::where('email', self::DEMO_EMAIL)->forceDelete();

        // Borrar cliente
        $client->forceDelete();
    }

    private function loadCatalogs(): void
    {
        $this->technicians = User::whereHas('roles', fn ($q) => $q->where('name', 'technician'))->pluck('id')->toArray();
        if (empty($this->technicians)) {
            $this->technicians = [User::first()->id];
        }

        $this->rstpConditionKeys = Catalog::where('scope', 'RSTP')->where('category', 'initial_condition')->orderBy('sort_order')->pluck('key')->toArray();
        $this->rstcConditionKeys = Catalog::where('scope', 'RSTC')->where('category', 'initial_condition')->orderBy('sort_order')->pluck('key')->toArray();
        $this->rstpActivities = Catalog::where('scope', 'RSTP')->where('category', 'rstp_activity')->orderBy('sort_order')->get()->toArray();
        $this->rsteWorks = Catalog::where('scope', 'RSTE')->where('category', 'rste_work')->orderBy('sort_order')->get()->toArray();
    }

    private function createClientAndEquipment(): array
    {
        $client = Client::create([
            'business_name' => 'Edificio Inteligente Poblado',
            'nit' => self::DEMO_NIT,
            'contact_name' => 'Alejandra Vélez Restrepo',
            'contact_email' => 'admin@edificiointeligente.co',
            'contact_phone' => '+57 604 555 8000',
            'address' => 'Carrera 43A # 16 Sur-55, El Poblado',
            'city' => 'Medellín',
            'department' => 'Antioquia',
            'active' => true,
        ]);

        $site = Site::create([
            'client_id' => $client->id,
            'name' => 'Torre Ejecutiva',
            'address' => 'Carrera 43A # 16 Sur-55, Piso Lobby',
            'city' => 'Medellín',
            'department' => 'Antioquia',
            'contact_name_onsite' => 'Roberto Salazar Mejía',
            'contact_phone_onsite' => '+57 310 555 8001',
            'contact_email_onsite' => 'encargado@edificiointeligente.co',
            'active' => true,
        ]);

        $equipments = [];

        $equipments[] = Equipment::create([
            'site_id' => $site->id, 'internal_code' => 'TZ-DEMO-0001', 'customer_code' => 'EIP-ASC-01',
            'equipment_type' => 'ascensor', 'brand' => 'Otis', 'model' => 'Gen2 Comfort',
            'serial_number' => 'OT-2019-MDE-44521', 'capacity_kg' => 1000, 'capacity_persons' => 13,
            'stops' => 12, 'speed_mps' => 1.75, 'installation_date' => '2019-03-15',
            'contract_type' => 'integral', 'contract_start' => '2024-01-01', 'contract_end' => '2026-12-31',
            'maintenance_frequency_days' => 30, 'status' => 'activo',
            'notes' => 'Ascensor principal del edificio. Uso intensivo diario.',
        ]);

        $equipments[] = Equipment::create([
            'site_id' => $site->id, 'internal_code' => 'TZ-DEMO-0002', 'customer_code' => 'EIP-ASC-02',
            'equipment_type' => 'ascensor', 'brand' => 'Mitsubishi', 'model' => 'NexWay-S',
            'serial_number' => 'ME-2020-MDE-77103', 'capacity_kg' => 1600, 'capacity_persons' => 21,
            'stops' => 12, 'speed_mps' => 1.50, 'installation_date' => '2020-08-10',
            'contract_type' => 'mantenimiento', 'contract_start' => '2024-06-01', 'contract_end' => '2026-05-31',
            'maintenance_frequency_days' => 30, 'status' => 'activo',
            'notes' => 'Ascensor de servicio y mudanzas. Capacidad extendida.',
        ]);

        $equipments[] = Equipment::create([
            'site_id' => $site->id, 'internal_code' => 'TZ-DEMO-0003', 'customer_code' => 'EIP-ESC-01',
            'equipment_type' => 'escalera_electrica', 'brand' => 'Schindler', 'model' => '9300 AE',
            'serial_number' => 'SC-2021-MDE-03387', 'stops' => 2, 'speed_mps' => 0.50,
            'installation_date' => '2021-01-20',
            'contract_type' => 'mantenimiento', 'contract_start' => '2024-01-01', 'contract_end' => '2026-12-31',
            'maintenance_frequency_days' => 30, 'status' => 'activo',
            'notes' => 'Escalera eléctrica lobby principal a mezzanine.',
        ]);

        return [$client, $site, $equipments];
    }

    private function createDemoUser(Client $client): void
    {
        $user = User::create([
            'name' => 'Alejandra Vélez Restrepo',
            'email' => self::DEMO_EMAIL,
            'password' => Hash::make('demo2026'),
            'phone' => '+57 604 555 8000',
            'document_type' => 'CC',
            'document_number' => '43.987.654',
            'active' => true,
            'client_id' => $client->id,
        ]);
        $user->assignRole('admin');
    }

    // ── RSTP ──────────────────────────────────────────────────────────────

    private function generateRSTP(array $equipments, Client $client, Site $site): int
    {
        // Meses sin mantenimiento por equipo (índice 0,1,2)
        $skip = [
            0 => [],                                                    // Equipo 1: todos los meses
            1 => ['2024-10', '2024-11', '2025-03', '2025-09', '2026-02'],   // Equipo 2
            2 => ['2025-06', '2025-12'],                                 // Equipo 3
        ];

        $observations = [
            'Mantenimiento preventivo completado sin novedad significativa.',
            'Equipo en condiciones óptimas de operación.',
            'Se recomienda revisión de guías en próxima visita.',
            'Nivel de aceite verificado y dentro de parámetros.',
            'Limpieza general realizada. Sin observaciones adicionales.',
            'Se detectó desgaste menor en patines, se programa cambio.',
            'Ajuste de velocidad de puertas realizado.',
            'Filtro de cuarto de máquinas requiere cambio próximo mes.',
        ];

        $activityObs = [
            'Verificado sin novedad', 'Ajuste realizado', 'Limpieza completa',
            'Nivel de aceite correcto', 'Torque de bornes verificado',
            'Requiere seguimiento', '', '', '', '',
        ];

        $count = 0;
        $start = Carbon::create(2024, 7, 1);
        $end = Carbon::create(2026, 5, 31);

        for ($date = $start->copy(); $date->lte($end); $date->addMonth()) {
            foreach ($equipments as $eqIdx => $eq) {
                $period = $date->format('Y-m');
                if (in_array($period, $skip[$eqIdx])) {
                    continue;
                }

                $serviceDate = $date->copy()->addDays(8 + $eqIdx * 3); // Días 8, 11, 14 del mes
                $techIdx = ($count % count($this->technicians));
                $techId = $this->technicians[$techIdx];
                $isRecent = $serviceDate->gt(now()->subMonths(2));

                $status = $isRecent
                    ? ['firmado_tecnico', 'firmado_cliente'][($count % 2)]
                    : 'cerrado';

                $report = $this->createBaseReport('RSTP', $eq, $client, $site, $serviceDate, $techId, $status, [
                    'time_in' => sprintf('%02d:%02d', 7 + ($eqIdx), 30),
                    'time_out' => sprintf('%02d:%02d', 10 + ($eqIdx), 15 + ($count % 30)),
                    'equipment_functional' => $count % 20 !== 0,
                    'generates_quotation' => $count % 7 === 0,
                    'requires_parts_change' => $count % 10 === 0,
                    'conclusion_notes' => $observations[$count % count($observations)],
                ]);

                // Condiciones iniciales RSTP
                foreach ($this->rstpConditionKeys as $i => $key) {
                    $report->initialConditions()->create([
                        'condition_key' => $key,
                        'value' => ($i < 6 || $count % 5 !== 0) ? 'si' : 'no',
                        'observation' => ($i === 7 && $count % 4 === 0) ? 'Luminaria piso 5 intermitente' : null,
                    ]);
                }

                // Actividades RSTP
                foreach ($this->rstpActivities as $i => $act) {
                    $report->rstpActivities()->create([
                        'group_key' => $act['group_key'],
                        'activity_key' => $act['key'],
                        'is_ok' => ! ($i === 3 && $count % 8 === 0),
                        'observation' => ($i % 4 === 0) ? $activityObs[$count % count($activityObs)] : null,
                    ]);
                }

                // Mes del mantenimiento
                $report->rstpMonth()->create([
                    'year' => $serviceDate->year,
                    'month' => $serviceDate->month,
                ]);

                $this->createAuditEntries($report, $techId, $status);
                $count++;
            }
        }

        return $count;
    }

    // ── RSTC ──────────────────────────────────────────────────────────────

    private function generateRSTC(array $equipments, Client $client, Site $site): int
    {
        // [equipo_idx, año, mes]
        $schedule = [
            [0, 2024, 8], [0, 2024, 10], [0, 2024, 12],
            [0, 2025, 2], [0, 2025, 4],  [0, 2025, 7], [0, 2025, 9], [0, 2025, 11],
            [0, 2026, 1], [0, 2026, 4],
            [1, 2024, 11],
            [1, 2025, 3], [1, 2025, 8], [1, 2025, 10],
            [1, 2026, 2], [1, 2026, 5],
            [2, 2024, 9],
            [2, 2025, 5], [2, 2025, 12],
            [2, 2026, 3],
        ];

        $faultLocations = ['sistema_puertas', 'sistema_puertas', 'control_maniobras', 'control_maniobras', 'maquinaria', 'maquinaria', 'recorrido', 'recorrido', 'otra', 'sistema_puertas',
            'control_maniobras', 'maquinaria', 'sistema_puertas', 'recorrido', 'control_maniobras', 'sistema_puertas', 'maquinaria', 'otra', 'control_maniobras', 'sistema_puertas'];
        $faultCauses = ['tecnica_equipo', 'energia_externa', 'tecnica_equipo', 'tercero', 'tecnica_equipo', 'inundacion_humedad', 'tecnica_equipo', 'energia_externa', 'tecnica_equipo', 'tecnica_equipo',
            'energia_externa', 'tecnica_equipo', 'tercero', 'tecnica_equipo', 'energia_externa', 'tecnica_equipo', 'tecnica_equipo', 'inundacion_humedad', 'tercero', 'tecnica_equipo'];
        $faultSolutions = ['perifericos', 'control_maniobras', 'control_maniobras', 'puertas_piso', 'operador_puertas_cabina', 'control_maniobras', 'activacion_interruptores', 'perifericos', 'control_maniobras', 'puertas_piso',
            'control_maniobras', 'operador_puertas_cabina', 'puertas_piso', 'perifericos', 'activacion_interruptores', 'control_maniobras', 'operador_puertas_cabina', 'perifericos', 'puertas_piso', 'control_maniobras'];
        $faultCodesPool = ['C3', 'C7', 'C1', 'C3', 'C12', 'C7', 'C1', 'C3', 'C5', 'C7', 'C12', 'C3', 'C1', 'C7', 'C15', 'C3', 'C9', 'C7', 'C1', 'C12'];

        $analysisNotes = [
            'Se detectó desgaste en rodamientos del operador de puertas de cabina.',
            'Tarjeta de control principal presenta código de error E47.',
            'Cortocircuito en sensor de posición del piso 3.',
            'Fallo en contactor principal por sobrecalentamiento.',
            'Desalineación del limitador de velocidad detectada.',
            'Sensor de carga presenta lecturas inconsistentes.',
            'Interruptor de seguridad del techo de cabina activado por vibración.',
            'Fuga de aceite en reductor detectada en inspección visual.',
            'Cable viajero presenta desgaste visible en tramo inferior.',
            'Variador de frecuencia muestra error de sobrecorriente.',
        ];

        $causeNotes = [
            'Desgaste natural por uso intensivo del equipo.',
            'Fluctuación de voltaje en la red eléctrica del edificio.',
            'Trabajo de terceros en el cuarto de máquinas sin autorización.',
            'Filtración de agua por tubería adyacente al pozo.',
            'Componente fuera de vida útil esperada.',
        ];

        $solutionNotes = [
            'Se reemplazan rodamientos y se ajusta tensión de correa.',
            'Se reprograma tarjeta de control y se actualiza firmware.',
            'Se reemplaza sensor defectuoso y se verifica cableado.',
            'Se reemplaza contactor y se instala protección térmica adicional.',
            'Se realinea limitador y se verifica cable de seguridad.',
        ];

        $count = 0;
        foreach ($schedule as $i => [$eqIdx, $year, $month]) {
            $eq = $equipments[$eqIdx];
            $serviceDate = Carbon::create($year, $month, 5 + ($i % 20));
            $techId = $this->technicians[$i % count($this->technicians)];
            $isRecent = $serviceDate->gt(now()->subMonths(1));
            $status = $isRecent ? 'firmado_cliente' : 'cerrado';

            $callHour = 6 + ($i % 14);
            $callTime = $serviceDate->copy()->setTime($callHour, 10 + ($i * 7) % 50);
            $responseMin = 20 + ($i * 13) % 70;
            $entryTime = $callTime->copy()->addMinutes($responseMin);
            $repairMin = 45 + ($i * 17) % 180;
            $exitTime = $entryTime->copy()->addMinutes($repairMin);

            $report = $this->createBaseReport('RSTC', $eq, $client, $site, $serviceDate, $techId, $status, [
                'time_in' => $entryTime->format('H:i'),
                'time_out' => $exitTime->format('H:i'),
                'equipment_functional' => $i % 6 !== 0,
                'generates_quotation' => $i % 3 === 0,
                'requires_parts_change' => $i % 3 === 0,
                'conclusion_notes' => $i % 3 === 0
                    ? 'Reparación temporal. Se genera cotización para repuesto definitivo.'
                    : 'Falla corregida. Equipo restituido a operación normal.',
            ]);

            // Condiciones iniciales (20 ítems RSTC)
            foreach ($this->rstcConditionKeys as $j => $key) {
                $report->initialConditions()->create([
                    'condition_key' => $key,
                    'value' => ($j < 3 || ($j > 5 && $j < 15)) ? 'si' : (($i + $j) % 4 === 0 ? 'no' : 'si'),
                ]);
            }

            // Detalles RSTC
            $report->rstcDetails()->create([
                'call_time' => $callTime,
                'entry_time' => $entryTime,
                'exit_time' => $exitTime,
                'response_time_hh' => intdiv($responseMin, 60),
                'response_time_mm' => $responseMin % 60,
                'fault_location' => $faultLocations[$i],
                'fault_cause' => $faultCauses[$i],
                'fault_solution_area' => $faultSolutions[$i],
                'analysis_notes' => $analysisNotes[$i % count($analysisNotes)],
                'cause_notes' => $causeNotes[$i % count($causeNotes)],
                'solution_notes' => $solutionNotes[$i % count($solutionNotes)],
            ]);

            // Códigos de falla (1-3 por reporte)
            $codeCount = 1 + ($i % 3);
            $usedCodes = [];
            for ($c = 0; $c < $codeCount; $c++) {
                $code = $faultCodesPool[($i + $c) % count($faultCodesPool)];
                if (in_array($code, $usedCodes)) {
                    continue;
                }
                $usedCodes[] = $code;
                $report->faultCodes()->create([
                    'code' => $code,
                    'severity' => ['media', 'alta', 'baja', 'media', 'alta'][$c % 5],
                ]);
            }

            $this->createAuditEntries($report, $techId, $status);
            $count++;
        }

        return $count;
    }

    // ── RSTE ──────────────────────────────────────────────────────────────

    private function generateRSTE(array $equipments, Client $client, Site $site): int
    {
        // [equipo_idx, año, mes, descripción del trabajo]
        $schedule = [
            [0, 2024, 10, 'Modernización panel de control principal'],
            [2, 2024, 8,  'Revisión y ajuste general post-instalación'],
            [1, 2025, 1,  'Cambio de operador de puertas de cabina'],
            [0, 2025, 4,  'Cambio de cables de tracción (6 cables)'],
            [2, 2025, 6,  'Cambio de peines y placas de piso'],
            [1, 2025, 7,  'Modernización botoneras de cabina y piso'],
            [0, 2025, 9,  'Instalación sistema de monitoreo remoto IoT'],
            [2, 2025, 11, 'Modernización sistema de seguridad escalera'],
            [1, 2026, 1,  'Reemplazo cortina infrarroja de cabina'],
            [0, 2026, 3,  'Actualización variador de frecuencia'],
        ];

        $count = 0;
        foreach ($schedule as $i => [$eqIdx, $year, $month, $description]) {
            $eq = $equipments[$eqIdx];
            $serviceDate = Carbon::create($year, $month, 12 + ($i % 10));
            $techId = $this->technicians[$i % count($this->technicians)];

            $report = $this->createBaseReport('RSTE', $eq, $client, $site, $serviceDate, $techId, 'cerrado', [
                'time_in' => sprintf('%02d:00', 7 + ($i % 2)),
                'time_out' => sprintf('%02d:%02d', 15 + ($i % 3), 30),
                'equipment_functional' => $i !== 3, // Cable change may leave offline briefly
                'generates_quotation' => $i % 2 === 0,
                'requires_parts_change' => true,
                'conclusion_notes' => "{$description}. Trabajo completado según especificaciones técnicas. Se recomienda seguimiento en próxima visita preventiva.",
            ]);

            // Condiciones iniciales (20 ítems)
            foreach ($this->rstcConditionKeys as $j => $key) {
                $report->initialConditions()->create([
                    'condition_key' => $key,
                    'value' => $j < 10 ? 'si' : (($i + $j) % 5 === 0 ? 'no' : 'si'),
                ]);
            }

            // Trabajos RSTE
            foreach ($this->rsteWorks as $j => $work) {
                $report->rsteWorks()->create([
                    'group_key' => $work['group_key'],
                    'work_key' => $work['key'],
                    'is_ok' => ! ($j === ($i % count($this->rsteWorks))),
                    'observation' => ($j === 0) ? $description : null,
                ]);
            }

            // Códigos de falla (0-2)
            if ($i % 3 !== 2) {
                $report->faultCodes()->create([
                    'code' => 'C'.(1 + ($i * 3) % 20),
                    'severity' => 'media',
                ]);
            }

            $this->createAuditEntries($report, $techId, 'cerrado');
            $count++;
        }

        return $count;
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    private function createBaseReport(string $type, Equipment $eq, Client $client, Site $site, Carbon $date, int $techId, string $status, array $extra): ServiceReport
    {
        $seq = match ($type) {
            'RSTP' => ++$this->rstpSeq,
            'RSTC' => ++$this->rstcSeq,
            'RSTE' => ++$this->rsteSeq,
        };
        $number = sprintf('%s-%d-D%04d', $type, $date->year, $seq);

        return ServiceReport::create(array_merge([
            'report_number' => $number,
            'report_type' => $type,
            'equipment_id' => $eq->id,
            'client_id' => $client->id,
            'site_id' => $site->id,
            'service_date' => $date->toDateString(),
            'technician_id' => $techId,
            'status' => $status,
            'created_by' => $techId,
        ], $extra));
    }

    private function createAuditEntries(ServiceReport $report, int $techId, string $status): void
    {
        $serviceDate = Carbon::parse($report->service_date);

        ServiceReportAuditLog::create([
            'service_report_id' => $report->id,
            'user_id' => $techId,
            'action' => 'created',
            'created_at' => $serviceDate->copy()->setTime(7, 0),
        ]);

        if (in_array($status, ['firmado_tecnico', 'firmado_cliente', 'cerrado'])) {
            $report->update(['technician_signed_at' => $serviceDate->copy()->setTime(12, 0)]);
            ServiceReportAuditLog::create([
                'service_report_id' => $report->id,
                'user_id' => $techId,
                'action' => 'signed_tech',
                'created_at' => $serviceDate->copy()->setTime(12, 0),
            ]);
        }

        if (in_array($status, ['firmado_cliente', 'cerrado'])) {
            $report->update([
                'customer_signer_name' => 'Roberto Salazar Mejía',
                'customer_signer_document' => '71.234.567',
                'customer_signed_at' => $serviceDate->copy()->setTime(13, 0),
            ]);
            ServiceReportAuditLog::create([
                'service_report_id' => $report->id,
                'user_id' => $techId,
                'action' => 'signed_customer',
                'created_at' => $serviceDate->copy()->setTime(13, 0),
            ]);
        }
    }
}
