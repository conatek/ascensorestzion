<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Plan;
use App\Services\SubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentAdminController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Payment::with('company:id,name');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('payu_reference_code', 'like', "%{$search}%")
                  ->orWhereHas('company', fn ($q2) => $q2->where('name', 'like', "%{$search}%"));
            });
        }

        // Total filtrado (antes de paginar)
        $totalAmount = (clone $query)->where('status', 'approved')->sum('amount');

        $payments = $query->latest()->paginate(20);

        return response()->json([
            'payments'     => $payments,
            'total_amount' => (float) $totalAmount,
        ]);
    }

    public function show(Payment $payment): JsonResponse
    {
        $payment->load(['company:id,name,slug', 'subscription.plan']);

        return response()->json($payment);
    }

    public function approve(Payment $payment, SubscriptionService $subscriptionService): JsonResponse
    {
        if ($payment->status !== 'pending') {
            return response()->json([
                'message' => 'Solo se pueden aprobar pagos pendientes.',
            ], 422);
        }

        $payment->update([
            'status'  => 'approved',
            'paid_at' => now(),
        ]);

        $company = $payment->company;

        if ($company) {
            // Determinar plan desde metadata o suscripción
            $planId = $payment->metadata['extra1'] ?? $payment->subscription?->plan_id;
            $plan = $planId ? Plan::find($planId) : null;

            if ($plan) {
                $billingPeriod = $payment->metadata['extra2'] ?? 'monthly';
                $subscription = $subscriptionService->activateSubscription($company, $plan, 'manual');

                if ($billingPeriod === 'yearly') {
                    $subscription->update(['current_period_end' => now()->addYear()]);
                }

                $payment->update(['subscription_id' => $subscription->id]);

                // Promover owner a Admin
                $owner = $company->owner;
                if ($owner && $owner->hasRole('Guest')) {
                    $owner->syncRoles(['Admin']);
                }
            }
        }

        Log::info("Pago #{$payment->id} aprobado manualmente por Master");

        return response()->json([
            'message' => 'Pago aprobado y suscripción activada.',
            'payment' => $payment->fresh(),
        ]);
    }

    public function refund(Payment $payment): JsonResponse
    {
        if ($payment->status !== 'approved') {
            return response()->json([
                'message' => 'Solo se pueden reembolsar pagos aprobados.',
            ], 422);
        }

        $payment->update(['status' => 'refunded']);

        Log::info("Pago #{$payment->id} reembolsado por Master");

        return response()->json([
            'message' => 'Pago marcado como reembolsado.',
            'payment' => $payment->fresh(),
        ]);
    }
}
