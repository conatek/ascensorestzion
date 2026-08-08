<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreScheduledVisitRequest;
use App\Http\Requests\UpdateScheduledVisitRequest;
use App\Models\Equipment;
use App\Models\ScheduledVisit;
use App\Models\ScheduleSetting;
use App\Models\User;
use App\Services\ScheduleService;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Cronograma de coordinacion: el tablero donde se programan y mueven las visitas.
 * La agenda del tecnico y la del portal son otros controllers (fase 2), con su
 * propio scoping.
 */
class ScheduledVisitController extends Controller
{
    public function __construct(
        private ScheduleService $schedule,
    ) {}

    /**
     * Visitas en un rango. El calendario siempre manda from/to (el mes o la semana
     * visible); sin rango se responderia el historico entero.
     */
    public function index(Request $request): JsonResponse
    {
        abort_if(! $request->user()->can('view_schedule'), 403, 'No autorizado.');

        $validated = $request->validate([
            'from' => ['required', 'date'],
            'to' => ['required', 'date', 'after_or_equal:from'],
            'technician_id' => ['nullable', 'exists:users,id'],
            'client_id' => ['nullable', 'exists:clients,id'],
            'site_id' => ['nullable', 'exists:sites,id'],
            'equipment_id' => ['nullable', 'exists:equipment,id'],
            'status' => ['nullable', 'string'],
        ]);

        $visits = ScheduledVisit::query()
            // El drawer del calendario se pinta con estos mismos datos, sin volver
            // a pedir la visita: por eso van tambien la direccion de la sede y
            // quien la programo.
            ->with([
                'equipment:id,internal_code,equipment_type',
                'site:id,name,address',
                'client:id,business_name',
                'technician:id,name',
                'creator:id,name',
            ])
            ->between(
                CarbonImmutable::parse($validated['from']),
                CarbonImmutable::parse($validated['to']),
            )
            ->when($validated['technician_id'] ?? null, fn ($q, $v) => $q->forTechnician($v))
            ->when($validated['client_id'] ?? null, fn ($q, $v) => $q->forClient($v))
            ->when($validated['site_id'] ?? null, fn ($q, $v) => $q->where('site_id', $v))
            ->when($validated['equipment_id'] ?? null, fn ($q, $v) => $q->where('equipment_id', $v))
            ->when($validated['status'] ?? null, fn ($q, $v) => $q->whereIn('status', explode(',', $v)))
            ->orderBy('scheduled_start')
            ->get();

        return response()->json($visits);
    }

    public function store(StoreScheduledVisitRequest $request): JsonResponse
    {
        $visit = $this->schedule->create($request->validated(), $request->user());

        return response()->json($this->withRelations($visit), 201);
    }

    public function show(Request $request, ScheduledVisit $scheduledVisit): JsonResponse
    {
        abort_if(! $request->user()->can('view_schedule'), 403, 'No autorizado.');

        return response()->json($this->withRelations($scheduledVisit));
    }

    /**
     * Edicion desde el modal y tambien drag & drop del calendario. Mover siempre
     * revalida: arrastrar puede dejar la visita en el descanso o en fin de semana.
     */
    public function update(UpdateScheduledVisitRequest $request, ScheduledVisit $scheduledVisit): JsonResponse
    {
        $data = $request->validated();

        abort_if(
            in_array($scheduledVisit->status, ['completada', 'cancelada'], true),
            422,
            'Una visita completada o cancelada no se puede modificar.',
        );

        $technician = isset($data['technician_id'])
            ? User::findOrFail($data['technician_id'])
            : $scheduledVisit->technician;

        // Las fechas van juntas (lo fuerza required_with): o se mueve, o solo se
        // editan los campos sueltos sin tocar el horario.
        if (isset($data['scheduled_start'], $data['scheduled_end'])) {
            $this->schedule->reschedule(
                $scheduledVisit,
                CarbonImmutable::parse($data['scheduled_start']),
                CarbonImmutable::parse($data['scheduled_end']),
                $technician,
            );
        } elseif (isset($data['technician_id'])) {
            // Cambio de tecnico sin mover la hora: el hueco tiene que estar libre
            // en la agenda del nuevo.
            $this->schedule->reschedule(
                $scheduledVisit,
                CarbonImmutable::parse($scheduledVisit->scheduled_start),
                CarbonImmutable::parse($scheduledVisit->scheduled_end),
                $technician,
            );
        }

        $scheduledVisit->update(array_intersect_key($data, array_flip(['visit_type', 'notes'])));

        return response()->json($this->withRelations($scheduledVisit->refresh()));
    }

    public function cancel(Request $request, ScheduledVisit $scheduledVisit): JsonResponse
    {
        abort_if(! $request->user()->can('manage_schedule'), 403, 'No autorizado.');

        $data = $request->validate([
            'cancel_reason' => ['nullable', 'string', 'max:255'],
        ]);

        abort_if(
            $scheduledVisit->status === 'completada',
            422,
            'Una visita completada no se puede cancelar.',
        );

        $visit = $this->schedule->cancel($scheduledVisit, $data['cancel_reason'] ?? null);

        return response()->json($this->withRelations($visit));
    }

    /**
     * Tecnicos activos con su color y su jornada ya resuelta: el calendario necesita
     * la jornada para pintar el grid y las franjas muertas de cada columna.
     */
    public function technicians(Request $request): JsonResponse
    {
        abort_if(! $request->user()->can('view_schedule'), 403, 'No autorizado.');

        $technicians = User::role('technician')
            ->where('active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return response()->json([
            'technicians' => $technicians->values()->map(fn (User $t, int $i) => [
                'id' => $t->id,
                'name' => $t->name,
                'email' => $t->email,
                'color' => self::PALETTE[$i % count(self::PALETTE)],
                'working_window' => $this->schedule->workingWindowFor($t),
            ]),
            'defaults' => [
                'working_hours' => ScheduleSetting::get('working_hours'),
                'working_break' => ScheduleSetting::get('working_break'),
                'working_days' => ScheduleSetting::get('working_days'),
                'default_duration_minutes' => ScheduleSetting::get('default_duration_minutes'),
            ],
        ]);
    }

    /**
     * Duracion sugerida al elegir equipo en el modal: la del equipo o la global.
     */
    public function equipmentDuration(Request $request, Equipment $equipment): JsonResponse
    {
        abort_if(! $request->user()->can('view_schedule'), 403, 'No autorizado.');

        return response()->json([
            'equipment_id' => $equipment->id,
            'duration_minutes' => $this->schedule->defaultDurationFor($equipment),
            'is_custom' => $equipment->default_visit_duration_minutes !== null,
        ]);
    }

    /**
     * Colores de las columnas por tecnico. Verde de marca primero; el resto son
     * tonos que contrastan sobre blanco y entre si.
     */
    private const PALETTE = ['#30ab0a', '#2563eb', '#ba2831', '#7c3aed', '#ea580c', '#0891b2', '#c026d3', '#606060'];

    private function withRelations(ScheduledVisit $visit): ScheduledVisit
    {
        return $visit->load([
            'equipment:id,internal_code,equipment_type,site_id',
            'site:id,name,address',
            'client:id,business_name',
            'technician:id,name,email',
            'creator:id,name',
            // El drawer muestra que avisos salieron y cuales quedan: es la unica
            // forma de responder "¿al cliente se le avisó?" sin entrar a la base.
            'reminders' => fn ($q) => $q->with('user:id,name')->orderBy('send_at'),
        ]);
    }
}
