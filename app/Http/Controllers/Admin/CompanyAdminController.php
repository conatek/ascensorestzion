<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompanyAdminController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Company::withCount(['cards', 'services', 'products', 'users'])
            ->with(['subscriptions' => function ($q) {
                $q->whereIn('status', ['trial', 'active', 'past_due'])
                  ->latest()
                  ->with('plan:id,name,display_name')
                  ->limit(1);
            }]);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if ($request->filled('plan_id')) {
            $query->whereHas('subscriptions', function ($q) use ($request) {
                $q->where('plan_id', $request->plan_id)
                  ->whereIn('status', ['trial', 'active', 'past_due']);
            });
        }

        if ($request->filled('subscription_status')) {
            $query->whereHas('subscriptions', function ($q) use ($request) {
                $q->where('status', $request->subscription_status);
            });
        }

        $companies = $query->latest()->paginate(20);

        // Transformar para incluir plan/suscripción de forma plana
        $companies->getCollection()->transform(function ($company) {
            $sub = $company->subscriptions->first();
            $company->current_plan = $sub?->plan?->display_name;
            $company->subscription_status = $sub?->status;
            $company->subscription_ends = $sub?->current_period_end;
            unset($company->subscriptions);
            return $company;
        });

        return response()->json($companies);
    }
}
