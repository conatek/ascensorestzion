<template>
    <div>
        <div class="app-page-title">
            <div class="page-title-wrapper">
                <div class="page-title-heading">
                    <div class="page-title-icon">
                        <i class="fa fa-tachometer-alt icon-gradient bg-mean-fruit"></i>
                    </div>
                    <div>
                        Panel de Administracion
                        <div class="page-title-subheading text-muted">
                            Metricas globales de Ascensores Tzion
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="filter-bar mb-4">
            <div class="row g-3 align-items-end">
                <div class="col">
                    <label class="form-label fw-semibold">Desde</label>
                    <input type="date" class="form-control" v-model="filters.date_from">
                </div>
                <div class="col">
                    <label class="form-label fw-semibold">Hasta</label>
                    <input type="date" class="form-control" v-model="filters.date_to">
                </div>
                <div class="col">
                    <label class="form-label fw-semibold">Cliente</label>
                    <select class="form-select" v-model="filters.client_id">
                        <option value="">Todos los clientes</option>
                        <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.business_name }}</option>
                    </select>
                </div>
                <div class="col" v-if="isAdmin">
                    <label class="form-label fw-semibold">Tecnico</label>
                    <select class="form-select" v-model="filters.technician_id">
                        <option value="">Todos los tecnicos</option>
                        <option v-for="t in technicians" :key="t.id" :value="t.id">{{ t.name }}</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="loading-state">
            <div class="spinner-border text-success"></div>
            <p class="text-muted mt-3">Cargando metricas...</p>
        </div>

        <template v-else>
            <!-- KPI Cards -->
            <div class="stats-grid">
                <div class="stat-card stat-green">
                    <div class="stat-icon"><i class="fa fa-arrows-alt-v"></i></div>
                    <div class="stat-content">
                        <div class="stat-number">{{ stats.active_equipment || 0 }}</div>
                        <div class="stat-label">Equipos Activos</div>
                    </div>
                </div>

                <div class="stat-card" :class="complianceCardClass">
                    <div class="stat-icon"><i class="fa fa-chart-line"></i></div>
                    <div class="stat-content">
                        <div class="stat-number">{{ stats.maintenance_compliance_percent || 0 }}%</div>
                        <div class="stat-label">Cumplimiento Mant.</div>
                    </div>
                </div>

                <div class="stat-card stat-red">
                    <div class="stat-icon"><i class="fa fa-exclamation-triangle"></i></div>
                    <div class="stat-content">
                        <div class="stat-number">{{ stats.rstc_open || 0 }} / {{ stats.rstc_closed || 0 }}</div>
                        <div class="stat-label">RSTC Abiertos / Cerrados</div>
                    </div>
                </div>

                <div class="stat-card stat-gray">
                    <div class="stat-icon"><i class="fa fa-clock"></i></div>
                    <div class="stat-content">
                        <div class="stat-number">{{ stats.avg_response_time_minutes != null ? stats.avg_response_time_minutes + ' min' : '-' }}</div>
                        <div class="stat-label">Tiempo Resp. RSTC</div>
                    </div>
                </div>
            </div>

            <!-- Charts Grid -->
            <div class="charts-grid">
                <!-- Chart 1: Reportes por Mes -->
                <div class="section-card">
                    <div class="section-header">
                        <i class="fa fa-chart-bar section-icon icon-green"></i>
                        <span>Reportes por Mes</span>
                    </div>
                    <div class="section-body">
                        <apexchart
                            v-if="chartsReady"
                            type="bar"
                            height="280"
                            :options="reportsByMonthOptions"
                            :series="reportsByMonthSeries"
                        />
                    </div>
                </div>

                <!-- Chart 2: Equipos con mas Fallas -->
                <div class="section-card">
                    <div class="section-header">
                        <i class="fa fa-tools section-icon icon-red"></i>
                        <span>Equipos con mas Fallas</span>
                    </div>
                    <div class="section-body">
                        <apexchart
                            v-if="chartsReady && topFaults.length"
                            type="bar"
                            height="280"
                            :options="topFaultsOptions"
                            :series="topFaultsSeries"
                        />
                        <div v-else-if="!loading && !topFaults.length" class="empty-chart">Sin datos disponibles</div>
                    </div>
                </div>

                <!-- Chart 3: Codigos de Falla -->
                <div class="section-card">
                    <div class="section-header">
                        <i class="fa fa-code section-icon icon-amber"></i>
                        <span>Codigos de Falla</span>
                    </div>
                    <div class="section-body">
                        <apexchart
                            v-if="chartsReady && faultCodes.length"
                            type="bar"
                            height="280"
                            :options="faultCodesOptions"
                            :series="faultCodesSeries"
                        />
                        <div v-else-if="!loading && !faultCodes.length" class="empty-chart">Sin datos disponibles</div>
                    </div>
                </div>

                <!-- Chart 4: Carga por Tecnico -->
                <div class="section-card">
                    <div class="section-header">
                        <i class="fa fa-hard-hat section-icon icon-green"></i>
                        <span>Carga por Tecnico</span>
                    </div>
                    <div class="section-body">
                        <apexchart
                            v-if="chartsReady && techWorkload.length"
                            type="bar"
                            height="280"
                            :options="techWorkloadOptions"
                            :series="techWorkloadSeries"
                        />
                        <div v-else-if="!loading && !techWorkload.length" class="empty-chart">Sin datos disponibles</div>
                    </div>
                </div>
            </div>

            <!-- Compliance Table -->
            <div class="section-card mt-4" v-if="complianceData.length">
                <div class="section-header">
                    <i class="fa fa-clipboard-check section-icon icon-green"></i>
                    <span>Cumplimiento de Mantenimiento por Cliente</span>
                </div>
                <div class="section-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4">Cliente</th>
                                    <th class="text-center">Esperados</th>
                                    <th class="text-center">Realizados</th>
                                    <th class="text-center">Cumplimiento</th>
                                    <th style="min-width: 200px;">Progreso</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="row in complianceData" :key="row.client_name">
                                    <td class="ps-4 fw-medium">{{ row.client_name }}</td>
                                    <td class="text-center">{{ row.expected }}</td>
                                    <td class="text-center">{{ row.completed }}</td>
                                    <td class="text-center fw-bold" :class="complianceTextClass(row.compliance_percent)">
                                        {{ row.compliance_percent }}%
                                    </td>
                                    <td>
                                        <div class="progress" style="height: 10px;">
                                            <div
                                                class="progress-bar"
                                                :class="complianceBarClass(row.compliance_percent)"
                                                role="progressbar"
                                                :style="{ width: row.compliance_percent + '%' }"
                                                :aria-valuenow="row.compliance_percent"
                                                aria-valuemin="0"
                                                aria-valuemax="100"
                                            ></div>
                                        </div>
                                    </td>
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
import statsService from '@/services/statsService.js';
import clientService from '@/services/clientService.js';
import adminService from '@/services/adminService.js';
import { useAuth } from '@/stores/auth.js';

export default {
    name: 'AdminDashboard',

    data() {
        const auth = useAuth();
        return {
            auth,
            loading: true,
            chartsReady: false,
            clients: [],
            technicians: [],
            filters: {
                date_from: '',
                date_to: '',
                client_id: '',
                technician_id: '',
            },
            stats: {},
            reportsByMonth: [],
            topFaults: [],
            faultCodes: [],
            techWorkload: [],
            complianceData: [],
        };
    },

    computed: {
        isAdmin() {
            return this.auth.isMaster() || this.auth.isAdmin() || this.auth.isCoordinator();
        },

        complianceCardClass() {
            const val = this.stats.maintenance_compliance_percent || 0;
            if (val >= 80) return 'stat-green';
            if (val >= 50) return 'stat-amber';
            return 'stat-red';
        },

        activeFilters() {
            const params = {};
            if (this.filters.date_from) params.date_from = this.filters.date_from;
            if (this.filters.date_to) params.date_to = this.filters.date_to;
            if (this.filters.client_id) params.client_id = this.filters.client_id;
            if (this.filters.technician_id) params.technician_id = this.filters.technician_id;
            return params;
        },

        /* Chart: Reportes por Mes (Stacked) */
        reportsByMonthOptions() {
            return {
                chart: { type: 'bar', stacked: true, toolbar: { show: false } },
                plotOptions: { bar: { columnWidth: '50%', borderRadius: 4 } },
                xaxis: {
                    categories: this.reportsByMonth.map(r => r.month),
                    labels: { style: { fontSize: '11px', colors: '#64748b' } },
                },
                yaxis: {
                    labels: { style: { fontSize: '11px', colors: '#64748b' } },
                    forceNiceScale: true,
                },
                legend: { position: 'top', fontSize: '12px', labels: { colors: '#475569' } },
                colors: ['#30ab0a', '#ba2831', '#d97706'],
                dataLabels: { enabled: false },
                grid: { borderColor: '#f1f5f9' },
                tooltip: { y: { formatter: (val) => val + ' reportes' } },
            };
        },

        reportsByMonthSeries() {
            return [
                { name: 'RSTP', data: this.reportsByMonth.map(r => r.rstp || 0) },
                { name: 'RSTC', data: this.reportsByMonth.map(r => r.rstc || 0) },
                { name: 'RSTE', data: this.reportsByMonth.map(r => r.rste || 0) },
            ];
        },

        /* Chart: Equipos con mas Fallas (Horizontal) */
        topFaultsOptions() {
            return {
                chart: { type: 'bar', toolbar: { show: false } },
                plotOptions: { bar: { horizontal: true, borderRadius: 4 } },
                xaxis: {
                    labels: { style: { fontSize: '11px', colors: '#64748b' } },
                    forceNiceScale: true,
                },
                yaxis: {
                    labels: { style: { fontSize: '11px', colors: '#475569' } },
                },
                colors: ['#ba2831'],
                dataLabels: { enabled: false },
                grid: { borderColor: '#f1f5f9' },
                tooltip: { y: { formatter: (val) => val + ' fallas' } },
            };
        },

        topFaultsSeries() {
            return [{
                name: 'Fallas',
                data: this.topFaults.map(e => ({
                    x: `${e.internal_code} - ${e.brand}`,
                    y: e.fault_count || 0,
                })),
            }];
        },

        /* Chart: Codigos de Falla (Horizontal) */
        faultCodesOptions() {
            return {
                chart: { type: 'bar', toolbar: { show: false } },
                plotOptions: { bar: { horizontal: true, borderRadius: 4 } },
                xaxis: {
                    labels: { style: { fontSize: '11px', colors: '#64748b' } },
                    forceNiceScale: true,
                },
                yaxis: {
                    labels: { style: { fontSize: '11px', colors: '#475569' } },
                },
                colors: ['#d97706'],
                dataLabels: { enabled: false },
                grid: { borderColor: '#f1f5f9' },
                tooltip: { y: { formatter: (val) => val + ' ocurrencias' } },
            };
        },

        faultCodesSeries() {
            return [{
                name: 'Ocurrencias',
                data: this.faultCodes.map(f => ({
                    x: f.code,
                    y: f.count || 0,
                })),
            }];
        },

        /* Chart: Carga por Tecnico (Stacked) */
        techWorkloadOptions() {
            return {
                chart: { type: 'bar', stacked: true, toolbar: { show: false } },
                plotOptions: { bar: { columnWidth: '50%', borderRadius: 4 } },
                xaxis: {
                    categories: this.techWorkload.map(t => t.technician_name),
                    labels: { style: { fontSize: '11px', colors: '#64748b' } },
                },
                yaxis: {
                    labels: { style: { fontSize: '11px', colors: '#64748b' } },
                    forceNiceScale: true,
                },
                legend: { position: 'top', fontSize: '12px', labels: { colors: '#475569' } },
                colors: ['#30ab0a', '#ba2831', '#d97706'],
                dataLabels: { enabled: false },
                grid: { borderColor: '#f1f5f9' },
                tooltip: { y: { formatter: (val) => val + ' reportes' } },
            };
        },

        techWorkloadSeries() {
            return [
                { name: 'RSTP', data: this.techWorkload.map(t => t.rstp_count || 0) },
                { name: 'RSTC', data: this.techWorkload.map(t => t.rstc_count || 0) },
                { name: 'RSTE', data: this.techWorkload.map(t => t.rste_count || 0) },
            ];
        },
    },

    watch: {
        filters: {
            deep: true,
            handler() {
                clearTimeout(this._filterTimer);
                this._filterTimer = setTimeout(() => this.loadAllData(), 400);
            },
        },
    },

    async mounted() {
        await Promise.all([
            this.loadDropdowns(),
            this.loadAllData(),
        ]);

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
        async loadDropdowns() {
            try {
                const clientsRes = await clientService.all();
                this.clients = clientsRes.data?.data || clientsRes.data || [];

                if (this.isAdmin) {
                    const usersRes = await adminService.getUsers({ role: 'technician' });
                    this.technicians = usersRes.data?.data || usersRes.data || [];
                }
            } catch (e) {
                console.error('Error cargando dropdowns:', e);
            }
        },

        async loadAllData() {
            this.loading = true;
            this.chartsReady = false;
            try {
                const params = this.activeFilters;
                const [
                    overviewRes,
                    reportsByMonthRes,
                    topFaultsRes,
                    faultCodesRes,
                    techWorkloadRes,
                    complianceRes,
                ] = await Promise.all([
                    statsService.overview(params),
                    statsService.reportsByMonth(params),
                    statsService.topEquipmentFaults(params),
                    statsService.faultCodesFrequency(params),
                    statsService.technicianWorkload(params),
                    statsService.maintenanceCompliance(params),
                ]);

                this.stats = overviewRes.data?.data || overviewRes.data || {};
                this.reportsByMonth = reportsByMonthRes.data?.data || reportsByMonthRes.data || [];
                this.topFaults = topFaultsRes.data?.data || topFaultsRes.data || [];
                this.faultCodes = faultCodesRes.data?.data || faultCodesRes.data || [];
                this.techWorkload = techWorkloadRes.data?.data || techWorkloadRes.data || [];
                this.complianceData = complianceRes.data?.data || complianceRes.data || [];
            } catch (e) {
                console.error('Error cargando datos del dashboard:', e);
            } finally {
                this.loading = false;
                this.$nextTick(() => { this.chartsReady = true; });
            }
        },

        complianceTextClass(percent) {
            if (percent >= 80) return 'text-success';
            if (percent >= 50) return 'text-warning';
            return 'text-danger';
        },

        complianceBarClass(percent) {
            if (percent >= 80) return 'bg-success';
            if (percent >= 50) return 'bg-warning';
            return 'bg-danger';
        },
    },
};
</script>

<style scoped>
/* Filter Bar */
.filter-bar {
    background: white;
    border-radius: 12px;
    padding: 1.25rem;
    border: 1px solid #e2e8f0;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

.filter-bar .form-label {
    font-size: 0.8rem;
    color: #606060;
    margin-bottom: 0.3rem;
}

.filter-bar .form-control,
.filter-bar .form-select {
    border-color: #d1d5db;
    font-size: 0.9rem;
}

.filter-bar .form-control:focus,
.filter-bar .form-select:focus {
    border-color: #30ab0a;
    box-shadow: 0 0 0 0.2rem rgba(48, 171, 10, 0.15);
}

/* KPI Cards */
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
    border: 1px solid #e2e8f0;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    border-top: 3px solid transparent;
}

.stat-card.stat-green {
    border-top-color: #30ab0a;
}

.stat-card.stat-red {
    border-top-color: #ba2831;
}

.stat-card.stat-amber {
    border-top-color: #d97706;
}

.stat-card.stat-gray {
    border-top-color: #606060;
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    flex-shrink: 0;
}

.stat-green .stat-icon {
    background: #e8f5e4;
    color: #279208;
}

.stat-red .stat-icon {
    background: #fee2e2;
    color: #ba2831;
}

.stat-amber .stat-icon {
    background: #fef3c7;
    color: #d97706;
}

.stat-gray .stat-icon {
    background: #f3f4f6;
    color: #606060;
}

.stat-number {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1e293b;
    line-height: 1.2;
}

.stat-label {
    font-size: 0.8rem;
    color: #64748b;
    margin-top: 0.1rem;
}

/* Charts Grid */
.charts-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.25rem;
}

@media (max-width: 991.98px) {
    .charts-grid {
        grid-template-columns: 1fr;
    }
}

/* Section Card */
.section-card {
    background: white;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

.section-header {
    padding: 1rem 1.25rem;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-weight: 600;
    font-size: 0.95rem;
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
    font-size: 0.85rem;
}

.icon-green {
    background: #e8f5e4;
    color: #279208;
}

.icon-red {
    background: #fee2e2;
    color: #ba2831;
}

.icon-amber {
    background: #fef3c7;
    color: #d97706;
}

.section-body {
    padding: 1.25rem;
}

.empty-chart {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 200px;
    color: #94a3b8;
    font-size: 0.9rem;
}

/* Compliance Table */
.table th {
    background: #f8fafc;
    font-size: 0.8rem;
    text-transform: uppercase;
    color: #606060;
    font-weight: 600;
    letter-spacing: 0.03em;
    border-bottom: 2px solid #e2e8f0;
    padding: 0.85rem 0.75rem;
}

.table td {
    vertical-align: middle;
    padding: 0.85rem 0.75rem;
    font-size: 0.9rem;
    color: #334155;
}

.table tbody tr:hover {
    background: #f0fdf4;
}

.progress {
    border-radius: 6px;
    background-color: #e2e8f0;
}

.progress-bar {
    border-radius: 6px;
    transition: width 0.5s ease;
}

.progress-bar.bg-success {
    background-color: #30ab0a !important;
}

/* Loading */
.loading-state {
    text-align: center;
    padding: 4rem 0;
}
</style>
