<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreServiceRequest;
use App\Models\Company;
use App\Models\Service;
use App\Services\CloudinaryService;
use App\Traits\HasCompanyAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    use HasCompanyAccess;

    public function __construct(private CloudinaryService $cloudinary) {}

    public function index(Request $request, Company $company): JsonResponse
    {
        abort_if(!$request->user()->can('view_services'), 403, 'No autorizado.');

        return response()->json($company->services);
    }

    public function show(Request $request, Company $company, Service $service): JsonResponse
    {
        abort_if(!$request->user()->can('view_services'), 403, 'No autorizado.');
        abort_if($service->company_id !== $company->id, 404);

        return response()->json($service);
    }

    public function store(StoreServiceRequest $request, Company $company): JsonResponse
    {
        abort_if(!$request->user()->can('create_service'), 403, 'No autorizado.');

        $data = $request->validated();
        $data['company_id'] = $company->id;

        if ($request->hasFile('image')) {
            $uploaded = $this->cloudinary->upload($request->file('image'), 'companies/services');
            $data['image_path'] = $uploaded['url'];
        }

        $service = Service::create($data);

        return response()->json($service, 201);
    }

    public function update(StoreServiceRequest $request, Company $company, Service $service): JsonResponse
    {
        abort_if(!$request->user()->can('edit_service'), 403, 'No autorizado.');
        abort_if($service->company_id !== $company->id, 404);

        $data = $request->validated();

        if ($request->hasFile('image')) {
            if ($service->image_path) {
                $this->cloudinary->destroy($service->image_path);
            }
            $uploaded = $this->cloudinary->upload($request->file('image'), 'companies/services');
            $data['image_path'] = $uploaded['url'];
        }

        $service->update($data);

        return response()->json($service->fresh());
    }

    public function destroy(Request $request, Company $company, Service $service): JsonResponse
    {
        abort_if(!$request->user()->can('delete_service'), 403, 'No autorizado.');
        abort_if($service->company_id !== $company->id, 404);

        if ($service->image_path) {
            $this->cloudinary->destroy($service->image_path);
        }

        $service->delete();

        return response()->json(null, 204);
    }
}
