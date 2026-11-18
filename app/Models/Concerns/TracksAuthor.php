<?php

namespace App\Models\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

/**
 * Guarda quien creo y quien cambio por ultima vez el registro. Los datos de
 * proveedores son de todos los master: no hay dueño, pero si autoria visible.
 */
trait TracksAuthor
{
    public static function bootTracksAuthor(): void
    {
        static::creating(function ($model) {
            $id = Auth::id();
            $model->created_by ??= $id;
            $model->updated_by ??= $id;
        });

        static::updating(function ($model) {
            if ($id = Auth::id()) {
                $model->updated_by = $id;
            }
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
