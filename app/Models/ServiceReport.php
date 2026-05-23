<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceReport extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'report_number',
        'report_type',
        'equipment_id',
        'client_id',
        'site_id',
        'customer_order_ref',
        'service_date',
        'time_in',
        'time_out',
        'technician_id',
        'secondary_technician_id',
        'equipment_functional',
        'generates_quotation',
        'requires_parts_change',
        'conclusion_notes',
        'technician_signature_path',
        'technician_signed_at',
        'customer_signer_name',
        'customer_signer_document',
        'customer_signature_path',
        'customer_signed_at',
        'status',
        'final_pdf_path',
        'created_by',
        'last_edited_by',
    ];

    protected $casts = [
        'service_date'          => 'date',
        'equipment_functional'  => 'boolean',
        'generates_quotation'   => 'boolean',
        'requires_parts_change' => 'boolean',
        'technician_signed_at'  => 'datetime',
        'customer_signed_at'    => 'datetime',
    ];

    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function secondaryTechnician()
    {
        return $this->belongsTo(User::class, 'secondary_technician_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function initialConditions()
    {
        return $this->hasMany(ServiceReportInitialCondition::class);
    }

    public function rstpActivities()
    {
        return $this->hasMany(ServiceReportRstpActivity::class);
    }

    public function rstpMonth()
    {
        return $this->hasOne(ServiceReportRstpMonth::class);
    }

    public function rstcDetails()
    {
        return $this->hasOne(ServiceReportRstcDetail::class);
    }

    public function faultCodes()
    {
        return $this->hasMany(ServiceReportFaultCode::class);
    }

    public function rsteWorks()
    {
        return $this->hasMany(ServiceReportRsteWork::class);
    }

    public function attachments()
    {
        return $this->hasMany(ServiceReportAttachment::class);
    }

    public function auditLog()
    {
        return $this->hasMany(ServiceReportAuditLog::class)->orderByDesc('created_at');
    }
}
