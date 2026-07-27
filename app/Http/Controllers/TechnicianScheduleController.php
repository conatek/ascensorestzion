<?php

namespace App\Http\Controllers;

use App\Models\TechnicianSchedule;
use App\Models\User;
use App\Services\ScheduleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Jornada propia de un tecnico, para los casos especiales (turno distinto, sin
 * descanso, sabados). Sin fila el tecnico hereda la jornada global.
 */
class TechnicianScheduleController extends Controller
{
    public function __construct(
        private ScheduleService $schedule,
    ) {}

    /**
     * Devuelve el override guardado (si lo hay) y la jornada ya resuelta, que es
     * lo que de verdad se aplica.
     */
    public function show(Request $request, User $user): JsonResponse
    {
        abort_if(! $request->user()->can('manage_technician_schedules'), 403, 'No autorizado.');

        return response()->json([
            'user_id' => $user->id,
            'override' => TechnicianSchedule::query()->where('user_id', $user->id)->first(),
            'resolved' => $this->schedule->workingWindowFor($user),
        ]);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        abort_if(! $request->user()->can('manage_technician_schedules'), 403, 'No autorizado.');

        $data = $request->validate([
            'enabled' => ['nullable', 'boolean'],
            'working_days' => ['nullable', 'array', 'max:7'],
            'working_days.*' => ['integer', 'between:1,7'],
            'working_hours' => ['nullable', 'array'],
            'working_hours.start' => ['required_with:working_hours', 'date_format:H:i'],
            'working_hours.end' => ['required_with:working_hours', 'date_format:H:i', 'after:working_hours.start'],
            'break_start' => ['nullable', 'date_format:H:i', 'required_with:break_end'],
            'break_end' => ['nullable', 'date_format:H:i', 'after:break_start', 'required_with:break_start'],
        ], [
            'working_hours.end.after' => 'La hora de fin de jornada debe ser posterior a la de inicio.',
            'break_end.after' => 'El fin del descanso debe ser posterior a su inicio.',
        ]);

        $schedule = TechnicianSchedule::updateOrCreate(
            ['user_id' => $user->id],
            [
                'enabled' => $data['enabled'] ?? true,
                'working_days' => $data['working_days'] ?? null,
                'working_hours' => $data['working_hours'] ?? null,
                'break_start' => $data['break_start'] ?? null,
                'break_end' => $data['break_end'] ?? null,
            ],
        );

        return response()->json([
            'user_id' => $user->id,
            'override' => $schedule,
            'resolved' => $this->schedule->workingWindowFor($user),
        ]);
    }

    /** Quita el override: el tecnico vuelve a la jornada global. */
    public function destroy(Request $request, User $user): JsonResponse
    {
        abort_if(! $request->user()->can('manage_technician_schedules'), 403, 'No autorizado.');

        TechnicianSchedule::query()->where('user_id', $user->id)->delete();

        return response()->json([
            'user_id' => $user->id,
            'override' => null,
            'resolved' => $this->schedule->workingWindowFor($user),
        ]);
    }
}
