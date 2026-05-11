<template>
    <div>
        <div class="app-page-title">
            <div class="page-title-wrapper">
                <div class="page-title-heading">
                    <div class="page-title-icon">
                        <i class="fa fa-receipt icon-gradient bg-mean-fruit"></i>
                    </div>
                    <div>
                        Pagos
                        <div class="page-title-subheading text-muted">
                            Historial de pagos de todas las empresas
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="filters-bar">
            <select v-model="filters.status" @change="load" class="filter-select">
                <option value="">Todos los estados</option>
                <option value="pending">Pendiente</option>
                <option value="approved">Aprobado</option>
                <option value="declined">Rechazado</option>
                <option value="refunded">Reembolsado</option>
            </select>
            <input v-model="filters.date_from" @change="load" type="date" class="filter-input" style="width:auto" />
            <input v-model="filters.date_to" @change="load" type="date" class="filter-input" style="width:auto" />
            <input v-model="filters.search" @input="debounceSearch" type="text" class="filter-input" placeholder="Buscar empresa o referencia..." />
        </div>

        <!-- Total -->
        <div v-if="totalAmount > 0" class="total-bar">
            <i class="fa fa-dollar-sign"></i>
            Total aprobado: <strong>${{ Number(totalAmount).toLocaleString('es-CO') }} COP</strong>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="loading-state">
            <div class="spinner-border text-primary"></div>
        </div>

        <!-- Table -->
        <div v-else class="section-card">
            <div class="section-body table-responsive">
                <table class="data-table" v-if="payments.length">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Empresa</th>
                            <th>Monto</th>
                            <th>Metodo</th>
                            <th>Estado</th>
                            <th>Referencia</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="p in payments" :key="p.id">
                            <td>{{ formatDate(p.paid_at || p.created_at) }}</td>
                            <td><span class="cell-primary">{{ p.company?.name }}</span></td>
                            <td>${{ Number(p.amount).toLocaleString('es-CO') }}</td>
                            <td>{{ p.payment_method || '-' }}</td>
                            <td><span class="status-badge" :class="'status-' + p.status">{{ statusLabel(p.status) }}</span></td>
                            <td class="ref-cell">{{ p.payu_reference_code || '-' }}</td>
                            <td>
                                <div class="action-buttons">
                                    <router-link :to="{ name: 'admin.payments.show', params: { id: p.id } }" class="action-btn" title="Ver detalle">
                                        <i class="fa fa-eye"></i>
                                    </router-link>
                                    <button v-if="p.status === 'pending'" @click="approve(p)" class="action-btn action-success" title="Aprobar" :disabled="acting === p.id">
                                        <i class="fa fa-check"></i>
                                    </button>
                                    <button v-if="p.status === 'approved'" @click="refund(p)" class="action-btn action-warning" title="Reembolsar" :disabled="acting === p.id">
                                        <i class="fa fa-undo"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div v-else class="empty-state">
                    <p class="text-muted">No se encontraron pagos.</p>
                </div>
            </div>

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
    </div>
</template>

<script>
import adminService from '@/services/adminService.js';

export default {
    name: 'AdminPayments',

    data() {
        return {
            payments: [],
            totalAmount: 0,
            loading: true,
            filters: { status: '', date_from: '', date_to: '', search: '' },
            pagination: {},
            acting: null,
            searchTimeout: null,
        };
    },

    async created() {
        await this.load();
    },

    methods: {
        async load(page = 1) {
            this.loading = true;
            try {
                const params = { ...this.filters, page };
                const { data } = await adminService.getPayments(params);
                this.payments = data.payments.data;
                this.totalAmount = data.total_amount;
                this.pagination = { current_page: data.payments.current_page, last_page: data.payments.last_page };
            } finally {
                this.loading = false;
            }
        },

        debounceSearch() {
            clearTimeout(this.searchTimeout);
            this.searchTimeout = setTimeout(() => this.load(), 400);
        },

        goToPage(page) { this.load(page); },

        statusLabel(s) {
            return { pending: 'Pendiente', approved: 'Aprobado', declined: 'Rechazado', refunded: 'Reembolsado' }[s] || s;
        },

        formatDate(d) {
            if (!d) return '-';
            return new Date(d).toLocaleDateString('es-CO', { year: 'numeric', month: 'short', day: 'numeric' });
        },

        async approve(p) {
            if (!confirm(`¿Aprobar pago de $${Number(p.amount).toLocaleString('es-CO')} para ${p.company?.name}?`)) return;
            this.acting = p.id;
            try {
                await adminService.approvePayment(p.id);
                await this.load(this.pagination.current_page);
            } finally {
                this.acting = null;
            }
        },

        async refund(p) {
            if (!confirm(`¿Registrar reembolso de $${Number(p.amount).toLocaleString('es-CO')}?`)) return;
            this.acting = p.id;
            try {
                await adminService.refundPayment(p.id);
                await this.load(this.pagination.current_page);
            } finally {
                this.acting = null;
            }
        },
    },
};
</script>

<style scoped>
.filters-bar { display: flex; gap: 0.75rem; margin-bottom: 1.25rem; flex-wrap: wrap; }
.filter-select, .filter-input { padding: 0.5rem 0.75rem; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 0.9rem; background: white; }
.filter-input { flex: 1; min-width: 180px; }
.filter-select:focus, .filter-input:focus { outline: none; border-color: #7c3aed; box-shadow: 0 0 0 3px rgba(124,58,237,0.1); }

.total-bar { display: flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1rem; background: #d1fae5; border-radius: 8px; color: #047857; font-size: 0.9rem; margin-bottom: 1.25rem; }
.total-bar i { color: #059669; }

.section-card { background: white; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 2px 8px rgba(0,0,0,0.04); }
.section-body { padding: 0; }
.table-responsive { overflow-x: auto; }
.data-table { width: 100%; border-collapse: collapse; }
.data-table th { text-align: left; padding: 0.875rem 1rem; font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; background: #f8fafc; border-bottom: 2px solid #e2e8f0; white-space: nowrap; }
.data-table td { padding: 0.875rem 1rem; font-size: 0.9rem; color: #334155; border-bottom: 1px solid #f1f5f9; white-space: nowrap; }
.data-table tbody tr:hover { background: #fafbfc; }
.cell-primary { font-weight: 600; color: #1e293b; }
.ref-cell { font-family: monospace; font-size: 0.8rem; color: #94a3b8; }

.status-badge { display: inline-block; padding: 0.2rem 0.6rem; border-radius: 6px; font-size: 0.8rem; font-weight: 600; }
.status-pending { background: #fef3c7; color: #d97706; }
.status-approved { background: #d1fae5; color: #059669; }
.status-declined { background: #fee2e2; color: #dc2626; }
.status-refunded { background: #e0f2fe; color: #0284c7; }

.action-buttons { display: flex; gap: 0.4rem; }
.action-btn { width: 32px; height: 32px; border-radius: 8px; border: 1px solid #e2e8f0; background: white; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; color: #64748b; text-decoration: none; transition: all 0.2s; }
.action-btn:hover { background: #f1f5f9; color: #7c3aed; }
.action-success:hover { color: #059669; }
.action-warning:hover { color: #d97706; }
.action-btn:disabled { opacity: 0.5; cursor: not-allowed; }

.pagination-bar { display: flex; align-items: center; justify-content: center; gap: 1rem; padding: 1rem; border-top: 1px solid #e2e8f0; }
.page-btn { width: 32px; height: 32px; border-radius: 8px; border: 1px solid #e2e8f0; background: white; cursor: pointer; display: flex; align-items: center; justify-content: center; }
.page-btn:hover:not(:disabled) { background: #f1f5f9; }
.page-btn:disabled { opacity: 0.4; cursor: not-allowed; }
.page-info { font-size: 0.9rem; color: #64748b; }

.empty-state { text-align: center; padding: 3rem 0; }
.loading-state { text-align: center; padding: 4rem 0; }
</style>
