<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Site extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'client_id',
        'name',
        'address',
        'city',
        'department',
        'contact_name_onsite',
        'contact_phone_onsite',
        'contact_email_onsite',
        'latitude',
        'longitude',
        'geo_radius_meters',
        'notes',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'geo_radius_meters' => 'integer',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function equipment()
    {
        return $this->hasMany(Equipment::class);
    }
}
