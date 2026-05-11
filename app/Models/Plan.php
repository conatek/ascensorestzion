<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name',
        'price_monthly',
        'price_yearly',
        'max_cards',
        'max_products',
        'max_services',
        'available_templates',
        'show_watermark',
        'features',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'price_monthly'       => 'decimal:2',
        'price_yearly'        => 'decimal:2',
        'max_cards'           => 'integer',
        'max_products'        => 'integer',
        'max_services'        => 'integer',
        'available_templates' => 'array',
        'show_watermark'      => 'boolean',
        'features'            => 'array',
        'is_active'           => 'boolean',
        'sort_order'          => 'integer',
    ];

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function isUnlimited(string $resource): bool
    {
        $field = "max_{$resource}";
        return is_null($this->$field);
    }

    public function getLimit(string $resource): ?int
    {
        $field = "max_{$resource}";
        return $this->$field;
    }
}
