<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'business_name',
        'nit',
        'contact_name',
        'contact_email',
        'contact_phone',
        'whatsapp_phone',
        'address',
        'city',
        'department',
        'notes',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function sites()
    {
        return $this->hasMany(Site::class);
    }

    public function users()
    {
        return $this->hasMany(User::class, 'client_id');
    }

    public function equipment()
    {
        return $this->hasManyThrough(Equipment::class, Site::class);
    }
}
