<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use Illuminate\Console\Command;

class CheckSubscriptionExpiry extends Command
{
    protected $signature = 'subscriptions:check-expiry';
    protected $description = 'Verificar vencimientos de suscripciones y actualizar estados';

    public function handle(): int
    {
        $this->handleExpiredTrials();
        $this->handlePastDueSubscriptions();
        $this->handleFullyExpired();

        return Command::SUCCESS;
    }

    /**
     * Suscripciones trial que vencieron → expired
     */
    private function handleExpiredTrials(): void
    {
        $expired = Subscription::where('status', 'trial')
            ->where('trial_ends_at', '<', now())
            ->get();

        foreach ($expired as $subscription) {
            $subscription->update(['status' => 'expired']);
            $this->info("Trial expirado: empresa #{$subscription->company_id}");
        }

        if ($expired->count() > 0) {
            $this->info("{$expired->count()} trial(s) expirado(s).");
        }
    }

    /**
     * Suscripciones active cuyo periodo terminó → past_due
     */
    private function handlePastDueSubscriptions(): void
    {
        $pastDue = Subscription::where('status', 'active')
            ->where('current_period_end', '<', now())
            ->get();

        foreach ($pastDue as $subscription) {
            $subscription->update(['status' => 'past_due']);
            $this->info("Suscripción vencida (past_due): empresa #{$subscription->company_id}");
        }

        if ($pastDue->count() > 0) {
            $this->info("{$pastDue->count()} suscripción(es) pasada(s) a past_due.");
        }
    }

    /**
     * Suscripciones past_due con más de 10 días → expired + degradar rol
     */
    private function handleFullyExpired(): void
    {
        $fullyExpired = Subscription::where('status', 'past_due')
            ->where('current_period_end', '<', now()->subDays(10))
            ->get();

        foreach ($fullyExpired as $subscription) {
            $subscription->update(['status' => 'expired']);

            // Degradar usuarios de la empresa a Guest
            $company = $subscription->company;
            if ($company) {
                foreach ($company->users as $user) {
                    if ($user->hasRole('Admin')) {
                        $user->syncRoles(['Guest']);
                        $this->info("Usuario #{$user->id} degradado a Guest.");
                    }
                }
            }

            $this->info("Suscripción expirada completamente: empresa #{$subscription->company_id}");
        }

        if ($fullyExpired->count() > 0) {
            $this->info("{$fullyExpired->count()} suscripción(es) expirada(s) completamente.");
        }
    }
}
