<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Equipment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'equipment';

    protected $fillable = [
        'site_id',
        'internal_code',
        'customer_code',
        'equipment_type',
        'brand',
        'model',
        'serial_number',
        'capacity_kg',
        'capacity_persons',
        'stops',
        'speed_mps',
        'installation_date',
        'commissioning_date',
        'contract_type',
        'contract_start',
        'contract_end',
        'maintenance_frequency_days',
        'photo_path',
        'qr_code_path',
        'status',
        'notes',
    ];

    protected $casts = [
        'capacity_kg'               => 'integer',
        'capacity_persons'          => 'integer',
        'stops'                     => 'integer',
        'speed_mps'                 => 'decimal:2',
        'installation_date'         => 'date',
        'commissioning_date'        => 'date',
        'contract_start'            => 'date',
        'contract_end'              => 'date',
        'maintenance_frequency_days' => 'integer',
    ];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function attachments()
    {
        return $this->hasMany(EquipmentAttachment::class);
    }

    public function serviceReports()
    {
        return $this->hasMany(ServiceReport::class);
    }

    public function getClientAttribute()
    {
        return $this->site?->client;
    }
}
