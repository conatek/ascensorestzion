<template>
    <div class="tech-dashboard">
        <!-- Saludo -->
        <div class="dash-greeting">
            <h1>Hola, {{ firstName }}</h1>
            <p>{{ todayFormatted }}</p>
        </div>

        <!-- Boton principal check-in -->
        <router-link to="/tech/checkin" class="dash-checkin-btn">
            <div class="dash-checkin-btn__icon">
                <i class="fa fa-qrcode"></i>
            </div>
            <div class="dash-checkin-btn__text">
                <span class="dash-checkin-btn__title">Registrar Llegada</span>
                <span class="dash-checkin-btn__hint">Escanear QR o código manual</span>
            </div>
            <i class="fa fa-arrow-right dash-checkin-btn__arrow"></i>
        </router-link>

        <!-- Pendientes de sync -->
        <div v-if="pendingSyncCount > 0" class="dash-pending-sync">
            <i class="fa fa-cloud-upload-alt"></i>
            <span>{{ pendingSyncCount }} reporte{{ pendingSyncCount > 1 ? 's' : '' }} pendiente{{ pendingSyncCount > 1 ? 's' : '' }} de sincronizar</span>
        </div>

        <!-- Stats del dia -->
        <div class="dash-stats">
            <div class="dash-stat">
                <span class="dash-stat__value">{{ todayCheckins }}</span>
                <span class="dash-stat__label">Check-ins hoy</span>
            </div>
            <div class="dash-stat">
                <span class="dash-stat__value">{{ draftReports }}</span>
                <span class="dash-stat__label">Borradores</span>
            </div>
            <div class="dash-stat">
                <span class="dash-stat__value">{{ monthReports }}</span>
                <span class="dash-stat__label">Este mes</span>
            </div>
        </div>

        <!-- Reportes recientes -->
        <div class="dash-section">
            <h2 class="dash-section__title">Reportes recientes</h2>

            <div v-if="loadingReports" class="dash-loading">
                <i class="fa fa-spinner fa-spin"></i>
            </div>

            <div v-else-if="recentReports.length === 0" class="dash-empty">
                <p>No hay reportes recientes</p>
            </div>

            <div v-else class="dash-report-list">
                <router-link
                    v-for="report in recentReports"
                    :key="report.id"
                    :to="{ name: 'reports.show', params: { id: report.id } }"
                    class="dash-report-item"
                >
                    <div class="dash-report-item__type" :class="'type-' + report.report_type">
                        {{ report.report_type }}
                    </div>
                    <div class="dash-report-item__info">
                        <span class="dash-report-item__number">{{ report.report_number }}</span>
                        <span class="dash-report-item__detail">
                            {{ report.equipment?.internal_code }} — {{ formatDate(report.service_date) }}
                        </span>
                    </div>
                    <div class="dash-report-item__status" :class="'status-' + report.status">
                        {{ statusLabel(report.status) }}
                    </div>
                </router-link>
            </div>
        </div>
    </div>
</template>

<script>
import { useAuth } from '@/stores/auth';
import reportService from '@/services/reportService.js';
import checkinService from '@/services/checkinService.js';
import offlineManager from '@/utils/offlineManager.js';

export default {
    name: 'TechDashboard',

    data() {
        return {
            recentReports: [],
            loadingReports: true,
            todayCheckins: 0,
            draftReports: 0,
            monthReports: 0,
            pendingSyncCount: 0,
        };
    },

    computed: {
        firstName() {
            const auth = useAuth();
            const name = auth.state.user?.name || 'Técnico';
            return name.split(' ')[0];
        },
        todayFormatted() {
            return new Date().toLocaleDateString('es-CO', {
                weekday: 'long',
                day: 'numeric',
                month: 'long',
            });
        },
    },

    async created() {
        await this.loadData();
    },

    methods: {
        async loadData() {
            this.loadingReports = true;
            this.pendingSyncCount = offlineManager.state.pendingCount;

            try {
                const [reportsRes, checkinsRes] = await Promise.all([
                    reportService.all({
                        per_page: 5,
                        sort: '-service_date',
                    }),
                    checkinService.index({
                        per_page: 50,
                    }),
                ]);

                const reports = reportsRes.data?.data || reportsRes.data || [];
                this.recentReports = reports.slice(0, 5);
                this.draftReports = reports.filter(r => r.status === 'borrador').length;

                // Contar reportes del mes actual
                const now = new Date();
                const currentMonth = now.getMonth();
                const currentYear = now.getFullYear();
                this.monthReports = reports.filter(r => {
                    const d = new Date(r.service_date);
                    return d.getMonth() === currentMonth && d.getFullYear() === currentYear;
                }).length;

                // Contar check-ins de hoy
                const checkins = checkinsRes.data?.data || checkinsRes.data || [];
                const today = now.toISOString().split('T')[0];
                this.todayCheckins = checkins.filter(c => {
                    return c.checked_in_at && c.checked_in_at.startsWith(today);
                }).length;
            } catch {
                this.recentReports = [];
            } finally {
                this.loadingReports = false;
            }
        },

        formatDate(dateStr) {
            if (!dateStr) return '';
            return new Date(dateStr).toLocaleDateString('es-CO', {
                day: 'numeric',
                month: 'short',
            });
        },

        statusLabel(status) {
            const labels = {
                borrador: 'Borrador',
                firmado_tecnico: 'Firmado',
                firmado_cliente: 'Completo',
                cerrado: 'Cerrado',
                anulado: 'Anulado',
            };
            return labels[status] || status;
        },
    },
};
</script>

<style scoped>
.tech-dashboard {
    padding: 1rem;
}

/* Greeting */
.dash-greeting {
    margin-bottom: 1.25rem;
}

.dash-greeting h1 {
    font-size: 1.4rem;
    font-weight: 700;
    color: #1e293b;
    margin: 0;
}

.dash-greeting p {
    font-size: 0.85rem;
    color: #64748b;
    margin: 0.15rem 0 0;
    text-transform: capitalize;
}

/* Boton check-in */
.dash-checkin-btn {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem 1.25rem;
    background: linear-gradient(135deg, #30ab0a, #279208);
    border-radius: 16px;
    text-decoration: none;
    color: white;
    margin-bottom: 1rem;
    box-shadow: 0 4px 16px rgba(48, 171, 10, 0.3);
    transition: transform 0.2s;
    min-height: 72px;
}

.dash-checkin-btn:active {
    transform: scale(0.98);
}

.dash-checkin-btn__icon {
    width: 48px;
    height: 48px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
    flex-shrink: 0;
}

.dash-checkin-btn__text {
    flex: 1;
}

.dash-checkin-btn__title {
    display: block;
    font-size: 1rem;
    font-weight: 700;
}

.dash-checkin-btn__hint {
    display: block;
    font-size: 0.75rem;
    opacity: 0.85;
}

.dash-checkin-btn__arrow {
    opacity: 0.7;
}

/* Pending sync */
.dash-pending-sync {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.6rem 1rem;
    background: #fffbeb;
    border: 1px solid #fde68a;
    border-radius: 10px;
    color: #d97706;
    font-size: 0.8rem;
    font-weight: 500;
    margin-bottom: 1rem;
}

/* Stats */
.dash-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.75rem;
    margin-bottom: 1.5rem;
}

.dash-stat {
    background: white;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    padding: 0.75rem;
    text-align: center;
}

.dash-stat__value {
    display: block;
    font-size: 1.5rem;
    font-weight: 700;
    color: #1e293b;
    line-height: 1;
}

.dash-stat__label {
    display: block;
    font-size: 0.7rem;
    color: #94a3b8;
    margin-top: 0.2rem;
}

/* Section */
.dash-section__title {
    font-size: 1rem;
    font-weight: 600;
    color: #1e293b;
    margin: 0 0 0.75rem;
}

.dash-loading {
    text-align: center;
    padding: 2rem;
    color: #94a3b8;
}

.dash-empty {
    text-align: center;
    padding: 2rem;
    color: #94a3b8;
    font-size: 0.9rem;
}

/* Report list */
.dash-report-list {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.dash-report-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem;
    background: white;
    border-radius: 10px;
    border: 1px solid #e2e8f0;
    text-decoration: none;
    transition: background 0.15s;
    min-height: 56px;
}

.dash-report-item:active {
    background: #f8fafc;
}

.dash-report-item__type {
    font-size: 0.7rem;
    font-weight: 700;
    padding: 0.2rem 0.5rem;
    border-radius: 6px;
    flex-shrink: 0;
}

.type-RSTP { background: #e8f5e4; color: #30ab0a; }
.type-RSTC { background: #fef2f2; color: #ba2831; }
.type-RSTE { background: #fffbeb; color: #d97706; }

.dash-report-item__info {
    flex: 1;
    min-width: 0;
}

.dash-report-item__number {
    display: block;
    font-size: 0.85rem;
    font-weight: 600;
    color: #1e293b;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.dash-report-item__detail {
    display: block;
    font-size: 0.75rem;
    color: #94a3b8;
}

.dash-report-item__status {
    font-size: 0.7rem;
    font-weight: 600;
    padding: 0.2rem 0.5rem;
    border-radius: 6px;
    flex-shrink: 0;
}

.status-borrador { background: #f1f5f9; color: #64748b; }
.status-firmado_tecnico { background: #dbeafe; color: #2563eb; }
.status-firmado_cliente { background: #e8f5e4; color: #279208; }
.status-cerrado { background: #e8f5e4; color: #279208; }
.status-anulado { background: #fef2f2; color: #ba2831; }
</style>
