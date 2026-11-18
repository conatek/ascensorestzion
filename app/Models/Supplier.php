<?php

namespace App\Models;

use App\Models\Concerns\TracksAuthor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use SoftDeletes, TracksAuthor;

    public const TYPES = ['fabricante', 'distribuidor', 'importador', 'taller', 'servicios'];

    public const CURRENCIES = ['COP', 'USD'];

    protected $fillable = [
        'business_name',
        'trade_name',
        'nit',
        'supplier_type',
        'categories',
        'brands',
        'website',
        'payment_terms',
        'credit_days',
        'default_currency',
        'default_lead_time_days',
        'rating',
        'notes',
        'active',
        'is_example',
    ];

    protected $casts = [
        'categories' => 'array',
        'brands' => 'array',
        'credit_days' => 'integer',
        'default_lead_time_days' => 'integer',
        'rating' => 'integer',
        'active' => 'boolean',
        'is_example' => 'boolean',
    ];

    public function branches(): HasMany
    {
        return $this->hasMany(SupplierBranch::class)->orderByDesc('is_main')->orderBy('name');
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(SupplierContact::class)->orderByDesc('is_primary')->orderBy('name');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SupplierItem::class);
    }

    /** La sede que se muestra en el directorio: la principal o, si no hay, la primera. */
    public function mainBranch(): HasOne
    {
        return $this->hasOne(SupplierBranch::class)->orderByDesc('is_main')->orderBy('id');
    }

    /** El asesor al que apuntan los botones de llamar, WhatsApp y correo. */
    public function primaryContact(): HasOne
    {
        return $this->hasOne(SupplierContact::class)
            ->where('active', true)
            ->orderByDesc('is_primary')
            ->orderBy('id');
    }
}
