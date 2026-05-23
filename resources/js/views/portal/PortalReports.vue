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
                        Mis Reportes
                        <div class="page-title-subheading text-muted">
                            Consulta los reportes de servicio de tus equipos
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
                    <label class="filter-label">Estado</label>
                    <select v-model="filters.status" class="filter-select">
                        <option value="">Todos</option>
                        <option v-for="(label, key) in statusLabels" :key="key" :value="key">
                            {{ label }}
                        </option>
                    </select>
                </div>
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
                    </svg>
                </div>
                <h3 class="empty-title">No hay reportes de servicio</h3>
                <p class="empty-description">
                    No se encontraron reportes asociados a tus equipos.
                </p>
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
                            <td>{{ report.technician?.name || '-' }}</td>
                            <td>
                                <span class="status-badge" :class="'status-' + report.status">
                                    {{ statusLabels[report.status] || report.status }}
                                </span>
                            </td>
                            <td>
                                <div class="item-actions">
                                    <router-link :to="'/reportes/' + report.id"
                                                 class="action-btn" title="Ver">
                                        <i class="fa fa-eye"></i>
                                    </router-link>
                                    <button @click="downloadPdf(report)" class="action-btn" title="PDF"
                                            :disabled="pdfLoading === report.id">
                                        <i v-if="pdfLoading === report.id" class="fa fa-spinner fa-spin"></i>
                                        <i v-else class="fa fa-file-pdf"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script>
import portalService from '@/services/portalService.js';
import reportService from '@/services/reportService.js';

export default {
    name: 'PortalReports',

    data() {
        return {
            reports: [],
            loading: true,
            pdfLoading: null,
            filters: {
                report_type: '',
                status: '',
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

            if (this.filters.status) {
                results = results.filter(r => r.status === this.filters.status);
            }

            if (this.filters.date_from) {
                results = results.filter(r => r.service_date >= this.filters.date_from);
            }

            if (this.filters.date_to) {
                results = results.filter(r => r.service_date <= this.filters.date_to);
            }

            return results;
        },
    },

    async mounted() {
        await this.load();
    },

    methods: {
        async load() {
            this.loading = true;
            try {
                const { data } = await portalService.reports();
                this.reports = data;
            } finally {
                this.loading = false;
            }
        },

        formatDate(dateStr) {
            if (!dateStr) return '-';
            const dateOnly = dateStr.split('T')[0];
            const date = new Date(dateOnly + 'T00:00:00');
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
    },
};
</script>

<style scoped>
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

@media (max-width: 992px) {
    .filter-row {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 576px) {
    .filter-row {
        grid-template-columns: 1fr;
    }
}

.filter-label {
    display: block;
    font-family: 'Poppins', sans-serif;
    font-size: 0.8rem;
    font-weight: 500;
    color: #64748b;
    margin-bottom: 0.375rem;
}

.filter-select,
.filter-input {
    width: 100%;
    padding: 0.5rem 0.75rem;
    font-family: 'Poppins', sans-serif;
    font-size: 0.875rem;
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
</style>
