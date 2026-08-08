<template>
    <div>
        <!-- Titulo -->
        <div class="page-header">
            <h1 class="page-title">Panel de Control</h1>
            <p class="page-subtitle">Resumen general de tus equipos y reportes</p>
        </div>

        <!-- Estado de carga -->
        <div v-if="loading" class="loading-state">
            <div class="spinner-border text-primary"></div>
            <p class="text-muted mt-3">Cargando datos...</p>
        </div>

        <template v-else>
            <!-- Hero Section -->
            <div class="welcome-hero">
                <div class="hero-content">
                    <div class="hero-text">
                        <h1 class="hero-title">
                            Gestion integral de
                            <span class="gradient-text">transporte vertical</span>
                        </h1>
                        <p class="hero-description">
                            Consulta el estado de tus equipos, revisa los reportes de servicio
                            y haz seguimiento al mantenimiento desde un solo lugar.
                        </p>
                    </div>
                    <div class="hero-illustration">
                        <svg viewBox="0 0 400 300" class="building-illustration">
                            <circle cx="200" cy="150" r="120" fill="url(#pg1)" opacity="0.1">
                                <animate attributeName="r" values="120;130;120" dur="4s" repeatCount="indefinite"/>
                            </circle>
                            <circle cx="200" cy="150" r="80" fill="url(#pg2)" opacity="0.15">
                                <animate attributeName="r" values="80;90;80" dur="3s" repeatCount="indefinite"/>
                            </circle>
                            <g transform="translate(120, 40)">
                                <rect x="20" y="20" width="120" height="200" rx="6" fill="#e8f5e4" stroke="#30ab0a" stroke-width="2"/>
                                <rect x="35" y="40" width="25" height="20" rx="3" fill="#30ab0a" opacity="0.3"/>
                                <rect x="100" y="40" width="25" height="20" rx="3" fill="#30ab0a" opacity="0.3"/>
                                <rect x="35" y="75" width="25" height="20" rx="3" fill="#30ab0a" opacity="0.3"/>
                                <rect x="100" y="75" width="25" height="20" rx="3" fill="#30ab0a" opacity="0.3"/>
                                <rect x="35" y="110" width="25" height="20" rx="3" fill="#30ab0a" opacity="0.3"/>
                                <rect x="100" y="110" width="25" height="20" rx="3" fill="#30ab0a" opacity="0.3"/>
                                <rect x="35" y="145" width="25" height="20" rx="3" fill="#30ab0a" opacity="0.3"/>
                                <rect x="100" y="145" width="25" height="20" rx="3" fill="#30ab0a" opacity="0.3"/>
                                <rect x="65" y="35" width="30" height="180" rx="2" fill="#279208" opacity="0.15"/>
                                <rect x="68" y="140" width="24" height="30" rx="2" fill="#30ab0a" opacity="0.9">
                                    <animate attributeName="y" values="140;50;140" dur="4s" repeatCount="indefinite"/>
                                </rect>
                                <path d="M80 25 L74 32 L86 32 Z" fill="#30ab0a"/>
                                <path d="M80 222 L74 215 L86 215 Z" fill="#30ab0a"/>
                                <rect x="65" y="185" width="30" height="35" rx="3" fill="#606060" opacity="0.3"/>
                            </g>
                            <circle cx="320" cy="60" r="8" fill="#30ab0a" opacity="0.6">
                                <animate attributeName="cy" values="60;50;60" dur="2s" repeatCount="indefinite"/>
                            </circle>
                            <circle cx="80" cy="200" r="6" fill="#279208" opacity="0.6">
                                <animate attributeName="cy" values="200;210;200" dur="2.5s" repeatCount="indefinite"/>
                            </circle>
                            <circle cx="350" cy="180" r="10" fill="#606060" opacity="0.4">
                                <animate attributeName="cy" values="180;170;180" dur="3s" repeatCount="indefinite"/>
                            </circle>
                            <defs>
                                <linearGradient id="pg1" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" style="stop-color:#30ab0a"/>
                                    <stop offset="100%" style="stop-color:#279208"/>
                                </linearGradient>
                                <linearGradient id="pg2" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" style="stop-color:#279208"/>
                                    <stop offset="100%" style="stop-color:#30ab0a"/>
                                </linearGradient>
                            </defs>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Proxima visita programada -->
            <router-link v-if="nextVisit" to="/portal/cronograma" class="next-visit-card">
                <div class="next-visit-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="5" width="18" height="16" rx="2"/>
                        <line x1="3" y1="10" x2="21" y2="10"/>
                        <line x1="8" y1="3" x2="8" y2="7"/>
                        <line x1="16" y1="3" x2="16" y2="7"/>
                    </svg>
                </div>
                <div class="next-visit-body">
                    <span class="next-visit-label">Próxima visita</span>
                    <span class="next-visit-title">
                        {{ nextVisit.equipment?.internal_code }} · {{ nextVisit.site?.name }}
                    </span>
                    <span class="next-visit-when">{{ nextVisitWhen }}</span>
                </div>
                <div class="next-visit-aside">
                    <span class="next-visit-countdown">{{ nextVisitCountdown }}</span>
                    <span class="next-visit-tech">{{ nextVisit.technician?.name || 'Técnico por asignar' }}</span>
                </div>
            </router-link>

            <!-- Stat Cards -->
            <div class="stats-grid">
                <div class="stat-card stat-green">
                    <div class="stat-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="4" y="2" width="16" height="20" rx="2"/>
                            <line x1="12" y1="6" x2="12" y2="18"/>
                            <line x1="8" y1="10" x2="16" y2="10"/>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <span class="stat-number">{{ stats.total_equipment }}</span>
                        <span class="stat-label">Total Equipos</span>
                    </div>
                </div>

                <div class="stat-card stat-blue">
                    <div class="stat-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 11.08V12a10 10 0 11-5.93-9.14"/>
                            <polyline points="22 4 12 14.01 9 11.01"/>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <span class="stat-number">{{ stats.active_equipment }}</span>
                        <span class="stat-label">Equipos Activos</span>
                    </div>
                </div>

                <div class="stat-card stat-orange">
                    <div class="stat-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="5" y="2" width="14" height="20" rx="2"/>
                            <line x1="9" y1="7" x2="15" y2="7"/>
                            <line x1="9" y1="11" x2="15" y2="11"/>
                            <line x1="9" y1="15" x2="13" y2="15"/>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <span class="stat-number">{{ stats.total_reports }}</span>
                        <span class="stat-label">Total Reportes</span>
                    </div>
                </div>

                <div class="stat-card stat-gray">
                    <div class="stat-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="4" width="18" height="18" rx="2"/>
                            <line x1="16" y1="2" x2="16" y2="6"/>
                            <line x1="8" y1="2" x2="8" y2="6"/>
                            <line x1="3" y1="10" x2="21" y2="10"/>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <span class="stat-number">{{ stats.reports_this_month }}</span>
                        <span class="stat-label">Reportes Este Mes</span>
                    </div>
                </div>
            </div>

            <!-- Graficos -->
            <div class="charts-grid">
                <!-- Cumplimiento de Mantenimiento -->
                <div class="chart-card">
                    <h3 class="card-title">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 11.08V12a10 10 0 11-5.93-9.14"/>
                            <polyline points="22 4 12 14.01 9 11.01"/>
                        </svg>
                        Cumplimiento de Mantenimiento
                    </h3>
                    <apexchart
                        v-if="chartsReady"
                        type="radialBar"
                        height="280"
                        :options="complianceChartOptions"
                        :series="[compliancePercent]"
                    />
                    <p class="compliance-detail">
                        {{ stats.compliance_detail?.completed ?? 0 }} de
                        {{ stats.compliance_detail?.expected ?? 0 }}
                        mantenimientos preventivos completados este mes
                    </p>
                </div>

                <!-- Reportes Ultimos 6 Meses -->
                <div v-if="stats.reports_by_month && stats.reports_by_month.length" class="chart-card">
                    <h3 class="card-title">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="3" width="18" height="18" rx="2"/>
                            <line x1="9" y1="14" x2="9" y2="21"/>
                            <line x1="15" y1="10" x2="15" y2="21"/>
                            <line x1="21" y1="6" x2="21" y2="21"/>
                        </svg>
                        Reportes Ultimos 6 Meses
                    </h3>
                    <apexchart
                        v-if="chartsReady"
                        type="bar"
                        height="280"
                        :options="reportsByMonthOptions"
                        :series="reportsByMonthSeries"
                    />
                </div>
            </div>

            <!-- Reportes del Mes -->
            <div v-if="stats.recent_reports && stats.recent_reports.length" class="recent-reports-card">
                <h3 class="card-title">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="5" y="2" width="14" height="20" rx="2"/>
                        <line x1="9" y1="7" x2="15" y2="7"/>
                        <line x1="9" y1="11" x2="15" y2="11"/>
                        <line x1="9" y1="15" x2="13" y2="15"/>
                    </svg>
                    Reportes del Mes
                </h3>
                <div class="table-responsive">
                    <table class="reports-table data-table">
                        <thead>
                            <tr>
                                <th>N° Reporte</th>
                                <th>Tipo</th>
                                <th>Equipo</th>
                                <th>Fecha</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="report in stats.recent_reports" :key="report.id">
                                <td class="fw-500" data-label="N° Reporte">{{ report.report_number }}</td>
                                <td data-label="Tipo">
                                    <span class="tipo-badge" :class="'tipo-' + (report.report_type || '').toLowerCase()">
                                        {{ report.report_type }}
                                    </span>
                                </td>
                                <td data-label="Equipo">{{ report.equipment?.internal_code || '-' }}</td>
                                <td data-label="Fecha">{{ formatDate(report.service_date) }}</td>
                                <td data-label="Estado">
                                    <span class="status-badge" :class="'status-' + report.status">
                                        {{ statusLabels[report.status] || report.status }}
                                    </span>
                                </td>
                                <td class="cell-actions" data-label="Acciones">
                                    <div class="item-actions">
                                        <router-link :to="'/reportes/' + report.id"
                                                     class="action-btn" title="Ver detalle">
                                            <i class="fa fa-eye"></i>
                                        </router-link>
                                        <button @click="downloadPdf(report)" class="action-btn" title="Descargar PDF"
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

            <!-- Quick links -->
            <div class="quick-links">
                <router-link to="/portal/equipos" class="quick-link-btn quick-link-green">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="4" y="2" width="16" height="20" rx="2"/>
                        <line x1="12" y1="6" x2="12" y2="18"/>
                        <line x1="8" y1="10" x2="16" y2="10"/>
                    </svg>
                    Ver Equipos
                </router-link>
                <router-link to="/portal/reportes" class="quick-link-btn quick-link-blue">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="5" y="2" width="14" height="20" rx="2"/>
                        <line x1="9" y1="7" x2="15" y2="7"/>
                        <line x1="9" y1="11" x2="15" y2="11"/>
                        <line x1="9" y1="15" x2="13" y2="15"/>
                    </svg>
                    Ver Reportes
                </router-link>
            </div>
        </template>
    </div>
</template>

<script>
import portalService from '@/services/portalService.js';
import reportService from '@/services/reportService.js';

export default {
    name: 'PortalDashboard',

    data() {
        return {
            loading: true,
            chartsReady: false,
            pdfLoading: null,
            nextVisit: null,
            stats: {
                total_equipment: 0,
                active_equipment: 0,
                total_reports: 0,
                reports_this_month: 0,
                recent_reports: [],
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
        /** "Hoy" / "Mañana" / "En N días" para la tarjeta de próxima visita. */
        nextVisitCountdown() {
            if (!this.nextVisit) return '';

            const start = new Date(this.nextVisit.scheduled_start);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            start.setHours(0, 0, 0, 0);

            const days = Math.round((start - today) / 86400000);
            if (days <= 0) return 'Hoy';
            if (days === 1) return 'Mañana';
            return `En ${days} días`;
        },

        nextVisitWhen() {
            if (!this.nextVisit) return '';

            const start = new Date(this.nextVisit.scheduled_start);
            const end = new Date(this.nextVisit.scheduled_end);
            const day = start.toLocaleDateString('es-CO', { weekday: 'long', day: 'numeric', month: 'long' });
            // hour12 explícito: el locale es-CO devuelve "08:30 a. m." y aquí todo
            // el cronograma está en 24 horas.
            const hour = t => t.toLocaleTimeString('es-CO', { hour: '2-digit', minute: '2-digit', hour12: false });

            return `${day.charAt(0).toUpperCase() + day.slice(1)} · ${hour(start)}–${hour(end)}`;
        },

        compliancePercent() {
            return Math.min(100, Math.max(0, Math.round(this.stats.maintenance_compliance_percent || 0)));
        },

        complianceColor() {
            if (this.compliancePercent >= 80) return '#30ab0a';
            if (this.compliancePercent >= 50) return '#d97706';
            return '#ba2831';
        },

        complianceChartOptions() {
            return {
                chart: { type: 'radialBar' },
                plotOptions: {
                    radialBar: {
                        startAngle: -135,
                        endAngle: 135,
                        hollow: { size: '65%' },
                        track: { background: '#f1f5f9', strokeWidth: '100%' },
                        dataLabels: {
                            name: {
                                show: true,
                                fontSize: '13px',
                                color: '#64748b',
                                offsetY: 60,
                            },
                            value: {
                                show: true,
                                fontSize: '2rem',
                                fontWeight: 700,
                                color: this.complianceColor,
                                offsetY: -5,
                                formatter: (val) => val + '%',
                            },
                        },
                    },
                },
                fill: { colors: [this.complianceColor] },
                stroke: { lineCap: 'round' },
                labels: [
                    this.compliancePercent >= 80
                        ? 'Excelente'
                        : this.compliancePercent >= 50
                            ? 'Aceptable'
                            : 'Necesita atencion',
                ],
            };
        },

        reportsByMonthOptions() {
            const months = (this.stats.reports_by_month || []).map(m => m.month);
            return {
                chart: { type: 'bar', stacked: true, toolbar: { show: false } },
                plotOptions: {
                    bar: { columnWidth: '50%', borderRadius: 4 },
                },
                xaxis: {
                    categories: months,
                    labels: { style: { fontSize: '11px', colors: '#64748b' } },
                },
                yaxis: {
                    labels: { style: { fontSize: '11px', colors: '#64748b' } },
                    forceNiceScale: true,
                },
                legend: {
                    position: 'top',
                    fontSize: '12px',
                    labels: { colors: '#475569' },
                },
                colors: ['#30ab0a', '#ba2831', '#d97706'],
                dataLabels: { enabled: false },
                grid: { borderColor: '#f1f5f9' },
                tooltip: {
                    y: { formatter: (val) => val + ' reportes' },
                },
                responsive: [{
                    breakpoint: 768,
                    options: {
                        chart: { height: 260 },
                        plotOptions: { bar: { columnWidth: '70%' } },
                        legend: { position: 'bottom', fontSize: '11px', offsetY: 4, itemMargin: { horizontal: 6 } },
                        xaxis: { labels: { rotate: -45, rotateAlways: true, trim: true, style: { fontSize: '9px' } } },
                    },
                }],
            };
        },

        reportsByMonthSeries() {
            const data = this.stats.reports_by_month || [];
            return [
                { name: 'Preventivo (RSTP)', data: data.map(m => m.rstp) },
                { name: 'Correctivo (RSTC)', data: data.map(m => m.rstc) },
                { name: 'Emergencia (RSTE)', data: data.map(m => m.rste) },
            ];
        },
    },

    async mounted() {
        await this.load();
        this._resizeObserver = new ResizeObserver(() => {
            window.dispatchEvent(new Event('resize'));
        });
        this._resizeObserver.observe(this.$el);
    },

    beforeUnmount() {
        this.chartsReady = false;
        if (this._resizeObserver) {
            this._resizeObserver.disconnect();
        }
    },

    methods: {
        async load() {
            this.loading = true;
            try {
                const { data } = await portalService.dashboard();
                this.stats = {
                    total_equipment: data.total_equipment ?? 0,
                    active_equipment: data.active_equipment ?? 0,
                    total_reports: data.total_reports ?? 0,
                    reports_this_month: data.reports_this_month ?? 0,
                    recent_reports: data.recent_reports ?? [],
                    maintenance_compliance_percent: data.maintenance_compliance_percent ?? 0,
                    reports_by_month: data.reports_by_month ?? [],
                    compliance_detail: data.compliance_detail ?? { completed: 0, expected: 0 },
                };
                this.nextVisit = data.next_visit ?? null;
            } finally {
                this.loading = false;
                this.$nextTick(() => { this.chartsReady = true; });
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
                const link = document.createElement('a');
                link.href = url;
                link.download = `${report.report_number || 'reporte'}.pdf`;
                link.click();
                window.URL.revokeObjectURL(url);
            } catch {
                alert('No se pudo descargar el PDF.');
            } finally {
                this.pdfLoading = null;
            }
        },
    },
};
</script>

<style scoped>
/* Page header */
.page-header {
    margin-bottom: 1.5rem;
}

.page-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1e293b;
    margin: 0;
}

.page-subtitle {
    font-size: 0.9rem;
    color: #64748b;
    margin: 0.25rem 0 0;
}

/* Loading */
.loading-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 4rem 2rem;
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border-radius: 20px;
}

/* Proxima visita */
.next-visit-card {
    display: flex;
    align-items: center;
    gap: 1.25rem;
    padding: 1.1rem 1.4rem;
    background: white;
    border: 1px solid #e2e8f0;
    border-left: 4px solid #30ab0a;
    border-radius: 16px;
    margin-bottom: 1.5rem;
    text-decoration: none;
    transition: box-shadow 0.2s;
}

.next-visit-card:hover {
    box-shadow: 0 4px 16px rgba(15, 23, 42, 0.08);
}

.next-visit-icon {
    width: 48px;
    height: 48px;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 14px;
    background: #eefbe9;
    color: #30ab0a;
}

.next-visit-icon svg {
    width: 24px;
    height: 24px;
}

.next-visit-body {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
}

.next-visit-label {
    font-size: 0.68rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: #94a3b8;
}

.next-visit-title {
    font-size: 1rem;
    font-weight: 700;
    color: #1e293b;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.next-visit-when {
    font-size: 0.82rem;
    color: #64748b;
}

.next-visit-aside {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 0.25rem;
    flex-shrink: 0;
}

.next-visit-countdown {
    font-size: 0.78rem;
    font-weight: 700;
    color: #227a0c;
    background: #eefbe9;
    border-radius: 999px;
    padding: 0.25rem 0.75rem;
    white-space: nowrap;
}

.next-visit-tech {
    font-size: 0.75rem;
    color: #94a3b8;
}

@media (max-width: 576px) {
    .next-visit-card {
        flex-wrap: wrap;
        gap: 0.85rem;
    }

    .next-visit-aside {
        align-items: flex-start;
        width: 100%;
        flex-direction: row;
        justify-content: space-between;
    }
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1.25rem;
    margin-bottom: 1.5rem;
}

@media (max-width: 992px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 576px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
}

.stat-card {
    background: white;
    border-radius: 12px;
    padding: 1.25rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    border: 1px solid #e2e8f0;
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.stat-icon svg {
    width: 24px;
    height: 24px;
}

.stat-green .stat-icon {
    background: #e8f5e4;
    color: #30ab0a;
}

.stat-blue .stat-icon {
    background: #eff6ff;
    color: #2563eb;
}

.stat-orange .stat-icon {
    background: #fff7ed;
    color: #d97706;
}

.stat-gray .stat-icon {
    background: #f1f5f9;
    color: #606060;
}

.stat-content {
    display: flex;
    flex-direction: column;
}

.stat-number {
    font-size: 1.75rem;
    font-weight: 700;
    color: #1e293b;
    line-height: 1;
}

.stat-label {
    font-size: 0.8rem;
    color: #64748b;
    font-weight: 500;
    margin-top: 0.25rem;
}

/* Recent reports card */
.recent-reports-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    border: 1px solid #e2e8f0;
}

.card-title {
    font-size: 1rem;
    font-weight: 600;
    color: #1e293b;
    margin: 0 0 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.card-title svg {
    width: 20px;
    height: 20px;
    color: #64748b;
}

.table-responsive {
    overflow-x: auto;
}

.reports-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.85rem;
}

.reports-table th {
    font-size: 0.75rem;
    color: #94a3b8;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    padding: 0.5rem 0.75rem;
    border-bottom: 1px solid #e2e8f0;
    text-align: left;
    white-space: nowrap;
}

.reports-table td {
    padding: 0.6rem 0.75rem;
    border-bottom: 1px solid #f1f5f9;
    color: #1e293b;
    white-space: nowrap;
}

.reports-table tbody tr:last-child td {
    border-bottom: none;
}

.fw-500 {
    font-weight: 500;
}

.item-actions {
    display: flex;
    gap: 0.4rem;
}

.action-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 30px;
    height: 30px;
    border-radius: 6px;
    border: 1px solid #e2e8f0;
    background: white;
    color: #475569;
    cursor: pointer;
    text-decoration: none;
    font-size: 0.8rem;
    transition: all 0.15s;
}

.action-btn:hover {
    background: #f1f5f9;
    color: #1e293b;
}

.action-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* Badges */
.tipo-badge {
    display: inline-block;
    font-size: 0.75rem;
    font-weight: 600;
    padding: 0.2rem 0.5rem;
    border-radius: 6px;
    letter-spacing: 0.03em;
    width: fit-content;
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

.status-badge {
    display: inline-block;
    font-size: 0.75rem;
    font-weight: 500;
    padding: 0.2rem 0.5rem;
    border-radius: 20px;
    white-space: nowrap;
    width: fit-content;
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

/* Charts grid */
.charts-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}

@media (max-width: 992px) {
    .charts-grid {
        grid-template-columns: 1fr;
    }
}

.chart-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    border: 1px solid #e2e8f0;
}

.compliance-detail {
    font-size: 0.8rem;
    color: #64748b;
    text-align: center;
    margin: 0;
}

/* Quick links */
.quick-links {
    display: flex;
    gap: 1rem;
}

.quick-link-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    font-weight: 600;
    font-size: 0.9rem;
    border-radius: 10px;
    text-decoration: none;
    transition: all 0.2s;
}

.quick-link-btn svg {
    width: 18px;
    height: 18px;
}

.quick-link-green {
    background: #279208;
    color: white;
    box-shadow: 0 2px 8px rgba(39, 146, 8, 0.25);
}

.quick-link-green:hover {
    background: #1f7506;
    color: white;
    box-shadow: 0 4px 12px rgba(39, 146, 8, 0.35);
}

.quick-link-blue {
    background: #2563eb;
    color: white;
    box-shadow: 0 2px 8px rgba(37, 99, 235, 0.25);
}

.quick-link-blue:hover {
    background: #1d4ed8;
    color: white;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.35);
}

@media (max-width: 576px) {
    .quick-links {
        flex-direction: column;
    }

    .last-report-info {
        flex-direction: column;
        gap: 0.75rem;
    }
}

/* Hero Section */
.welcome-hero {
    background: white;
    border-radius: 12px;
    padding: 3rem;
    margin-bottom: 1.5rem;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    border: 1px solid #e2e8f0;
}

.hero-content {
    display: flex;
    align-items: center;
    gap: 3rem;
}

.hero-text {
    flex: 1;
}

.hero-title {
    font-size: 2.5rem;
    font-weight: 700;
    color: #1e293b;
    line-height: 1.2;
    margin-bottom: 1.5rem;
}

.gradient-text {
    background: linear-gradient(135deg, #30ab0a, #279208);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.hero-description {
    font-size: 1.1rem;
    color: #606060;
    line-height: 1.7;
    margin: 0;
}

.hero-illustration {
    flex: 1;
    display: flex;
    justify-content: center;
}

.building-illustration {
    width: 100%;
    max-width: 400px;
    height: auto;
}

@media (max-width: 992px) {
    .hero-content {
        flex-direction: column;
        text-align: center;
    }

    .hero-title {
        font-size: 2rem;
    }

    .hero-illustration {
        order: -1;
    }

    .building-illustration {
        max-width: 300px;
    }
}

@media (max-width: 576px) {
    .welcome-hero {
        padding: 2rem 1.5rem;
    }

    .hero-title {
        font-size: 1.75rem;
    }

    .hero-description {
        font-size: 1rem;
    }
}
</style>
