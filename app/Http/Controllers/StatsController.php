<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Equipment;
use App\Models\ServiceReport;
use App\Models\ServiceReportFaultCode;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatsController extends Controller
{
    public function overview(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_if(!$user->can('view_reports'), 403, 'No autorizado.');

        $equipmentQuery = Equipment::query();
        $reportQuery    = ServiceReport::query();

        // Scope para cliente externo
        if ($user->hasRole('admin') && $user->client_id) {
            $equipmentQuery->whereHas('site', fn($q) => $q->where('client_id', $user->client_id));
            $reportQuery->where('client_id', $user->client_id);
        }

        $this->applyFilters($equipmentQuery, $reportQuery, $request);

        $activeEquipment = (clone $equipmentQuery)->where('status', 'activo')->count();
        $totalReports    = (clone $reportQuery)->count();
        $reportsThisMonth = (clone $reportQuery)
            ->whereMonth('service_date', now()->month)
            ->whereYear('service_date', now()->year)
            ->count();

        // Cumplimiento mantenimiento: RSTP realizados este mes / equipos activos con contrato
        $equiposConContrato = (clone $equipmentQuery)
            ->where('status', 'activo')
            ->whereNotNull('contract_type')
            ->count();
        $rstpEsteMes = (clone $reportQuery)
            ->where('report_type', 'RSTP')
            ->whereMonth('service_date', now()->month)
            ->whereYear('service_date', now()->year)
            ->whereIn('status', ['firmado_tecnico', 'firmado_cliente', 'cerrado'])
            ->count();
        $compliance = $equiposConContrato > 0
            ? round(($rstpEsteMes / $equiposConContrato) * 100, 1)
            : 0;

        // RSTC abiertos vs cerrados
        $rstcOpen   = (clone $reportQuery)->where('report_type', 'RSTC')->where('status', 'borrador')->count();
        $rstcClosed = (clone $reportQuery)->where('report_type', 'RSTC')->whereIn('status', ['firmado_cliente', 'cerrado'])->count();

        // Tiempo medio de respuesta RSTC
        $avgResponse = DB::table('service_reports')
            ->join('service_report_rstc_details', 'service_reports.id', '=', 'service_report_rstc_details.service_report_id')
            ->where('service_reports.report_type', 'RSTC')
            ->whereNotNull('service_report_rstc_details.call_time')
            ->whereNotNull('service_report_rstc_details.entry_time')
            ->when($user->hasRole('admin') && $user->client_id, fn($q) => $q->where('service_reports.client_id', $user->client_id))
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, service_report_rstc_details.call_time, service_report_rstc_details.entry_time)) as avg_minutes')
            ->value('avg_minutes');

        return response()->json([
            'active_equipment'              => $activeEquipment,
            'total_reports'                 => $totalReports,
            'reports_this_month'            => $reportsThisMonth,
            'maintenance_compliance_percent' => $compliance,
            'rstc_open'                     => $rstcOpen,
            'rstc_closed'                   => $rstcClosed,
            'avg_response_time_minutes'     => $avgResponse ? round($avgResponse) : null,
        ]);
    }

    public function reportsByMonth(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_if(!$user->can('view_reports'), 403, 'No autorizado.');

        $query = ServiceReport::query()
            ->where('service_date', '>=', now()->subMonths(12)->startOfMonth())
            ->when($user->hasRole('admin') && $user->client_id, fn($q) => $q->where('client_id', $user->client_id));

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        $raw = $query->selectRaw("
                DATE_FORMAT(service_date, '%Y-%m') as period,
                report_type,
                COUNT(*) as count
            ")
            ->groupBy('period', 'report_type')
            ->orderBy('period')
            ->get();

        // Generar los 12 meses
        $months = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $key  = $date->format('Y-m');
            $months[$key] = [
                'month' => $date->translatedFormat('M Y'),
                'rstp'  => 0,
                'rstc'  => 0,
                'rste'  => 0,
            ];
        }

        foreach ($raw as $row) {
            if (isset($months[$row->period])) {
                $type = strtolower($row->report_type);
                $months[$row->period][$type] = $row->count;
            }
        }

        return response()->json(array_values($months));
    }

    public function topEquipmentFaults(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_if(!$user->can('view_reports'), 403, 'No autorizado.');

        $query = ServiceReport::where('report_type', 'RSTC')
            ->where('service_date', '>=', now()->subDays(90))
            ->when($user->hasRole('admin') && $user->client_id, fn($q) => $q->where('client_id', $user->client_id));

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        $results = $query->select('equipment_id', DB::raw('COUNT(*) as fault_count'))
            ->groupBy('equipment_id')
            ->orderByDesc('fault_count')
            ->limit(10)
            ->with(['equipment:id,internal_code,brand,model', 'equipment.site:id,name,client_id', 'equipment.site.client:id,business_name'])
            ->get()
            ->map(fn($r) => [
                'internal_code' => $r->equipment->internal_code ?? '',
                'brand'         => $r->equipment->brand ?? '',
                'model'         => $r->equipment->model ?? '',
                'site_name'     => $r->equipment->site->name ?? '',
                'client_name'   => $r->equipment->site->client->business_name ?? '',
                'fault_count'   => $r->fault_count,
            ]);

        return response()->json($results);
    }

    public function faultCodesFrequency(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_if(!$user->can('view_reports'), 403, 'No autorizado.');

        $query = ServiceReportFaultCode::query()
            ->when($user->hasRole('admin') && $user->client_id, function ($q) use ($user) {
                $q->whereHas('serviceReport', fn($sr) => $sr->where('client_id', $user->client_id));
            });

        if ($request->filled('client_id')) {
            $query->whereHas('serviceReport', fn($sr) => $sr->where('client_id', $request->client_id));
        }

        $total = (clone $query)->count();

        $results = $query->select('code', DB::raw('COUNT(*) as count'))
            ->groupBy('code')
            ->orderByDesc('count')
            ->get()
            ->map(fn($r) => [
                'code'       => $r->code,
                'count'      => $r->count,
                'percentage' => $total > 0 ? round(($r->count / $total) * 100, 1) : 0,
            ]);

        return response()->json($results);
    }

    public function technicianWorkload(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_if(!$user->can('view_reports'), 403, 'No autorizado.');

        $dateFrom = $request->input('date_from', now()->subDays(30)->toDateString());
        $dateTo   = $request->input('date_to', now()->toDateString());

        $results = ServiceReport::whereBetween('service_date', [$dateFrom, $dateTo])
            ->when($user->hasRole('admin') && $user->client_id, fn($q) => $q->where('client_id', $user->client_id))
            ->select(
                'technician_id',
                DB::raw("SUM(CASE WHEN report_type='RSTP' THEN 1 ELSE 0 END) as rstp_count"),
                DB::raw("SUM(CASE WHEN report_type='RSTC' THEN 1 ELSE 0 END) as rstc_count"),
                DB::raw("SUM(CASE WHEN report_type='RSTE' THEN 1 ELSE 0 END) as rste_count"),
                DB::raw("COUNT(*) as total")
            )
            ->groupBy('technician_id')
            ->with('technician:id,name')
            ->get()
            ->map(fn($r) => [
                'technician_name' => $r->technician->name ?? 'Sin asignar',
                'rstp_count'      => $r->rstp_count,
                'rstc_count'      => $r->rstc_count,
                'rste_count'      => $r->rste_count,
                'total'           => $r->total,
            ]);

        return response()->json($results);
    }

    public function maintenanceCompliance(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_if(!$user->can('view_reports'), 403, 'No autorizado.');

        $clients = Client::with(['equipment' => function ($q) {
            $q->where('status', 'activo')->whereNotNull('contract_type');
        }])->when($user->hasRole('admin') && $user->client_id, fn($q) => $q->where('id', $user->client_id))
          ->when($request->filled('client_id'), fn($q) => $q->where('id', $request->client_id))
          ->get();

        $results = $clients->map(function ($client) {
            $equipmentCount = $client->equipment->count();
            $completed = ServiceReport::where('client_id', $client->id)
                ->where('report_type', 'RSTP')
                ->whereMonth('service_date', now()->month)
                ->whereYear('service_date', now()->year)
                ->whereIn('status', ['firmado_tecnico', 'firmado_cliente', 'cerrado'])
                ->count();

            return [
                'client_name'        => $client->business_name,
                'expected'           => $equipmentCount,
                'completed'          => $completed,
                'compliance_percent' => $equipmentCount > 0 ? round(($completed / $equipmentCount) * 100, 1) : 0,
            ];
        });

        return response()->json($results);
    }

    private function applyFilters($equipmentQuery, $reportQuery, Request $request): void
    {
        if ($request->filled('client_id')) {
            $equipmentQuery->whereHas('site', fn($q) => $q->where('client_id', $request->client_id));
            $reportQuery->where('client_id', $request->client_id);
        }
        if ($request->filled('equipment_type')) {
            $equipmentQuery->where('equipment_type', $request->equipment_type);
        }
    }
}
