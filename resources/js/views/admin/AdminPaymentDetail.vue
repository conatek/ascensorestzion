<template>
    <div>
        <div v-if="loading" class="loading-state">
            <div class="spinner-border text-primary"></div>
        </div>

        <template v-else-if="payment">
            <div class="app-page-title">
                <div class="page-title-wrapper">
                    <div class="page-title-heading">
                        <div class="page-title-icon">
                            <i class="fa fa-receipt icon-gradient bg-mean-fruit"></i>
                        </div>
                        <div>
                            Pago #{{ payment.id }}
                            <div class="page-title-subheading text-muted">{{ payment.company?.name }}</div>
                        </div>
                    </div>
                    <div class="page-title-actions d-flex gap-2">
                        <router-link :to="{ name: 'admin.payments' }" class="btn-action btn-back">
                            <i class="fa fa-arrow-left me-1"></i> Volver
                        </router-link>
                        <button v-if="payment.status === 'pending'" @click="approve" class="btn-action btn-approve" :disabled="acting">
                            <i class="fa fa-check me-1"></i> Aprobar
                        </button>
                        <button v-if="payment.status === 'approved'" @click="refund" class="btn-action btn-refund" :disabled="acting">
                            <i class="fa fa-undo me-1"></i> Reembolsar
                        </button>
                    </div>
                </div>
            </div>

            <div class="detail-grid">
                <!-- Info pago -->
                <div class="section-card">
                    <div class="section-header">
                        <i class="fa fa-info-circle section-icon icon-purple"></i>
                        <span>Detalles del pago</span>
                    </div>
                    <div class="section-body">
                        <div class="detail-row">
                            <span>Monto</span>
                            <span class="fw-600">${{ Number(payment.amount).toLocaleString('es-CO') }} {{ payment.currency }}</span>
                        </div>
                        <div class="detail-row">
                            <span>Estado</span>
                            <span class="status-badge" :class="'status-' + payment.status">{{ statusLabel(payment.status) }}</span>
                        </div>
                        <div class="detail-row">
                            <span>Metodo</span>
                            <span>{{ payment.payment_method || '-' }}</span>
                        </div>
                        <div class="detail-row">
                            <span>Fecha pago</span>
                            <span>{{ formatDate(payment.paid_at) }}</span>
                        </div>
                        <div class="detail-row">
                            <span>Fecha creacion</span>
                            <span>{{ formatDate(payment.created_at) }}</span>
                        </div>
                        <div class="detail-row" v-if="payment.payu_reference_code">
                            <span>Referencia PayU</span>
                            <span class="mono">{{ payment.payu_reference_code }}</span>
                        </div>
                        <div class="detail-row" v-if="payment.payu_transaction_id">
                            <span>Transaccion PayU</span>
                            <span class="mono">{{ payment.payu_transaction_id }}</span>
                        </div>
                        <div class="detail-row" v-if="payment.payu_order_id">
                            <span>Orden PayU</span>
                            <span class="mono">{{ payment.payu_order_id }}</span>
                        </div>
                        <div class="detail-row" v-if="payment.response_code">
                            <span>Codigo respuesta</span>
                            <span class="mono">{{ payment.response_code }}</span>
                        </div>
                    </div>
                </div>

                <!-- Info empresa + Metadata -->
                <div>
                    <!-- Empresa -->
                    <div class="section-card">
                        <div class="section-header">
                            <i class="fa fa-building section-icon icon-purple"></i>
                            <span>Empresa</span>
                        </div>
                        <div class="section-body">
                            <div class="detail-row">
                                <span>Nombre</span>
                                <span class="fw-600">{{ payment.company?.name }}</span>
                            </div>
                            <div class="detail-row" v-if="payment.company?.slug">
                                <span>Slug</span>
                                <span class="mono">{{ payment.company.slug }}</span>
                            </div>
                            <div class="detail-row" v-if="payment.subscription?.plan">
                                <span>Plan asociado</span>
                                <span>{{ payment.subscription.plan.display_name }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Metadata PayU -->
                    <div class="section-card" v-if="payment.metadata && Object.keys(payment.metadata).length">
                        <div class="section-header">
                            <i class="fa fa-code section-icon icon-purple"></i>
                            <span>Metadata PayU</span>
                            <button @click="showMetadata = !showMetadata" class="toggle-meta">
                                {{ showMetadata ? 'Ocultar' : 'Mostrar' }}
                            </button>
                        </div>
                        <div class="section-body" v-if="showMetadata">
                            <pre class="metadata-json">{{ JSON.stringify(payment.metadata, null, 2) }}</pre>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</template>

<script>
import adminService from '@/services/adminService.js';

export default {
    name: 'AdminPaymentDetail',

    data() {
        return {
            payment: null,
            loading: true,
            acting: false,
            showMetadata: false,
        };
    },

    async created() {
        await this.load();
    },

    methods: {
        async load() {
            this.loading = true;
            try {
                const { data } = await adminService.getPayment(this.$route.params.id);
                this.payment = data;
            } finally {
                this.loading = false;
            }
        },

        statusLabel(s) {
            return { pending: 'Pendiente', approved: 'Aprobado', declined: 'Rechazado', refunded: 'Reembolsado' }[s] || s;
        },

        formatDate(d) {
            if (!d) return '-';
            return new Date(d).toLocaleDateString('es-CO', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
        },

        async approve() {
            if (!confirm('¿Aprobar este pago y activar la suscripción?')) return;
            this.acting = true;
            try {
                await adminService.approvePayment(this.payment.id);
                await this.load();
            } finally {
                this.acting = false;
            }
        },

        async refund() {
            if (!confirm('¿Registrar reembolso para este pago?')) return;
            this.acting = true;
            try {
                await adminService.refundPayment(this.payment.id);
                await this.load();
            } finally {
                this.acting = false;
            }
        },
    },
};
</script>

<style scoped>
.detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; align-items: start; }
.section-card { background: white; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 2px 8px rgba(0,0,0,0.04); margin-bottom: 1.5rem; }
.section-header { padding: 1rem 1.25rem; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; gap: 0.75rem; font-weight: 600; color: #1e293b; border-radius: 12px 12px 0 0; }
.section-icon { width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 8px; }
.icon-purple { background: #f3e8ff; color: #7c3aed; }
.section-body { padding: 1.25rem; }

.detail-row { display: flex; justify-content: space-between; padding: 0.6rem 0; border-bottom: 1px solid #f1f5f9; font-size: 0.9rem; }
.detail-row span:first-child { color: #64748b; }
.fw-600 { font-weight: 600; color: #1e293b; }
.mono { font-family: monospace; font-size: 0.85rem; color: #475569; }

.status-badge { display: inline-block; padding: 0.2rem 0.6rem; border-radius: 6px; font-size: 0.8rem; font-weight: 600; }
.status-pending { background: #fef3c7; color: #d97706; }
.status-approved { background: #d1fae5; color: #059669; }
.status-declined { background: #fee2e2; color: #dc2626; }
.status-refunded { background: #e0f2fe; color: #0284c7; }

.toggle-meta { margin-left: auto; padding: 0.2rem 0.6rem; background: #e2e8f0; border: none; border-radius: 6px; font-size: 0.75rem; font-weight: 600; color: #64748b; cursor: pointer; }
.toggle-meta:hover { background: #cbd5e1; }

.metadata-json { background: #0f172a; color: #e2e8f0; padding: 1rem; border-radius: 8px; font-size: 0.8rem; overflow-x: auto; white-space: pre; margin: 0; }

.btn-action { padding: 0.5rem 1rem; border-radius: 8px; text-decoration: none; font-size: 0.9rem; font-weight: 500; border: 1px solid #e2e8f0; cursor: pointer; transition: all 0.2s; }
.btn-back { background: #f1f5f9; color: #475569; }
.btn-approve { background: #059669; color: white; border-color: #059669; }
.btn-approve:hover:not(:disabled) { background: #047857; }
.btn-refund { background: #d97706; color: white; border-color: #d97706; }
.btn-refund:hover:not(:disabled) { background: #b45309; }
.btn-action:disabled { opacity: 0.6; cursor: not-allowed; }

.d-flex { display: flex; }
.gap-2 { gap: 0.5rem; }
.loading-state { text-align: center; padding: 4rem 0; }

@media (max-width: 768px) { .detail-grid { grid-template-columns: 1fr; } }
</style>
