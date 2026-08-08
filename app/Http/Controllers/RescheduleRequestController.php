<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRescheduleRequestRequest;
use App\Models\ScheduledVisit;
use App\Services\RescheduleRequestService;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;

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
}
