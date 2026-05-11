<template>
    <div>
        <div class="app-page-title">
            <div class="page-title-wrapper">
                <div class="page-title-heading">
                    <div class="page-title-icon">
                        <i class="fa fa-crown icon-gradient bg-mean-fruit"></i>
                    </div>
                    <div>
                        Mi Suscripcion
                        <div class="page-title-subheading text-muted">
                            Estado de tu plan y uso de recursos
                        </div>
                    </div>
                </div>
                <div class="page-title-actions">
                    <router-link :to="{ name: 'subscription.plans' }" class="btn-action btn-upgrade">
                        <i class="fa fa-arrow-up me-1"></i> Mejorar plan
                    </router-link>
                </div>
            </div>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="loading-state">
            <div class="spinner-border text-primary"></div>
            <p class="text-muted mt-3">Cargando suscripcion...</p>
        </div>

        <template v-else>
            <!-- Plan info -->
            <div class="info-grid">
                <div class="info-card plan-info">
                    <div class="info-header">
                        <i class="fa fa-crown"></i>
                        <span>Plan actual</span>
                    </div>
                    <div class="info-body">
                        <div class="plan-badge" :class="'badge-' + (plan?.name || 'guest')">
                            {{ plan?.display_name || 'Sin plan' }}
                        </div>
                        <div class="plan-meta" v-if="subscription">
                            <div class="meta-row">
                                <span>Estado</span>
                                <span class="status-tag" :class="'status-' + subscription.status">
                                    {{ statusLabel }}
                                </span>
                            </div>
                            <div class="meta-row" v-if="subscription.current_period_end">
                                <span>Vence</span>
                                <span>{{ formatDate(subscription.current_period_end) }}</span>
                            </div>
                            <div class="meta-row" v-if="daysLeft !== null">
                                <span>Dias restantes</span>
                                <span :class="{ 'text-danger': daysLeft <= 5 }">{{ daysLeft }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Usage -->
                <div class="info-card">
                    <div class="info-header">
                        <i class="fa fa-chart-bar"></i>
                        <span>Uso de recursos</span>
                    </div>
                    <div class="info-body">
                        <div class="usage-item" v-for="(res, key) in usage" :key="key">
                            <div class="usage-label">
                                <span>{{ resourceNames[key] }}</span>
                                <span class="usage-count">{{ res.current }} / {{ res.limit ?? '∞' }}</span>
                            </div>
                            <div class="usage-bar">
                                <div class="usage-fill" :style="{ width: usagePercent(res) + '%' }"
                                     :class="{ 'usage-full': usagePercent(res) >= 100 }"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment history -->
            <div class="section-card">
                <div class="section-header">
                    <i class="fa fa-receipt section-icon icon-purple"></i>
                    <span>Historial de pagos</span>
                </div>
                <div class="section-body">
                    <div v-if="payments.length === 0" class="empty-section">
                        <div class="empty-icon"><i class="fa fa-receipt"></i></div>
                        <p>Sin pagos registrados.</p>
                    </div>
                    <table v-else class="payments-table">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Monto</th>
                                <th>Metodo</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="payment in payments" :key="payment.id">
                                <td>{{ formatDate(payment.paid_at || payment.created_at) }}</td>
                                <td>${{ Number(payment.amount).toLocaleString('es-CO') }}</td>
                                <td>{{ payment.payment_method || '-' }}</td>
                                <td>
                                    <span class="status-tag" :class="'status-' + payment.status">
                                        {{ paymentStatusLabel(payment.status) }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </template>
    </div>
</template>

<script>
import subscriptionService from '@/services/subscriptionService.js';
import paymentService from '@/services/paymentService.js';

export default {
    name: 'MySubscription',

    data() {
        return {
            subscription: null,
            plan: null,
            usage: {},
            payments: [],
            loading: true,
            resourceNames: {
                cards: 'Tarjetas',
                products: 'Productos',
                services: 'Servicios',
            },
        };
    },

    computed: {
        statusLabel() {
            const labels = {
                trial: 'Prueba',
                active: 'Activo',
                past_due: 'Vencido',
                cancelled: 'Cancelado',
                expired: 'Expirado',
            };
            return labels[this.subscription?.status] || this.subscription?.status;
        },

        daysLeft() {
            if (!this.subscription?.current_period_end) return null;
            const end = new Date(this.subscription.current_period_end);
            const now = new Date();
            const diff = Math.ceil((end - now) / (1000 * 60 * 60 * 24));
            return Math.max(0, diff);
        },
    },

    async created() {
        await this.load();
    },

    methods: {
        async load() {
            this.loading = true;
            try {
                const [subRes, payRes] = await Promise.all([
                    subscriptionService.current(),
                    paymentService.history(),
                ]);
                this.subscription = subRes.data.subscription;
                this.plan = subRes.data.plan;
                this.usage = subRes.data.usage || {};
                this.payments = payRes.data.data || payRes.data || [];
            } finally {
                this.loading = false;
            }
        },

        usagePercent(resource) {
            if (!resource.limit) return 0; // ilimitado
            return Math.min(100, (resource.current / resource.limit) * 100);
        },

        formatDate(dateStr) {
            if (!dateStr) return '-';
            return new Date(dateStr).toLocaleDateString('es-CO', {
                year: 'numeric', month: 'short', day: 'numeric',
            });
        },

        paymentStatusLabel(status) {
            const labels = {
                pending: 'Pendiente',
                approved: 'Aprobado',
                declined: 'Rechazado',
                refunded: 'Reembolsado',
            };
            return labels[status] || status;
        },
    },
};
</script>

<style scoped>
.info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}

.info-card {
    background: white;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}

.info-header {
    padding: 1rem 1.25rem;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-weight: 600;
    color: #1e293b;
    border-radius: 12px 12px 0 0;
}

.info-header i {
    color: #7c3aed;
}

.info-body {
    padding: 1.25rem;
}

/* Plan badge */
.plan-badge {
    display: inline-block;
    padding: 0.5rem 1.25rem;
    border-radius: 10px;
    font-weight: 700;
    font-size: 1.1rem;
    margin-bottom: 1rem;
}

.badge-guest { background: #f1f5f9; color: #64748b; }
.badge-basico { background: linear-gradient(135deg, #dbeafe, #e0f2fe); color: #2563eb; }
.badge-pro { background: linear-gradient(135deg, #f3e8ff, #fce7f3); color: #8b5cf6; }

/* Meta rows */
.plan-meta {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.meta-row {
    display: flex;
    justify-content: space-between;
    font-size: 0.9rem;
}

.meta-row span:first-child { color: #64748b; }
.meta-row span:last-child { font-weight: 500; color: #1e293b; }

/* Status tags */
.status-tag {
    display: inline-block;
    padding: 0.2rem 0.6rem;
    border-radius: 6px;
    font-size: 0.8rem;
    font-weight: 600;
}

.status-trial { background: #fef3c7; color: #d97706; }
.status-active { background: #d1fae5; color: #059669; }
.status-past_due { background: #fee2e2; color: #dc2626; }
.status-cancelled { background: #f1f5f9; color: #64748b; }
.status-expired { background: #fecaca; color: #b91c1c; }
.status-pending { background: #fef3c7; color: #d97706; }
.status-approved { background: #d1fae5; color: #059669; }
.status-declined { background: #fee2e2; color: #dc2626; }
.status-refunded { background: #e0f2fe; color: #0284c7; }

/* Usage bars */
.usage-item {
    margin-bottom: 1rem;
}

.usage-item:last-child {
    margin-bottom: 0;
}

.usage-label {
    display: flex;
    justify-content: space-between;
    font-size: 0.9rem;
    margin-bottom: 0.4rem;
    color: #475569;
}

.usage-count {
    font-weight: 600;
    color: #1e293b;
}

.usage-bar {
    height: 8px;
    background: #e2e8f0;
    border-radius: 4px;
    overflow: hidden;
}

.usage-fill {
    height: 100%;
    background: linear-gradient(135deg, #8b5cf6, #a78bfa);
    border-radius: 4px;
    transition: width 0.5s ease;
}

.usage-fill.usage-full {
    background: linear-gradient(135deg, #ef4444, #f87171);
}

/* Section card (payment history) */
.section-card {
    background: white;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}

.section-header {
    padding: 1rem 1.25rem;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-weight: 600;
    color: #1e293b;
    border-radius: 12px 12px 0 0;
}

.section-icon {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
}

.icon-purple {
    background: #f3e8ff;
    color: #7c3aed;
}

.section-body {
    padding: 1.25rem;
}

.empty-section {
    text-align: center;
    padding: 2rem 0;
    color: #94a3b8;
}

.empty-icon {
    font-size: 2rem;
    margin-bottom: 0.5rem;
}

/* Payments table */
.payments-table {
    width: 100%;
    border-collapse: collapse;
}

.payments-table th {
    text-align: left;
    padding: 0.75rem;
    font-size: 0.8rem;
    font-weight: 600;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    border-bottom: 2px solid #e2e8f0;
}

.payments-table td {
    padding: 0.75rem;
    font-size: 0.9rem;
    color: #334155;
    border-bottom: 1px solid #f1f5f9;
}

/* Buttons */
.btn-action.btn-upgrade {
    padding: 0.5rem 1rem;
    background: linear-gradient(135deg, #8b5cf6, #ec4899);
    color: white;
    border-radius: 8px;
    text-decoration: none;
    font-size: 0.9rem;
    font-weight: 600;
    transition: all 0.2s;
    box-shadow: 0 2px 8px rgba(139, 92, 246, 0.3);
}

.btn-action.btn-upgrade:hover {
    transform: translateY(-1px);
    color: white;
}

.text-danger { color: #dc2626 !important; font-weight: 600; }

.loading-state {
    text-align: center;
    padding: 4rem 0;
}

/* Responsive */
@media (max-width: 768px) {
    .info-grid {
        grid-template-columns: 1fr;
    }
}
</style>
