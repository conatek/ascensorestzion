<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\EquipmentAccessUnlock;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Desbloqueo del ingreso a un equipo por el dia de hoy. Solo master (lo impone
 * la ruta): es la valvula de escape del bloqueo de llegada en preventivos.
 */
class EquipmentAccessUnlockController extends Controller
{
    public function show(Equipment $equipment): JsonResponse
    {
        return response()->json(['unlock' => $this->today($equipment)?->load('unlocker:id,name')]);
    }

    public function store(Request $request, Equipment $equipment): JsonResponse
    {
        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $unlock = EquipmentAccessUnlock::firstOrCreate(
            ['equipment_id' => $equipment->id, 'unlock_date' => now()->toDateString()],
            ['unlocked_by' => $request->user()->id, 'reason' => $data['reason'] ?? null],
        );

        return response()->json(['unlock' => $unlock->load('unlocker:id,name')], 201);
    }

    public function destroy(Equipment $equipment): JsonResponse
    {
        $this->today($equipment)?->delete();

        return response()->json(['unlock' => null]);
    }

    private function today(Equipment $equipment): ?EquipmentAccessUnlock
    {
        return EquipmentAccessUnlock::where('equipment_id', $equipment->id)
            ->where('unlock_date', now()->toDateString())
            ->first();
    }
}
