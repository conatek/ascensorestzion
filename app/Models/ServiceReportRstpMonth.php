<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceReportRstpMonth extends Model
{
    public $timestamps = false;

    protected $table = 'service_report_rstp_month';

    protected $fillable = [
        'service_report_id',
        'year',
        'month',
    ];

    protected $casts = [
        'year'  => 'integer',
        'month' => 'integer',
    ];

    public function serviceReport()
    {
        return $this->belongsTo(ServiceReport::class);
    }
}
