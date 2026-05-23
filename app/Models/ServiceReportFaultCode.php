<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceReportFaultCode extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'service_report_id',
        'code',
        'description',
        'severity',
    ];

    public function serviceReport()
    {
        return $this->belongsTo(ServiceReport::class);
    }
}
