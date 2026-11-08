<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Desbloqueo del ingreso a un equipo para un dia, hecho por un master. Habilita
 * el check-in de cualquier tecnico en ese equipo ese dia, haya o no visita.
 *
 * unlock_date va sin cast date a proposito: el cast guarda "00:00:00" y rompe la
 * comparacion por fecha en sqlite.
 */
class EquipmentAccessUnlock extends Model
{
    protected $fillable = [
        'equipment_id',
        'unlock_date',
        'unlocked_by',
        'reason',
    ];

    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }

    public function unlocker()
    {
        return $this->belongsTo(User::class, 'unlocked_by');
    }
}
