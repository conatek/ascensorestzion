<?php

namespace App\Http\Controllers;

use App\Events\TechnicianCheckedIn;
use App\Models\Equipment;
use App\Models\TechnicianCheckin;
use App\Models\User;
use App\Notifications\TechnicianCheckedInNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class TechnicianCheckinController extends Controller
{
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
        // Para check-ins sincronizados offline, usar la hora real del dispositivo
        $checkedInAt = ! empty($validated['checked_in_at'])
            ? \Carbon\Carbon::parse($validated['checked_in_at'])
            : $now;

        // Calcular tiempo de respuesta si es emergencia
        $responseTimeMinutes = null;
        if ($validated['is_emergency'] ?? false) {
            $callTime = $validated['call_received_at'] ?? null;
            if ($callTime) {
                $responseTimeMinutes = (int) $checkedInAt->diffInMinutes($callTime);
            }
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
            'technician_id' => $user->id,
            'client_uuid' => $validated['client_uuid'] ?? null,
            'checked_in_at' => $checkedInAt,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'accuracy' => $validated['accuracy'] ?? null,
            'distance_meters' => $proximity['distance_meters'] ?? null,
            'within_radius' => $proximity['within_radius'] ?? null,
            'method' => $validated['method'],
            'is_emergency' => $validated['is_emergency'] ?? false,
            'call_received_at' => $validated['call_received_at'] ?? null,
            'response_time_minutes' => $responseTimeMinutes,
        ]);

        // Notificar a usuarios master
        $masterIds = User::role('master')->pluck('id')->toArray();
        $masters = User::role('master')->get();

        if ($masterIds) {
            TechnicianCheckedIn::dispatch($checkin, $masterIds);
            Notification::send($masters, new TechnicianCheckedInNotification($checkin));
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
