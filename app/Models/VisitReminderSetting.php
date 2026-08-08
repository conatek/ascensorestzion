<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Preferencia de recordatorios de un usuario. Sin fila, valen los defaults
 * globales que correspondan a su rol.
 */
class VisitReminderSetting extends Model
{
    protected $fillable = ['user_id', 'enabled', 'offsets'];

    protected $casts = [
        'enabled' => 'boolean',
        'offsets' => 'array',
    ];

    public const MAX_OFFSETS = 3;

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
