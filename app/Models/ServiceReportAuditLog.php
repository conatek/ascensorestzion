<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceReportAuditLog extends Model
{
    public $timestamps = false;

    protected $table = 'service_report_audit_log';

    protected $fillable = [
        'service_report_id',
        'user_id',
        'action',
        'changes_json',
        'ip',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'changes_json' => 'array',
        'created_at' => 'datetime',
    ];

    public function serviceReport()
    {
        return $this->belongsTo(ServiceReport::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
