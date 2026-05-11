<template>
    <div>
        <div class="app-page-title">
            <div class="page-title-wrapper">
                <div class="page-title-heading">
                    <div class="page-title-icon">
                        <i class="fa fa-layer-group icon-gradient bg-mean-fruit"></i>
                    </div>
                    <div>
                        Gestion de Planes
                        <div class="page-title-subheading text-muted">
                            Configura precios, limites y caracteristicas
                        </div>
                    </div>
                </div>
                <div class="page-title-actions">
                    <router-link :to="{ name: 'admin.plans.create' }" class="btn-create">
                        <i class="fa fa-plus me-2"></i> Nuevo Plan
                    </router-link>
                </div>
            </div>
        </div>

        <div v-if="loading" class="loading-state">
            <div class="spinner-border text-primary"></div>
            <p class="text-muted mt-3">Cargando planes...</p>
        </div>

        <template v-else>
            <!-- Filtros -->
            <div class="filters-bar">
                <div class="filter-search">
                    <i class="fa fa-search filter-search-icon"></i>
                    <input v-model="search" type="text" class="filter-input" placeholder="Buscar plan..." />
                </div>
                <select v-model="filterStatus" class="filter-select">
                    <option value="">Todos los estados</option>
                    <option value="active">Activo</option>
                    <option value="inactive">Inactivo</option>
                </select>
            </div>

            <!-- Tabla -->
            <div class="section-card">
                <vue-good-table
                    :columns="columns"
                    :rows="filteredRows"
                    :search-options="{ enabled: false }"
                    :sort-options="{ enabled: false }"
                    :pagination-options="paginationOptions"
                    styleClass="vgt-table striped"
                >
                    <template #table-row="props">
                        <span v-if="props.column.field === 'display_name'">
                            <div class="cell-primary">{{ props.row.display_name }}</div>
                            <div class="cell-slug">{{ props.row.name }}</div>
                        </span>

                        <span v-else-if="props.column.field === 'price_monthly'">
                            ${{ formatPrice(props.row.price_monthly) }}
                        </span>

                        <span v-else-if="props.column.field === 'price_yearly'">
                            ${{ formatPrice(props.row.price_yearly) }}
                        </span>

                        <span v-else-if="props.column.field === 'max_products'">
                            {{ props.row.max_products ?? '∞' }}
                        </span>

                        <span v-else-if="props.column.field === 'max_services'">
                            {{ props.row.max_services ?? '∞' }}
                        </span>

                        <span v-else-if="props.column.field === 'subscriptions_count'">
                            <span class="count-badge">{{ props.row.subscriptions_count }}</span>
                        </span>

                        <span v-else-if="props.column.field === 'is_active'">
                            <span class="status-badge" :class="props.row.is_active ? 'status-active' : 'status-inactive'">
                                {{ props.row.is_active ? 'Activo' : 'Inactivo' }}
                            </span>
                        </span>

                        <span v-else-if="props.column.field === 'actions'">
                            <div class="action-buttons">
                                <router-link :to="{ name: 'admin.plans.edit', params: { id: props.row.id } }"
                                             class="action-btn" title="Editar">
                                    <i class="fa fa-edit"></i>
                                </router-link>
                                <button @click="togglePlan(props.row)" class="action-btn"
                                        :class="props.row.is_active ? 'action-warning' : 'action-success'"
                                        :title="props.row.is_active ? 'Desactivar' : 'Activar'"
                                        :disabled="toggling === props.row.id">
                                    <i :class="props.row.is_active ? 'fa fa-eye-slash' : 'fa fa-eye'"></i>
                                </button>
                            </div>
                        </span>

                        <span v-else>
                            {{ props.formattedRow[props.column.field] }}
                        </span>
                    </template>

                    <template #emptystate>
                        <div class="empty-state">
                            <i class="fa fa-layer-group empty-icon"></i>
                            <p>No se encontraron planes.</p>
                        </div>
                    </template>
                </vue-good-table>
            </div>
        </template>
    </div>
</template>

<script>
import { VueGoodTable } from 'vue-good-table-next';
import 'vue-good-table-next/dist/vue-good-table-next.css';
import adminService from '@/services/adminService.js';

export default {
    name: 'AdminPlans',

    components: {
        VueGoodTable,
    },

    data() {
        return {
            plans: [],
            loading: true,
            toggling: null,
            search: '',
            filterStatus: '',
            columns: [
                { label: '#', field: 'sort_order', sortable: false, tdClass: 'text-center', thClass: 'text-center', width: '60px' },
                { label: 'Nombre', field: 'display_name', sortable: false },
                { label: 'Precio Mensual', field: 'price_monthly', sortable: false },
                { label: 'Precio Anual', field: 'price_yearly', sortable: false },
                { label: 'Tarjetas', field: 'max_cards', sortable: false, type: 'number', tdClass: 'text-center', thClass: 'text-center' },
                { label: 'Productos', field: 'max_products', sortable: false, tdClass: 'text-center', thClass: 'text-center' },
                { label: 'Servicios', field: 'max_services', sortable: false, tdClass: 'text-center', thClass: 'text-center' },
                { label: 'Suscripciones', field: 'subscriptions_count', sortable: false, type: 'number', tdClass: 'text-center', thClass: 'text-center' },
                { label: 'Estado', field: 'is_active', sortable: false },
                { label: '', field: 'actions', sortable: false, tdClass: 'text-center', width: '100px' },
            ],
            paginationOptions: {
                enabled: true,
                perPage: 15,
                perPageDropdown: [10, 15, 25, 50],
                nextLabel: '',
                prevLabel: '',
                rowsPerPageLabel: 'Filas',
                ofLabel: 'de',
                allLabel: 'Todos',
            },
        };
    },

    computed: {
        filteredRows() {
            let rows = this.plans;

            if (this.filterStatus === 'active') {
                rows = rows.filter(r => r.is_active);
            } else if (this.filterStatus === 'inactive') {
                rows = rows.filter(r => !r.is_active);
            }

            if (this.search) {
                const term = this.search.toLowerCase();
                rows = rows.filter(r =>
                    (r.display_name && r.display_name.toLowerCase().includes(term)) ||
                    (r.name && r.name.toLowerCase().includes(term))
                );
            }

            return rows;
        },
    },

    async created() {
        await this.load();
    },

    methods: {
        async load() {
            this.loading = true;
            try {
                const { data } = await adminService.getPlans();
                this.plans = data;
            } finally {
                this.loading = false;
            }
        },

        formatPrice(value) {
            if (!value || value === '0.00') return '0';
            return Number(value).toLocaleString('es-CO', { maximumFractionDigits: 0 });
        },

        async togglePlan(plan) {
            this.toggling = plan.id;
            try {
                const { data } = await adminService.togglePlan(plan.id);
                plan.is_active = data.is_active;
            } finally {
                this.toggling = null;
            }
        },
    },
};
</script>

<style scoped>
/* Filtros */
.filters-bar {
    display: flex;
    gap: 0.75rem;
    margin-bottom: 1rem;
    flex-wrap: wrap;
}

.filter-search {
    position: relative;
    flex: 1;
    min-width: 220px;
}

.filter-search-icon {
    position: absolute;
    left: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    font-size: 0.85rem;
    pointer-events: none;
}

.filter-input {
    width: 100%;
    padding: 0.5rem 0.75rem 0.5rem 2.25rem;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-size: 0.9rem;
    background: white;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.filter-input:focus {
    outline: none;
    border-color: #7c3aed;
    box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.1);
}

.filter-select {
    padding: 0.5rem 0.75rem;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-size: 0.9rem;
    background: white;
    color: #334155;
    cursor: pointer;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.filter-select:focus {
    outline: none;
    border-color: #7c3aed;
    box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.1);
}

/* Card */
.section-card {
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    overflow: hidden;
}

/* Celdas */
.cell-primary { font-weight: 600; color: #1e293b; }
.cell-slug { font-size: 0.8rem; color: #94a3b8; }
.text-center { text-align: center; }

/* Badges */
.count-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 28px;
    padding: 0.15rem 0.5rem;
    background: #f1f5f9;
    border-radius: 6px;
    font-size: 0.85rem;
    font-weight: 600;
    color: #475569;
}

.status-badge { display: inline-block; padding: 0.2rem 0.6rem; border-radius: 6px; font-size: 0.8rem; font-weight: 600; white-space: nowrap; }
.status-active { background: #d1fae5; color: #059669; }
.status-inactive { background: #f1f5f9; color: #94a3b8; }

/* Acciones */
.action-buttons { display: flex; gap: 0.4rem; justify-content: center; }

.action-btn {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
    background: white;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
    color: #64748b;
    text-decoration: none;
    transition: all 0.2s;
}

.action-btn:hover { background: #f1f5f9; color: #7c3aed; border-color: #cbd5e1; }
.action-warning:hover { color: #d97706; }
.action-success:hover { color: #059669; }
.action-btn:disabled { opacity: 0.5; cursor: not-allowed; }

/* Boton crear */
.btn-create {
    display: inline-flex;
    align-items: center;
    padding: 0.5rem 1.25rem;
    background: linear-gradient(135deg, #8b5cf6, #7c3aed);
    color: white;
    border-radius: 8px;
    text-decoration: none;
    font-size: 0.9rem;
    font-weight: 600;
    transition: all 0.2s;
    box-shadow: 0 2px 8px rgba(124, 58, 237, 0.25);
}

.btn-create:hover {
    transform: translateY(-1px);
    color: white;
}

/* Empty & loading */
.empty-state { text-align: center; padding: 3rem 1rem; color: #94a3b8; }
.empty-icon { font-size: 2rem; margin-bottom: 0.75rem; display: block; opacity: 0.4; }
.empty-state p { margin: 0; font-size: 0.95rem; }
.loading-state { text-align: center; padding: 4rem 0; }

@media (max-width: 640px) {
    .filter-select { flex: 1; min-width: 0; }
}
</style>

<style>
/* vue-good-table overrides (mismos que AdminCompanies) */
.vgt-wrap,
.vgt-inner-wrap { border-radius: 0 !important; box-shadow: none !important; }
.vgt-responsive { overflow-x: auto; }
.vgt-table.striped { font-size: 0.9rem; border-collapse: collapse; border: none !important; }

.vgt-table.striped thead th {
    background: #f8fafc !important;
    color: #64748b !important;
    font-size: 0.75rem !important;
    font-weight: 600 !important;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    border-bottom: 1px solid #e2e8f0 !important;
    padding: 1rem 1.25rem !important;
    cursor: default !important;
}

.vgt-table.striped thead th span::after { display: none !important; }
.vgt-table.striped thead th.sorting-asc span::after,
.vgt-table.striped thead th.sorting-desc span::after { display: none !important; }

.vgt-table.striped td {
    padding: 0.75rem 1.25rem !important;
    color: #334155 !important;
    border-bottom: 1px solid #f1f5f9 !important;
}

.vgt-table.striped tbody tr:last-child td {
    border-bottom: 1px solid #e2e8f0 !important;
}

.vgt-table.striped tbody tr:hover td {
    background: #fafbfc !important;
}

.vgt-wrap__footer {
    border-top: none !important;
    border-bottom: none !important;
    background: #f8fafc !important;
    padding: 0.75rem 1.25rem !important;
    font-size: 0.85rem !important;
    color: #64748b !important;
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
}

.vgt-wrap__footer .footer__row-count {
    position: relative;
    display: flex;
    align-items: center;
}
.vgt-wrap__footer .footer__row-count::after { display: none !important; }
.vgt-wrap__footer .footer__row-count__label {
    font-size: 0.85rem !important;
    font-weight: 400 !important;
    color: #64748b !important;
}
.vgt-wrap__footer .footer__row-count__select {
    font-size: 0.85rem !important;
    font-weight: 400 !important;
    color: #334155 !important;
    background: white !important;
    border: 1px solid #e2e8f0 !important;
    border-radius: 6px !important;
    padding: 0.25rem 0.5rem !important;
    margin-left: 0.4rem;
    cursor: pointer;
    -webkit-appearance: auto !important;
    -moz-appearance: auto !important;
    appearance: auto !important;
}
.vgt-wrap__footer .footer__row-count__select:focus {
    border-color: #7c3aed !important;
    outline: none;
}

.vgt-wrap__footer .footer__navigation {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.vgt-wrap__footer .footer__navigation > button:first-of-type {
    margin-right: 0 !important;
}
.vgt-wrap__footer .footer__navigation__page-info {
    font-size: 0.85rem !important;
    font-weight: 400 !important;
    color: #64748b !important;
    margin: 0 !important;
}
.vgt-wrap__footer .footer__navigation__page-info__current-entry {
    width: 32px !important;
    text-align: center !important;
    font-weight: 500 !important;
    color: #1e293b !important;
    background: white !important;
    border: 1px solid #e2e8f0 !important;
    border-radius: 6px !important;
    padding: 0.2rem 0 !important;
    margin: 0 0.25rem !important;
}
.vgt-wrap__footer .footer__navigation__page-info__current-entry:focus {
    border-color: #7c3aed !important;
    outline: none;
    box-shadow: 0 0 0 2px rgba(124, 58, 237, 0.1);
}

.vgt-wrap__footer .footer__navigation__page-btn {
    background: white !important;
    border: 1px solid #e2e8f0 !important;
    border-radius: 6px !important;
    padding: 0.2rem 0.45rem !important;
    cursor: pointer;
    transition: all 0.15s;
    line-height: 1 !important;
}
.vgt-wrap__footer .footer__navigation__page-btn .chevron {
    width: 18px !important;
    height: 18px !important;
}
.vgt-wrap__footer .footer__navigation__page-btn .chevron::after {
    margin-top: -5px !important;
    border-top-width: 5px !important;
    border-bottom-width: 5px !important;
}
.vgt-wrap__footer .footer__navigation__page-btn .chevron.left::after {
    border-right-width: 5px !important;
    border-right-color: #7c3aed !important;
}
.vgt-wrap__footer .footer__navigation__page-btn .chevron.right::after {
    border-left-width: 5px !important;
    border-left-color: #7c3aed !important;
}
.vgt-wrap__footer .footer__navigation__page-btn:hover:not(.disabled) {
    background: #f3e8ff !important;
    border-color: #c4b5fd !important;
}
.vgt-wrap__footer .footer__navigation__page-btn.disabled {
    opacity: 0.4 !important;
    cursor: not-allowed !important;
}
.vgt-wrap__footer .footer__navigation__page-btn.disabled .chevron.left::after {
    border-right-color: #94a3b8 !important;
}
.vgt-wrap__footer .footer__navigation__page-btn.disabled .chevron.right::after {
    border-left-color: #94a3b8 !important;
}

.vgt-global-search { display: none !important; }
</style>
