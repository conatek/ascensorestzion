<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'name',
        'image_path',
        'price',
        'discount',
        'comment',
        'description',
        'order',
        'is_active',
    ];

    protected $casts = [
        'price'     => 'decimal:2',
        'discount'  => 'decimal:2',
        'is_active' => 'boolean',
        'order'     => 'integer',
    ];

    // Precio con descuento aplicado
    public function getFinalPriceAttribute(): float
    {
        return round($this->price * (1 - $this->discount / 100), 2);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
