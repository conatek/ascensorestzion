<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'document_type',
        'document_number',
        'active',
        'company_id',
        'client_id',
        'image_public_id',
        'image_url',
        'notification_preferences',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at'        => 'datetime',
        'active'                   => 'boolean',
        'notification_preferences' => 'array',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Numero de WhatsApp para notificaciones.
     * Formato esperado: 573001234567 (codigo pais + numero)
     */
    public function routeNotificationForWhatsApp($notification = null): ?string
    {
        return $this->phone;
    }
}
