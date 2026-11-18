<?php

namespace App\Models;

use App\Models\Concerns\TracksAuthor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierContact extends Model
{
    use TracksAuthor;

    protected $fillable = [
        'supplier_id',
        'supplier_branch_id',
        'name',
        'position',
        'phone',
        'extension',
        'mobile',
        'whatsapp',
        'email',
        'is_primary',
        'notes',
        'active',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'active' => 'boolean',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(SupplierBranch::class, 'supplier_branch_id');
    }
}
