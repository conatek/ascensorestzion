<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Jornada propia de un tecnico. Es la excepcion: sin fila, hereda la global.
 *
 * Cada campo nullable se resuelve por separado, asi que un tecnico puede heredar
 * los dias globales y sobrescribir solo el horario. El merge no se hace aqui sino
 * en ScheduleService::workingWindowFor().
 */
class TechnicianSchedule extends Model
{
    protected $fillable = [
        'user_id',
        'enabled',
        'working_days',
        'working_hours',
        'break_start',
        'break_end',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'working_days' => 'array',
        'working_hours' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
