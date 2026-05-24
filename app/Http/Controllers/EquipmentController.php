<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEquipmentRequest;
use App\Models\Equipment;
use App\Services\CloudinaryService;
use App\Services\QrCodeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EquipmentController extends Controller
{
    public function __construct(
        private CloudinaryService $cloudinary,
        private QrCodeService $qrService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_if(!$user->can('view_equipment'), 403, 'No autorizado.');

        $query = Equipment::with(['site:id,name,client_id', 'site.client:id,business_name']);

        // admin (externo): solo equipos de su cliente
        if ($user->hasRole('admin') && $user->client_id) {
            $query->whereHas('site', function ($q) use ($user) {
                $q->where('client_id', $user->client_id);
            });
        }

        if ($request->filled('client_id')) {
            $query->whereHas('site', fn($q) => $q->where('client_id', $request->client_id));
        }

        if ($request->filled('site_id')) {
            $query->where('site_id', $request->site_id);
        }

        if ($request->filled('equipment_type')) {
            $query->where('equipment_type', $request->equipment_type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('internal_code', 'like', "%{$s}%")
                  ->orWhere('brand', 'like', "%{$s}%")
                  ->orWhere('model', 'like', "%{$s}%")
                  ->orWhere('serial_number', 'like', "%{$s}%");
            });
        }

        $equipment = $query->orderBy('internal_code')->get();

        return response()->json($equipment);
    }

    public function store(StoreEquipmentRequest $request): JsonResponse
    {
        abort_if(!$request->user()->can('create_equipment'), 403, 'No autorizado.');

        $data = $request->validated();

        if ($request->hasFile('photo')) {
            $folder = 'ascensorestzion/equipment';
            $uploaded = $this->cloudinary->upload($request->file('photo'), $folder);
            $data['photo_path'] = $uploaded['url'];
        }

        unset($data['photo']);
        $equipment = Equipment::create($data);

        return response()->json($equipment->load('site.client'), 201);
    }

    public function show(Request $request, Equipment $equipment): JsonResponse
    {
        $user = $request->user();
        abort_if(!$user->can('view_equipment'), 403, 'No autorizado.');

        $equipment->load(['site.client', 'attachments.uploader:id,name']);

        if ($user->hasRole('admin') && $user->client_id) {
            if ((int) $equipment->site?->client_id !== (int) $user->client_id) {
                abort(403, 'No autorizado.');
            }
        }

        return response()->json($equipment);
    }

    public function update(StoreEquipmentRequest $request, Equipment $equipment): JsonResponse
    {
        abort_if(!$request->user()->can('edit_equipment'), 403, 'No autorizado.');

        $data = $request->validated();

        if ($request->hasFile('photo')) {
            $folder = 'ascensorestzion/equipment';
            $uploaded = $this->cloudinary->upload($request->file('photo'), $folder);
            $data['photo_path'] = $uploaded['url'];
        }

        unset($data['photo']);
        $equipment->update($data);

        return response()->json($equipment->fresh()->load('site.client'));
    }

    public function destroy(Request $request, Equipment $equipment): JsonResponse
    {
        abort_if(!$request->user()->can('delete_equipment'), 403, 'No autorizado.');

        $equipment->delete();

        return response()->json(null, 204);
    }

    public function history(Request $request, Equipment $equipment): JsonResponse
    {
        $user = $request->user();
        abort_if(!$user->can('view_equipment'), 403, 'No autorizado.');

        $equipment->loadMissing('site');

        if ($user->hasRole('admin') && $user->client_id) {
            if ((int) $equipment->site?->client_id !== (int) $user->client_id) {
                abort(403, 'No autorizado.');
            }
        }

        $query = $equipment->serviceReports()
            ->with(['technician:id,name'])
            ->select(['id', 'report_number', 'report_type', 'equipment_id', 'service_date', 'status', 'technician_id', 'equipment_functional', 'conclusion_notes']);

        if ($request->filled('report_type')) {
            $query->where('report_type', $request->report_type);
        }
        if ($request->filled('date_from')) {
            $query->where('service_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('service_date', '<=', $request->date_to);
        }

        return response()->json($query->orderByDesc('service_date')->get());
    }

    public function uploadAttachment(Request $request, Equipment $equipment): JsonResponse
    {
        $user = $request->user();
        abort_if(!$user->can('edit_equipment'), 403, 'No autorizado.');

        $request->validate([
            'file' => ['required', 'file', 'max:10240'],
            'type' => ['required', 'in:plano,certificado,manual,foto,otro'],
        ]);

        $file = $request->file('file');
        $path = $file->store('equipment-attachments/' . $equipment->id, 'local');

        $attachment = $equipment->attachments()->create([
            'type'          => $request->type,
            'file_path'     => $path,
            'original_name' => $file->getClientOriginalName(),
            'uploaded_by'   => $user->id,
        ]);

        return response()->json($attachment->load('uploader:id,name'), 201);
    }

    public function generateQr(Request $request, Equipment $equipment): JsonResponse
    {
        abort_if(! $request->user()->can('edit_equipment'), 403, 'No autorizado.');

        $path = $this->qrService->generate($equipment);
        $url = \Illuminate\Support\Facades\Storage::disk('public')->url($path);

        return response()->json([
            'qr_code_path' => $path,
            'qr_code_url' => $url,
            'content' => $this->qrService->getContent($equipment),
        ]);
    }

    public function deleteAttachment(Request $request, Equipment $equipment, \App\Models\EquipmentAttachment $attachment): JsonResponse
    {
        abort_if(!$request->user()->can('edit_equipment'), 403, 'No autorizado.');

        if ((int) $attachment->equipment_id !== (int) $equipment->id) {
            abort(404);
        }

        \Illuminate\Support\Facades\Storage::disk('local')->delete($attachment->file_path);
        $attachment->delete();

        return response()->json(null, 204);
    }
}
