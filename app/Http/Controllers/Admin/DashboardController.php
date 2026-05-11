<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        $companiesByPlan = Plan::withCount(['subscriptions' => function ($q) {
            $q->whereIn('status', ['trial', 'active']);
        }])->orderBy('sort_order')->get(['id', 'name', 'display_name', 'subscriptions_count']);

        return response()->json([
            'total_companies'      => Company::count(),
            'total_users'          => User::count(),
            'active_subscriptions' => Subscription::active()->count(),
            'trial_subscriptions'  => Subscription::where('status', 'trial')->count(),
            'monthly_revenue'      => (float) Payment::approved()
                ->whereMonth('paid_at', now()->month)
                ->whereYear('paid_at', now()->year)
                ->sum('amount'),
            'expiring_soon'        => Subscription::expiringSoon(5)->count(),
            'companies_by_plan'    => $companiesByPlan,
        ]);
    }
}
