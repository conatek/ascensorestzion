<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierPriceLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['supplier_item_id', 'price', 'currency', 'price_date', 'source', 'user_id'];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
