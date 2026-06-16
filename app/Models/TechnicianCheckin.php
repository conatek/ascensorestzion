<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TechnicianCheckin extends Model
{
    protected $fillable = [
        'equipment_id',
        'technician_id',
        'client_uuid',
        'checked_in_at',
        'latitude',
        'longitude',
        'accuracy',
        'distance_meters',
        'within_radius',
        'method',
        'is_emergency',
        'call_received_at',
        'response_time_minutes',
        'service_report_id',
    ];

    protected $casts = [
        'checked_in_at' => 'datetime',
        'call_received_at' => 'datetime',
        'is_emergency' => 'boolean',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'accuracy' => 'decimal:2',
        'distance_meters' => 'integer',
        'within_radius' => 'boolean',
        'response_time_minutes' => 'integer',
    ];

    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function serviceReport()
    {
        return $this->belongsTo(ServiceReport::class);
    }
}
