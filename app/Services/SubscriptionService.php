<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Plan;
use App\Models\Subscription;

class SubscriptionService
{
    public function createTrialSubscription(Company $company): Subscription
    {
        $guestPlan = Plan::where('name', 'guest')->firstOrFail();

        return Subscription::create([
            'company_id'           => $company->id,
            'plan_id'              => $guestPlan->id,
            'status'               => 'trial',
            'trial_ends_at'        => now()->addDays(30),
            'current_period_start' => now(),
            'current_period_end'   => now()->addDays(30),
        ]);
    }

    public function activateSubscription(Company $company, Plan $plan, string $paymentMethod = 'payu'): Subscription
    {
        // Cancelar suscripción activa anterior si existe
        $current = $company->activeSubscription();
        if ($current) {
            $current->update([
                'status'       => 'cancelled',
                'cancelled_at' => now(),
            ]);
        }

        return Subscription::create([
            'company_id'           => $company->id,
            'plan_id'              => $plan->id,
            'status'               => 'active',
            'payment_method'       => $paymentMethod,
            'current_period_start' => now(),
            'current_period_end'   => now()->addMonth(),
        ]);
    }

    public function cancelSubscription(Subscription $subscription): void
    {
        $subscription->update([
            'status'       => 'cancelled',
            'cancelled_at' => now(),
        ]);
    }

    public function isActive(Company $company): bool
    {
        $subscription = $company->activeSubscription();
        return $subscription && $subscription->isActive();
    }

    public function getRemainingCards(Company $company): ?int
    {
        $plan = $company->currentPlan();
        if (!$plan) {
            return 0;
        }

        if ($plan->isUnlimited('cards')) {
            return null; // ilimitado
        }

        $current = $company->cards()->count();
        return max(0, $plan->max_cards - $current);
    }

    public function checkLimit(Company $company, string $resource): bool
    {
        $plan = $company->currentPlan();
        if (!$plan) {
            return false;
        }

        if ($plan->isUnlimited($resource)) {
            return true;
        }

        $limit = $plan->getLimit($resource);
        $current = $company->{$resource}()->count();

        return $current < $limit;
    }
}
