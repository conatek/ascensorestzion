<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\ServiceReport;
use App\Models\Site;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PortalController extends Controller
{
    private function getClientId(Request $request): int
    {
        $clientId = $request->user()->client_id;
        abort_if(!$clientId, 403, 'No tienes un cliente asignado.');
        return $clientId;
    }

    public function dashboard(Request $request): JsonResponse
    {
        $clientId = $this->getClientId($request);

        $totalEquipment  = Equipment::whereHas('site', fn($q) => $q->where('client_id', $clientId))->count();
        $activeEquipment = Equipment::whereHas('site', fn($q) => $q->where('client_id', $clientId))->where('status', 'activo')->count();
        $totalReports    = ServiceReport::where('client_id', $clientId)->count();
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
        $equipConContrato = Equipment::whereHas('site', fn($q) => $q->where('client_id', $clientId))
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

        // Reportes últimos 6 meses desglosados por tipo
        $rawByMonth = ServiceReport::where('client_id', $clientId)
            ->where('service_date', '>=', now()->subMonths(6)->startOfMonth())
            ->selectRaw("DATE_FORMAT(service_date, '%Y-%m') as period, report_type, COUNT(*) as count")
            ->groupBy('period', 'report_type')
            ->orderBy('period')
            ->get();

        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $key  = $date->format('Y-m');
            $months[$key] = [
                'month' => $date->translatedFormat('M Y'),
                'rstp'  => 0,
                'rstc'  => 0,
                'rste'  => 0,
            ];
        }
        foreach ($rawByMonth as $row) {
            if (isset($months[$row->period])) {
                $months[$row->period][strtolower($row->report_type)] = $row->count;
            }
        }

        return response()->json([
            'total_equipment'                => $totalEquipment,
            'active_equipment'               => $activeEquipment,
            'total_sites'                    => $totalSites,
            'total_reports'                  => $totalReports,
            'reports_this_month'             => $reportsThisMonth,
            'maintenance_compliance_percent' => $compliance,
            'recent_reports'                 => $recentReports,
            'reports_by_month'               => array_values($months),
            'compliance_detail'              => [
                'completed' => $rstpMes,
                'expected'  => $equipConContrato,
            ],
        ]);
    }

    public function equipment(Request $request): JsonResponse
    {
        $clientId = $this->getClientId($request);

        $query = Equipment::with(['site:id,name,client_id'])
            ->whereHas('site', fn($q) => $q->where('client_id', $clientId));

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
