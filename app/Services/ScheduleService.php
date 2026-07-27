<?php

namespace App\Services;

use App\Models\Equipment;
use App\Models\ScheduledVisit;
use App\Models\ScheduleSetting;
use App\Models\TechnicianSchedule;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

/**
 * Reglas del cronograma: jornada, duracion y validacion de una cita.
 *
 * Es la unica fuente de verdad de la jornada. La usan la validacion al crear/mover
 * una visita, el grid del calendario y (desde la fase 4) el calculo de espacios
 * libres del portal; si cada uno resolviera la jornada por su cuenta acabarian
 * discrepando y el portal ofreceria horarios que el backend rechaza.
 */
class ScheduleService
{
    /**
     * Jornada efectiva de un tecnico: la global con los overrides propios encima.
     *
     * Cada campo se resuelve por separado a proposito, para poder cambiarle a un
     * tecnico solo el horario y que siga heredando los dias.
     *
     * @return array{days: int[], start: string, end: string, break_start: ?string, break_end: ?string}
     */
    public function workingWindowFor(User $technician): array
    {
        $hours = ScheduleSetting::get('working_hours');
        $break = ScheduleSetting::get('working_break');

        $window = [
            'days' => ScheduleSetting::get('working_days'),
            'start' => $hours['start'] ?? '08:00',
            'end' => $hours['end'] ?? '18:00',
            'break_start' => $break['start'] ?? null,
            'break_end' => $break['end'] ?? null,
        ];

        $own = TechnicianSchedule::query()->where('user_id', $technician->id)->first();

        if (! $own || ! $own->enabled) {
            return $window;
        }

        if (! empty($own->working_days)) {
            $window['days'] = $own->working_days;
        }

        if (! empty($own->working_hours['start']) && ! empty($own->working_hours['end'])) {
            $window['start'] = $own->working_hours['start'];
            $window['end'] = $own->working_hours['end'];
        }

        // El descanso se sobrescribe en bloque: una fila con break_start nulo es un
        // tecnico SIN descanso, no un tecnico que hereda el descanso global. Si no
        // fuera asi no habria forma de quitarselo a nadie.
        $window['break_start'] = $this->normalizeTime($own->break_start);
        $window['break_end'] = $this->normalizeTime($own->break_end);

        return $window;
    }

    /**
     * Duracion por defecto en minutos: la del equipo si la tiene, si no la global.
     * El tercer nivel (el rango explicito de la visita) lo decide quien agenda.
     */
    public function defaultDurationFor(?Equipment $equipment = null): int
    {
        return $equipment?->default_visit_duration_minutes
            ?: (int) ScheduleSetting::get('default_duration_minutes');
    }

    /**
     * Programa una visita. Deriva sede y cliente del equipo (columnas desnormalizadas,
     * patron de service_reports) para no depender de que quien llama los mande bien.
     *
     * @param  array{equipment_id:int, technician_id:int, scheduled_start:string, scheduled_end:string, visit_type?:string, notes?:string}  $data
     *
     * @throws ValidationException
     */
    public function create(array $data, User $actor): ScheduledVisit
    {
        $equipment = Equipment::with('site')->findOrFail($data['equipment_id']);
        $technician = User::findOrFail($data['technician_id']);
        $start = CarbonImmutable::parse($data['scheduled_start']);
        $end = CarbonImmutable::parse($data['scheduled_end']);

        $this->assertSlotIsFree($technician, $start, $end);

        return ScheduledVisit::create([
            'equipment_id' => $equipment->id,
            'site_id' => $equipment->site_id,
            'client_id' => $equipment->site->client_id,
            'technician_id' => $technician->id,
            'scheduled_start' => $start,
            'scheduled_end' => $end,
            'visit_type' => $data['visit_type'] ?? 'preventivo',
            'notes' => $data['notes'] ?? null,
            'created_by' => $actor->id,
        ]);
    }

    /**
     * Mueve una visita (edicion del modal o drag & drop del calendario). Revalida
     * siempre: arrastrar tambien puede dejarla en el descanso o en sabado.
     *
     * @throws ValidationException
     */
    public function reschedule(
        ScheduledVisit $visit,
        CarbonImmutable $start,
        CarbonImmutable $end,
        ?User $technician = null,
    ): ScheduledVisit {
        $technician ??= $visit->technician;

        $this->assertSlotIsFree($technician, $start, $end, $visit->id);

        $visit->update([
            'technician_id' => $technician->id,
            'scheduled_start' => $start,
            'scheduled_end' => $end,
        ]);

        return $visit->refresh();
    }

    public function cancel(ScheduledVisit $visit, ?string $reason = null): ScheduledVisit
    {
        $visit->update([
            'status' => 'cancelada',
            'cancelled_at' => now(),
            'cancel_reason' => $reason,
        ]);

        return $visit->refresh();
    }

    /**
     * Valida que la cita quepa: dentro de la jornada del tecnico, en dia laborable,
     * sin pisar el descanso y sin solaparse con otra visita suya.
     *
     * @param  int|null  $ignoreVisitId  visita que se esta editando (no cuenta como solape)
     *
     * @throws ValidationException
     */
    public function assertSlotIsFree(
        User $technician,
        CarbonImmutable $start,
        CarbonImmutable $end,
        ?int $ignoreVisitId = null,
    ): void {
        $errors = array_merge(
            $this->workingHoursErrors($technician, $start, $end),
            $this->overlapErrors($technician, $start, $end, $ignoreVisitId),
        );

        if ($errors) {
            throw ValidationException::withMessages(['scheduled_start' => $errors]);
        }
    }

    /**
     * @return string[]
     */
    private function workingHoursErrors(User $technician, CarbonImmutable $start, CarbonImmutable $end): array
    {
        if ($end <= $start) {
            return ['La hora de fin debe ser posterior a la de inicio.'];
        }

        // Una visita que cruza la medianoche no cabe en ninguna jornada; ademas
        // romperia las comparaciones de hora de abajo, que asumen un mismo dia.
        if (! $start->isSameDay($end)) {
            return ['La visita debe empezar y terminar el mismo dia.'];
        }

        $window = $this->workingWindowFor($technician);
        $errors = [];

        if (! in_array($start->dayOfWeekIso, $window['days'], true)) {
            $errors[] = sprintf('%s no es un dia laborable para %s.',
                $this->dayName($start->dayOfWeekIso), $technician->name);
        }

        $dayStart = $this->atTime($start, $window['start']);
        $dayEnd = $this->atTime($start, $window['end']);

        if ($start < $dayStart || $end > $dayEnd) {
            $errors[] = sprintf('La jornada de %s es de %s a %s.',
                $technician->name, $window['start'], $window['end']);
        }

        if ($window['break_start'] && $window['break_end']) {
            $breakStart = $this->atTime($start, $window['break_start']);
            $breakEnd = $this->atTime($start, $window['break_end']);

            // Cruce de rangos: basta con que se toquen para invadir el descanso.
            if ($start < $breakEnd && $end > $breakStart) {
                $errors[] = sprintf('La visita cae en el descanso (%s a %s).',
                    $window['break_start'], $window['break_end']);
            }
        }

        return $errors;
    }

    /**
     * @return string[]
     */
    private function overlapErrors(
        User $technician,
        CarbonImmutable $start,
        CarbonImmutable $end,
        ?int $ignoreVisitId,
    ): array {
        $clashes = $this->overlapping($technician, $start, $end, $ignoreVisitId);

        if ($clashes->isEmpty()) {
            return [];
        }

        return $clashes->map(fn (ScheduledVisit $v) => sprintf(
            'Se cruza con %s (%s a %s).',
            $v->equipment?->internal_code ?? 'otra visita',
            $v->scheduled_start->format('H:i'),
            $v->scheduled_end->format('H:i'),
        ))->all();
    }

    /**
     * Visitas del tecnico que pisan el rango dado.
     *
     * @return Collection<int, ScheduledVisit>
     */
    public function overlapping(
        User $technician,
        CarbonImmutable $start,
        CarbonImmutable $end,
        ?int $ignoreVisitId = null,
    ): Collection {
        return ScheduledVisit::query()
            ->with('equipment:id,internal_code')
            ->forTechnician($technician->id)
            ->blocking()
            ->between($start, $end)
            ->when($ignoreVisitId, fn ($q) => $q->whereKeyNot($ignoreVisitId))
            ->orderBy('scheduled_start')
            ->get();
    }

    /** Combina la fecha de $day con una hora "HH:MM". */
    private function atTime(CarbonImmutable $day, string $time): CarbonImmutable
    {
        [$h, $m] = array_pad(explode(':', $time), 2, '0');

        return $day->setTime((int) $h, (int) $m);
    }

    /** Las columnas `time` vuelven como "13:00:00"; la jornada se maneja en "HH:MM". */
    private function normalizeTime(?string $time): ?string
    {
        return $time ? substr($time, 0, 5) : null;
    }

    private function dayName(int $isoDay): string
    {
        return [
            1 => 'Lunes', 2 => 'Martes', 3 => 'Miercoles', 4 => 'Jueves',
            5 => 'Viernes', 6 => 'Sabado', 7 => 'Domingo',
        ][$isoDay] ?? "Dia $isoDay";
    }
}
