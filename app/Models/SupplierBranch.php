<?php

namespace App\Models;

use App\Models\Concerns\TracksAuthor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierBranch extends Model
{
    use TracksAuthor;

    protected $fillable = [
        'supplier_id',
        'name',
        'is_main',
        'address',
        'city',
        'department',
        'phone',
        'mobile',
        'email',
        'business_hours',
        'notes',
        'active',
    ];

    protected $casts = [
        'is_main' => 'boolean',
        'active' => 'boolean',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }
}
