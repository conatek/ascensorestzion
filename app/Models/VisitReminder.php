<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Un aviso concreto: a quien, cuando y por que canales.
 */
class VisitReminder extends Model
{
    protected $fillable = [
        'scheduled_visit_id',
        'user_id',
        'send_at',
        'channels',
        'status',
        'sent_at',
        'error',
    ];

    protected $casts = [
        'send_at' => 'datetime',
        'sent_at' => 'datetime',
        'channels' => 'array',
    ];

    public function scheduledVisit()
    {
        return $this->belongsTo(ScheduledVisit::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** Lo que le toca enviar al comando en esta pasada. */
    public function scopeDue(Builder $query): Builder
    {
        return $query->where('status', 'pendiente')->where('send_at', '<=', now());
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pendiente');
    }
}
