<?php

namespace App\Http\Controllers;

use App\Models\ScheduledVisit;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Agenda del tecnico: las mismas visitas del cronograma, pero acotadas a las
 * suyas y con lo que hace falta en sitio (contacto de la sede, geo, ficha del
 * equipo). El scoping es por Auth::id(), nunca por lo que mande el cliente.
 */
class TechScheduleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_if(! $user->can('view_own_schedule'), 403, 'No autorizado.');

        $validated = $request->validate([
            'from' => ['required', 'date'],
            'to' => ['required', 'date', 'after_or_equal:from'],
        ]);

        $visits = ScheduledVisit::query()
            ->with([
                'equipment:id,internal_code,equipment_type,brand,model',
                'site:id,name,address,city,latitude,longitude',
                'client:id,business_name',
            ])
            ->forTechnician($user->id)
            ->between(
                CarbonImmutable::parse($validated['from']),
                CarbonImmutable::parse($validated['to']),
            )
            // Una visita cancelada ya no es trabajo suyo: solo ensuciaria el dia.
            ->where('status', '!=', 'cancelada')
            ->orderBy('scheduled_start')
            ->get();

        return response()->json($visits);
    }

    /**
     * Detalle para trabajar: ficha tecnica del equipo, sede con geo y contacto,
     * y el ultimo reporte para saber que se hizo la vez anterior.
     */
    public function show(Request $request, ScheduledVisit $scheduledVisit): JsonResponse
    {
        $user = $request->user();
        abort_if(! $user->can('view_own_schedule'), 403, 'No autorizado.');
        abort_if((int) $scheduledVisit->technician_id !== (int) $user->id, 403, 'No autorizado.');

        $scheduledVisit->load([
            'equipment:id,internal_code,customer_code,equipment_type,brand,model,serial_number,capacity_kg,capacity_persons,stops,speed_mps,installation_date,contract_type,contract_start,contract_end,maintenance_frequency_days,status,photo_path,notes,site_id',
            'site:id,name,address,city,department,client_id,contact_name_onsite,contact_phone_onsite,latitude,longitude,geo_radius_meters',
            'client:id,business_name,contact_name,contact_phone',
        ]);

        $lastReport = $scheduledVisit->equipment
            ? $scheduledVisit->equipment->serviceReports()
                ->select('id', 'equipment_id', 'report_number', 'report_type', 'service_date', 'technician_id', 'status')
                ->with('technician:id,name')
                ->orderByDesc('service_date')
                ->orderByDesc('id')
                ->first()
            : null;

        return response()->json([
            'visit' => $scheduledVisit,
            'last_report' => $lastReport,
        ]);
    }
}
