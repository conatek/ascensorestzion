<?php

namespace App\Http\Controllers;

use App\Events\EquipmentAccessBlocked;
use App\Events\TechnicianCheckedIn;
use App\Models\Equipment;
use App\Models\TechnicianCheckin;
use App\Models\User;
use App\Notifications\EquipmentAccessBlockedNotification;
use App\Notifications\TechnicianCheckedInNotification;
use App\Services\EquipmentAccessService;
use App\Services\ScheduleService;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;

class TechnicianCheckinController extends Controller
{
    public function __construct(
        private ScheduleService $schedule,
        private EquipmentAccessService $access,
    ) {}

    /**
     * Agenda del dia para decidir el bloqueo sin conexion. El telefono la guarda
     * cada vez que la app abre con senal.
     */
    public function accessAgenda(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_if(! $user->can('checkin_equipment'), 403, 'No autorizado.');

        return response()->json($this->access->agendaFor($user, now()));
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_if(! $user->can('checkin_equipment'), 403, 'No autorizado.');

        $validated = $request->validate([
            'equipment_code' => ['required', 'string', 'max:50'],
            'method' => ['required', 'in:qr,manual'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'accuracy' => ['nullable', 'numeric', 'min:0'],
            'is_emergency' => ['boolean'],
            'call_received_at' => ['nullable', 'date'],
            'client_uuid' => ['nullable', 'uuid'],
            'checked_in_at' => ['nullable', 'date'],
            // Lo que decidio el telefono al registrar sin conexion
            'offline_check' => ['nullable', 'in:permitido,sin_agenda'],
        ]);

        // Idempotencia: si ya existe un check-in con este client_uuid (sync reintentado),
        // devolverlo sin duplicar ni re-notificar.
        if (! empty($validated['client_uuid'])) {
            $existing = TechnicianCheckin::where('client_uuid', $validated['client_uuid'])->first();
            if ($existing) {
                return $this->checkinResponse($existing, 200);
            }
        }

        // Buscar equipo por internal_code o customer_code
        $equipment = Equipment::where('internal_code', $validated['equipment_code'])
            ->orWhere('customer_code', $validated['equipment_code'])
            ->first();

        if (! $equipment) {
            return response()->json([
                'message' => 'Equipo no encontrado con el código proporcionado.',
            ], 404);
        }

        if ($equipment->status === 'retirado') {
            return response()->json([
                'message' => 'Este equipo está marcado como retirado.',
            ], 422);
        }

        $now = now();
        // Un check-in con hora propia viene de la cola sin conexion: ya ocurrio en
        // sitio, asi que no se rechaza; se verifica y, si no cuadra, se marca.
        $isDeferred = ! empty($validated['checked_in_at']) || ! empty($validated['client_uuid']);
        // El telefono manda ISO en UTC ("...Z"): sin pasarlo a la zona de la app,
        // Eloquent guardaba la hora UTC como si fuera local (5 h de mas).
        $checkedInAt = ! empty($validated['checked_in_at'])
            ? \Carbon\Carbon::parse($validated['checked_in_at'])->setTimezone(config('app.timezone'))
            : $now;

        $isEmergency = (bool) ($validated['is_emergency'] ?? false);
        $access = $this->access->evaluate($equipment, $user, $checkedInAt, $isEmergency);

        if (! $access['allowed'] && ! $isDeferred) {
            $this->notifyBlocked($equipment, $user, $access);

            return response()->json([
                'code' => 'access_locked',
                'reason' => $access['status'],
                'message' => $access['message'],
                'scheduled_start' => $access['visit']?->scheduled_start?->toIso8601String(),
            ], 423);
        }

        $accessStatus = $access['status'];
        $needsReview = false;
        if ($isDeferred) {
            // Hora del telefono por delante del servidor: el reloj se toco.
            if ($checkedInAt->gt($now->copy()->addMinutes(5))) {
                $accessStatus = EquipmentAccessService::RELOJ_INCONSISTENTE;
                $needsReview = true;
            }
            if (! $access['allowed'] || ($validated['offline_check'] ?? null) === 'sin_agenda') {
                $needsReview = true;
            }
        }

        // Hora de la llamada: la que digito el tecnico o, si no, la que anoto
        // coordinacion al programar el correctivo.
        $callTime = $validated['call_received_at'] ?? $access['visit']?->call_received_at;

        $responseTimeMinutes = null;
        if ($callTime) {
            $responseTimeMinutes = (int) \Carbon\Carbon::parse($callTime)->diffInMinutes($checkedInAt);
        }

        // Calcular proximidad respecto a la sede del equipo (advertir y registrar)
        $proximity = null;
        $site = $equipment->site()->first();
        if ($site && $site->latitude && $site->longitude && ($validated['latitude'] ?? null) !== null) {
            $distance = $this->haversineDistance(
                (float) $validated['latitude'],
                (float) $validated['longitude'],
                (float) $site->latitude,
                (float) $site->longitude,
            );
            $radius = $site->geo_radius_meters ?? 500;
            $proximity = [
                'within_radius' => $distance <= $radius,
                'distance_meters' => (int) round($distance),
                'radius_meters' => $radius,
            ];
        }

        $checkin = TechnicianCheckin::create([
            'equipment_id' => $equipment->id,
            'scheduled_visit_id' => $access['visit']?->id,
            'technician_id' => $user->id,
            'client_uuid' => $validated['client_uuid'] ?? null,
            'checked_in_at' => $checkedInAt,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'accuracy' => $validated['accuracy'] ?? null,
            'distance_meters' => $proximity['distance_meters'] ?? null,
            'within_radius' => $proximity['within_radius'] ?? null,
            'method' => $validated['method'],
            'access_status' => $accessStatus,
            'offline_check' => $isDeferred ? ($validated['offline_check'] ?? null) : null,
            'needs_review' => $needsReview,
            'is_emergency' => $isEmergency || (bool) $access['visit']?->call_received_at,
            'call_received_at' => $callTime,
            'response_time_minutes' => $responseTimeMinutes,
        ]);

        // El cronograma deja de ser un plan: la visita programada de ese equipo
        // pasa a en_curso en cuanto el tecnico llega.
        $this->schedule->startVisitFor(
            $equipment->id,
            $user->id,
            CarbonImmutable::parse($checkedInAt),
        );

        // Notificar al equipo de operación: master, coordinación y super (activos).
        $recipients = User::role(['master', 'coordinator', 'super'])->where('active', true)->get();
        $recipientIds = $recipients->pluck('id')->toArray();

        if ($recipientIds) {
            TechnicianCheckedIn::dispatch($checkin, $recipientIds);
            Notification::send($recipients, new TechnicianCheckedInNotification($checkin));
        }

        return $this->checkinResponse($checkin, 201);
    }

    /**
     * Respuesta estándar de un check-in (equipo + último reporte + proximidad).
     * Reutilizada por la creación y por la respuesta idempotente del sync.
     */
    private function checkinResponse(TechnicianCheckin $checkin, int $status): JsonResponse
    {
        $checkin->loadMissing([
            'equipment.site:id,name,address,city,client_id,latitude,longitude,geo_radius_meters',
            'equipment.site.client:id,business_name,contact_name,contact_phone',
        ]);

        $equipment = $checkin->equipment;

        // Para la firma diferida: cuántos equipos tiene la sede (se cachea offline).
        $equipment?->site?->loadCount('equipment');

        $proximity = null;
        if ($checkin->distance_meters !== null) {
            $proximity = [
                'within_radius' => (bool) $checkin->within_radius,
                'distance_meters' => (int) $checkin->distance_meters,
                'radius_meters' => $equipment?->site?->geo_radius_meters ?? 500,
            ];
        }

        $lastReport = $equipment
            ? $equipment->serviceReports()
                ->select('id', 'report_number', 'report_type', 'service_date', 'technician_id', 'status')
                ->with('technician:id,name')
                ->orderByDesc('service_date')
                ->first()
            : null;

        return response()->json([
            'checkin' => $checkin,
            'equipment' => $equipment,
            'last_report' => $lastReport,
            'proximity' => $proximity,
        ], $status);
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = TechnicianCheckin::with([
            'equipment:id,internal_code,equipment_type,brand,model',
            'equipment.site:id,name,client_id',
            'equipment.site.client:id,business_name',
        ]);

        // Tecnicos solo ven sus propios check-ins
        if ($user->hasRole('technician')) {
            $query->where('technician_id', $user->id);
        }

        if ($request->filled('equipment_id')) {
            $query->where('equipment_id', $request->equipment_id);
        }

        $checkins = $query->orderByDesc('checked_in_at')
            ->paginate($request->input('per_page', 20));

        return response()->json($checkins);
    }

    public function show(Request $request, TechnicianCheckin $checkin): JsonResponse
    {
        $user = $request->user();

        // Tecnicos solo ven sus propios check-ins
        if ($user->hasRole('technician') && (int) $checkin->technician_id !== (int) $user->id) {
            abort(403, 'No autorizado.');
        }

        $checkin->load([
            'equipment:id,internal_code,customer_code,equipment_type,brand,model,serial_number,capacity_kg,capacity_persons,stops,speed_mps,installation_date,contract_type,contract_start,contract_end,maintenance_frequency_days,status,photo_path',
            'equipment.site:id,name,address,city,department,client_id,contact_name_onsite,contact_phone_onsite',
            'equipment.site.client:id,business_name,contact_name,contact_phone',
            'technician:id,name,phone',
            'serviceReport:id,report_number,report_type,status',
        ]);

        return response()->json($checkin);
    }

    /**
     * Avisa a los masters (los unicos que pueden desbloquear) de que un tecnico
     * esta en sitio sin poder empezar. Un aviso por tecnico y equipo cada 10
     * minutos: reintentar el escaneo no debe llenar la campana.
     */
    private function notifyBlocked(Equipment $equipment, User $technician, array $access): void
    {
        $key = "access-blocked:{$technician->id}:{$equipment->id}";
        if (! Cache::add($key, true, now()->addMinutes(10))) {
            return;
        }

        $masters = User::role('master')->where('active', true)->get();
        if ($masters->isEmpty()) {
            return;
        }

        $payload = [
            'equipment_id' => $equipment->id,
            'equipment_code' => $equipment->internal_code,
            'technician_name' => $technician->name,
            'reason' => $access['status'],
            'scheduled_start' => $access['visit']?->scheduled_start?->toIso8601String(),
        ];

        Notification::send($masters, new EquipmentAccessBlockedNotification($payload));
        EquipmentAccessBlocked::dispatch($payload, $masters->pluck('id')->all());
    }

    private function haversineDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371000; // metros
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) * sin($dLat / 2)
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
            * sin($dLng / 2) * sin($dLng / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
