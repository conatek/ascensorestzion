<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Plan;

class PayUService
{
    private string $apiKey;
    private string $merchantId;
    private string $accountId;
    private string $currency;

    public function __construct()
    {
        $this->apiKey     = config('payu.api_key', '');
        $this->merchantId = config('payu.merchant_id', '');
        $this->accountId  = config('payu.account_id', '');
        $this->currency   = config('payu.currency', 'COP');
    }

    /**
     * Genera los datos necesarios para el formulario WebCheckout de PayU.
     */
    public function generateCheckoutData(Payment $payment, Plan $plan, string $billingPeriod = 'monthly'): array
    {
        $amount        = $billingPeriod === 'yearly' ? $plan->price_yearly : $plan->price_monthly;
        $referenceCode = $this->generateReferenceCode($payment);
        $description   = "NexosCard - Plan {$plan->display_name} ({$billingPeriod})";

        // Formato del monto: sin decimales si es entero, con 2 si no
        $formattedAmount = $this->formatAmount($amount);

        // Firma: md5(ApiKey~merchantId~referenceCode~amount~currency)
        $signature = md5("{$this->apiKey}~{$this->merchantId}~{$referenceCode}~{$formattedAmount}~{$this->currency}");

        // Actualizar payment con referenceCode
        $payment->update([
            'payu_reference_code' => $referenceCode,
            'amount'              => $amount,
        ]);

        return [
            'merchantId'      => $this->merchantId,
            'accountId'       => $this->accountId,
            'description'     => $description,
            'referenceCode'   => $referenceCode,
            'amount'          => $formattedAmount,
            'tax'             => '0',
            'taxReturnBase'   => '0',
            'currency'        => $this->currency,
            'signature'       => $signature,
            'test'            => config('payu.environment') === 'sandbox' ? '1' : '0',
            'buyerEmail'      => $payment->company->owner->email ?? '',
            'responseUrl'     => config('payu.response_url'),
            'confirmationUrl' => config('payu.confirmation_url'),
        ];
    }

    /**
     * Valida la firma del webhook de confirmación de PayU.
     *
     * Firma esperada: md5(ApiKey~merchantId~referenceCode~TX_VALUE~currency~state_pol)
     * PayU puede enviar TX_VALUE con 1 decimal (ej: 49900.0), probar ambos formatos.
     */
    public function validateWebhookSignature(array $data): bool
    {
        $sign          = $data['sign'] ?? '';
        $referenceCode = $data['reference_sale'] ?? '';
        $txValue       = $data['value'] ?? '';
        $currency      = $data['currency'] ?? '';
        $statePol      = $data['state_pol'] ?? '';

        // Intentar con el valor tal cual viene
        $expectedSign = md5("{$this->apiKey}~{$this->merchantId}~{$referenceCode}~{$txValue}~{$currency}~{$statePol}");

        if (strtolower($expectedSign) === strtolower($sign)) {
            return true;
        }

        // PayU puede enviar con formato diferente, probar con 1 decimal
        $txValueOneDecimal = number_format((float) $txValue, 1, '.', '');
        $expectedSign2 = md5("{$this->apiKey}~{$this->merchantId}~{$referenceCode}~{$txValueOneDecimal}~{$currency}~{$statePol}");

        if (strtolower($expectedSign2) === strtolower($sign)) {
            return true;
        }

        // Probar con 2 decimales
        $txValueTwoDecimals = number_format((float) $txValue, 2, '.', '');
        $expectedSign3 = md5("{$this->apiKey}~{$this->merchantId}~{$referenceCode}~{$txValueTwoDecimals}~{$currency}~{$statePol}");

        return strtolower($expectedSign3) === strtolower($sign);
    }

    /**
     * URL de WebCheckout según el entorno configurado.
     */
    public function getCheckoutUrl(): string
    {
        return config('payu.checkout_url');
    }

    /**
     * Genera un referenceCode único basado en el ID del payment.
     */
    private function generateReferenceCode(Payment $payment): string
    {
        return 'NEXOS-' . $payment->id . '-' . time();
    }

    /**
     * Formatea el monto para PayU (sin separador de miles, con decimales si necesario).
     */
    private function formatAmount(float $amount): string
    {
        if ($amount == (int) $amount) {
            return (string) (int) $amount;
        }

        return number_format($amount, 2, '.', '');
    }
}
