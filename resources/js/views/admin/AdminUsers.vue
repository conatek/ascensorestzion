<template>
    <div>
        <div class="app-page-title">
            <div class="page-title-wrapper">
                <div class="page-title-heading">
                    <div class="page-title-icon">
                        <i class="fa fa-users icon-gradient bg-mean-fruit"></i>
                    </div>
                    <div>
                        Usuarios
                        <div class="page-title-subheading text-muted">
                            Gestiona los usuarios de la plataforma
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="loading" class="loading-state">
            <div class="spinner-border text-primary"></div>
            <p class="text-muted mt-3">Cargando usuarios...</p>
        </div>

        <template v-else>
            <!-- Filtros -->
            <div class="filters-bar">
                <div class="filter-search">
                    <i class="fa fa-search filter-search-icon"></i>
                    <input v-model="search" type="text" class="filter-input" placeholder="Buscar por nombre o email..." />
                </div>
                <select v-model="filterRole" class="filter-select">
                    <option value="">Todos los roles</option>
                    <option value="Master">Master</option>
                    <option value="Admin">Admin</option>
                    <option value="Guest">Guest</option>
                </select>
                <select v-model="filterCompany" class="filter-select">
                    <option value="">Todas las empresas</option>
                    <option value="__none__">Sin empresa</option>
                    <option v-for="c in companies" :key="c.id" :value="c.id">{{ c.name }}</option>
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
                        <span v-if="props.column.field === 'name'">
                            <div class="cell-primary">{{ props.row.name }}</div>
                            <div class="cell-email">{{ props.row.email }}</div>
                        </span>

                        <span v-else-if="props.column.field === 'role'">
                            <span class="role-badge" :class="'role-' + roleName(props.row).toLowerCase()">
                                {{ roleName(props.row) }}
                            </span>
                        </span>

                        <span v-else-if="props.column.field === 'company'">
                            {{ props.row.company?.name || '-' }}
                        </span>

                        <span v-else-if="props.column.field === 'created_at'">
                            {{ formatDate(props.row.created_at) }}
                        </span>

                        <span v-else-if="props.column.field === 'actions'">
                            <button @click="openDetail(props.row)" class="action-btn" title="Ver detalle">
                                <i class="fa fa-eye"></i>
                            </button>
                        </span>

                        <span v-else>
                            {{ props.formattedRow[props.column.field] }}
                        </span>
                    </template>

                    <template #emptystate>
                        <div class="empty-state">
                            <i class="fa fa-users empty-icon"></i>
                            <p>No se encontraron usuarios.</p>
                        </div>
                    </template>
                </vue-good-table>
            </div>
        </template>

        <!-- Modal detalle usuario -->
        <div v-if="selectedUser" class="modal-overlay" @click.self="closeDetail">
            <div class="modal-container">
                <!-- Header -->
                <div class="modal-header">
                    <div class="modal-user-info">
                        <div class="modal-avatar">
                            <i class="fa fa-user"></i>
                        </div>
                        <div>
                            <h4 class="modal-name">{{ selectedUser.name }}</h4>
                            <p class="modal-email">{{ selectedUser.email }}</p>
                        </div>
                    </div>
                    <button @click="closeDetail" class="modal-close">
                        <i class="fa fa-times"></i>
                    </button>
                </div>

                <!-- Body -->
                <div class="modal-body">
                    <div class="detail-row">
                        <span class="detail-label">Rol</span>
                        <span class="role-badge" :class="'role-' + roleName(selectedUser).toLowerCase()">
                            {{ roleName(selectedUser) }}
                        </span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Empresa</span>
                        <span class="detail-value">{{ selectedUser.company?.name || 'Sin empresa' }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Registro</span>
                        <span class="detail-value">{{ formatDate(selectedUser.created_at) }}</span>
                    </div>
                </div>

                <!-- Footer -->
                <div v-if="selectedUser.company" class="modal-footer">
                    <router-link :to="{ name: 'companies.show', params: { id: selectedUser.company_id }, query: { from: 'admin' } }"
                                 class="modal-link">
                        <i class="fa fa-building me-1"></i> Ver empresa
                    </router-link>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { VueGoodTable } from 'vue-good-table-next';
import 'vue-good-table-next/dist/vue-good-table-next.css';
import adminService from '@/services/adminService.js';

export default {
    name: 'AdminUsers',

    components: {
        VueGoodTable,
    },

    data() {
        return {
            users: [],
            companies: [],
            loading: true,
            search: '',
            filterRole: '',
            filterCompany: '',
            selectedUser: null,
            columns: [
                { label: 'Usuario', field: 'name', sortable: false },
                { label: 'Rol', field: 'role', sortable: false, width: '110px' },
                { label: 'Empresa', field: 'company', sortable: false },
                { label: 'Registro', field: 'created_at', sortable: false },
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
            let rows = this.users;

            if (this.filterRole) {
                rows = rows.filter(r => this.roleName(r) === this.filterRole);
            }

            if (this.filterCompany === '__none__') {
                rows = rows.filter(r => !r.company);
            } else if (this.filterCompany) {
                rows = rows.filter(r => r.company?.id === Number(this.filterCompany));
            }

            if (this.search) {
                const term = this.search.toLowerCase();
                rows = rows.filter(r =>
                    (r.name && r.name.toLowerCase().includes(term)) ||
                    (r.email && r.email.toLowerCase().includes(term))
                );
            }

            return rows;
        },
    },

    async created() {
        try {
            const { data } = await adminService.getCompanies({ per_page: 999 });
            this.companies = (data.data || data).sort((a, b) => a.name.localeCompare(b.name));
        } catch { /* empresas opcionales */ }
        await this.load();
    },

    methods: {
        async load() {
            this.loading = true;
            try {
                const { data } = await adminService.getUsers({ per_page: 999 });
                this.users = data.data || data;
            } finally {
                this.loading = false;
            }
        },

        roleName(user) {
            return user.roles?.[0]?.name || 'Sin rol';
        },

        formatDate(d) {
            if (!d) return '-';
            return new Date(d).toLocaleDateString('es-CO', { year: 'numeric', month: 'short', day: 'numeric' });
        },

        openDetail(user) {
            this.selectedUser = user;
        },

        closeDetail() {
            this.selectedUser = null;
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
.cell-email { font-size: 0.8rem; color: #94a3b8; }
.text-center { text-align: center; }

/* Badges rol */
.role-badge { display: inline-block; padding: 0.2rem 0.6rem; border-radius: 6px; font-size: 0.8rem; font-weight: 600; white-space: nowrap; }
.role-master { background: linear-gradient(135deg, #ede9fe, #f3e8ff); color: #7c3aed; }
.role-admin { background: linear-gradient(135deg, #dbeafe, #e0f2fe); color: #2563eb; }
.role-guest { background: #f1f5f9; color: #64748b; }
.role-sin { background: #fef2f2; color: #dc2626; }

/* Boton accion tabla */
.action-btn {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
    background: white;
    cursor: pointer;
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

/* ───── Modal detalle ───── */
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.6);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 99999;
    padding: 1rem;
}

.modal-container {
    background: white;
    border-radius: 16px;
    width: min(460px, 95vw);
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
    overflow: hidden;
}

.modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.25rem 1.5rem;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
}

.modal-user-info {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.modal-avatar {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: linear-gradient(135deg, #ede9fe, #f3e8ff);
    color: #7c3aed;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    flex-shrink: 0;
}

.modal-name {
    font-size: 1rem;
    font-weight: 600;
    color: #1e293b;
    margin: 0;
    line-height: 1.3;
}

.modal-email {
    font-size: 0.8rem;
    color: #94a3b8;
    margin: 0;
}

.modal-close {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    border: none;
    background: #f1f5f9;
    color: #64748b;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.modal-close:hover { background: #e2e8f0; color: #475569; }

.modal-body { padding: 1.25rem 1.5rem; }

.detail-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.6rem 0;
    border-bottom: 1px solid #f1f5f9;
    font-size: 0.9rem;
}

.detail-row:last-child { border-bottom: none; }

.detail-label { color: #64748b; }
.detail-value { font-weight: 500; color: #1e293b; }

.modal-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem 1.5rem;
    border-top: 1px solid #e2e8f0;
    background: #f8fafc;
}

.modal-link {
    font-size: 0.85rem;
    color: #7c3aed;
    text-decoration: none;
    font-weight: 500;
    transition: color 0.2s;
}

.modal-link:hover { color: #6d28d9; text-decoration: underline; }

@media (max-width: 640px) {
    .filter-select { flex: 1; min-width: 0; }
}
</style>

<style>
/* vue-good-table overrides */
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
