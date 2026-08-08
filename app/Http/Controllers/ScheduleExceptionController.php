<?php

namespace App\Http\Controllers;

use App\Models\ScheduledVisit;
use App\Models\ScheduleException;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Dias que se salen de la jornada: festivos (sin tecnico) y vacaciones o turnos
 * sueltos (con tecnico).
 */
class ScheduleExceptionController extends Controller
{
    /** Un rango de vacaciones largo se corta aqui: es un error de dedo, no un caso real. */
    private const MAX_RANGE_DAYS = 90;

    public function index(Request $request): JsonResponse
    {
        abort_if(! $request->user()->can('view_schedule'), 403, 'No autorizado.');

        $data = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            // 'generales' devuelve solo las que aplican a todos.
            'scope' => ['nullable', 'in:todas,generales,tecnico'],
        ]);

        $scope = $data['scope'] ?? 'todas';

        $exceptions = ScheduleException::query()
            ->with('user:id,name')
            ->when($scope === 'generales', fn ($q) => $q->whereNull('user_id'))
            // Con tecnico se devuelven tambien las generales: son las que de verdad
            // le aplican, y la ficha las enseña como heredadas.
            ->when($scope !== 'generales' && ! empty($data['user_id']),
                fn ($q) => $q->applyingTo((int) $data['user_id']))
            ->when($scope === 'tecnico' && ! empty($data['user_id']),
                fn ($q) => $q->where('user_id', $data['user_id']))
            ->when(! empty($data['from']) && ! empty($data['to']), fn ($q) => $q->betweenDates(
                CarbonImmutable::parse($data['from']),
                CarbonImmutable::parse($data['to']),
            ))
            // Sin rango, de hoy en adelante: el historial de festivos viejos no
            // le sirve a nadie.
            ->when(empty($data['from']), fn ($q) => $q->whereDate('date', '>=', now()->toDateString()))
            ->orderBy('date')
            ->get();

        return response()->json(['exceptions' => $exceptions]);
    }

    /**
     * Crea o actualiza. Acepta un rango (`date` + `date_end`) para no obligar a
     * meter una fila por dia en unas vacaciones.
     */
    public function store(Request $request): JsonResponse
    {
        abort_if(! $request->user()->can('manage_technician_schedules'), 403, 'No autorizado.');

        $data = $request->validate([
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'date' => ['required', 'date'],
            'date_end' => ['nullable', 'date', 'after_or_equal:date'],
            'working_hours' => ['nullable', 'array'],
            'working_hours.start' => ['required_with:working_hours', 'date_format:H:i'],
            'working_hours.end' => ['required_with:working_hours', 'date_format:H:i', 'after:working_hours.start'],
            'note' => ['nullable', 'string', 'max:255'],
        ], [
            'working_hours.end.after' => 'La hora de fin debe ser posterior a la de inicio.',
        ]);

        $from = CarbonImmutable::parse($data['date'])->startOfDay();
        $to = CarbonImmutable::parse($data['date_end'] ?? $data['date'])->startOfDay();

        abort_if(
            $from->diffInDays($to) + 1 > self::MAX_RANGE_DAYS,
            422,
            'El rango no puede pasar de '.self::MAX_RANGE_DAYS.' dias.',
        );

        $created = [];

        for ($day = $from; $day <= $to; $day = $day->addDay()) {
            // updateOrCreate y no create: volver a guardar el mismo dia lo corrige
            // en vez de dejar dos filas peleandose.
            $created[] = ScheduleException::updateOrCreate(
                ['user_id' => $data['user_id'] ?? null, 'date' => $day->toDateString()],
                ['working_hours' => $data['working_hours'] ?? null, 'note' => $data['note'] ?? null],
            );
        }

        return response()->json([
            'exceptions' => $created,
            // No se bloquea: coordinacion manda. Pero si el dia que acaba de
            // cerrar ya tenia visitas, tiene que enterarse ahora y no el lunes.
            'affected_visits' => $this->affectedVisits($data['user_id'] ?? null, $from, $to, $data['working_hours'] ?? null),
        ], 201);
    }

    public function destroy(Request $request, ScheduleException $scheduleException): JsonResponse
    {
        abort_if(! $request->user()->can('manage_technician_schedules'), 403, 'No autorizado.');

        $scheduleException->delete();

        return response()->json(['deleted' => true]);
    }

    /**
     * Visitas ya programadas que quedan fuera de la jornada por la excepcion nueva.
     *
     * @return array<int, array<string, mixed>>
     */
    private function affectedVisits(?int $userId, CarbonImmutable $from, CarbonImmutable $to, ?array $hours): array
    {
        return ScheduledVisit::query()
            ->with(['equipment:id,internal_code', 'technician:id,name'])
            ->blocking()
            ->whereBetween('scheduled_start', [$from->startOfDay(), $to->endOfDay()])
            ->when($userId, fn ($q) => $q->where('technician_id', $userId))
            ->orderBy('scheduled_start')
            ->get()
            ->filter(function (ScheduledVisit $visit) use ($hours) {
                if (! $hours) {
                    return true;   // el dia se cierra entero
                }

                // Con horario especial solo estorban las que se salen de el.
                return $visit->scheduled_start->format('H:i') < $hours['start']
                    || $visit->scheduled_end->format('H:i') > $hours['end'];
            })
            ->map(fn (ScheduledVisit $visit) => [
                'id' => $visit->id,
                'equipment_code' => $visit->equipment?->internal_code,
                'technician_name' => $visit->technician?->name,
                'scheduled_start' => $visit->scheduled_start,
            ])
            ->values()
            ->all();
    }
}
