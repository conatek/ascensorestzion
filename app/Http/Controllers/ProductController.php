<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Models\Company;
use App\Models\Product;
use App\Services\CloudinaryService;
use App\Traits\HasCompanyAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    use HasCompanyAccess;

    public function __construct(private CloudinaryService $cloudinary) {}

    public function index(Request $request, Company $company): JsonResponse
    {
        abort_if(!$request->user()->can('view_products'), 403, 'No autorizado.');

        return response()->json($company->products);
    }

    public function show(Request $request, Company $company, Product $product): JsonResponse
    {
        abort_if(!$request->user()->can('view_products'), 403, 'No autorizado.');
        abort_if($product->company_id !== $company->id, 404);

        return response()->json($product);
    }

    public function store(StoreProductRequest $request, Company $company): JsonResponse
    {
        abort_if(!$request->user()->can('create_product'), 403, 'No autorizado.');

        $data = $request->validated();
        $data['company_id'] = $company->id;

        if ($request->hasFile('image')) {
            $uploaded = $this->cloudinary->upload($request->file('image'), 'companies/products');
            $data['image_path'] = $uploaded['url'];
        }

        $product = Product::create($data);

        return response()->json($product, 201);
    }

    public function update(StoreProductRequest $request, Company $company, Product $product): JsonResponse
    {
        abort_if(!$request->user()->can('edit_product'), 403, 'No autorizado.');
        abort_if($product->company_id !== $company->id, 404);

        $data = $request->validated();

        if ($request->hasFile('image')) {
            if ($product->image_path) {
                $this->cloudinary->destroy($product->image_path);
            }
            $uploaded = $this->cloudinary->upload($request->file('image'), 'companies/products');
            $data['image_path'] = $uploaded['url'];
        }

        $product->update($data);

        return response()->json($product->fresh());
    }

    public function destroy(Request $request, Company $company, Product $product): JsonResponse
    {
        abort_if(!$request->user()->can('delete_product'), 403, 'No autorizado.');
        abort_if($product->company_id !== $company->id, 404);

        if ($product->image_path) {
            $this->cloudinary->destroy($product->image_path);
        }

        $product->delete();

        return response()->json(null, 204);
    }
}
