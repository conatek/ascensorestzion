<?php

namespace App\Services;

use App\Models\ServiceReport;
use App\Models\ServiceReportAuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ServiceReportSigningService
{
    public function signAsTechnician(ServiceReport $report, string $signatureBase64, User $user): void
    {
        $path = $this->saveSignature($signatureBase64, $report->report_number, 'tech');

        $report->update([
            'technician_signature_path' => $path,
            'technician_signed_at'      => now(),
            'status'                    => 'firmado_tecnico',
        ]);

        ServiceReportAuditLog::create([
            'service_report_id' => $report->id,
            'user_id'           => $user->id,
            'action'            => 'signed_tech',
            'ip'                => request()->ip(),
            'user_agent'        => request()->userAgent(),
            'created_at'        => now(),
        ]);
    }

    public function signAsCustomer(
        ServiceReport $report,
        string $signatureBase64,
        string $name,
        string $document,
        Request $request
    ): void {
        $path = $this->saveSignature($signatureBase64, $report->report_number, 'customer');

        $report->update([
            'customer_signer_name'     => $name,
            'customer_signer_document' => $document,
            'customer_signature_path'  => $path,
            'customer_signed_at'       => now(),
            'status'                   => 'firmado_cliente',
        ]);

        ServiceReportAuditLog::create([
            'service_report_id' => $report->id,
            'user_id'           => $request->user()->id,
            'action'            => 'signed_customer',
            'changes_json'      => ['signer_name' => $name, 'signer_document' => $document],
            'ip'                => $request->ip(),
            'user_agent'        => $request->userAgent(),
            'created_at'        => now(),
        ]);
    }

    private function saveSignature(string $base64, string $reportNumber, string $type): string
    {
        $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64));
        $year = date('Y');
        $filename = "{$reportNumber}_{$type}.png";
        $path = "signatures/{$year}/{$filename}";

        Storage::disk('local')->put($path, $imageData);

        return $path;
    }
}
