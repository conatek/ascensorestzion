<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlanAdminController extends Controller
{
    public function index(): JsonResponse
    {
        $plans = Plan::withCount(['subscriptions' => function ($q) {
            $q->whereIn('status', ['trial', 'active']);
        }])->orderBy('sort_order')->get();

        return response()->json($plans);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'                => 'required|string|max:50|unique:plans,name',
            'display_name'        => 'required|string|max:100',
            'price_monthly'       => 'required|numeric|min:0',
            'price_yearly'        => 'required|numeric|min:0',
            'max_cards'           => 'required|integer|min:1',
            'max_products'        => 'nullable|integer|min:1',
            'max_services'        => 'nullable|integer|min:1',
            'available_templates' => 'nullable|array',
            'show_watermark'      => 'boolean',
            'features'            => 'nullable|array',
            'is_active'           => 'boolean',
            'sort_order'          => 'integer|min:0',
        ]);

        $plan = Plan::create($data);

        return response()->json($plan, 201);
    }

    public function update(Request $request, Plan $plan): JsonResponse
    {
        $data = $request->validate([
            'name'                => 'required|string|max:50|unique:plans,name,' . $plan->id,
            'display_name'        => 'required|string|max:100',
            'price_monthly'       => 'required|numeric|min:0',
            'price_yearly'        => 'required|numeric|min:0',
            'max_cards'           => 'required|integer|min:1',
            'max_products'        => 'nullable|integer|min:1',
            'max_services'        => 'nullable|integer|min:1',
            'available_templates' => 'nullable|array',
            'show_watermark'      => 'boolean',
            'features'            => 'nullable|array',
            'is_active'           => 'boolean',
            'sort_order'          => 'integer|min:0',
        ]);

        $plan->update($data);

        return response()->json($plan->fresh());
    }

    public function toggle(Plan $plan): JsonResponse
    {
        $plan->update(['is_active' => !$plan->is_active]);

        return response()->json([
            'message'   => $plan->is_active ? 'Plan activado' : 'Plan desactivado',
            'is_active' => $plan->is_active,
        ]);
    }
}
