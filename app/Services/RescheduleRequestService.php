<?php

namespace App\Services;

use App\Models\RescheduleRequest;
use App\Models\ScheduledVisit;
use App\Models\ScheduleSetting;
use App\Models\User;
use App\Notifications\RescheduleRequestedNotification;
use App\Notifications\RescheduleResolvedNotification;
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
     * Coordinacion aprueba: la visita se mueve a la fecha propuesta.
     *
     * Sin transaccion envolvente a proposito. reschedule() despacha jobs encolados
     * y con QUEUE_CONNECTION=database un worker puede tomarlos antes de que el
     * commit externo cierre, leyendo un estado a medias (las notificaciones usan
     * fresh()). El precio es una ventana minuscula con la visita movida y la
     * solicitud aun pendiente, y eso lo cubre la guarda de idempotencia de abajo.
     *
     * @throws ValidationException
     */
    public function approve(
        RescheduleRequest $request,
        User $actor,
        bool $force = false,
        ?string $notes = null,
    ): RescheduleRequest {
        abort_if(! $request->isPending(), 422, 'Esta solicitud ya fue resuelta.');

        $visit = $request->scheduledVisit;
        $start = CarbonImmutable::parse($request->proposed_start);
        $end = CarbonImmutable::parse($request->proposed_end);

        // Segunda validacion: entre que el cliente la mando y ahora, el hueco pudo
        // ocuparse. Coordinacion puede seguir adelante, pero avisada.
        if (! $force) {
            $this->schedule->assertSlotIsFree($visit->technician, $start, $end, $visit->id);
        }

        $this->schedule->reschedule($visit, $start, $end, $visit->technician, force: $force);

        DB::transaction(function () use ($visit, $request, $actor, $notes) {
            // reschedule() mueve las fechas pero no toca el estado: si no se hace
            // aqui, la visita se queda ambar para siempre.
            $visit->update(['status' => 'programada']);
            $request->resolve(RescheduleRequest::APROBADA, $actor, $notes);
        });

        $this->notifyResolution($request->refresh());

        return $request->load(['requester:id,name', 'resolver:id,name']);
    }

    /** Coordinacion rechaza: la visita se queda donde estaba, con sus recordatorios. */
    public function reject(RescheduleRequest $request, User $actor, string $notes): RescheduleRequest
    {
        abort_if(! $request->isPending(), 422, 'Esta solicitud ya fue resuelta.');

        $visit = $request->scheduledVisit;

        DB::transaction(function () use ($visit, $request, $actor, $notes) {
            $request->resolve(RescheduleRequest::RECHAZADA, $actor, $notes);

            if ($visit->status === 'reprogramacion_solicitada') {
                $visit->update(['status' => 'programada']);
            }
        });

        $this->notifyResolution($request->refresh());

        return $request->load(['requester:id,name', 'resolver:id,name']);
    }

    /**
     * ¿El tecnico sigue libre en el horario propuesto? Es lo que pinta la bandeja
     * en verde o en rojo.
     *
     * Se resuelve con el mismo validador que usara la aprobacion, envuelto en un
     * try/catch: una consulta paralela acabaria discrepando de el.
     *
     * @return array{technician_free: bool, problems: string[]}
     */
    public function availabilityCheck(RescheduleRequest $request): array
    {
        $visit = $request->scheduledVisit;

        if (! $visit || ! $visit->technician) {
            return ['technician_free' => false, 'problems' => ['La visita ya no tiene tecnico asignado.']];
        }

        try {
            $this->schedule->assertSlotIsFree(
                $visit->technician,
                CarbonImmutable::parse($request->proposed_start),
                CarbonImmutable::parse($request->proposed_end),
                $visit->id,
            );

            return ['technician_free' => true, 'problems' => []];
        } catch (ValidationException $e) {
            return [
                'technician_free' => false,
                'problems' => $e->validator->errors()->get('scheduled_start'),
            ];
        }
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

    /** Avisa al cliente y al tecnico, con la misma regla de partes de siempre. */
    private function notifyResolution(RescheduleRequest $request): void
    {
        $this->schedule->notifyParties(
            $request->scheduledVisit,
            new RescheduleResolvedNotification($request),
        );
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
