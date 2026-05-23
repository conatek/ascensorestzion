<template>
    <div>
        <!-- Cabecera -->
        <div class="app-page-title">
            <div class="page-title-wrapper">
                <div class="page-title-heading">
                    <div class="page-title-icon">
                        <i class="fa fa-file-alt icon-gradient bg-mean-fruit"></i>
                    </div>
                    <div>
                        Reportes de Servicio
                        <div class="page-title-subheading text-muted">
                            Gestiona los reportes de servicio tecnico
                        </div>
                    </div>
                </div>
                <div class="page-title-actions d-flex gap-2">
                    <button class="btn-export" @click="exportCsv" :disabled="exporting">
                        <i v-if="exporting" class="fa fa-spinner fa-spin me-2"></i>
                        <i v-else class="fa fa-file-csv me-2"></i>
                        {{ exporting ? 'Exportando...' : 'Exportar CSV' }}
                    </button>
                    <div class="dropdown-wrapper" ref="dropdownWrapper">
                        <button class="btn-create" @click="showDropdown = !showDropdown">
                            <i class="fa fa-plus me-2"></i> Nuevo Reporte
                            <i class="fa fa-chevron-down ms-2" style="font-size: 0.75rem;"></i>
                        </button>
                        <div v-if="showDropdown" class="dropdown-menu-custom">
                            <router-link to="/reportes/nuevo?type=RSTP" class="dropdown-item-custom" @click="showDropdown = false">
                                <span class="tipo-badge tipo-rstp">RSTP</span> Preventivo
                            </router-link>
                            <router-link to="/reportes/nuevo?type=RSTC" class="dropdown-item-custom" @click="showDropdown = false">
                                <span class="tipo-badge tipo-rstc">RSTC</span> Correctivo
                            </router-link>
                            <router-link to="/reportes/nuevo?type=RSTE" class="dropdown-item-custom" @click="showDropdown = false">
                                <span class="tipo-badge tipo-rste">RSTE</span> Especial
                            </router-link>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="filter-bar">
            <div class="filter-row">
                <div class="filter-group">
                    <label class="filter-label">Tipo</label>
                    <select v-model="filters.report_type" class="filter-select">
                        <option value="">Todos</option>
                        <option value="RSTP">RSTP - Preventivo</option>
                        <option value="RSTC">RSTC - Correctivo</option>
                        <option value="RSTE">RSTE - Especial</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label class="filter-label">Cliente</label>
                    <select v-model="filters.client_id" class="filter-select">
                        <option value="">Todos</option>
                        <option v-for="client in clients" :key="client.id" :value="client.id">
                            {{ client.business_name }}
                        </option>
                    </select>
                </div>
                <div class="filter-group">
                    <label class="filter-label">Estado</label>
                    <select v-model="filters.status" class="filter-select">
                        <option value="">Todos</option>
                        <option v-for="(label, key) in statusLabels" :key="key" :value="key">
                            {{ label }}
                        </option>
                    </select>
                </div>
                <div class="filter-group filter-search">
                    <label class="filter-label">Buscar</label>
                    <input v-model="filters.search" type="text" class="filter-input"
                           placeholder="N° reporte, equipo, tecnico..." @input="onSearch" />
                </div>
            </div>
            <div class="filter-row filter-row-dates">
                <div class="filter-group">
                    <label class="filter-label">Desde</label>
                    <input v-model="filters.date_from" type="date" class="filter-input" />
                </div>
                <div class="filter-group">
                    <label class="filter-label">Hasta</label>
                    <input v-model="filters.date_to" type="date" class="filter-input" />
                </div>
            </div>
        </div>

        <!-- Estado de carga -->
        <div v-if="loading" class="loading-state">
            <div class="spinner-border text-primary"></div>
            <p class="text-muted mt-3">Cargando reportes...</p>
        </div>

        <!-- Sin reportes -->
        <div v-else-if="filteredReports.length === 0" class="empty-state">
            <div class="empty-state-content">
                <div class="empty-illustration">
                    <svg viewBox="0 0 200 160" class="empty-svg">
                        <rect x="50" y="20" width="100" height="125" rx="6" fill="#e2e8f0"/>
                        <rect x="65" y="35" width="70" height="6" rx="3" fill="#cbd5e1"/>
                        <rect x="65" y="50" width="50" height="6" rx="3" fill="#cbd5e1"/>
                        <rect x="65" y="65" width="60" height="6" rx="3" fill="#cbd5e1"/>
                        <rect x="65" y="80" width="40" height="6" rx="3" fill="#cbd5e1"/>
                        <rect x="65" y="100" width="70" height="20" rx="4" fill="#94a3b8"/>
                        <circle cx="155" cy="35" r="22" fill="url(#reportGradient)"/>
                        <path d="M146 35 L164 35 M155 26 L155 44" stroke="white" stroke-width="3.5" stroke-linecap="round"/>
                        <defs>
                            <linearGradient id="reportGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" style="stop-color:#30ab0a"/>
                                <stop offset="100%" style="stop-color:#ba2831"/>
                            </linearGradient>
                        </defs>
                    </svg>
                </div>
                <h3 class="empty-title">No hay reportes de servicio</h3>
                <p class="empty-description">
                    Crea el primer reporte de servicio para comenzar a documentar el mantenimiento.
                </p>
                <router-link to="/reportes/nuevo?type=RSTP" class="btn-create">
                    <i class="fa fa-plus me-2"></i> Crear primer reporte
                </router-link>
            </div>
        </div>

        <!-- Tabla de reportes -->
        <div v-else class="table-card">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>N° Reporte</th>
                            <th>Tipo</th>
                            <th>Fecha</th>
                            <th>Equipo</th>
                            <th>Cliente</th>
                            <th>Tecnico</th>
                            <th>Estado</th>
                            <th class="th-actions">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="report in filteredReports" :key="report.id">
                            <td>
                                <span class="code-text">{{ report.report_number || '-' }}</span>
                            </td>
                            <td>
                                <span class="tipo-badge" :class="'tipo-' + (report.report_type || '').toLowerCase()">
                                    {{ report.report_type }}
                                </span>
                            </td>
                            <td>{{ formatDate(report.service_date) }}</td>
                            <td>{{ report.equipment?.internal_code || '-' }}</td>
                            <td>{{ report.client?.business_name || report.equipment?.site?.client?.business_name || '-' }}</td>
                            <td>{{ report.technician?.name || '-' }}</td>
                            <td>
                                <span class="status-badge" :class="'status-' + report.status">
                                    {{ statusLabels[report.status] || report.status }}
                                </span>
                            </td>
                            <td>
                                <div class="item-actions">
                                    <router-link :to="{ name: 'reports.show', params: { id: report.id } }"
                                                 class="action-btn" title="Ver">
                                        <i class="fa fa-eye"></i>
                                    </router-link>
                                    <button @click="downloadPdf(report)" class="action-btn" title="PDF"
                                            :disabled="pdfLoading === report.id">
                                        <i v-if="pdfLoading === report.id" class="fa fa-spinner fa-spin"></i>
                                        <i v-else class="fa fa-file-pdf"></i>
                                    </button>
                                    <button v-if="report.status === 'borrador'"
                                            @click="confirmDelete(report)"
                                            class="action-btn action-delete" title="Eliminar">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal confirmacion de borrado -->
        <div v-if="toDelete" class="modal-overlay" @click.self="toDelete = null">
            <div class="modal-container">
                <div class="modal-icon-wrapper">
                    <div class="modal-icon">
                        <i class="fa fa-exclamation-triangle"></i>
                    </div>
                </div>
                <h4 class="modal-title">Eliminar reporte</h4>
                <p class="modal-message">
                    ¿Estas seguro de eliminar el reporte <strong>{{ toDelete.report_number }}</strong>?
                    Esta accion no se puede deshacer.
                </p>
                <div class="modal-actions">
                    <button class="modal-btn modal-btn-cancel" @click="toDelete = null">
                        Cancelar
                    </button>
                    <button class="modal-btn modal-btn-danger" @click="deleteReport" :disabled="deleting">
                        <span v-if="deleting" class="spinner-border spinner-border-sm me-2"></span>
                        {{ deleting ? 'Eliminando...' : 'Eliminar' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import reportService from '@/services/reportService.js';
import clientService from '@/services/clientService.js';

export default {
    name: 'ServiceReportIndex',

    data() {
        return {
            reports: [],
            clients: [],
            loading: true,
            toDelete: null,
            deleting: false,
            pdfLoading: null,
            exporting: false,
            searchTimeout: null,
            showDropdown: false,
            filters: {
                report_type: '',
                client_id: '',
                status: '',
                search: '',
                date_from: '',
                date_to: '',
            },
            statusLabels: {
                borrador: 'Borrador',
                firmado_tecnico: 'Firmado Tecnico',
                firmado_cliente: 'Firmado Cliente',
                cerrado: 'Cerrado',
                anulado: 'Anulado',
            },
        };
    },

    computed: {
        filteredReports() {
            let results = this.reports;

            if (this.filters.report_type) {
                results = results.filter(r => r.report_type === this.filters.report_type);
            }

            if (this.filters.client_id) {
                const clientId = parseInt(this.filters.client_id);
                results = results.filter(r => {
                    const rClientId = r.client?.id || r.equipment?.site?.client?.id;
                    return rClientId === clientId;
                });
            }

            if (this.filters.status) {
                results = results.filter(r => r.status === this.filters.status);
            }

            if (this.filters.search) {
                const term = this.filters.search.toLowerCase();
                results = results.filter(r => {
                    const number = (r.report_number || '').toLowerCase();
                    const equipment = (r.equipment?.internal_code || '').toLowerCase();
                    const technician = (r.technician?.name || '').toLowerCase();
                    return number.includes(term) || equipment.includes(term) || technician.includes(term);
                });
            }

            if (this.filters.date_from) {
                results = results.filter(r => r.service_date >= this.filters.date_from);
            }

            if (this.filters.date_to) {
                results = results.filter(r => r.service_date <= this.filters.date_to);
            }

            return results;
        },

        activeFilters() {
            const params = {};
            if (this.filters.report_type) params.report_type = this.filters.report_type;
            if (this.filters.client_id) params.client_id = this.filters.client_id;
            if (this.filters.status) params.status = this.filters.status;
            if (this.filters.search) params.search = this.filters.search;
            if (this.filters.date_from) params.date_from = this.filters.date_from;
            if (this.filters.date_to) params.date_to = this.filters.date_to;
            return params;
        },
    },

    async mounted() {
        await Promise.all([this.load(), this.loadClients()]);
        document.addEventListener('click', this.handleClickOutside);
    },

    beforeUnmount() {
        document.removeEventListener('click', this.handleClickOutside);
    },

    methods: {
        async load() {
            this.loading = true;
            try {
                const { data } = await reportService.all();
                this.reports = data;
            } finally {
                this.loading = false;
            }
        },

        async loadClients() {
            try {
                const { data } = await clientService.all();
                this.clients = data;
            } catch {
                this.clients = [];
            }
        },

        onSearch() {
            clearTimeout(this.searchTimeout);
            this.searchTimeout = setTimeout(() => {
                // Filtering is done via computed, no need to reload
            }, 300);
        },

        formatDate(dateStr) {
            if (!dateStr) return '-';
            const date = new Date(dateStr + 'T00:00:00');
            return date.toLocaleDateString('es-VE', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
            });
        },

        async downloadPdf(report) {
            this.pdfLoading = report.id;
            try {
                const response = await reportService.getPdf(report.id);
                const blob = new Blob([response.data], { type: 'application/pdf' });
                const url = window.URL.createObjectURL(blob);
                window.open(url, '_blank');
                setTimeout(() => window.URL.revokeObjectURL(url), 10000);
            } catch {
                alert('Error al generar el PDF');
            } finally {
                this.pdfLoading = null;
            }
        },

        confirmDelete(report) {
            this.toDelete = report;
        },

        async deleteReport() {
            this.deleting = true;
            try {
                await reportService.destroy(this.toDelete.id);
                this.reports = this.reports.filter(r => r.id !== this.toDelete.id);
                this.toDelete = null;
            } finally {
                this.deleting = false;
            }
        },

        async exportCsv() {
            this.exporting = true;
            try {
                const { data } = await reportService.exportCsv(this.activeFilters);
                const url = URL.createObjectURL(new Blob([data]));
                const link = document.createElement('a');
                link.href = url;
                link.download = `reportes-${new Date().toISOString().slice(0, 10)}.csv`;
                link.click();
                URL.revokeObjectURL(url);
            } catch (err) {
                console.error('Error exportando CSV', err);
            } finally {
                this.exporting = false;
            }
        },

        handleClickOutside(event) {
            const wrapper = this.$refs.dropdownWrapper;
            if (wrapper && !wrapper.contains(event.target)) {
                this.showDropdown = false;
            }
        },
    },
};
</script>

<style scoped>
/* Boton exportar */
.btn-export {
    display: inline-flex;
    align-items: center;
    padding: 0.625rem 1.25rem;
    background: #f1f5f9;
    color: #475569;
    font-weight: 600;
    border-radius: 10px;
    text-decoration: none;
    transition: all 0.2s ease;
    border: 1px solid #e2e8f0;
    cursor: pointer;
    font-size: 0.9rem;
}

.btn-export:hover:not(:disabled) {
    background: #e2e8f0;
    color: #334155;
}

.btn-export:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

/* Boton crear */
.btn-create {
    display: inline-flex;
    align-items: center;
    padding: 0.625rem 1.25rem;
    background: #279208;
    color: white;
    font-weight: 600;
    border-radius: 10px;
    text-decoration: none;
    transition: all 0.2s ease;
    box-shadow: 0 2px 8px rgba(39, 146, 8, 0.25);
    border: none;
    cursor: pointer;
}

.btn-create:hover {
    background: #1f7506;
    box-shadow: 0 4px 12px rgba(39, 146, 8, 0.35);
    color: white;
}

/* Dropdown */
.dropdown-wrapper {
    position: relative;
}

.dropdown-menu-custom {
    position: absolute;
    top: calc(100% + 0.5rem);
    right: 0;
    background: white;
    border-radius: 10px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
    border: 1px solid #e2e8f0;
    min-width: 220px;
    z-index: 100;
    overflow: hidden;
}

.dropdown-item-custom {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    text-decoration: none;
    color: #1e293b;
    font-weight: 500;
    font-size: 0.9rem;
    transition: background 0.15s;
}

.dropdown-item-custom:hover {
    background: #f8fafc;
    color: #1e293b;
}

.dropdown-item-custom.disabled-link {
    opacity: 0.45;
    cursor: not-allowed;
    pointer-events: auto;
}

.dropdown-item-custom.disabled-link:hover {
    background: transparent;
}

/* Filtros */
.filter-bar {
    background: white;
    border-radius: 12px;
    padding: 1.25rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    border: 1px solid #e2e8f0;
}

.filter-row {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
}

.filter-row-dates {
    grid-template-columns: repeat(2, 1fr);
    margin-top: 1rem;
    max-width: 50%;
}

@media (max-width: 992px) {
    .filter-row {
        grid-template-columns: repeat(2, 1fr);
    }
    .filter-row-dates {
        max-width: 100%;
    }
}

@media (max-width: 576px) {
    .filter-row {
        grid-template-columns: 1fr;
    }
    .filter-row-dates {
        grid-template-columns: 1fr;
        max-width: 100%;
    }
}

.filter-label {
    display: block;
    font-size: 0.8rem;
    font-weight: 500;
    color: #64748b;
    margin-bottom: 0.375rem;
}

.filter-select,
.filter-input {
    width: 100%;
    padding: 0.5rem 0.75rem;
    font-size: 0.9rem;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    background: white;
    transition: all 0.2s;
    color: #1e293b;
}

.filter-select:focus,
.filter-input:focus {
    outline: none;
    border-color: #279208;
    box-shadow: 0 0 0 3px rgba(39, 146, 8, 0.1);
}

.filter-input::placeholder {
    color: #94a3b8;
}

/* Estado de carga */
.loading-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 4rem 2rem;
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border-radius: 20px;
}

/* Estado vacio */
.empty-state {
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border-radius: 20px;
    padding: 4rem 2rem;
}

.empty-state-content {
    max-width: 400px;
    margin: 0 auto;
    text-align: center;
}

.empty-illustration {
    margin-bottom: 2rem;
}

.empty-svg {
    width: 200px;
    height: 160px;
}

.empty-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 0.75rem;
}

.empty-description {
    color: #64748b;
    line-height: 1.6;
    margin-bottom: 1.5rem;
}

/* Tabla */
.table-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    border: 1px solid #e2e8f0;
    overflow: hidden;
}

.table-responsive {
    overflow-x: auto;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table thead {
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
}

.data-table th {
    padding: 0.875rem 1.25rem;
    font-size: 0.8rem;
    font-weight: 600;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    text-align: left;
    white-space: nowrap;
}

.th-actions {
    text-align: center;
}

.data-table td {
    padding: 0.875rem 1.25rem;
    font-size: 0.9rem;
    color: #475569;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
}

.data-table tbody tr:last-child td {
    border-bottom: none;
}

.data-table tbody tr:hover {
    background: #fafafa;
}

.code-text {
    font-weight: 600;
    color: #1e293b;
}

/* Badges de tipo */
.tipo-badge {
    display: inline-block;
    font-size: 0.75rem;
    font-weight: 600;
    padding: 0.25rem 0.6rem;
    border-radius: 6px;
    letter-spacing: 0.03em;
}

.tipo-rstp {
    background: #e8f5e4;
    color: #30ab0a;
}

.tipo-rstc {
    background: #fef2f2;
    color: #ba2831;
}

.tipo-rste {
    background: #fff7ed;
    color: #d97706;
}

/* Badges de estado */
.status-badge {
    display: inline-block;
    font-size: 0.75rem;
    font-weight: 500;
    padding: 0.3rem 0.6rem;
    border-radius: 20px;
    white-space: nowrap;
}

.status-borrador {
    background: #f1f5f9;
    color: #606060;
}

.status-firmado_tecnico {
    background: #fff7ed;
    color: #d97706;
}

.status-firmado_cliente {
    background: #eff6ff;
    color: #2563eb;
}

.status-cerrado {
    background: #e8f5e4;
    color: #30ab0a;
}

.status-anulado {
    background: #fef2f2;
    color: #ba2831;
}

/* Acciones */
.item-actions {
    display: flex;
    gap: 0.5rem;
    justify-content: center;
}

.action-btn {
    width: 32px;
    height: 32px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f1f5f9;
    color: #64748b;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
    text-decoration: none;
}

.action-btn:hover {
    background: #e2e8f0;
    color: #475569;
}

.action-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.action-delete:hover {
    background: #fef2f2;
    color: #ba2831;
}

/* Modal */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(15, 23, 42, 0.6);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    padding: 1rem;
}

.modal-container {
    background: white;
    border-radius: 20px;
    padding: 2rem;
    max-width: 400px;
    width: 100%;
    text-align: center;
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
}

.modal-icon-wrapper {
    margin-bottom: 1.5rem;
}

.modal-icon {
    width: 64px;
    height: 64px;
    background: linear-gradient(135deg, #fef2f2, #fee2e2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    color: #ba2831;
    font-size: 1.5rem;
}

.modal-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 0.75rem;
}

.modal-message {
    color: #64748b;
    line-height: 1.6;
    margin-bottom: 1.5rem;
}

.modal-actions {
    display: flex;
    gap: 0.75rem;
}

.modal-btn {
    flex: 1;
    padding: 0.75rem 1.5rem;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.95rem;
    border: none;
    cursor: pointer;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.modal-btn-cancel {
    background: #f1f5f9;
    color: #64748b;
}

.modal-btn-cancel:hover {
    background: #e2e8f0;
}

.modal-btn-danger {
    background: linear-gradient(135deg, #ba2831, #dc2626);
    color: white;
}

.modal-btn-danger:hover:not(:disabled) {
    background: linear-gradient(135deg, #dc2626, #b91c1c);
}

.modal-btn-danger:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}
</style>
