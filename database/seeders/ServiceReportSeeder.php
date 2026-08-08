<?php

namespace Database\Seeders;

use App\Models\Catalog;
use App\Models\Equipment;
use App\Models\ReportSequence;
use App\Models\ServiceReport;
use App\Models\User;
use Illuminate\Database\Seeder;

class ServiceReportSeeder extends Seeder
{
    public function run(): void
    {
        $technicians = User::whereHas('roles', fn ($q) => $q->where('name', 'technician'))->get();
        $equipment = Equipment::with('site')->get();

        if ($technicians->isEmpty() || $equipment->isEmpty()) {
            return;
        }

        $rstpConditions = Catalog::where('scope', 'RSTP')->where('category', 'initial_condition')->pluck('key')->toArray();
        $rstpActivities = Catalog::where('scope', 'RSTP')->where('category', 'rstp_activity')->get();
        $rstcConditions = Catalog::where('scope', 'RSTC')->where('category', 'initial_condition')->pluck('key')->toArray();
        $rsteWorks = Catalog::where('scope', 'RSTE')->where('category', 'rste_work')->get();

        $statuses = ['borrador', 'firmado_tecnico', 'firmado_cliente', 'cerrado'];

        // 15 RSTP distribuidos en últimos 6 meses
        for ($i = 0; $i < 15; $i++) {
            $eq = $equipment->random();
            $tech = $technicians->random();
            $date = now()->subDays(rand(1, 180));
            $status = $statuses[array_rand($statuses)];

            $report = ServiceReport::create([
                'report_number' => ReportSequence::nextNumber('RSTP'),
                'report_type' => 'RSTP',
                'equipment_id' => $eq->id,
                'client_id' => $eq->site->client_id,
                'site_id' => $eq->site_id,
                'service_date' => $date->toDateString(),
                'time_in' => sprintf('%02d:%02d', rand(7, 10), rand(0, 59)),
                'time_out' => sprintf('%02d:%02d', rand(11, 16), rand(0, 59)),
                'technician_id' => $tech->id,
                'equipment_functional' => rand(0, 10) > 1,
                'generates_quotation' => rand(0, 5) === 0,
                'requires_parts_change' => rand(0, 5) === 0,
                'conclusion_notes' => 'Mantenimiento preventivo realizado sin novedad.',
                'status' => $status,
                'created_by' => $tech->id,
            ]);

            foreach ($rstpConditions as $key) {
                $report->initialConditions()->create([
                    'condition_key' => $key,
                    'value' => ['si', 'no', 'na'][array_rand(['si', 'no', 'na'])],
                ]);
            }

            foreach ($rstpActivities as $act) {
                $report->rstpActivities()->create([
                    'group_key' => $act->group_key,
                    'activity_key' => $act->key,
                    'is_ok' => rand(0, 4) > 0,
                ]);
            }

            $report->rstpMonth()->create([
                'year' => $date->year,
                'month' => $date->month,
            ]);
        }

        // 10 RSTC
        $faultLocations = ['sistema_puertas', 'control_maniobras', 'maquinaria', 'recorrido', 'otra'];
        $faultCauses = ['energia_externa', 'inundacion_humedad', 'tercero', 'tecnica_equipo', 'otra'];
        $faultSolutions = ['control_maniobras', 'perifericos', 'puertas_piso', 'operador_puertas_cabina', 'activacion_interruptores', 'otra'];

        for ($i = 0; $i < 10; $i++) {
            $eq = $equipment->random();
            $tech = $technicians->random();
            $date = now()->subDays(rand(1, 180));
            $status = $statuses[array_rand($statuses)];

            $callTime = $date->copy()->setTime(rand(6, 12), rand(0, 59));
            $entryTime = $callTime->copy()->addMinutes(rand(15, 120));
            $exitTime = $entryTime->copy()->addMinutes(rand(30, 180));
            $responseMins = $callTime->diffInMinutes($entryTime);

            $report = ServiceReport::create([
                'report_number' => ReportSequence::nextNumber('RSTC'),
                'report_type' => 'RSTC',
                'equipment_id' => $eq->id,
                'client_id' => $eq->site->client_id,
                'site_id' => $eq->site_id,
                'service_date' => $date->toDateString(),
                'time_in' => $entryTime->format('H:i'),
                'time_out' => $exitTime->format('H:i'),
                'technician_id' => $tech->id,
                'equipment_functional' => rand(0, 4) > 0,
                'generates_quotation' => rand(0, 3) === 0,
                'requires_parts_change' => rand(0, 3) === 0,
                'conclusion_notes' => 'Falla corregida. Equipo operativo.',
                'status' => $status,
                'created_by' => $tech->id,
            ]);

            foreach ($rstcConditions as $key) {
                $report->initialConditions()->create([
                    'condition_key' => $key,
                    'value' => ['si', 'no', 'na'][array_rand(['si', 'no', 'na'])],
                ]);
            }

            $report->rstcDetails()->create([
                'call_time' => $callTime,
                'entry_time' => $entryTime,
                'exit_time' => $exitTime,
                'response_time_hh' => intdiv($responseMins, 60),
                'response_time_mm' => $responseMins % 60,
                'fault_location' => $faultLocations[array_rand($faultLocations)],
                'fault_cause' => $faultCauses[array_rand($faultCauses)],
                'fault_solution_area' => $faultSolutions[array_rand($faultSolutions)],
                'analysis_notes' => 'Se identificó la falla y se procedió a reparar.',
            ]);

            $codesCount = rand(1, 4);
            for ($c = 0; $c < $codesCount; $c++) {
                $report->faultCodes()->create([
                    'code' => 'C'.rand(1, 20),
                    'severity' => ['baja', 'media', 'alta'][array_rand(['baja', 'media', 'alta'])],
                ]);
            }
        }

        // 5 RSTE
        for ($i = 0; $i < 5; $i++) {
            $eq = $equipment->random();
            $tech = $technicians->random();
            $date = now()->subDays(rand(1, 180));
            $status = $statuses[array_rand($statuses)];

            $report = ServiceReport::create([
                'report_number' => ReportSequence::nextNumber('RSTE'),
                'report_type' => 'RSTE',
                'equipment_id' => $eq->id,
                'client_id' => $eq->site->client_id,
                'site_id' => $eq->site_id,
                'service_date' => $date->toDateString(),
                'time_in' => sprintf('%02d:%02d', rand(7, 10), rand(0, 59)),
                'time_out' => sprintf('%02d:%02d', rand(14, 18), rand(0, 59)),
                'technician_id' => $tech->id,
                'equipment_functional' => rand(0, 3) > 0,
                'generates_quotation' => rand(0, 2) === 0,
                'requires_parts_change' => true,
                'conclusion_notes' => 'Trabajo especial completado según especificaciones.',
                'status' => $status,
                'created_by' => $tech->id,
            ]);

            foreach ($rstcConditions as $key) {
                $report->initialConditions()->create([
                    'condition_key' => $key,
                    'value' => ['si', 'no', 'na'][array_rand(['si', 'no', 'na'])],
                ]);
            }

            foreach ($rsteWorks as $work) {
                $report->rsteWorks()->create([
                    'group_key' => $work->group_key,
                    'work_key' => $work->key,
                    'is_ok' => rand(0, 3) > 0,
                ]);
            }

            $codesCount = rand(0, 3);
            for ($c = 0; $c < $codesCount; $c++) {
                $report->faultCodes()->create([
                    'code' => 'C'.rand(1, 20),
                    'severity' => ['baja', 'media', 'alta'][array_rand(['baja', 'media', 'alta'])],
                ]);
            }
        }
    }
}
