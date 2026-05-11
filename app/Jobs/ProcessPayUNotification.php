<?php

namespace App\Jobs;

use App\Models\Payment;
use App\Models\Plan;
use App\Services\SubscriptionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessPayUNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private array $notificationData
    ) {}

    public function handle(SubscriptionService $subscriptionService): void
    {
        $referenceCode = $this->notificationData['reference_sale'] ?? '';
        $statePol      = $this->notificationData['state_pol'] ?? '';
        $transactionId = $this->notificationData['transaction_id'] ?? '';
        $orderId       = $this->notificationData['reference_pol'] ?? '';
        $responseCode  = $this->notificationData['response_code_pol'] ?? '';
        $paymentMethod = $this->notificationData['payment_method_type'] ?? '';

        $payment = Payment::where('payu_reference_code', $referenceCode)->first();

        if (!$payment) {
            Log::warning("PayU webhook: payment no encontrado para referenceCode: {$referenceCode}");
            return;
        }

        // Actualizar datos de PayU en el payment
        $payment->update([
            'payu_transaction_id' => $transactionId,
            'payu_order_id'       => $orderId,
            'response_code'       => $responseCode,
            'payment_method'      => $this->mapPaymentMethod($paymentMethod),
            'metadata'            => $this->notificationData,
        ]);

        // state_pol: 4 = Aprobada, 6 = Rechazada, 5 = Expirada, 7 = Pendiente
        switch ($statePol) {
            case '4': // Aprobada
                $this->handleApproved($payment, $subscriptionService);
                break;

            case '6': // Rechazada
                $payment->update(['status' => 'declined']);
                Log::info("PayU: Pago rechazado - payment #{$payment->id}");
                break;

            case '5': // Expirada
                $payment->update(['status' => 'declined']);
                Log::info("PayU: Pago expirado - payment #{$payment->id}");
                break;

            case '7': // Pendiente (PSE, efectivo)
                Log::info("PayU: Pago pendiente - payment #{$payment->id}");
                break;

            default:
                Log::warning("PayU: state_pol desconocido: {$statePol} - payment #{$payment->id}");
                break;
        }
    }

    private function handleApproved(Payment $payment, SubscriptionService $subscriptionService): void
    {
        $payment->update([
            'status'  => 'approved',
            'paid_at' => now(),
        ]);

        $company = $payment->company;
        if (!$company) {
            Log::error("PayU: company no encontrada para payment #{$payment->id}");
            return;
        }

        // Determinar el plan del payment via la suscripción o metadata
        $subscription = $payment->subscription;
        $plan = $subscription?->plan;

        if (!$plan) {
            // Intentar obtener plan_id del metadata
            $planId = $this->notificationData['extra1'] ?? null;
            $plan = $planId ? Plan::find($planId) : null;
        }

        if ($plan) {
            // Activar suscripción
            $billingPeriod = $this->notificationData['extra2'] ?? 'monthly';
            $newSubscription = $subscriptionService->activateSubscription($company, $plan, 'payu');

            // Ajustar periodo si es anual
            if ($billingPeriod === 'yearly') {
                $newSubscription->update([
                    'current_period_end' => now()->addYear(),
                ]);
            }

            // Vincular payment a la nueva suscripción
            $payment->update(['subscription_id' => $newSubscription->id]);

            // Cambiar rol del owner a Admin si es Guest
            $owner = $company->owner;
            if ($owner && $owner->hasRole('Guest')) {
                $owner->syncRoles(['Admin']);
                Log::info("PayU: Usuario #{$owner->id} promovido a Admin");
            }
        }

        Log::info("PayU: Pago aprobado - payment #{$payment->id}, company #{$company->id}");
    }

    private function mapPaymentMethod(string $payuMethod): string
    {
        $map = [
            '2'  => 'credit_card',   // Tarjeta de crédito
            '4'  => 'pse',           // PSE
            '6'  => 'debit_card',    // Tarjeta débito
            '7'  => 'cash',          // Efectivo
            '8'  => 'bank_transfer', // Referencia bancaria
            '10' => 'bank_transfer', // Débito bancario
            '14' => 'cash',          // Efecty/Baloto
        ];

        return $map[$payuMethod] ?? 'other';
    }
}
