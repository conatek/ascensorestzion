<template>
    <div class="result-container">
        <!-- Loading -->
        <div v-if="loading" class="loading-state">
            <div class="spinner-border text-primary"></div>
            <p class="text-muted mt-3">Verificando estado del pago...</p>
        </div>

        <!-- Result -->
        <div v-else class="result-card">
            <!-- Approved -->
            <template v-if="state === 'approved'">
                <div class="result-icon result-approved">
                    <i class="fa fa-check-circle"></i>
                </div>
                <h2 class="result-title">Pago aprobado</h2>
                <p class="result-message">
                    Tu plan ha sido activado exitosamente. Ya puedes disfrutar de todas las funcionalidades.
                </p>
                <div class="result-details" v-if="paymentData">
                    <div class="detail-row">
                        <span>Referencia</span>
                        <span>{{ paymentData.reference_code }}</span>
                    </div>
                    <div class="detail-row">
                        <span>Monto</span>
                        <span>${{ Number(paymentData.amount).toLocaleString('es-CO') }} {{ paymentData.currency }}</span>
                    </div>
                </div>
                <router-link :to="{ name: 'home' }" class="btn-result btn-success-action">
                    <i class="fa fa-home me-2"></i> Ir al panel
                </router-link>
            </template>

            <!-- Pending -->
            <template v-else-if="state === 'pending'">
                <div class="result-icon result-pending">
                    <i class="fa fa-clock"></i>
                </div>
                <h2 class="result-title">Pago pendiente</h2>
                <p class="result-message">
                    Tu pago esta siendo procesado. Dependiendo del metodo de pago, puede tomar unos minutos u horas.
                    Te notificaremos cuando se confirme.
                </p>
                <router-link :to="{ name: 'home' }" class="btn-result btn-pending-action">
                    <i class="fa fa-home me-2"></i> Ir al panel
                </router-link>
            </template>

            <!-- Declined -->
            <template v-else-if="state === 'declined' || state === 'expired'">
                <div class="result-icon result-declined">
                    <i class="fa fa-times-circle"></i>
                </div>
                <h2 class="result-title">Pago no aprobado</h2>
                <p class="result-message">
                    Tu pago no pudo ser procesado. Puedes intentar nuevamente con otro metodo de pago.
                </p>
                <router-link :to="{ name: 'subscription.plans' }" class="btn-result btn-retry-action">
                    <i class="fa fa-redo me-2"></i> Intentar de nuevo
                </router-link>
            </template>

            <!-- Unknown -->
            <template v-else>
                <div class="result-icon result-pending">
                    <i class="fa fa-question-circle"></i>
                </div>
                <h2 class="result-title">Estado desconocido</h2>
                <p class="result-message">
                    No pudimos determinar el estado de tu pago. Contacta soporte si necesitas ayuda.
                </p>
                <router-link :to="{ name: 'home' }" class="btn-result btn-pending-action">
                    <i class="fa fa-home me-2"></i> Ir al panel
                </router-link>
            </template>
        </div>
    </div>
</template>

<script>
import paymentService from '@/services/paymentService.js';

export default {
    name: 'PaymentResult',

    data() {
        return {
            loading: true,
            state: null,
            paymentData: null,
        };
    },

    async created() {
        await this.checkResult();
    },

    methods: {
        async checkResult() {
            const referenceCode = this.$route.query.referenceCode;
            const transactionState = this.$route.query.transactionState;

            if (!referenceCode) {
                this.state = 'unknown';
                this.loading = false;
                return;
            }

            try {
                const { data } = await paymentService.result(referenceCode, transactionState);
                this.state = data.transaction_state;
                this.paymentData = data;
            } catch {
                this.state = 'unknown';
            } finally {
                this.loading = false;
            }
        },
    },
};
</script>

<style scoped>
.result-container {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 70vh;
    padding: 2rem;
}

.result-card {
    text-align: center;
    max-width: 460px;
    background: white;
    border-radius: 20px;
    padding: 3rem 2.5rem;
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
    border: 1px solid #e2e8f0;
}

.result-icon {
    font-size: 4rem;
    margin-bottom: 1.5rem;
}

.result-approved { color: #10b981; }
.result-pending  { color: #f59e0b; }
.result-declined { color: #ef4444; }

.result-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 0.75rem;
}

.result-message {
    font-size: 0.95rem;
    color: #64748b;
    line-height: 1.6;
    margin-bottom: 1.5rem;
}

.result-details {
    background: #f8fafc;
    border-radius: 10px;
    padding: 1rem;
    margin-bottom: 1.5rem;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    padding: 0.4rem 0;
    font-size: 0.9rem;
}

.detail-row span:first-child {
    color: #64748b;
}

.detail-row span:last-child {
    font-weight: 600;
    color: #1e293b;
}

.btn-result {
    display: inline-flex;
    align-items: center;
    padding: 0.75rem 1.5rem;
    border-radius: 10px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s;
}

.btn-success-action {
    background: linear-gradient(135deg, #10b981, #34d399);
    color: white;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.btn-success-action:hover {
    transform: translateY(-1px);
    color: white;
}

.btn-pending-action {
    background: linear-gradient(135deg, #8b5cf6, #a78bfa);
    color: white;
    box-shadow: 0 4px 12px rgba(139, 92, 246, 0.3);
}

.btn-pending-action:hover {
    transform: translateY(-1px);
    color: white;
}

.btn-retry-action {
    background: linear-gradient(135deg, #f59e0b, #fbbf24);
    color: white;
    box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
}

.btn-retry-action:hover {
    transform: translateY(-1px);
    color: white;
}

.loading-state {
    text-align: center;
    padding: 4rem 0;
}
</style>
