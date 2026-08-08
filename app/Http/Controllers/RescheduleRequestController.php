<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRescheduleRequestRequest;
use App\Models\RescheduleRequest;
use App\Models\ScheduledVisit;
use App\Services\RescheduleRequestService;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RescheduleRequestController extends Controller
{
    public function __construct(private RescheduleRequestService $requests) {}

    /** Portal: el cliente propone una fecha nueva para su visita. */
    public function store(StoreRescheduleRequestRequest $request, ScheduledVisit $scheduledVisit): JsonResponse
    {
        $clientId = $request->user()->client_id;
        abort_if((int) $scheduledVisit->client_id !== (int) $clientId, 403, 'No autorizado.');

        $created = $this->requests->request(
            $scheduledVisit,
            $request->user(),
            CarbonImmutable::parse($request->validated('proposed_start')),
            $request->validated('reason'),
        );

        return response()->json([
            'request' => $created,
            'visit' => $scheduledVisit->refresh()->load([
                'equipment:id,internal_code,equipment_type',
                'site:id,name,address',
                'technician:id,name',
                'pendingRescheduleRequest',
            ]),
            'message' => 'Enviamos tu solicitud a coordinación.',
        ], 201);
    }

    /**
     * Bandeja de coordinacion. Cada pendiente viene con si el tecnico sigue libre,
     * que es lo unico que hace falta para decidir sin abrir el calendario.
     */
    public function index(Request $request): JsonResponse
    {
        abort_if(! $request->user()->can('view_schedule'), 403, 'No autorizado.');

        $validated = $request->validate([
            'status' => ['nullable', Rule::in([
                RescheduleRequest::PENDIENTE, RescheduleRequest::APROBADA, RescheduleRequest::RECHAZADA, 'todas',
            ])],
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $status = $validated['status'] ?? RescheduleRequest::PENDIENTE;

        $requests = RescheduleRequest::query()
            ->with([
                'requester:id,name',
                'resolver:id,name',
                'scheduledVisit.equipment:id,internal_code',
                'scheduledVisit.site:id,name',
                'scheduledVisit.client:id,business_name',
                'scheduledVisit.technician:id,name',
            ])
            ->when($status !== 'todas', fn ($q) => $q->where('status', $status))
            // Por el horario propuesto y no por la fecha de creacion: lo urgente es
            // lo que ocurre antes, no lo que se pidio antes.
            ->orderBy('proposed_start')
            ->limit($validated['limit'] ?? 50)
            ->get();

        // Solo las pendientes: revalidar el hueco de una resuelta es una consulta
        // por fila para pintar un dato que ya no cambia nada.
        $requests->each(fn (RescheduleRequest $r) => $r->setAttribute(
            'availability',
            $r->isPending() ? $this->requests->availabilityCheck($r) : null,
        ));

        return response()->json([
            'pending_count' => RescheduleRequest::query()->pending()->count(),
            'requests' => $requests,
        ]);
    }

    public function approve(Request $request, RescheduleRequest $rescheduleRequest): JsonResponse
    {
        abort_if(! $request->user()->can('manage_schedule'), 403, 'No autorizado.');

        $data = $request->validate([
            'force' => ['nullable', 'boolean'],
            'resolution_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $resolved = $this->requests->approve(
            $rescheduleRequest,
            $request->user(),
            (bool) ($data['force'] ?? false),
            $data['resolution_notes'] ?? null,
        );

        return response()->json([
            'request' => $resolved,
            'visit' => $this->visitPayload($rescheduleRequest->scheduledVisit->refresh()),
        ]);
    }

    public function reject(Request $request, RescheduleRequest $rescheduleRequest): JsonResponse
    {
        abort_if(! $request->user()->can('manage_schedule'), 403, 'No autorizado.');

        // Obligatorio: al cliente le llega un correo con el motivo, y "porque no"
        // no es una respuesta.
        $data = $request->validate([
            'resolution_notes' => ['required', 'string', 'max:1000'],
        ], [
            'resolution_notes.required' => 'Explica al cliente por que no se puede.',
        ]);

        $resolved = $this->requests->reject($rescheduleRequest, $request->user(), $data['resolution_notes']);

        return response()->json([
            'request' => $resolved,
            'visit' => $this->visitPayload($rescheduleRequest->scheduledVisit->refresh()),
        ]);
    }

    private function visitPayload(ScheduledVisit $visit): ScheduledVisit
    {
        return $visit->load([
            'equipment:id,internal_code,equipment_type,site_id',
            'site:id,name,address',
            'client:id,business_name',
            'technician:id,name,email',
        ]);
    }
}
