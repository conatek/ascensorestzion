<?php

namespace App\Services;

use App\Models\Equipment;
use App\Models\EquipmentAccessUnlock;
use App\Models\ScheduledVisit;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * Bloqueo de llegada: decide si un tecnico puede registrar la llegada a un equipo.
 *
 * Reglas (pedido del cliente, 2026-11-08):
 *  - La emergencia nunca se bloquea.
 *  - Un desbloqueo del master para ese equipo y ese dia habilita a cualquiera.
 *  - Sin visita del tecnico en ese equipo ese dia: bloqueado.
 *  - Correctivos y especiales programados: pasan a cualquier hora.
 *  - Preventivo: solo desde la hora de inicio exacta, sin margen.
 *
 * La misma regla corre en el telefono sin conexion (resources/js/utils/accessAgenda.js)
 * con la agenda guardada; si se cambia aqui, cambiarla alla.
 */
class EquipmentAccessService
{
    public const A_TIEMPO = 'a_tiempo';

    public const EMERGENCIA = 'emergencia';

    public const DESBLOQUEADO = 'desbloqueado';

    public const ANTICIPADO = 'anticipado';

    public const SIN_PROGRAMAR = 'sin_programar';

    public const RELOJ_INCONSISTENTE = 'reloj_inconsistente';

    /** Visitas que todavia se pueden ejecutar ese dia. */
    private const OPEN_STATUSES = ['programada', 'reprogramacion_solicitada', 'en_curso'];

    /**
     * @return array{allowed: bool, status: string, visit: ?ScheduledVisit, message: ?string}
     */
    public function evaluate(Equipment $equipment, User $technician, CarbonInterface $when, bool $emergency): array
    {
        $visits = $this->visitsFor($equipment->id, $technician->id, $when);

        // La visita a la que se liga el check-in: la que ya arranco o la primera del dia.
        $visit = $visits->firstWhere('status', 'en_curso') ?? $visits->first();

        if ($emergency) {
            return $this->allow(self::EMERGENCIA, $visit);
        }

        if ($this->isUnlocked($equipment->id, $when)) {
            return $this->allow(self::DESBLOQUEADO, $visit);
        }

        if ($visits->isEmpty()) {
            return $this->deny(
                self::SIN_PROGRAMAR,
                null,
                'Este equipo no tiene una visita programada para ti hoy. Solo un master puede desbloquear el ingreso.'
            );
        }

        $open = $visits->first(fn (ScheduledVisit $v) => $v->status === 'en_curso'
            || $v->visit_type !== 'preventivo'
            || $v->scheduled_start->lte($when));

        if ($open) {
            return $this->allow(self::A_TIEMPO, $open);
        }

        $next = $visits->first();

        return $this->deny(
            self::ANTICIPADO,
            $next,
            "La visita está programada a las {$next->scheduled_start->format('g:i A')}; podrás registrar la llegada a esa hora."
        );
    }

    /** @return Collection<int, ScheduledVisit> */
    public function visitsFor(int $equipmentId, int $technicianId, CarbonInterface $when): Collection
    {
        return ScheduledVisit::query()
            ->where('equipment_id', $equipmentId)
            ->where('technician_id', $technicianId)
            ->whereDate('scheduled_start', $when->toDateString())
            ->whereIn('status', self::OPEN_STATUSES)
            ->orderBy('scheduled_start')
            ->get();
    }

    public function isUnlocked(int $equipmentId, CarbonInterface $when): bool
    {
        return EquipmentAccessUnlock::where('equipment_id', $equipmentId)
            ->where('unlock_date', $when->toDateString())
            ->exists();
    }

    /**
     * Lo que el telefono guarda para decidir sin conexion: sus visitas abiertas
     * del dia y los equipos desbloqueados, con los dos codigos que se pueden
     * escanear o digitar.
     */
    public function agendaFor(User $technician, CarbonInterface $day): array
    {
        $visits = ScheduledVisit::query()
            ->with('equipment:id,internal_code,customer_code')
            ->where('technician_id', $technician->id)
            ->whereDate('scheduled_start', $day->toDateString())
            ->whereIn('status', self::OPEN_STATUSES)
            ->orderBy('scheduled_start')
            ->get()
            ->map(fn (ScheduledVisit $v) => [
                'id' => $v->id,
                'equipment_id' => $v->equipment_id,
                'internal_code' => $v->equipment?->internal_code,
                'customer_code' => $v->equipment?->customer_code,
                'visit_type' => $v->visit_type,
                'status' => $v->status,
                'scheduled_start' => $v->scheduled_start->toIso8601String(),
            ])
            ->values();

        $unlocked = EquipmentAccessUnlock::query()
            ->with('equipment:id,internal_code,customer_code')
            ->where('unlock_date', $day->toDateString())
            ->get()
            ->map(fn (EquipmentAccessUnlock $u) => [
                'equipment_id' => $u->equipment_id,
                'internal_code' => $u->equipment?->internal_code,
                'customer_code' => $u->equipment?->customer_code,
            ])
            ->values();

        return [
            'date' => $day->toDateString(),
            'generated_at' => now()->toIso8601String(),
            'visits' => $visits,
            'unlocked' => $unlocked,
        ];
    }

    private function allow(string $status, ?ScheduledVisit $visit): array
    {
        return ['allowed' => true, 'status' => $status, 'visit' => $visit, 'message' => null];
    }

    private function deny(string $status, ?ScheduledVisit $visit, string $message): array
    {
        return ['allowed' => false, 'status' => $status, 'visit' => $visit, 'message' => $message];
    }
}
