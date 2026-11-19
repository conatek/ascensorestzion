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
        'default_visit_duration_minutes',
        'photo_path',
        'qr_code_path',
        'status',
        'notes',
    ];

    protected $casts = [
        'capacity_kg' => 'integer',
        'capacity_persons' => 'integer',
        'stops' => 'integer',
        'speed_mps' => 'decimal:2',
        'installation_date' => 'date',
        'commissioning_date' => 'date',
        'contract_start' => 'date',
        'contract_end' => 'date',
        'maintenance_frequency_days' => 'integer',
        'default_visit_duration_minutes' => 'integer',
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

    public function scheduledVisits()
    {
        return $this->hasMany(ScheduledVisit::class);
    }

    public function getClientAttribute()
    {
        return $this->site?->client;
    }

    /**
     * Equipos a los que llega el usuario (ver HasAccessScope). Para el tecnico,
     * "los suyos" son los que atiende: tiene una visita asignada, hizo check-in
     * o hizo un reporte ahi.
     */
    public function scopeVisibleTo($query, User $user)
    {
        if ($user->isStaff()) {
            return $query;
        }

        if ($user->isFieldTechnician()) {
            return $query->where(function ($q) use ($user) {
                $q->whereIn('equipment.id', ScheduledVisit::query()->select('equipment_id')->where('technician_id', $user->id))
                    ->orWhereIn('equipment.id', TechnicianCheckin::query()->select('equipment_id')->where('technician_id', $user->id))
                    ->orWhereIn('equipment.id', ServiceReport::query()->select('equipment_id')->visibleTo($user));
            });
        }

        if ($user->isClientUser() && $user->client_id) {
            return $query->whereHas('site', fn ($q) => $q->where('client_id', $user->client_id));
        }

        return $query->whereRaw('1 = 0');
    }

    public function isVisibleTo(User $user): bool
    {
        return $user->isStaff() || static::query()->whereKey($this->getKey())->visibleTo($user)->exists();
    }
}
