<?php

namespace App\Http\Controllers;

use App\Models\ServiceReport;
use App\Models\ServiceReportAuditLog;
use App\Models\User;
use App\Notifications\ReportReceptionConfirmedNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class ReportConfirmationController extends Controller
{
    public function show(string $token): JsonResponse
    {
        $report = ServiceReport::where('reception_token', $token)
            ->with([
                'equipment:id,internal_code,equipment_type,brand,model',
                'client:id,business_name',
                'site:id,name,address',
                'technician:id,name',
            ])
            ->firstOrFail();

        // Ya fue confirmado
        if ($report->reception_confirmed_at) {
            return response()->json([
                'status' => 'already_confirmed',
                'confirmed_at' => $report->reception_confirmed_at,
                'report' => $this->reportSummary($report),
            ]);
        }

        return response()->json([
            'status' => 'pending',
            'report' => $this->reportSummary($report),
        ]);
    }

    public function confirm(Request $request, string $token): JsonResponse
    {
        $report = ServiceReport::where('reception_token', $token)->firstOrFail();

        if ($report->reception_confirmed_at) {
            return response()->json([
                'status' => 'already_confirmed',
                'message' => 'Este informe ya fue confirmado anteriormente.',
            ], 422);
        }

        $report->update([
            'reception_confirmed_at' => now(),
            'reception_confirmed_ip' => $request->ip(),
            'reception_confirmed_ua' => $request->userAgent(),
            'status' => 'cerrado',
        ]);

        // Audit log (user_id usa technician como referencia ya que es ruta publica)
        ServiceReportAuditLog::create([
            'service_report_id' => $report->id,
            'user_id' => $report->technician_id,
            'action' => 'confirmed_reception',
            'changes_json' => json_encode([
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);

        // Notificar a masters y al tecnico
        $masters = User::role('master')->get();
        Notification::send($masters, new ReportReceptionConfirmedNotification($report));

        if ($report->technician_id) {
            $technician = User::find($report->technician_id);
            $technician?->notify(new ReportReceptionConfirmedNotification($report));
        }

        return response()->json([
            'status' => 'confirmed',
            'message' => 'Recepción confirmada exitosamente. Gracias.',
        ]);
    }

    private function reportSummary(ServiceReport $report): array
    {
        $typeLabels = ['RSTP' => 'Preventivo', 'RSTC' => 'Correctivo', 'RSTE' => 'Especial'];

        return [
            'report_number' => $report->report_number,
            'report_type' => $report->report_type,
            'report_type_label' => $typeLabels[$report->report_type] ?? $report->report_type,
            'service_date' => $report->service_date?->format('d/m/Y'),
            'equipment_code' => $report->equipment?->internal_code,
            'equipment_type' => $report->equipment?->equipment_type,
            'equipment_brand' => $report->equipment?->brand . ' ' . $report->equipment?->model,
            'client_name' => $report->client?->business_name,
            'site_name' => $report->site?->name,
            'site_address' => $report->site?->address,
            'technician_name' => $report->technician?->name,
        ];
    }
}
