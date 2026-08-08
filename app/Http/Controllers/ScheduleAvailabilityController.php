<?php

namespace App\Http\Controllers;

use App\Models\ScheduledVisit;
use App\Models\ScheduleSetting;
use App\Services\AvailabilityService;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Espacios libres para mover una visita.
 *
 * El cliente nunca ve la agenda del tecnico: solo los horarios en los que cabe su
 * visita. Por eso la respuesta trae horas ya formateadas y ningun dato de las
 * demas visitas.
 */
class ScheduleAvailabilityController extends Controller
{
    /** Un mes vista es suficiente para reubicar un mantenimiento. */
    private const HORIZON_DAYS = 30;

    public function __construct(private AvailabilityService $availability) {}

    /** Portal: la visita tiene que ser del cliente autenticado. */
    public function forReschedule(Request $request, ScheduledVisit $scheduledVisit): JsonResponse
    {
        $clientId = $request->user()->client_id;
        abort_if(! $clientId, 403, 'No tienes un cliente asignado.');
        abort_if((int) $scheduledVisit->client_id !== (int) $clientId, 403, 'No autorizado.');

        return response()->json($this->payload($request, $scheduledVisit, enforceNotice: true));
    }

    /** Coordinacion: sin antelacion minima, que esa regla es para el cliente. */
    public function forVisit(Request $request, ScheduledVisit $scheduledVisit): JsonResponse
    {
        abort_if(! $request->user()->can('view_schedule'), 403, 'No autorizado.');

        return response()->json($this->payload($request, $scheduledVisit, enforceNotice: false));
    }

    private function payload(Request $request, ScheduledVisit $visit, bool $enforceNotice): array
    {
        $validated = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $earliest = $this->availability->earliestAllowed();

        // El horizonte arranca en el primer dia proponible, no hoy: enseñar dias
        // apagados por delante solo obliga a desplazarse.
        $from = ! empty($validated['from'])
            ? CarbonImmutable::parse($validated['from'])
            : ($enforceNotice ? $earliest : CarbonImmutable::now());

        $to = ! empty($validated['to'])
            ? CarbonImmutable::parse($validated['to'])
            : $from->addDays(self::HORIZON_DAYS - 1);

        $visit->loadMissing([
            'equipment:id,internal_code',
            'site:id,name',
            'technician:id,name',
            'pendingRescheduleRequest',
        ]);

        $slots = $this->availability->forVisit($visit, $from, $to, $enforceNotice);

        return array_merge($slots, [
            'visit' => [
                'id' => $visit->id,
                'status' => $visit->status,
                'scheduled_start' => $visit->scheduled_start,
                'duration_minutes' => (int) CarbonImmutable::parse($visit->scheduled_start)
                    ->diffInMinutes(CarbonImmutable::parse($visit->scheduled_end)),
                'equipment_code' => $visit->equipment?->internal_code,
                'site_name' => $visit->site?->name,
                'technician_name' => $visit->technician?->name,
            ],
            'can_request' => $enforceNotice
                ? ($visit->canBeRescheduledByClient() && ! $visit->pendingRescheduleRequest)
                : true,
            'blocked_reason' => $enforceNotice ? $this->blockedReason($visit) : null,
            'notice_hours' => (int) ScheduleSetting::get('min_reschedule_notice_hours'),
            'slot_minutes' => (int) ScheduleSetting::get('availability_slot_minutes'),
            'earliest' => $earliest->format('Y-m-d H:i'),
            'range' => ['from' => $from->toDateString(), 'to' => $to->toDateString()],
        ]);
    }

    private function blockedReason(ScheduledVisit $visit): ?string
    {
        if ($visit->pendingRescheduleRequest) {
            return 'pendiente';
        }

        if ($visit->status !== 'programada') {
            return 'estado';
        }

        if ($visit->scheduled_start < now()) {
            return 'pasada';
        }

        return $visit->canBeRescheduledByClient() ? null : 'antelacion';
    }
}
