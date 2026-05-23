<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Catalog extends Model
{
    protected $fillable = [
        'scope',
        'category',
        'group_key',
        'key',
        'label',
        'sort_order',
        'active',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'active'     => 'boolean',
    ];

    public function scopeForScope(Builder $query, string $scope): Builder
    {
        return $query->where('scope', $scope);
    }

    public function scopeForCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }
}
