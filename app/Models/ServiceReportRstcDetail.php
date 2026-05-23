<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceReportRstcDetail extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'service_report_id',
        'call_time',
        'entry_time',
        'exit_time',
        'response_time_hh',
        'response_time_mm',
        'fault_location',
        'fault_location_other',
        'fault_cause',
        'fault_cause_other',
        'fault_solution_area',
        'fault_solution_other',
        'analysis_notes',
        'cause_notes',
        'solution_notes',
    ];

    protected $casts = [
        'call_time'  => 'datetime',
        'entry_time' => 'datetime',
        'exit_time'  => 'datetime',
    ];

    public function serviceReport()
    {
        return $this->belongsTo(ServiceReport::class);
    }
}
