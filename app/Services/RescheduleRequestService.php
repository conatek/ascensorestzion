<?php

namespace App\Services;

use App\Models\RescheduleRequest;
use App\Models\ScheduledVisit;
use App\Models\ScheduleSetting;
use App\Models\User;
use App\Notifications\RescheduleRequestedNotification;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use Illuminate\Validation\ValidationException;

/**
 * El ciclo de una solicitud de reprogramacion: la pide el cliente, la resuelve
 * coordinacion.
 *
 * Nada de lo que hay aqui reimplementa a ScheduleService: mover la visita es
 * siempre reschedule(), y saber si el hueco sigue libre es siempre
 * assertSlotIsFree(). Duplicar cualquiera de las dos acabaria con el portal y el
 * tablero discrepando sobre lo que es un horario valido.
 */
class RescheduleRequestService
{
    public function __construct(
        private ScheduleService $schedule,
        private AvailabilityService $availability,
    ) {}

    /**
     * El cliente propone una fecha nueva. La visita pasa a ambar pero conserva su
     * fecha: si se rechaza no hay nada que deshacer y el hueco no se pierde.
     *
     * @throws ValidationException
     */
    public function request(
        ScheduledVisit $visit,
        User $requester,
        CarbonImmutable $proposedStart,
        ?string $reason = null,
    ): RescheduleRequest {
        $created = DB::transaction(function () use ($visit, $requester, $proposedStart, $reason) {
            // Bloqueo pesimista: dos toques seguidos en un movil lento crearian dos
            // solicitudes para la misma visita.
            $fresh = ScheduledVisit::query()->whereKey($visit->id)->lockForUpdate()->firstOrFail();

            if ($fresh->status !== 'programada') {
                throw ValidationException::withMessages([
                    'visit' => ['Esta visita ya no se puede reprogramar.'],
                ]);
            }

            if ($fresh->rescheduleRequests()->pending()->exists()) {
                throw ValidationException::withMessages([
                    'visit' => ['Ya hay una solicitud pendiente para esta visita.'],
                ]);
            }

            if ($proposedStart < $this->availability->earliestAllowed()) {
                throw ValidationException::withMessages([
                    'proposed_start' => [sprintf(
                        'Necesitamos al menos %d horas de antelacion.',
                        (int) ScheduleSetting::get('min_reschedule_notice_hours'),
                    )],
                ]);
            }

            $duration = CarbonImmutable::parse($fresh->scheduled_start)
                ->diffInMinutes(CarbonImmutable::parse($fresh->scheduled_end));
            $proposedEnd = $proposedStart->addMinutes((int) $duration);

            // Las dos validaciones: que el horario este en la rejilla que se ofrecio
            // y que el backend lo acepte tal cual. La segunda se repite al aprobar.
            $this->availability->assertProposalIsOffered($fresh, $proposedStart);
            $this->schedule->assertSlotIsFree($fresh->technician, $proposedStart, $proposedEnd, $fresh->id);

            $request = RescheduleRequest::create([
                'scheduled_visit_id' => $fresh->id,
                'requested_by' => $requester->id,
                'original_start' => $fresh->scheduled_start,
                'proposed_start' => $proposedStart,
                'proposed_end' => $proposedEnd,
                'reason' => $reason,
                'status' => RescheduleRequest::PENDIENTE,
            ]);

            $fresh->update(['status' => 'reprogramacion_solicitada']);

            return $request;
        });

        // Los recordatorios no se tocan: la fecha sigue siendo la de siempre y la
        // visita sigue ocupando agenda, asi que los avisos ya materializados valen.

        $coordination = $this->coordinationUsers();

        if ($coordination->isNotEmpty()) {
            NotificationFacade::send($coordination, new RescheduleRequestedNotification($created));
        }

        return $created->load('requester:id,name');
    }

    /**
     * Cierra las solicitudes pendientes de una visita que cambio por otra via
     * (reprogramacion directa, cancelacion, check-in, vencimiento).
     *
     * No notifica: quien mueve la visita ya manda su propio aviso, y dos correos
     * en el mismo segundo diciendo lo mismo es ruido.
     *
     * @return int solicitudes cerradas
     */
    public function closePending(ScheduledVisit $visit, string $status, ?User $actor, string $notes): int
    {
        return RescheduleRequest::query()
            ->where('scheduled_visit_id', $visit->id)
            ->pending()
            ->update([
                'status' => $status,
                'resolved_by' => $actor?->id,
                'resolved_at' => now(),
                'resolution_notes' => $notes,
            ]);
    }

    /**
     * Coordinacion al completo. Aqui si entran master y super, al reves que en las
     * notificaciones de ScheduleService: esto no es una accion suya de la que ya se
     * hayan enterado, es una peticion que espera respuesta.
     *
     * @return \Illuminate\Support\Collection<int, User>
     */
    private function coordinationUsers(): \Illuminate\Support\Collection
    {
        return User::query()
            ->role(['master', 'super', 'coordinator'])
            ->where('active', true)
            ->get();
    }
}
