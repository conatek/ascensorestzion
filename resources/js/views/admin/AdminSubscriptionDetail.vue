<template>
    <div>
        <div v-if="loading" class="loading-state">
            <div class="spinner-border text-primary"></div>
        </div>

        <template v-else-if="sub">
            <!-- Header -->
            <div class="app-page-title">
                <div class="page-title-wrapper">
                    <div class="page-title-heading">
                        <div class="page-title-icon">
                            <i class="fa fa-file-contract icon-gradient bg-mean-fruit"></i>
                        </div>
                        <div>
                            Suscripcion #{{ sub.id }}
                            <div class="page-title-subheading text-muted">{{ sub.company?.name }}</div>
                        </div>
                    </div>
                    <div class="page-title-actions">
                        <router-link :to="{ name: 'admin.subscriptions' }" class="btn-action btn-back">
                            <i class="fa fa-arrow-left me-1"></i> Volver
                        </router-link>
                    </div>
                </div>
            </div>

            <div class="detail-grid">
                <!-- Info suscripción -->
                <div class="section-card">
                    <div class="section-header">
                        <i class="fa fa-info-circle section-icon icon-purple"></i>
                        <span>Detalles</span>
                    </div>
                    <div class="section-body">
                        <div class="detail-row">
                            <span>Empresa</span>
                            <span class="fw-600">{{ sub.company?.name }}</span>
                        </div>
                        <div class="detail-row">
                            <span>Plan</span>
                            <span class="fw-600">{{ sub.plan?.display_name }}</span>
                        </div>
                        <div class="detail-row">
                            <span>Estado</span>
                            <span class="status-badge" :class="'status-' + sub.status">{{ statusLabel(sub.status) }}</span>
                        </div>
                        <div class="detail-row">
                            <span>Inicio</span>
                            <span>{{ formatDate(sub.current_period_start) }}</span>
                        </div>
                        <div class="detail-row">
                            <span>Vence</span>
                            <span>{{ formatDate(sub.current_period_end) }}</span>
                        </div>
                        <div class="detail-row">
                            <span>Dias restantes</span>
                            <span :class="{ 'text-danger fw-bold': daysLeft <= 5 }">{{ daysLeft }}</span>
                        </div>
                        <div class="detail-row" v-if="sub.payment_method">
                            <span>Metodo pago</span>
                            <span>{{ sub.payment_method }}</span>
                        </div>

                        <!-- Actions -->
                        <div class="detail-actions">
                            <div class="action-group">
                                <label class="form-label">Cambiar plan</label>
                                <div class="inline-action">
                                    <select v-model="newPlanId" class="filter-select">
                                        <option v-for="plan in plans" :key="plan.id" :value="plan.id">{{ plan.display_name }}</option>
                                    </select>
                                    <button @click="changePlan" class="btn-sm btn-primary" :disabled="saving || newPlanId === sub.plan_id">Cambiar</button>
                                </div>
                            </div>

                            <div class="action-group">
                                <label class="form-label">Extender periodo</label>
                                <div class="inline-action">
                                    <input v-model.number="extendDays" type="number" class="filter-input" min="1" max="365" style="width:100px" />
                                    <span class="text-muted">dias</span>
                                    <button @click="extend" class="btn-sm btn-primary" :disabled="saving">Extender</button>
                                </div>
                            </div>

                            <button v-if="sub.status !== 'cancelled' && sub.status !== 'expired'"
                                    @click="cancel" class="btn-sm btn-danger" :disabled="saving">
                                <i class="fa fa-ban me-1"></i> Cancelar suscripcion
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Pagos asociados -->
                <div class="section-card">
                    <div class="section-header">
                        <i class="fa fa-receipt section-icon icon-purple"></i>
                        <span>Pagos asociados</span>
                    </div>
                    <div class="section-body">
                        <div v-if="!sub.payments?.length" class="empty-state">
                            <p class="text-muted">Sin pagos registrados.</p>
                        </div>
                        <table v-else class="data-table">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Monto</th>
                                    <th>Estado</th>
                                    <th>Metodo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="p in sub.payments" :key="p.id">
                                    <td>{{ formatDate(p.paid_at || p.created_at) }}</td>
                                    <td>${{ Number(p.amount).toLocaleString('es-CO') }}</td>
                                    <td><span class="status-badge" :class="'status-' + p.status">{{ p.status }}</span></td>
                                    <td>{{ p.payment_method || '-' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </template>
    </div>
</template>

<script>
import adminService from '@/services/adminService.js';

export default {
    name: 'AdminSubscriptionDetail',

    data() {
        return {
            sub: null,
            plans: [],
            loading: true,
            saving: false,
            newPlanId: null,
            extendDays: 30,
        };
    },

    computed: {
        daysLeft() {
            if (!this.sub?.current_period_end) return 0;
            const diff = Math.ceil((new Date(this.sub.current_period_end) - new Date()) / (1000 * 60 * 60 * 24));
            return Math.max(0, diff);
        },
    },

    async created() {
        const [, plansRes] = await Promise.all([
            this.load(),
            adminService.getPlans(),
        ]);
        this.plans = plansRes.data;
    },

    methods: {
        async load() {
            this.loading = true;
            try {
                const { data } = await adminService.getSubscription(this.$route.params.id);
                this.sub = data;
                this.newPlanId = data.plan_id;
            } finally {
                this.loading = false;
            }
        },

        statusLabel(s) {
            return { trial: 'Prueba', active: 'Activo', past_due: 'Vencido', expired: 'Expirado', cancelled: 'Cancelado' }[s] || s;
        },

        formatDate(d) {
            if (!d) return '-';
            return new Date(d).toLocaleDateString('es-CO', { year: 'numeric', month: 'short', day: 'numeric' });
        },

        async changePlan() {
            this.saving = true;
            try {
                await adminService.updateSubscription(this.sub.id, { plan_id: this.newPlanId });
                await this.load();
            } finally {
                this.saving = false;
            }
        },

        async extend() {
            this.saving = true;
            try {
                await adminService.extendSubscription(this.sub.id, this.extendDays);
                await this.load();
            } finally {
                this.saving = false;
            }
        },

        async cancel() {
            if (!confirm('¿Cancelar esta suscripción?')) return;
            this.saving = true;
            try {
                await adminService.cancelSubscription(this.sub.id);
                await this.load();
            } finally {
                this.saving = false;
            }
        },
    },
};
</script>

<style scoped>
.detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; align-items: start; }
.section-card { background: white; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 2px 8px rgba(0,0,0,0.04); }
.section-header { padding: 1rem 1.25rem; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; gap: 0.75rem; font-weight: 600; color: #1e293b; border-radius: 12px 12px 0 0; }
.section-icon { width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 8px; }
.icon-purple { background: #f3e8ff; color: #7c3aed; }
.section-body { padding: 1.25rem; }

.detail-row { display: flex; justify-content: space-between; padding: 0.6rem 0; border-bottom: 1px solid #f1f5f9; font-size: 0.9rem; }
.detail-row span:first-child { color: #64748b; }
.fw-600 { font-weight: 600; color: #1e293b; }

.status-badge { display: inline-block; padding: 0.2rem 0.6rem; border-radius: 6px; font-size: 0.8rem; font-weight: 600; }
.status-trial { background: #fef3c7; color: #d97706; }
.status-active { background: #d1fae5; color: #059669; }
.status-past_due { background: #fee2e2; color: #dc2626; }
.status-expired { background: #fecaca; color: #b91c1c; }
.status-cancelled { background: #f1f5f9; color: #64748b; }
.status-pending { background: #fef3c7; color: #d97706; }
.status-approved { background: #d1fae5; color: #059669; }
.status-declined { background: #fee2e2; color: #dc2626; }

.detail-actions { margin-top: 1.5rem; padding-top: 1rem; border-top: 2px solid #e2e8f0; display: flex; flex-direction: column; gap: 1rem; }
.action-group { }
.form-label { display: block; font-size: 0.8rem; font-weight: 600; color: #64748b; margin-bottom: 0.35rem; text-transform: uppercase; letter-spacing: 0.04em; }
.inline-action { display: flex; align-items: center; gap: 0.5rem; }
.filter-select, .filter-input { padding: 0.4rem 0.6rem; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.9rem; }
.filter-select:focus, .filter-input:focus { outline: none; border-color: #7c3aed; }

.btn-sm { padding: 0.4rem 0.75rem; border-radius: 6px; font-size: 0.85rem; font-weight: 600; cursor: pointer; border: none; transition: all 0.2s; }
.btn-primary { background: #7c3aed; color: white; }
.btn-primary:hover:not(:disabled) { background: #6d28d9; }
.btn-danger { background: #ef4444; color: white; }
.btn-danger:hover:not(:disabled) { background: #dc2626; }
.btn-sm:disabled { opacity: 0.6; cursor: not-allowed; }

.text-danger { color: #dc2626 !important; }
.fw-bold { font-weight: 700; }
.text-muted { color: #94a3b8; }

.data-table { width: 100%; border-collapse: collapse; }
.data-table th { text-align: left; padding: 0.6rem 0.75rem; font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase; border-bottom: 2px solid #e2e8f0; }
.data-table td { padding: 0.6rem 0.75rem; font-size: 0.85rem; color: #334155; border-bottom: 1px solid #f1f5f9; }

.empty-state { text-align: center; padding: 2rem 0; }
.loading-state { text-align: center; padding: 4rem 0; }
.btn-action.btn-back { padding: 0.5rem 1rem; background: #f1f5f9; color: #475569; border-radius: 8px; text-decoration: none; font-size: 0.9rem; font-weight: 500; border: 1px solid #e2e8f0; }

@media (max-width: 768px) { .detail-grid { grid-template-columns: 1fr; } }
</style>
