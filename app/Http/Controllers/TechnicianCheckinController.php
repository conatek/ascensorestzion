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
        ]);

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

        // Calcular tiempo de respuesta si es emergencia
        $responseTimeMinutes = null;
        if ($validated['is_emergency'] ?? false) {
            $callTime = $validated['call_received_at'] ?? null;
            if ($callTime) {
                $responseTimeMinutes = (int) $now->diffInMinutes($callTime);
            }
        }

        $checkin = TechnicianCheckin::create([
            'equipment_id' => $equipment->id,
            'technician_id' => $user->id,
            'checked_in_at' => $now,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'accuracy' => $validated['accuracy'] ?? null,
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

        // Cargar equipo con relaciones para la respuesta
        $equipment->load([
            'site:id,name,address,city,client_id,latitude,longitude,geo_radius_meters',
            'site.client:id,business_name,contact_name,contact_phone',
        ]);

        // Info de proximidad
        $proximity = null;
        $site = $equipment->site;
        if ($site && $site->latitude && $site->longitude && $validated['latitude']) {
            $distance = $this->haversineDistance(
                $validated['latitude'],
                $validated['longitude'],
                (float) $site->latitude,
                (float) $site->longitude,
            );
            $radius = $site->geo_radius_meters ?? 500;
            $proximity = [
                'within_radius' => $distance <= $radius,
                'distance_meters' => round($distance),
                'radius_meters' => $radius,
            ];
        }

        // Ultimo reporte del equipo
        $lastReport = $equipment->serviceReports()
            ->select('id', 'report_number', 'report_type', 'service_date', 'technician_id', 'status')
            ->with('technician:id,name')
            ->orderByDesc('service_date')
            ->first();

        return response()->json([
            'checkin' => $checkin,
            'equipment' => $equipment,
            'last_report' => $lastReport,
            'proximity' => $proximity,
        ], 201);
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
