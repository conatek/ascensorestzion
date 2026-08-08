<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\ScheduledVisit;
use App\Models\ServiceReport;
use App\Models\Site;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PortalController extends Controller
{
    private function getClientId(Request $request): int
    {
        $clientId = $request->user()->client_id;
        abort_if(! $clientId, 403, 'No tienes un cliente asignado.');

        return $clientId;
    }

    public function dashboard(Request $request): JsonResponse
    {
        $clientId = $this->getClientId($request);

        $totalEquipment = Equipment::whereHas('site', fn ($q) => $q->where('client_id', $clientId))->count();
        $activeEquipment = Equipment::whereHas('site', fn ($q) => $q->where('client_id', $clientId))->where('status', 'activo')->count();
        $totalReports = ServiceReport::where('client_id', $clientId)->count();
        $reportsThisMonth = ServiceReport::where('client_id', $clientId)
            ->whereMonth('service_date', now()->month)
            ->whereYear('service_date', now()->year)
            ->count();
        $totalSites = Site::where('client_id', $clientId)->count();

        // Reportes del mes actual
        $recentReports = ServiceReport::where('client_id', $clientId)
            ->whereMonth('service_date', now()->month)
            ->whereYear('service_date', now()->year)
            ->with('equipment:id,internal_code')
            ->orderByDesc('service_date')
            ->limit(10)
            ->get(['id', 'report_number', 'report_type', 'service_date', 'status', 'equipment_id']);

        // Cumplimiento mantenimiento
        $equipConContrato = Equipment::whereHas('site', fn ($q) => $q->where('client_id', $clientId))
            ->where('status', 'activo')
            ->whereNotNull('contract_type')
            ->count();
        $rstpMes = ServiceReport::where('client_id', $clientId)
            ->where('report_type', 'RSTP')
            ->whereMonth('service_date', now()->month)
            ->whereYear('service_date', now()->year)
            ->whereIn('status', ['firmado_tecnico', 'firmado_cliente', 'cerrado'])
            ->count();
        $compliance = $equipConContrato > 0 ? round(($rstpMes / $equipConContrato) * 100, 1) : 0;

        // Reportes últimos 6 meses desglosados por tipo. El agrupado se hace en PHP
        // a propósito: DATE_FORMAT solo existe en MySQL y reventaba en sqlite (los
        // tests). Son como mucho unos cientos de filas de un solo cliente.
        $rawByMonth = ServiceReport::where('client_id', $clientId)
            ->where('service_date', '>=', now()->subMonths(6)->startOfMonth())
            ->orderBy('service_date')
            ->get(['service_date', 'report_type'])
            ->groupBy(fn ($r) => $r->service_date->format('Y-m'))
            ->flatMap(fn ($rows, $period) => $rows->groupBy('report_type')
                ->map(fn ($byType, $type) => (object) [
                    'period' => $period,
                    'report_type' => $type,
                    'count' => $byType->count(),
                ])->values());

        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $key = $date->format('Y-m');
            $months[$key] = [
                'month' => $date->translatedFormat('M Y'),
                'rstp' => 0,
                'rstc' => 0,
                'rste' => 0,
            ];
        }
        foreach ($rawByMonth as $row) {
            if (isset($months[$row->period])) {
                $months[$row->period][strtolower($row->report_type)] = $row->count;
            }
        }

        // Tarjeta "Proxima visita": la mas cercana que aun no ha terminado.
        $nextVisit = ScheduledVisit::query()
            ->with([
                'equipment:id,internal_code,equipment_type',
                'site:id,name,address',
                'technician:id,name',
            ])
            ->forClient($clientId)
            ->blocking()
            ->where('scheduled_end', '>=', now())
            ->orderBy('scheduled_start')
            ->first();

        return response()->json([
            'next_visit' => $nextVisit,
            'total_equipment' => $totalEquipment,
            'active_equipment' => $activeEquipment,
            'total_sites' => $totalSites,
            'total_reports' => $totalReports,
            'reports_this_month' => $reportsThisMonth,
            'maintenance_compliance_percent' => $compliance,
            'recent_reports' => $recentReports,
            'reports_by_month' => array_values($months),
            'compliance_detail' => [
                'completed' => $rstpMes,
                'expected' => $equipConContrato,
            ],
        ]);
    }

    public function equipment(Request $request): JsonResponse
    {
        $clientId = $this->getClientId($request);

        $query = Equipment::with(['site:id,name,client_id'])
            ->whereHas('site', fn ($q) => $q->where('client_id', $clientId));

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('equipment_type')) {
            $query->where('equipment_type', $request->equipment_type);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('internal_code', 'like', "%{$s}%")
                    ->orWhere('brand', 'like', "%{$s}%")
                    ->orWhere('model', 'like', "%{$s}%");
            });
        }

        return response()->json($query->orderBy('internal_code')->get());
    }

    public function equipmentShow(Request $request, Equipment $equipment): JsonResponse
    {
        $clientId = $this->getClientId($request);

        if ($equipment->site?->client_id !== $clientId) {
            abort(403, 'No autorizado.');
        }

        $equipment->load(['site.client', 'attachments']);

        return response()->json($equipment);
    }

    public function reports(Request $request): JsonResponse
    {
        $clientId = $this->getClientId($request);

        $query = ServiceReport::where('client_id', $clientId)
            ->with([
                'equipment:id,internal_code,brand,model',
                'technician:id,name',
            ]);

        if ($request->filled('report_type')) {
            $query->where('report_type', $request->report_type);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date_from')) {
            $query->where('service_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('service_date', '<=', $request->date_to);
        }

        return response()->json($query->orderByDesc('service_date')->get());
    }

    /**
     * Cronograma del cliente. Dos modos a proposito:
     *
     * - con from/to devuelve la lista plana del rango, que es lo que necesita la
     *   vista de calendario;
     * - sin rango devuelve proximas + historial ya separados, que es como se pinta
     *   la lista y evita traerse el historico entero al portal.
     */
    public function schedule(Request $request): JsonResponse
    {
        $clientId = $this->getClientId($request);

        $validated = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $base = fn () => ScheduledVisit::query()
            ->with([
                'equipment:id,internal_code,equipment_type',
                'site:id,name,address',
                'technician:id,name',
                // Para enlazar el PDF firmado desde el historial sin una segunda vuelta.
                'serviceReports:id,visit_uuid,report_number,report_type,status',
            ])
            ->forClient($clientId)
            // El cliente no ve las canceladas: para el son visitas que no existen.
            ->where('status', '!=', 'cancelada');

        if (! empty($validated['from']) && ! empty($validated['to'])) {
            return response()->json(
                $base()
                    ->between(
                        CarbonImmutable::parse($validated['from']),
                        CarbonImmutable::parse($validated['to']),
                    )
                    ->orderBy('scheduled_start')
                    ->get()
            );
        }

        // El corte es por fin de la visita: una que empezo hace una hora y sigue en
        // curso pertenece a "proximas", no al historial.
        $now = now();

        $upcoming = $base()
            ->where('scheduled_end', '>=', $now)
            ->orderBy('scheduled_start')
            ->get();

        $history = $base()
            ->where('scheduled_end', '<', $now)
            ->orderByDesc('scheduled_start')
            ->limit(50)
            ->get();

        return response()->json([
            'upcoming' => $upcoming,
            'history' => $history,
        ]);
    }

    public function scheduleShow(Request $request, ScheduledVisit $scheduledVisit): JsonResponse
    {
        $clientId = $this->getClientId($request);

        abort_if((int) $scheduledVisit->client_id !== $clientId, 403, 'No autorizado.');

        $scheduledVisit->load([
            'equipment:id,internal_code,equipment_type,brand,model,site_id',
            'site:id,name,address,city,contact_name_onsite,contact_phone_onsite',
            'technician:id,name',
        ]);

        // Los reportes de la visita ya ejecutada, para enlazar el PDF firmado.
        $reports = $scheduledVisit->visit_uuid
            ? ServiceReport::where('visit_uuid', $scheduledVisit->visit_uuid)
                ->where('client_id', $clientId)
                ->get(['id', 'report_number', 'report_type', 'service_date', 'status', 'equipment_id'])
            : collect();

        return response()->json([
            'visit' => $scheduledVisit,
            'reports' => $reports,
        ]);
    }

    public function reportShow(Request $request, ServiceReport $serviceReport): JsonResponse
    {
        $clientId = $this->getClientId($request);

        if ((int) $serviceReport->client_id !== $clientId) {
            abort(403, 'No autorizado.');
        }

        $relations = [
            'equipment.site.client',
            'technician:id,name',
            'initialConditions',
            'attachments',
        ];

        if ($serviceReport->report_type === 'RSTP') {
            $relations[] = 'rstpActivities';
            $relations[] = 'rstpMonth';
        }
        if ($serviceReport->report_type === 'RSTC') {
            $relations[] = 'rstcDetails';
            $relations[] = 'faultCodes';
        }
        if ($serviceReport->report_type === 'RSTE') {
            $relations[] = 'rsteWorks';
            $relations[] = 'faultCodes';
        }

        return response()->json($serviceReport->load($relations));
    }
}
