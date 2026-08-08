<?php

namespace App\Http\Controllers;

use App\Events\ReportCompleted;
use App\Models\ServiceReport;
use App\Models\User;
use App\Notifications\VisitReportsCompletedNotification;
use App\Services\ScheduleService;
use App\Services\ServiceReportSigningService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

/**
 * Firma diferida: cuando una sede tiene varios equipos, el tecnico deja cada
 * reporte firmado solo por el (estado firmado_tecnico) agrupado por visit_uuid,
 * y al terminar la visita una unica firma del cliente los cierra todos.
 */
class VisitSignatureController extends Controller
{
    public function __construct(
        private ServiceReportSigningService $signing,
        private ScheduleService $schedule,
    ) {}

    /**
     * Visitas con reportes pendientes de la firma del cliente.
     */
    public function pending(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_if(! $user->can('sign_report_customer') && ! $user->can('sign_report_technician'), 403, 'No autorizado.');

        $query = ServiceReport::with([
            'equipment:id,internal_code',
            'client:id,business_name',
            'site:id,name',
        ])
            ->whereNotNull('visit_uuid')
            ->where('status', 'firmado_tecnico')
            // Techo: la lista es del trabajo reciente del técnico, no un historial.
            ->where('service_date', '>=', now()->subDays(90)->toDateString());

        // El tecnico solo ve sus propias visitas.
        if (! $user->can('edit_any_report')) {
            $query->where('technician_id', $user->id);
        }

        $visits = $query->orderBy('service_date')->orderBy('id')->get()
            ->groupBy('visit_uuid')
            ->map(fn ($reports, $visitUuid) => [
                'visit_uuid' => $visitUuid,
                'service_date' => $reports->first()->service_date?->toDateString(),
                'client_id' => $reports->first()->client_id,
                'client_name' => $reports->first()->client?->business_name,
                'site_id' => $reports->first()->site_id,
                'site_name' => $reports->first()->site?->name,
                'reports_count' => $reports->count(),
                'reports' => $reports->map(fn ($r) => [
                    'id' => $r->id,
                    'report_number' => $r->report_number,
                    'report_type' => $r->report_type,
                    'equipment_code' => $r->equipment?->internal_code,
                ])->values(),
            ])
            ->values();

        return response()->json($visits);
    }

    /**
     * Aplica una unica firma de cliente a todos los reportes pendientes de la visita.
     */
    public function signBatch(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_if(! $user->can('sign_report_customer') && ! $user->can('sign_report_technician'), 403, 'No autorizado.');

        $data = $request->validate([
            'visit_uuid' => ['required', 'uuid'],
            'signature' => ['required', 'string'],
            'signer_name' => ['required', 'string', 'max:255'],
            'signer_document' => ['required', 'string', 'max:50'],
        ]);

        $query = ServiceReport::where('visit_uuid', $data['visit_uuid']);

        if (! $user->can('edit_any_report')) {
            $query->where('technician_id', $user->id);
        }

        $visitReports = $query->orderBy('id')->get();

        abort_if($visitReports->isEmpty(), 404, 'No se encontraron reportes para esta visita.');

        $pending = $visitReports->where('status', 'firmado_tecnico');

        // Idempotencia para el sync offline: si ya se firmo, devolver el resultado.
        if ($pending->isEmpty()) {
            return response()->json([
                'visit_uuid' => $data['visit_uuid'],
                'signed' => 0,
                'reports' => $visitReports->values(),
            ]);
        }

        DB::transaction(function () use ($pending, $data, $request) {
            $this->signing->signAsCustomerBatch(
                $pending,
                $data['signature'],
                $data['signer_name'],
                $data['signer_document'],
                $request
            );

            foreach ($pending as $report) {
                $report->update(['reception_token' => Str::random(64)]);
            }
        });

        // Una firma en lote cubre varios equipos de la sede y cada uno tiene su
        // propia visita programada: se cierran todas.
        $this->schedule->completeFromReports($pending);

        $this->notifyVisitCompleted($pending);

        return response()->json([
            'visit_uuid' => $data['visit_uuid'],
            'signed' => $pending->count(),
            'reports' => ServiceReport::whereIn('id', $pending->pluck('id'))->get(),
        ]);
    }

    /**
     * Un unico aviso agrupado por visita, en lugar de N avisos por reporte.
     *
     * @param  \Illuminate\Support\Collection<int, ServiceReport>  $reports
     */
    private function notifyVisitCompleted($reports): void
    {
        $reportIds = $reports->pluck('id')->all();
        $first = $reports->first()->fresh(['technician', 'client']);

        $masters = User::role('master')->get();

        if ($masters->isNotEmpty()) {
            // Un solo broadcast: la campana solo lo usa para refrescar.
            ReportCompleted::dispatch($first, $masters->pluck('id')->all());
            Notification::send($masters, new VisitReportsCompletedNotification($reportIds, 'master'));
        }

        if ($first->technician) {
            $first->technician->notify(new VisitReportsCompletedNotification($reportIds, 'technician'));
        }

        $clientAdmins = User::where('client_id', $first->client_id)->get();
        if ($clientAdmins->isNotEmpty()) {
            Notification::send($clientAdmins, new VisitReportsCompletedNotification($reportIds, 'client'));
        }
    }
}
