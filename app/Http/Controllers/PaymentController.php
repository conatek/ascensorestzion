<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessPayUNotification;
use App\Models\Payment;
use App\Models\Plan;
use App\Services\PayUService;
use App\Traits\HasCompanyAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    use HasCompanyAccess;

    public function __construct(private PayUService $payUService) {}

    /**
     * Iniciar proceso de pago (checkout).
     * POST /api/payments/checkout
     */
    public function checkout(Request $request): JsonResponse
    {
        $request->validate([
            'plan_id'        => 'required|exists:plans,id',
            'billing_period' => 'required|in:monthly,yearly',
        ]);

        $user    = $request->user();
        $company = $user->company;

        if (!$company) {
            return response()->json([
                'message' => 'No tienes una empresa asociada.',
            ], 422);
        }

        $plan = Plan::active()->findOrFail($request->plan_id);

        // No permitir contratar el plan gratuito
        if ($plan->name === 'guest') {
            return response()->json([
                'message' => 'El plan gratuito no requiere pago.',
            ], 422);
        }

        $amount = $request->billing_period === 'yearly'
            ? $plan->price_yearly
            : $plan->price_monthly;

        // Crear payment pendiente
        $payment = Payment::create([
            'company_id' => $company->id,
            'amount'     => $amount,
            'currency'   => config('payu.currency', 'COP'),
            'status'     => 'pending',
        ]);

        // Generar datos para WebCheckout
        $checkoutData = $this->payUService->generateCheckoutData($payment, $plan, $request->billing_period);

        // Guardar plan_id y billing_period en extra fields para el webhook
        // PayU permite enviar extra1, extra2, extra3
        $checkoutData['extra1'] = (string) $plan->id;
        $checkoutData['extra2'] = $request->billing_period;

        return response()->json([
            'checkout_url' => $this->payUService->getCheckoutUrl(),
            'form_data'    => $checkoutData,
            'payment_id'   => $payment->id,
        ]);
    }

    /**
     * Historial de pagos de la empresa.
     * GET /api/payments/history
     */
    public function history(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->hasRole('Master')) {
            $payments = Payment::with('company:id,name')
                ->latest()
                ->paginate(20);
        } else {
            $company = $user->company;
            if (!$company) {
                return response()->json(['data' => []]);
            }

            $payments = $company->payments()
                ->latest()
                ->paginate(20);
        }

        return response()->json($payments);
    }

    /**
     * Webhook de confirmación de PayU (público, sin auth).
     * POST /api/payu/webhook
     */
    public function webhook(Request $request): JsonResponse
    {
        $data = $request->all();

        Log::info('PayU webhook recibido', ['reference' => $data['reference_sale'] ?? 'N/A']);

        // Validar firma
        if (!$this->payUService->validateWebhookSignature($data)) {
            Log::warning('PayU webhook: firma inválida', ['data' => $data]);
            return response()->json(['message' => 'Firma inválida'], 400);
        }

        // Despachar job para procesamiento asíncrono
        ProcessPayUNotification::dispatch($data);

        // Responder 200 inmediatamente (PayU reintenta si no recibe 200)
        return response()->json(['message' => 'OK']);
    }

    /**
     * Página de resultado tras pago (retorno de PayU).
     * GET /api/payments/result
     */
    public function result(Request $request): JsonResponse
    {
        $referenceCode   = $request->query('referenceCode');
        $transactionState = $request->query('transactionState');

        if (!$referenceCode) {
            return response()->json(['message' => 'Referencia no proporcionada'], 400);
        }

        $payment = Payment::where('payu_reference_code', $referenceCode)->first();

        if (!$payment) {
            return response()->json(['message' => 'Pago no encontrado'], 404);
        }

        // Mapear transactionState de PayU a un estado legible
        // 4 = Aprobada, 6 = Rechazada, 5 = Expirada, 7 = Pendiente
        $stateMap = [
            '4' => 'approved',
            '6' => 'declined',
            '5' => 'expired',
            '7' => 'pending',
        ];

        return response()->json([
            'reference_code'    => $referenceCode,
            'transaction_state' => $stateMap[$transactionState] ?? 'unknown',
            'payment_status'    => $payment->status,
            'amount'            => $payment->amount,
            'currency'          => $payment->currency,
        ]);
    }
}
