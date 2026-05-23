<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EquipmentAttachment extends Model
{
    protected $fillable = [
        'equipment_id',
        'type',
        'file_path',
        'original_name',
        'uploaded_by',
    ];

    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
