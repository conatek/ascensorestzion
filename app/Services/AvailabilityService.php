<?php

namespace App\Services;

use App\Models\ScheduledVisit;
use App\Models\ScheduleException;
use App\Models\ScheduleSetting;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;

/**
 * Espacios libres en la agenda de un tecnico, para que el cliente proponga una
 * fecha nueva sin ver la agenda de nadie.
 *
 * Es deliberadamente MAS estricto que ScheduleService::assertSlotIsFree(), nunca
 * mas laxo: ademas de la jornada y los solapes aplica el colchon de viaje entre
 * visitas y la antelacion minima. Todo horario que se ofrece aqui pasa el
 * validador; lo contrario —ofrecer un hueco que luego el backend rechaza— seria
 * un callejon sin salida para el cliente. Hay un test que fija esa invariante.
 */
class AvailabilityService
{
    public function __construct(private ScheduleService $schedule) {}

    /**
     * Horizonte completo para mover una visita concreta.
     *
     * La duracion sale de la propia visita y no del equipo: si coordinacion le
     * estiro el rango a 120 minutos, el hueco tiene que caber igual de ancho.
     *
     * @return array{days: array<int, array{date: string, is_working_day: bool, slot_count: int, reason: ?string}>, slots: array<string, array<int, array{value: string, end_value: string, label: string, end_label: string}>>}
     */
    public function forVisit(
        ScheduledVisit $visit,
        CarbonImmutable $from,
        CarbonImmutable $to,
        bool $enforceNotice = true,
    ): array {
        $duration = CarbonImmutable::parse($visit->scheduled_start)
            ->diffInMinutes(CarbonImmutable::parse($visit->scheduled_end));

        return $this->slotsInRange(
            $visit->technician,
            $from,
            $to,
            (int) $duration,
            $visit->id,
            $enforceNotice ? $this->earliestAllowed() : CarbonImmutable::now(),
        );
    }

    /**
     * Nucleo del calculo: un dia por cada fecha del rango, con sus horarios libres.
     *
     * @param  int|null  $ignoreVisitId  la visita que se esta moviendo, que no se bloquea a si misma
     * @param  CarbonImmutable|null  $notBefore  nada antes de este instante
     */
    public function slotsInRange(
        User $technician,
        CarbonImmutable $from,
        CarbonImmutable $to,
        int $durationMinutes,
        ?int $ignoreVisitId = null,
        ?CarbonImmutable $notBefore = null,
    ): array {
        // Las dos, una sola vez: resolverlas dentro del bucle serian sesenta
        // consultas para responder treinta veces lo mismo.
        $baseWindow = $this->schedule->workingWindowFor($technician);
        $exceptions = ScheduleException::mapForRange($technician->id, $from->startOfDay(), $to->startOfDay());

        $step = max(5, (int) ScheduleSetting::get('availability_slot_minutes'));
        $buffer = max(0, (int) ScheduleSetting::get('travel_buffer_minutes'));

        $busy = $this->busyIntervalsFor($technician, $from, $to, $buffer, $ignoreVisitId);

        $days = [];
        $slots = [];

        for ($day = $from->startOfDay(); $day <= $to->startOfDay(); $day = $day->addDay()) {
            $key = $day->toDateString();
            $window = $this->schedule->applyException($baseWindow, $exceptions[$key] ?? null, $day);
            $isWorkingDay = in_array($day->dayOfWeekIso, $window['days'], true);

            $found = $isWorkingDay
                ? $this->daySlots($window, $day, $durationMinutes, $step, $busy[$key] ?? [], $notBefore)
                : ['slots' => [], 'candidates' => 0, 'blocked_by_notice' => 0];

            if ($found['slots']) {
                $slots[$key] = $found['slots'];
            }

            $days[] = [
                'date' => $key,
                'is_working_day' => $isWorkingDay,
                'slot_count' => count($found['slots']),
                'reason' => $this->reasonFor($isWorkingDay, $found, $window['exception']),
                // Para que el portal pueda decir "festivo" y no un generico "tu
                // tecnico no trabaja ese dia".
                'exception_note' => $window['exception']['note'] ?? null,
            ];
        }

        return ['days' => $days, 'slots' => $slots];
    }

    /**
     * El horario propuesto tiene que ser uno de los que el sistema habria ofrecido.
     *
     * Es la primera de las dos validaciones (la segunda es assertSlotIsFree al
     * aprobar) y es la que impide que un POST a mano se salte la rejilla: sin ella
     * un cliente podria proponer las 03:00 de un domingo con solo cambiar el JSON.
     *
     * @throws ValidationException
     */
    public function assertProposalIsOffered(
        ScheduledVisit $visit,
        CarbonImmutable $start,
        bool $enforceNotice = true,
    ): void {
        $day = $start->startOfDay();
        $offered = $this->forVisit($visit, $day, $day, $enforceNotice);
        $values = array_column($offered['slots'][$day->toDateString()] ?? [], 'value');

        if (! in_array($start->format('Y-m-d H:i'), $values, true)) {
            throw ValidationException::withMessages([
                'proposed_start' => ['Ese horario ya no esta disponible. Elige otro.'],
            ]);
        }
    }

    /** El instante mas temprano que el cliente puede proponer. */
    public function earliestAllowed(): CarbonImmutable
    {
        return CarbonImmutable::now()->addHours((int) ScheduleSetting::get('min_reschedule_notice_hours'));
    }

    /**
     * Horarios libres de un dia laborable.
     *
     * @param  array<int, array{0: CarbonImmutable, 1: CarbonImmutable}>  $busy
     * @return array{slots: array<int, array<string, string>>, candidates: int, blocked_by_notice: int}
     */
    private function daySlots(
        array $window,
        CarbonImmutable $day,
        int $duration,
        int $step,
        array $busy,
        ?CarbonImmutable $notBefore,
    ): array {
        $dayStart = $this->atTime($day, $window['start']);
        $dayEnd = $this->atTime($day, $window['end']);

        $forbidden = $busy;

        // El descanso entra SIN colchon: no es un desplazamiento, es una pausa. Con
        // colchon a los lados dejaria muerto de 12:30 a 14:30 y estaria escondiendo
        // horarios que assertSlotIsFree acepta sin rechistar.
        if ($window['break_start'] && $window['break_end']) {
            $forbidden[] = [
                $this->atTime($day, $window['break_start']),
                $this->atTime($day, $window['break_end']),
            ];
        }

        $slots = [];
        $candidates = 0;
        $blockedByNotice = 0;

        for ($start = $dayStart; $start->addMinutes($duration) <= $dayEnd; $start = $start->addMinutes($step)) {
            $end = $start->addMinutes($duration);
            $candidates++;

            if ($notBefore && $start < $notBefore) {
                $blockedByNotice++;

                continue;
            }

            foreach ($forbidden as [$busyStart, $busyEnd]) {
                // Cruce estricto, el mismo criterio del validador: dos visitas que
                // se tocan por el extremo no se solapan.
                if ($start < $busyEnd && $end > $busyStart) {
                    continue 2;
                }
            }

            $slots[] = [
                'value' => $start->format('Y-m-d H:i'),
                'end_value' => $end->format('Y-m-d H:i'),
                'label' => $start->format('H:i'),
                'end_label' => $end->format('H:i'),
            ];
        }

        return ['slots' => $slots, 'candidates' => $candidates, 'blocked_by_notice' => $blockedByNotice];
    }

    /**
     * Visitas que ocupan la agenda del tecnico en el rango, ya ensanchadas con el
     * colchon de viaje y agrupadas por dia.
     *
     * @return array<string, array<int, array{0: CarbonImmutable, 1: CarbonImmutable}>>
     */
    private function busyIntervalsFor(
        User $technician,
        CarbonImmutable $from,
        CarbonImmutable $to,
        int $buffer,
        ?int $ignoreVisitId,
    ): array {
        $visits = ScheduledVisit::query()
            ->forTechnician($technician->id)
            ->blocking()
            // Ensanchar la consulta tambien: una visita que termina justo antes del
            // rango proyecta su colchon dentro de el.
            ->between($from->startOfDay()->subMinutes($buffer + 1), $to->endOfDay()->addMinutes($buffer + 1))
            ->when($ignoreVisitId, fn ($q) => $q->whereKeyNot($ignoreVisitId))
            ->orderBy('scheduled_start')
            ->get(['id', 'scheduled_start', 'scheduled_end']);

        $grouped = [];

        foreach ($visits as $visit) {
            // Los casts devuelven Carbon en la zona de la app; agrupar por la cadena
            // serializada (UTC) mandaria todo lo de despues de las 19:00 al dia
            // siguiente.
            $start = CarbonImmutable::parse($visit->scheduled_start)->subMinutes($buffer);
            $end = CarbonImmutable::parse($visit->scheduled_end)->addMinutes($buffer);

            for ($day = $start->startOfDay(); $day <= $end->startOfDay(); $day = $day->addDay()) {
                $grouped[$day->toDateString()][] = [$start, $end];
            }
        }

        return $grouped;
    }

    /**
     * Por que un dia se quedo sin horarios. Se devuelve siempre para que el modal
     * diga algo util en vez de enseñar un vacio mudo.
     */
    private function reasonFor(bool $isWorkingDay, array $found, ?array $exception): ?string
    {
        if (! $isWorkingDay) {
            // Distinguirlo del domingo de siempre: "es festivo" y "tu tecnico no
            // trabaja los domingos" piden respuestas distintas del cliente.
            return $exception ? 'excepcion' : 'no_laborable';
        }

        if ($found['slots']) {
            return null;
        }

        if ($found['candidates'] === 0) {
            return 'no_cabe';
        }

        return $found['blocked_by_notice'] === $found['candidates'] ? 'antelacion' : 'agenda_llena';
    }

    private function atTime(CarbonImmutable $day, string $time): CarbonImmutable
    {
        [$h, $m] = array_pad(explode(':', $time), 2, '0');

        return $day->setTime((int) $h, (int) $m);
    }
}
