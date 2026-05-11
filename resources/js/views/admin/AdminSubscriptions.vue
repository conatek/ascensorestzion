<template>
    <div>
        <div class="app-page-title">
            <div class="page-title-wrapper">
                <div class="page-title-heading">
                    <div class="page-title-icon">
                        <i class="fa fa-file-contract icon-gradient bg-mean-fruit"></i>
                    </div>
                    <div>
                        Suscripciones
                        <div class="page-title-subheading text-muted">
                            Gestiona las suscripciones de todas las empresas
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="filters-bar">
            <select v-model="filters.status" @change="load" class="filter-select">
                <option value="">Todos los estados</option>
                <option value="trial">Prueba</option>
                <option value="active">Activo</option>
                <option value="past_due">Vencido</option>
                <option value="expired">Expirado</option>
                <option value="cancelled">Cancelado</option>
            </select>
            <select v-model="filters.plan_id" @change="load" class="filter-select">
                <option value="">Todos los planes</option>
                <option v-for="plan in plans" :key="plan.id" :value="plan.id">{{ plan.display_name }}</option>
            </select>
            <input v-model="filters.search" @input="debounceSearch" type="text" class="filter-input" placeholder="Buscar empresa..." />
        </div>

        <!-- Loading -->
        <div v-if="loading" class="loading-state">
            <div class="spinner-border text-primary"></div>
        </div>

        <!-- Table -->
        <div v-else class="section-card">
            <div class="section-body table-responsive">
                <table class="data-table" v-if="subscriptions.length">
                    <thead>
                        <tr>
                            <th>Empresa</th>
                            <th>Plan</th>
                            <th>Estado</th>
                            <th>Inicio</th>
                            <th>Vence</th>
                            <th>Dias</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="sub in subscriptions" :key="sub.id">
                            <td>
                                <div class="cell-primary">{{ sub.company?.name }}</div>
                            </td>
                            <td>{{ sub.plan?.display_name }}</td>
                            <td>
                                <span class="status-badge" :class="'status-' + sub.status">{{ statusLabel(sub.status) }}</span>
                            </td>
                            <td>{{ formatDate(sub.current_period_start) }}</td>
                            <td>{{ formatDate(sub.current_period_end) }}</td>
                            <td>
                                <span :class="{ 'text-danger fw-bold': daysLeft(sub) <= 5 && daysLeft(sub) >= 0 }">
                                    {{ daysLeft(sub) }}
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <router-link :to="{ name: 'admin.subscriptions.show', params: { id: sub.id } }" class="action-btn" title="Ver detalle">
                                        <i class="fa fa-eye"></i>
                                    </router-link>
                                    <button @click="openExtendModal(sub)" class="action-btn" title="Extender">
                                        <i class="fa fa-calendar-plus"></i>
                                    </button>
                                    <button v-if="sub.status !== 'cancelled' && sub.status !== 'expired'" @click="confirmCancel(sub)" class="action-btn action-warning" title="Cancelar" :disabled="cancelling === sub.id">
                                        <i class="fa fa-ban"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div v-else class="empty-state">
                    <p class="text-muted">No se encontraron suscripciones.</p>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="pagination.last_page > 1" class="pagination-bar">
                <button @click="goToPage(pagination.current_page - 1)" :disabled="pagination.current_page <= 1" class="page-btn">
                    <i class="fa fa-chevron-left"></i>
                </button>
                <span class="page-info">{{ pagination.current_page }} / {{ pagination.last_page }}</span>
                <button @click="goToPage(pagination.current_page + 1)" :disabled="pagination.current_page >= pagination.last_page" class="page-btn">
                    <i class="fa fa-chevron-right"></i>
                </button>
            </div>
        </div>

        <!-- Extend Modal -->
        <div v-if="extendTarget" class="modal-overlay" @click.self="extendTarget = null">
            <div class="modal-container">
                <h4 class="modal-title">Extender periodo</h4>
                <p class="modal-message">Empresa: <strong>{{ extendTarget.company?.name }}</strong></p>
                <div class="form-group">
                    <label class="form-label">Dias a agregar</label>
                    <input v-model.number="extendDays" type="number" class="form-input" min="1" max="365" />
                </div>
                <div class="modal-actions">
                    <button class="modal-btn modal-btn-cancel" @click="extendTarget = null">Cancelar</button>
                    <button class="modal-btn modal-btn-primary" @click="extend" :disabled="extending">
                        {{ extending ? 'Extendiendo...' : 'Extender' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import adminService from '@/services/adminService.js';

export default {
    name: 'AdminSubscriptions',

    data() {
        return {
            subscriptions: [],
            plans: [],
            loading: true,
            filters: { status: '', plan_id: '', search: '' },
            pagination: {},
            cancelling: null,
            extendTarget: null,
            extendDays: 30,
            extending: false,
            searchTimeout: null,
        };
    },

    async created() {
        const { data } = await adminService.getPlans();
        this.plans = data;
        await this.load();
    },

    methods: {
        async load(page = 1) {
            this.loading = true;
            try {
                const params = { ...this.filters, page };
                const { data } = await adminService.getSubscriptions(params);
                this.subscriptions = data.data;
                this.pagination = { current_page: data.current_page, last_page: data.last_page };
            } finally {
                this.loading = false;
            }
        },

        debounceSearch() {
            clearTimeout(this.searchTimeout);
            this.searchTimeout = setTimeout(() => this.load(), 400);
        },

        goToPage(page) {
            this.load(page);
        },

        statusLabel(status) {
            const labels = { trial: 'Prueba', active: 'Activo', past_due: 'Vencido', expired: 'Expirado', cancelled: 'Cancelado' };
            return labels[status] || status;
        },

        daysLeft(sub) {
            if (!sub.current_period_end) return 0;
            const diff = Math.ceil((new Date(sub.current_period_end) - new Date()) / (1000 * 60 * 60 * 24));
            return Math.max(0, diff);
        },

        formatDate(d) {
            if (!d) return '-';
            return new Date(d).toLocaleDateString('es-CO', { year: 'numeric', month: 'short', day: 'numeric' });
        },

        openExtendModal(sub) {
            this.extendTarget = sub;
            this.extendDays = 30;
        },

        async extend() {
            this.extending = true;
            try {
                await adminService.extendSubscription(this.extendTarget.id, this.extendDays);
                this.extendTarget = null;
                await this.load(this.pagination.current_page);
            } finally {
                this.extending = false;
            }
        },

        async confirmCancel(sub) {
            if (!confirm(`¿Cancelar suscripción de ${sub.company?.name}?`)) return;
            this.cancelling = sub.id;
            try {
                await adminService.cancelSubscription(sub.id);
                sub.status = 'cancelled';
            } finally {
                this.cancelling = null;
            }
        },
    },
};
</script>

<style scoped>
.filters-bar { display: flex; gap: 0.75rem; margin-bottom: 1.25rem; flex-wrap: wrap; }
.filter-select, .filter-input { padding: 0.5rem 0.75rem; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 0.9rem; background: white; }
.filter-input { flex: 1; min-width: 200px; }
.filter-select:focus, .filter-input:focus { outline: none; border-color: #7c3aed; box-shadow: 0 0 0 3px rgba(124,58,237,0.1); }

.section-card { background: white; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 2px 8px rgba(0,0,0,0.04); }
.section-body { padding: 0; }
.table-responsive { overflow-x: auto; }

.data-table { width: 100%; border-collapse: collapse; }
.data-table th { text-align: left; padding: 0.875rem 1rem; font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; background: #f8fafc; border-bottom: 2px solid #e2e8f0; white-space: nowrap; }
.data-table td { padding: 0.875rem 1rem; font-size: 0.9rem; color: #334155; border-bottom: 1px solid #f1f5f9; white-space: nowrap; }
.data-table tbody tr:hover { background: #fafbfc; }

.cell-primary { font-weight: 600; color: #1e293b; }
.text-danger { color: #dc2626 !important; }
.fw-bold { font-weight: 700; }

.status-badge { display: inline-block; padding: 0.2rem 0.6rem; border-radius: 6px; font-size: 0.8rem; font-weight: 600; }
.status-trial { background: #fef3c7; color: #d97706; }
.status-active { background: #d1fae5; color: #059669; }
.status-past_due { background: #fee2e2; color: #dc2626; }
.status-expired { background: #fecaca; color: #b91c1c; }
.status-cancelled { background: #f1f5f9; color: #64748b; }

.action-buttons { display: flex; gap: 0.4rem; }
.action-btn { width: 32px; height: 32px; border-radius: 8px; border: 1px solid #e2e8f0; background: white; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; color: #64748b; text-decoration: none; transition: all 0.2s; }
.action-btn:hover { background: #f1f5f9; color: #7c3aed; }
.action-warning:hover { color: #d97706; }
.action-btn:disabled { opacity: 0.5; cursor: not-allowed; }

.pagination-bar { display: flex; align-items: center; justify-content: center; gap: 1rem; padding: 1rem; border-top: 1px solid #e2e8f0; }
.page-btn { width: 32px; height: 32px; border-radius: 8px; border: 1px solid #e2e8f0; background: white; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s; }
.page-btn:hover:not(:disabled) { background: #f1f5f9; }
.page-btn:disabled { opacity: 0.4; cursor: not-allowed; }
.page-info { font-size: 0.9rem; color: #64748b; }

.empty-state { text-align: center; padding: 3rem 0; }
.loading-state { text-align: center; padding: 4rem 0; }

/* Modal */
.modal-overlay { position: fixed; inset: 0; background: rgba(15,23,42,0.6); backdrop-filter: blur(4px); display: flex; align-items: center; justify-content: center; z-index: 99999; padding: 1rem; }
.modal-container { background: white; border-radius: 16px; padding: 1.5rem; width: min(420px, 95vw); box-shadow: 0 25px 50px rgba(0,0,0,0.25); }
.modal-title { font-size: 1.1rem; font-weight: 600; color: #1e293b; margin-bottom: 0.5rem; }
.modal-message { font-size: 0.9rem; color: #64748b; margin-bottom: 1rem; }
.form-group { margin-bottom: 1rem; }
.form-label { display: block; font-size: 0.875rem; font-weight: 500; color: #374151; margin-bottom: 0.4rem; }
.form-input { width: 100%; padding: 0.625rem 0.875rem; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 0.95rem; }
.form-input:focus { outline: none; border-color: #7c3aed; box-shadow: 0 0 0 3px rgba(124,58,237,0.1); }
.modal-actions { display: flex; justify-content: flex-end; gap: 0.75rem; }
.modal-btn { padding: 0.5rem 1.25rem; border-radius: 8px; font-size: 0.9rem; font-weight: 600; cursor: pointer; border: none; transition: all 0.2s; }
.modal-btn-cancel { background: #f1f5f9; color: #475569; }
.modal-btn-primary { background: linear-gradient(135deg, #8b5cf6, #7c3aed); color: white; }
.modal-btn:disabled { opacity: 0.7; cursor: not-allowed; }
</style>
