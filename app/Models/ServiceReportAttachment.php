<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceReportAttachment extends Model
{
    protected $fillable = [
        'service_report_id',
        'client_uuid',
        'type',
        'condition_key',
        'activity_key',
        'group_key',
        'media_type',
        'url',
        'cloudinary_public_id',
        'duration',
        'file_path',
        'original_name',
        'uploaded_by',
    ];

    public function serviceReport()
    {
        return $this->belongsTo(ServiceReport::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
