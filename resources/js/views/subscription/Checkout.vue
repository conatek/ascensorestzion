<template>
    <div>
        <div class="app-page-title">
            <div class="page-title-wrapper">
                <div class="page-title-heading">
                    <div class="page-title-icon">
                        <i class="fa fa-credit-card icon-gradient bg-mean-fruit"></i>
                    </div>
                    <div>
                        Confirmar Plan
                        <div class="page-title-subheading text-muted">
                            Revisa los detalles antes de proceder al pago
                        </div>
                    </div>
                </div>
                <div class="page-title-actions">
                    <router-link :to="{ name: 'subscription.plans' }" class="btn-action btn-back">
                        <i class="fa fa-arrow-left me-1"></i> Volver
                    </router-link>
                </div>
            </div>
        </div>

        <!-- Loading plan -->
        <div v-if="loadingPlan" class="loading-state">
            <div class="spinner-border text-primary"></div>
            <p class="text-muted mt-3">Cargando plan...</p>
        </div>

        <!-- Checkout content -->
        <div v-else-if="plan" class="checkout-container">
            <div class="checkout-card">
                <div class="checkout-header">
                    <h3>Resumen del pedido</h3>
                </div>
                <div class="checkout-body">
                    <div class="checkout-row">
                        <span class="label">Plan</span>
                        <span class="value">{{ plan.display_name }}</span>
                    </div>
                    <div class="checkout-row">
                        <span class="label">Periodo</span>
                        <span class="value">{{ billingPeriod === 'yearly' ? 'Anual' : 'Mensual' }}</span>
                    </div>
                    <div class="checkout-divider"></div>
                    <div class="checkout-row total">
                        <span class="label">Total</span>
                        <span class="value">${{ formatPrice }} COP</span>
                    </div>
                </div>
                <div class="checkout-footer">
                    <div v-if="error" class="error-alert">
                        <i class="fa fa-exclamation-circle"></i>
                        {{ error }}
                    </div>
                    <button class="btn-checkout" :disabled="processing" @click="proceedToPayment">
                        <span v-if="processing" class="spinner"></span>
                        <i v-else class="fa fa-lock me-2"></i>
                        {{ processing ? 'Procesando...' : 'Proceder al pago' }}
                    </button>
                    <p class="checkout-note">
                        <i class="fa fa-shield-alt me-1"></i>
                        Seras redirigido a PayU para completar el pago de forma segura.
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import planService from '@/services/planService.js';
import paymentService from '@/services/paymentService.js';

export default {
    name: 'Checkout',

    data() {
        return {
            plan: null,
            loadingPlan: true,
            processing: false,
            error: null,
            billingPeriod: 'monthly',
        };
    },

    computed: {
        formatPrice() {
            if (!this.plan) return '0';
            const price = this.billingPeriod === 'yearly' ? this.plan.price_yearly : this.plan.price_monthly;
            return Number(price).toLocaleString('es-CO', { maximumFractionDigits: 0 });
        },
    },

    async created() {
        this.billingPeriod = this.$route.query.period || 'monthly';
        await this.loadPlan();
    },

    methods: {
        async loadPlan() {
            this.loadingPlan = true;
            try {
                const { data } = await planService.all();
                const planId = parseInt(this.$route.params.planId);
                this.plan = data.find(p => p.id === planId) || null;

                if (!this.plan) {
                    this.error = 'Plan no encontrado.';
                }
            } catch {
                this.error = 'Error al cargar el plan.';
            } finally {
                this.loadingPlan = false;
            }
        },

        async proceedToPayment() {
            this.processing = true;
            this.error = null;

            try {
                const { data } = await paymentService.checkout(this.plan.id, this.billingPeriod);

                // Crear form oculto y enviar a PayU WebCheckout
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = data.checkout_url;

                for (const [key, value] of Object.entries(data.form_data)) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = key;
                    input.value = value;
                    form.appendChild(input);
                }

                document.body.appendChild(form);
                form.submit();
            } catch (err) {
                this.error = err.response?.data?.message || 'Error al iniciar el pago.';
                this.processing = false;
            }
        },
    },
};
</script>

<style scoped>
.checkout-container {
    max-width: 480px;
    margin: 0 auto;
}

.checkout-card {
    background: white;
    border-radius: 16px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
    overflow: hidden;
}

.checkout-header {
    padding: 1.25rem 1.5rem;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
}

.checkout-header h3 {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 600;
    color: #1e293b;
}

.checkout-body {
    padding: 1.5rem;
}

.checkout-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
}

.checkout-row .label {
    color: #64748b;
    font-size: 0.95rem;
}

.checkout-row .value {
    font-weight: 600;
    color: #1e293b;
}

.checkout-row.total .value {
    font-size: 1.25rem;
    color: #7c3aed;
}

.checkout-divider {
    height: 1px;
    background: #e2e8f0;
    margin: 0.5rem 0;
}

.checkout-footer {
    padding: 1.5rem;
    border-top: 1px solid #e2e8f0;
}

.btn-checkout {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    padding: 0.875rem;
    background: linear-gradient(135deg, #8b5cf6, #ec4899);
    color: white;
    border: none;
    border-radius: 10px;
    font-weight: 600;
    font-size: 1rem;
    cursor: pointer;
    transition: all 0.2s;
    box-shadow: 0 4px 12px rgba(139, 92, 246, 0.3);
}

.btn-checkout:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 6px 16px rgba(139, 92, 246, 0.4);
}

.btn-checkout:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

.checkout-note {
    text-align: center;
    font-size: 0.8rem;
    color: #94a3b8;
    margin-top: 1rem;
    margin-bottom: 0;
}

.error-alert {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    background: #fef2f2;
    border: 1px solid #fecaca;
    border-radius: 8px;
    color: #dc2626;
    font-size: 0.9rem;
    margin-bottom: 1rem;
}

.spinner {
    width: 18px;
    height: 18px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-top-color: white;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
    margin-right: 0.5rem;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.loading-state {
    text-align: center;
    padding: 4rem 0;
}

.btn-action.btn-back {
    padding: 0.5rem 1rem;
    background: #f1f5f9;
    color: #475569;
    border-radius: 8px;
    text-decoration: none;
    font-size: 0.9rem;
    font-weight: 500;
    transition: all 0.2s;
    border: 1px solid #e2e8f0;
}

.btn-action.btn-back:hover {
    background: #e2e8f0;
}
</style>
