<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'first_name',
        'last_name',
        'slug',
        'job_title',
        'photo_path',
        'mobile_phone',
        'whatsapp',
        'email',
        'linkedin',
        'whatsapp_message',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Nombre completo como atributo virtual
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    // --- Relaciones ---

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
