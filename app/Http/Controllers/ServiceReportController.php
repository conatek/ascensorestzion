<?php

namespace App\Http\Controllers;

use App\Events\ReportCompleted;
use App\Http\Requests\StoreServiceReportRequest;
use App\Mail\ServiceReportMail;
use App\Models\Equipment;
use App\Models\ServiceReport;
use App\Models\ServiceReportAuditLog;
use App\Models\User;
use App\Notifications\ReportCompletedNotification;
use App\Services\ServiceReportNumberingService;
use App\Services\ServiceReportPdfService;
use App\Services\ServiceReportSigningService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class ServiceReportController extends Controller
{
    public function __construct(
        private ServiceReportNumberingService $numbering,
        private ServiceReportSigningService $signing,
        private \App\Services\CloudinaryService $cloudinary,
        private ServiceReportPdfService $pdfService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_if(! $user->can('view_reports'), 403, 'No autorizado.');

        $query = ServiceReport::with([
            'equipment:id,internal_code,brand,model',
            'client:id,business_name',
            'technician:id,name',
        ]);

        // admin (externo): solo reportes de su cliente
        if ($user->hasRole('admin') && $user->client_id) {
            $query->where('client_id', $user->client_id);
        }

        // technician: solo sus reportes
        if ($user->hasRole('technician')) {
            $query->where('technician_id', $user->id);
        }

        if ($request->filled('report_type')) {
            $query->where('report_type', $request->report_type);
        }
        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }
        if ($request->filled('equipment_id')) {
            $query->where('equipment_id', $request->equipment_id);
        }
        if ($request->filled('technician_id')) {
            $query->where('technician_id', $request->technician_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date_from')) {
            $query->where('service_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('service_date', '<=', $request->date_to);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('report_number', 'like', "%{$s}%")
                    ->orWhereHas('equipment', fn ($eq) => $eq->where('internal_code', 'like', "%{$s}%"));
            });
        }

        return response()->json($query->orderByDesc('service_date')->get());
    }

    public function store(StoreServiceReportRequest $request): JsonResponse
    {
        $user = $request->user();
        abort_if(! $user->can('create_report'), 403, 'No autorizado.');

        $data = $request->validated();

        // Idempotencia para sync offline: si el reporte ya existe (mismo client_uuid),
        // devolverlo sin duplicar.
        if (! empty($data['client_uuid'])) {
            $existing = ServiceReport::where('client_uuid', $data['client_uuid'])->first();
            if ($existing) {
                return response()->json(
                    $existing->load(['equipment.site.client', 'initialConditions', 'rstpActivities', 'rstpMonth', 'rstcDetails', 'faultCodes', 'rsteWorks', 'technician']),
                    200
                );
            }
        }

        $equipment = Equipment::with('site.client')->findOrFail($data['equipment_id']);

        // Crear reporte base
        $report = ServiceReport::create([
            'report_number' => $this->numbering->generate($data['report_type']),
            'client_uuid' => $data['client_uuid'] ?? null,
            'visit_uuid' => $data['visit_uuid'] ?? null,
            'report_type' => $data['report_type'],
            'equipment_id' => $equipment->id,
            'client_id' => $equipment->site->client_id,
            'site_id' => $equipment->site_id,
            'customer_order_ref' => $data['customer_order_ref'] ?? null,
            'service_date' => $data['service_date'],
            'time_in' => $data['time_in'] ?? null,
            'time_out' => $data['time_out'] ?? null,
            'technician_id' => $user->id,
            'secondary_technician_id' => $data['secondary_technician_id'] ?? null,
            'equipment_functional' => $data['equipment_functional'] ?? null,
            'generates_quotation' => $data['generates_quotation'] ?? false,
            'requires_parts_change' => $data['requires_parts_change'] ?? false,
            'conclusion_notes' => $data['conclusion_notes'] ?? null,
            'status' => 'borrador',
            'created_by' => $user->id,
        ]);

        // Guardar condiciones iniciales
        if (! empty($data['initial_conditions'])) {
            foreach ($data['initial_conditions'] as $condition) {
                $report->initialConditions()->create($condition);
            }
        }

        // RSTP: actividades
        if ($data['report_type'] === 'RSTP' && ! empty($data['rstp_activities'])) {
            foreach ($data['rstp_activities'] as $activity) {
                $report->rstpActivities()->create($activity);
            }
        }

        // RSTP: mes del mantenimiento
        if ($data['report_type'] === 'RSTP' && ! empty($data['rstp_month'])) {
            $report->rstpMonth()->create($data['rstp_month']);
        }

        // RSTC: detalles de análisis
        if ($data['report_type'] === 'RSTC' && ! empty($data['rstc_details'])) {
            $report->rstcDetails()->create($data['rstc_details']);
        }

        // RSTC/RSTE: códigos de falla
        if (in_array($data['report_type'], ['RSTC', 'RSTE']) && ! empty($data['fault_codes'])) {
            foreach ($data['fault_codes'] as $code) {
                $report->faultCodes()->create($code);
            }
        }

        // RSTE: trabajos
        if ($data['report_type'] === 'RSTE' && ! empty($data['rste_works'])) {
            foreach ($data['rste_works'] as $work) {
                $report->rsteWorks()->create($work);
            }
        }

        // Audit log
        ServiceReportAuditLog::create([
            'service_report_id' => $report->id,
            'user_id' => $user->id,
            'action' => 'created',
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);

        return response()->json(
            $report->load(['equipment.site.client', 'initialConditions', 'rstpActivities', 'rstpMonth', 'rstcDetails', 'faultCodes', 'rsteWorks', 'technician']),
            201
        );
    }

    public function show(Request $request, ServiceReport $serviceReport): JsonResponse
    {
        $user = $request->user();
        abort_if(! $user->can('view_reports'), 403, 'No autorizado.');

        if ($user->hasRole('admin') && $user->client_id !== $serviceReport->client_id) {
            abort(403, 'No autorizado.');
        }

        $relations = [
            'equipment.site.client',
            'technician:id,name,document_type,document_number',
            'secondaryTechnician:id,name',
            'initialConditions',
            'attachments',
            'auditLog.user:id,name',
        ];

        if ($serviceReport->report_type === 'RSTP') {
            $relations[] = 'rstpActivities';
            $relations[] = 'rstpMonth';
        }
        if ($serviceReport->report_type === 'RSTC') {
            $relations[] = 'rstcDetails';
            $relations[] = 'faultCodes';
        }
        if ($serviceReport->report_type === 'RSTE') {
            $relations[] = 'rsteWorks';
            $relations[] = 'faultCodes';
        }

        $serviceReport->load($relations);

        // El formulario del tecnico usa este conteo para ofrecer la firma diferida
        // (solo tiene sentido si la sede tiene mas de un equipo).
        $serviceReport->equipment?->site?->loadCount('equipment');

        return response()->json($serviceReport);
    }

    public function update(StoreServiceReportRequest $request, ServiceReport $serviceReport): JsonResponse
    {
        $user = $request->user();

        if ($serviceReport->status !== 'borrador' && ! $user->can('edit_any_report')) {
            abort(403, 'Solo se pueden editar reportes en borrador.');
        }
        if ($serviceReport->status === 'borrador' && $serviceReport->created_by !== $user->id && ! $user->can('edit_any_report')) {
            abort(403, 'No autorizado.');
        }

        $data = $request->validated();

        $serviceReport->update([
            'customer_order_ref' => $data['customer_order_ref'] ?? $serviceReport->customer_order_ref,
            'service_date' => $data['service_date'],
            'time_in' => $data['time_in'] ?? $serviceReport->time_in,
            'time_out' => $data['time_out'] ?? $serviceReport->time_out,
            'equipment_functional' => $data['equipment_functional'] ?? $serviceReport->equipment_functional,
            'generates_quotation' => $data['generates_quotation'] ?? $serviceReport->generates_quotation,
            'requires_parts_change' => $data['requires_parts_change'] ?? $serviceReport->requires_parts_change,
            'conclusion_notes' => $data['conclusion_notes'] ?? $serviceReport->conclusion_notes,
            'last_edited_by' => $user->id,
        ]);

        // Sincronizar condiciones iniciales
        if (isset($data['initial_conditions'])) {
            $serviceReport->initialConditions()->delete();
            foreach ($data['initial_conditions'] as $condition) {
                $serviceReport->initialConditions()->create($condition);
            }
        }

        // Sincronizar actividades RSTP
        if ($serviceReport->report_type === 'RSTP' && isset($data['rstp_activities'])) {
            $serviceReport->rstpActivities()->delete();
            foreach ($data['rstp_activities'] as $activity) {
                $serviceReport->rstpActivities()->create($activity);
            }
        }

        // Sincronizar mes RSTP
        if ($serviceReport->report_type === 'RSTP' && isset($data['rstp_month'])) {
            $serviceReport->rstpMonth()->delete();
            $serviceReport->rstpMonth()->create($data['rstp_month']);
        }

        // Sincronizar detalles RSTC
        if ($serviceReport->report_type === 'RSTC' && isset($data['rstc_details'])) {
            $serviceReport->rstcDetails()->delete();
            $serviceReport->rstcDetails()->create($data['rstc_details']);
        }

        // Sincronizar códigos de falla (RSTC/RSTE)
        if (in_array($serviceReport->report_type, ['RSTC', 'RSTE']) && isset($data['fault_codes'])) {
            $serviceReport->faultCodes()->delete();
            foreach ($data['fault_codes'] as $code) {
                $serviceReport->faultCodes()->create($code);
            }
        }

        // Sincronizar trabajos RSTE
        if ($serviceReport->report_type === 'RSTE' && isset($data['rste_works'])) {
            $serviceReport->rsteWorks()->delete();
            foreach ($data['rste_works'] as $work) {
                $serviceReport->rsteWorks()->create($work);
            }
        }

        ServiceReportAuditLog::create([
            'service_report_id' => $serviceReport->id,
            'user_id' => $user->id,
            'action' => 'edited',
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);

        return response()->json($serviceReport->fresh()->load([
            'equipment.site.client', 'initialConditions', 'rstpActivities', 'rstpMonth',
            'rstcDetails', 'faultCodes', 'rsteWorks', 'technician',
        ]));
    }

    public function destroy(Request $request, ServiceReport $serviceReport): JsonResponse
    {
        $user = $request->user();

        if ($serviceReport->status !== 'borrador') {
            abort(403, 'Solo se pueden eliminar reportes en borrador.');
        }

        abort_if(! $user->can('edit_any_report') && $serviceReport->created_by !== $user->id, 403, 'No autorizado.');

        $serviceReport->delete();

        return response()->json(null, 204);
    }

    public function signTechnician(Request $request, ServiceReport $serviceReport): JsonResponse
    {
        $user = $request->user();
        abort_if(! $user->can('sign_report_technician'), 403, 'No autorizado.');

        $request->validate([
            'signature' => ['required', 'string'],
            'visit_uuid' => ['nullable', 'uuid'],
        ]);

        // Firma diferida: el reporte queda agrupado en una visita a la espera
        // de la firma del cliente en lote.
        if ($request->filled('visit_uuid') && ! $serviceReport->visit_uuid) {
            $serviceReport->update(['visit_uuid' => $request->visit_uuid]);
        }

        $this->signing->signAsTechnician($serviceReport, $request->signature, $user);

        return response()->json($serviceReport->fresh());
    }

    public function signCustomer(Request $request, ServiceReport $serviceReport): JsonResponse
    {
        $user = $request->user();
        abort_if(! $user->can('sign_report_customer') && ! $user->can('sign_report_technician'), 403, 'No autorizado.');

        $request->validate([
            'signature' => ['required', 'string'],
            'signer_name' => ['required', 'string', 'max:255'],
            'signer_document' => ['required', 'string', 'max:50'],
        ]);

        $this->signing->signAsCustomer(
            $serviceReport,
            $request->signature,
            $request->signer_name,
            $request->signer_document,
            $request
        );

        // Generar token de confirmacion de recepcion
        $serviceReport->update([
            'reception_token' => Str::random(64),
        ]);

        // Notificar a masters, tecnico y cliente
        $serviceReport->refresh()->load(['technician', 'client']);

        $masterIds = User::role('master')->pluck('id')->toArray();
        $masters = User::role('master')->get();

        // Broadcast a masters
        if ($masterIds) {
            ReportCompleted::dispatch($serviceReport, $masterIds);
        }

        // Notificar masters
        Notification::send($masters, new ReportCompletedNotification($serviceReport, 'master'));

        // Notificar tecnico
        if ($serviceReport->technician) {
            $serviceReport->technician->notify(new ReportCompletedNotification($serviceReport, 'technician'));
        }

        // Notificar admin del cliente (si existe)
        $clientAdmins = User::where('client_id', $serviceReport->client_id)->get();
        if ($clientAdmins->isNotEmpty()) {
            Notification::send($clientAdmins, new ReportCompletedNotification($serviceReport, 'client'));
        }

        return response()->json($serviceReport->fresh());
    }

    public function pdf(Request $request, ServiceReport $serviceReport)
    {
        $user = $request->user();
        abort_if(! $user->can('export_report_pdf'), 403, 'No autorizado.');

        if ($user->hasRole('admin') && $user->client_id !== $serviceReport->client_id) {
            abort(403, 'No autorizado.');
        }

        $serviceReport->load([
            'equipment.site.client',
            'technician',
            'initialConditions',
            'rstpActivities',
            'rstpMonth',
            'rstcDetails',
            'faultCodes',
            'rsteWorks',
            'attachments',
        ]);

        $view = 'pdf.'.strtolower($serviceReport->report_type);
        $html = view($view, ['report' => $serviceReport])->render();

        $pdf = $this->generatePdf($html);

        $filename = "{$serviceReport->report_number}.pdf";

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"{$filename}\"",
        ]);
    }

    public function sendEmail(Request $request, ServiceReport $serviceReport): JsonResponse
    {
        $user = $request->user();
        abort_if(! $user->can('export_report_pdf'), 403, 'No autorizado.');

        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $serviceReport->load(['equipment.site.client', 'technician', 'initialConditions', 'rstpActivities', 'rstpMonth', 'rstcDetails', 'faultCodes', 'rsteWorks', 'attachments']);

        $view = 'pdf.'.strtolower($serviceReport->report_type);
        $html = view($view, ['report' => $serviceReport])->render();

        $pdf = $this->generatePdf($html);

        Mail::to($request->email)->send(new ServiceReportMail($serviceReport, $pdf));

        return response()->json(['message' => 'Correo enviado correctamente.']);
    }

    public function export(Request $request)
    {
        $user = $request->user();
        abort_if(! $user->can('view_reports'), 403, 'No autorizado.');

        $query = ServiceReport::with(['equipment:id,internal_code', 'client:id,business_name', 'technician:id,name']);

        if ($user->hasRole('admin') && $user->client_id) {
            $query->where('client_id', $user->client_id);
        }
        if ($request->filled('report_type')) {
            $query->where('report_type', $request->report_type);
        }
        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date_from')) {
            $query->where('service_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('service_date', '<=', $request->date_to);
        }

        $reports = $query->orderByDesc('service_date')->get();

        $headers = ['N° Reporte', 'Tipo', 'Fecha', 'Equipo', 'Cliente', 'Técnico', 'Estado', 'Funciona', 'Cotización', 'Repuestos'];

        $callback = function () use ($reports, $headers) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
            fputcsv($file, $headers, ';');

            foreach ($reports as $r) {
                fputcsv($file, [
                    $r->report_number,
                    $r->report_type,
                    $r->service_date?->format('d/m/Y'),
                    $r->equipment->internal_code ?? '',
                    $r->client->business_name ?? '',
                    $r->technician->name ?? '',
                    $r->status,
                    $r->equipment_functional ? 'Sí' : 'No',
                    $r->generates_quotation ? 'Sí' : 'No',
                    $r->requires_parts_change ? 'Sí' : 'No',
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="reportes-'.now()->format('Y-m-d').'.csv"',
        ]);
    }

    private function generatePdf(string $html): string
    {
        return $this->pdfService->fromHtml($html);
    }

    public function uploadAttachment(Request $request, ServiceReport $serviceReport): JsonResponse
    {
        $user = $request->user();

        // Permitir al creador o a quien tenga permiso de edicion
        $canEdit = $user->can('edit_any_report')
            || (int) $serviceReport->created_by === (int) $user->id
            || (int) $serviceReport->technician_id === (int) $user->id;
        abort_if(! $canEdit, 403, 'No autorizado.');

        $request->validate([
            'file' => ['required', 'file', 'max:61440'], // 60MB max (video de hasta 1 min)
            'type' => ['required', 'in:foto_antes,foto_despues,cotizacion,otro'],
            'condition_key' => ['nullable', 'string', 'max:50'],
            'activity_key' => ['nullable', 'string', 'max:50'],
            'group_key' => ['nullable', 'string', 'max:50'],
            'media_type' => ['nullable', 'in:foto,video'],
            'client_uuid' => ['nullable', 'uuid'],
        ]);

        // Idempotencia para sync offline: si el adjunto ya existe (mismo
        // client_uuid), devolverlo sin volver a subir el archivo.
        if ($request->filled('client_uuid')) {
            $existing = $serviceReport->attachments()
                ->where('client_uuid', $request->client_uuid)
                ->first();
            if ($existing) {
                return response()->json($existing->load('uploader:id,name'), 200);
            }
        }

        $file = $request->file('file');
        $folder = \App\Services\CloudinaryService::envFolder()."/reportes/{$serviceReport->id}";
        $uploaded = $this->cloudinary->upload($file, $folder);

        // Video: tope de 1 minuto. Si excede, borrar de Cloudinary y rechazar.
        $isVideo = $uploaded['resource_type'] === 'video';
        if ($isVideo && $uploaded['duration'] !== null && $uploaded['duration'] > 61) {
            $this->cloudinary->destroyVideo($uploaded['public_id']);

            return response()->json([
                'message' => 'El video supera el límite de 1 minuto.',
                'errors' => ['file' => ['El video supera el límite de 1 minuto.']],
            ], 422);
        }

        $attachment = $serviceReport->attachments()->create([
            'client_uuid' => $request->client_uuid,
            'type' => $request->type,
            'condition_key' => $request->condition_key,
            'activity_key' => $request->activity_key,
            'group_key' => $request->group_key,
            'media_type' => $isVideo ? 'video' : ($request->media_type ?? 'foto'),
            'url' => $uploaded['url'],
            'cloudinary_public_id' => $uploaded['public_id'],
            'duration' => $uploaded['duration'] !== null ? (int) round($uploaded['duration']) : null,
            'original_name' => $file->getClientOriginalName(),
            'uploaded_by' => $user->id,
        ]);

        return response()->json($attachment->load('uploader:id,name'), 201);
    }
}
