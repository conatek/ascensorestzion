<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceReportInitialCondition extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'service_report_id',
        'condition_key',
        'value',
        'observation',
    ];

    public function serviceReport()
    {
        return $this->belongsTo(ServiceReport::class);
    }
}
