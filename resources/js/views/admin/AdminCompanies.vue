<template>
    <div>
        <div class="app-page-title">
            <div class="page-title-wrapper">
                <div class="page-title-heading">
                    <div class="page-title-icon">
                        <i class="fa fa-building icon-gradient bg-mean-fruit"></i>
                    </div>
                    <div>
                        Empresas
                        <div class="page-title-subheading text-muted">
                            Todas las empresas con estado de suscripcion
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="loading" class="loading-state">
            <div class="spinner-border text-primary"></div>
            <p class="text-muted mt-3">Cargando empresas...</p>
        </div>

        <template v-else>
            <!-- Filtros fuera de la card -->
            <div class="filters-bar">
                <div class="filter-search">
                    <i class="fa fa-search filter-search-icon"></i>
                    <input v-model="search" type="text" class="filter-input" placeholder="Buscar empresa..." />
                </div>
                <select v-model="filterStatus" class="filter-select">
                    <option value="">Todos los estados</option>
                    <option value="trial">Prueba</option>
                    <option value="active">Activo</option>
                    <option value="past_due">Vencido</option>
                    <option value="expired">Expirado</option>
                    <option value="none">Sin suscripcion</option>
                </select>
                <select v-model="filterPlan" class="filter-select">
                    <option value="">Todos los planes</option>
                    <option v-for="plan in plans" :key="plan.id" :value="plan.display_name">{{ plan.display_name }}</option>
                </select>
            </div>

            <!-- Tabla -->
            <div class="section-card">
                <vue-good-table
                    :columns="columns"
                    :rows="filteredRows"
                    :search-options="{ enabled: false }"
                    :pagination-options="paginationOptions"
                    :sort-options="{ enabled: false }"
                    styleClass="vgt-table striped"
                >
                    <template #table-row="props">
                        <span v-if="props.column.field === 'name'">
                            <div class="cell-primary">{{ props.row.name }}</div>
                            <div class="cell-slug">/{{ props.row.slug }}</div>
                        </span>

                        <span v-else-if="props.column.field === 'current_plan'">
                            {{ props.row.current_plan || 'Sin plan' }}
                        </span>

                        <span v-else-if="props.column.field === 'subscription_status'">
                            <span v-if="props.row.subscription_status" class="status-badge" :class="'status-' + props.row.subscription_status">
                                {{ statusLabel(props.row.subscription_status) }}
                            </span>
                            <span v-else class="status-badge status-none">Sin suscripcion</span>
                        </span>

                        <span v-else-if="props.column.field === 'subscription_ends'">
                            {{ formatDate(props.row.subscription_ends) }}
                        </span>

                        <span v-else-if="props.column.field === 'actions'">
                            <router-link :to="{ name: 'companies.show', params: { id: props.row.id }, query: { from: 'admin' } }" class="action-btn" title="Ver empresa">
                                <i class="fa fa-eye"></i>
                            </router-link>
                        </span>

                        <span v-else>
                            {{ props.formattedRow[props.column.field] }}
                        </span>
                    </template>

                    <template #emptystate>
                        <div class="empty-state">
                            <i class="fa fa-building empty-icon"></i>
                            <p>No se encontraron empresas.</p>
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
    name: 'AdminCompanies',

    components: {
        VueGoodTable,
    },

    data() {
        return {
            companies: [],
            plans: [],
            loading: true,
            search: '',
            filterStatus: '',
            filterPlan: '',
            columns: [
                { label: 'Empresa', field: 'name', sortable: false },
                { label: 'Plan', field: 'current_plan', sortable: false },
                { label: 'Estado', field: 'subscription_status', sortable: false },
                { label: 'Vence', field: 'subscription_ends', sortable: false, type: 'date', dateInputFormat: 'yyyy-MM-dd', dateOutputFormat: 'dd MMM yyyy' },
                { label: 'Tarjetas', field: 'cards_count', sortable: false, type: 'number', tdClass: 'text-center', thClass: 'text-center' },
                { label: 'Productos', field: 'products_count', sortable: false, type: 'number', tdClass: 'text-center', thClass: 'text-center' },
                { label: 'Servicios', field: 'services_count', sortable: false, type: 'number', tdClass: 'text-center', thClass: 'text-center' },
                { label: 'Usuarios', field: 'users_count', sortable: false, type: 'number', tdClass: 'text-center', thClass: 'text-center' },
                { label: '', field: 'actions', sortable: false, tdClass: 'text-center', width: '60px' },
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
            let rows = this.companies;

            if (this.filterStatus) {
                if (this.filterStatus === 'none') {
                    rows = rows.filter(r => !r.subscription_status);
                } else {
                    rows = rows.filter(r => r.subscription_status === this.filterStatus);
                }
            }

            if (this.filterPlan) {
                rows = rows.filter(r => r.current_plan === this.filterPlan);
            }

            if (this.search) {
                const term = this.search.toLowerCase();
                rows = rows.filter(r =>
                    (r.name && r.name.toLowerCase().includes(term)) ||
                    (r.slug && r.slug.toLowerCase().includes(term))
                );
            }

            return rows;
        },
    },

    async created() {
        try {
            const { data } = await adminService.getPlans();
            this.plans = data;
        } catch { /* planes opcionales */ }
        await this.load();
    },

    methods: {
        async load() {
            this.loading = true;
            try {
                const { data } = await adminService.getCompanies({ per_page: 999 });
                this.companies = data.data || data;
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

/* Card — overflow:hidden recorta contenido pero NO el box-shadow propio */
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
.status-badge { display: inline-block; padding: 0.2rem 0.6rem; border-radius: 6px; font-size: 0.8rem; font-weight: 600; white-space: nowrap; }
.status-trial { background: #fef3c7; color: #d97706; }
.status-active { background: #d1fae5; color: #059669; }
.status-past_due { background: #fee2e2; color: #dc2626; }
.status-expired { background: #fecaca; color: #b91c1c; }
.status-none { background: #f1f5f9; color: #94a3b8; }

/* Boton accion */
.action-btn {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
    background: white;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
    color: #64748b;
    text-decoration: none;
    transition: all 0.2s;
}
.action-btn:hover { background: #f1f5f9; color: #7c3aed; }

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
/* vue-good-table — eliminar bordes/sombras internas (la card los maneja) */
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

/* Ocultar iconos de sort */
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

/* Footer */
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

/* Row count (selector de filas) */
.vgt-wrap__footer .footer__row-count {
    position: relative;
    display: flex;
    align-items: center;
}

.vgt-wrap__footer .footer__row-count::after {
    display: none !important;
}

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

/* Navegacion */
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

/* Botones prev/next — compactos */
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
}

.vgt-wrap__footer .footer__navigation__page-btn .chevron.right::after {
    border-left-width: 5px !important;
}

.vgt-wrap__footer .footer__navigation__page-btn:hover:not(.disabled) {
    background: #f3e8ff !important;
    border-color: #c4b5fd !important;
}

/* Flechas — triangulos CSS de la libreria, solo cambiar color */
.vgt-wrap__footer .footer__navigation__page-btn .chevron.left::after {
    border-right-color: #7c3aed !important;
}

.vgt-wrap__footer .footer__navigation__page-btn .chevron.right::after {
    border-left-color: #7c3aed !important;
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

/* Ocultar barra de busqueda global interna */
.vgt-global-search { display: none !important; }
</style>
