<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceReportRstpActivity extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'service_report_id',
        'group_key',
        'activity_key',
        'is_ok',
        'observation',
    ];

    protected $casts = [
        'is_ok' => 'boolean',
    ];

    public function serviceReport()
    {
        return $this->belongsTo(ServiceReport::class);
    }
}
