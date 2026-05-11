<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Services\SubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionAdminController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Subscription::with(['company:id,name,slug', 'plan:id,name,display_name']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('plan_id')) {
            $query->where('plan_id', $request->plan_id);
        }

        if ($request->filled('search')) {
            $query->whereHas('company', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            });
        }

        $subscriptions = $query->latest()->paginate(20);

        return response()->json($subscriptions);
    }

    public function show(Subscription $subscription): JsonResponse
    {
        $subscription->load([
            'company:id,name,slug',
            'plan',
            'payments' => fn ($q) => $q->latest()->limit(20),
        ]);

        return response()->json($subscription);
    }

    public function update(Request $request, Subscription $subscription): JsonResponse
    {
        $data = $request->validate([
            'plan_id'              => 'sometimes|exists:plans,id',
            'status'               => 'sometimes|in:trial,active,past_due,cancelled,expired',
            'current_period_end'   => 'sometimes|date',
        ]);

        $subscription->update($data);

        return response()->json($subscription->fresh()->load('plan:id,name,display_name'));
    }

    public function extend(Request $request, Subscription $subscription): JsonResponse
    {
        $request->validate([
            'days' => 'required|integer|min:1|max:365',
        ]);

        $currentEnd = $subscription->current_period_end ?? now();
        $subscription->update([
            'current_period_end' => $currentEnd->addDays($request->days),
        ]);

        if ($subscription->status === 'expired' || $subscription->status === 'past_due') {
            $subscription->update(['status' => 'active']);
        }

        return response()->json([
            'message'            => "Periodo extendido {$request->days} días.",
            'current_period_end' => $subscription->current_period_end,
            'status'             => $subscription->status,
        ]);
    }

    public function cancel(Subscription $subscription, SubscriptionService $service): JsonResponse
    {
        $service->cancelSubscription($subscription);

        return response()->json([
            'message' => 'Suscripción cancelada.',
            'status'  => 'cancelled',
        ]);
    }
}
