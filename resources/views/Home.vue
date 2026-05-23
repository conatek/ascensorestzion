<template>
    <div>
        <div class="app-page-title">
            <div class="page-title-wrapper">
                <div class="page-title-heading">
                    <div class="page-title-icon">
                        <i class="fa fa-chart-line icon-gradient bg-mean-fruit"></i>
                    </div>
                    <div>
                        Panel de Control
                        <div class="page-title-subheading text-muted">
                            Bienvenido a Ascensores Tzion
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mini-Dashboard para usuarios internos -->
        <div v-if="stats && !isGuest" class="dashboard-section">
            <div class="dash-stats-grid">
                <div class="dash-stat-card">
                    <div class="dash-stat-icon dash-icon-green">
                        <i class="fa fa-arrows-alt-v"></i>
                    </div>
                    <div class="dash-stat-info">
                        <span class="dash-stat-number">{{ stats.active_equipments ?? 0 }}</span>
                        <span class="dash-stat-label">Equipos Activos</span>
                    </div>
                </div>
                <div class="dash-stat-card">
                    <div class="dash-stat-icon dash-icon-blue">
                        <i class="fa fa-file-alt"></i>
                    </div>
                    <div class="dash-stat-info">
                        <span class="dash-stat-number">{{ stats.reports_this_month ?? 0 }}</span>
                        <span class="dash-stat-label">Reportes del Mes</span>
                    </div>
                </div>
                <div class="dash-stat-card">
                    <div class="dash-stat-icon dash-icon-orange">
                        <i class="fa fa-chart-pie"></i>
                    </div>
                    <div class="dash-stat-info">
                        <span class="dash-stat-number">{{ stats.maintenance_compliance_percent ?? 0 }}%</span>
                        <span class="dash-stat-label">Cumplimiento %</span>
                    </div>
                </div>
                <div class="dash-stat-card">
                    <div class="dash-stat-icon dash-icon-red">
                        <i class="fa fa-exclamation-triangle"></i>
                    </div>
                    <div class="dash-stat-info">
                        <span class="dash-stat-number">{{ stats.open_rstc ?? 0 }}</span>
                        <span class="dash-stat-label">RSTC Abiertos</span>
                    </div>
                </div>
            </div>

            <!-- Ultimos Reportes mini-tabla -->
            <div v-if="recentReports.length" class="dash-recent-card">
                <h3 class="dash-recent-title">
                    <i class="fa fa-clock me-2"></i>Ultimos Reportes
                </h3>
                <div class="table-responsive">
                    <table class="dash-recent-table">
                        <thead>
                            <tr>
                                <th>N° Reporte</th>
                                <th>Tipo</th>
                                <th>Fecha</th>
                                <th>Equipo</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="r in recentReports" :key="r.id">
                                <td class="fw-600">{{ r.report_number || '-' }}</td>
                                <td>
                                    <span class="dash-badge" :class="typeClass(r.report_type)">
                                        {{ r.report_type }}
                                    </span>
                                </td>
                                <td>{{ formatDate(r.service_date) }}</td>
                                <td>{{ r.equipment?.internal_code || '-' }}</td>
                                <td>
                                    <span class="dash-badge" :class="statusClass(r.status)">
                                        {{ statusLabel(r.status) }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Banner upgrade para Guest -->
        <div v-if="isGuest" class="upgrade-banner">
            <div class="banner-content">
                <div class="banner-text">
                    <i class="fa fa-info-circle me-2"></i>
                    <span>Estas en <strong>modo prueba</strong>. Mejora tu plan para desbloquear todas las funcionalidades.</span>
                </div>
                <router-link :to="{ name: 'home' }" class="banner-btn">
                    Contactar
                </router-link>
            </div>
        </div>

        <!-- Hero Section -->
        <div class="welcome-hero">
            <div class="hero-content">
                <div class="hero-text">
                    <h1 class="hero-title">
                        Gestion integral de
                        <span class="gradient-text">transporte vertical</span>
                    </h1>
                    <p class="hero-description">
                        Administra el mantenimiento, reparacion, modernizacion e instalacion
                        de ascensores y escaleras electricas desde un solo lugar.
                    </p>
                    <router-link :to="{ name: 'clients.index' }" class="btn-start">
                        <i class="fa fa-building me-2"></i>
                        Ir a Edificios
                    </router-link>
                </div>
                <div class="hero-illustration">
                    <!-- SVG Illustration -->
                    <svg viewBox="0 0 400 300" class="cards-illustration">
                        <!-- Background circles -->
                        <circle cx="200" cy="150" r="120" fill="url(#gradient1)" opacity="0.1">
                            <animate attributeName="r" values="120;130;120" dur="4s" repeatCount="indefinite"/>
                        </circle>
                        <circle cx="200" cy="150" r="80" fill="url(#gradient2)" opacity="0.15">
                            <animate attributeName="r" values="80;90;80" dur="3s" repeatCount="indefinite"/>
                        </circle>

                        <!-- Building -->
                        <g transform="translate(120, 40)">
                            <rect x="20" y="20" width="120" height="200" rx="6" fill="#e8f5e4" stroke="#30ab0a" stroke-width="2"/>
                            <!-- Windows -->
                            <rect x="35" y="40" width="25" height="20" rx="3" fill="#30ab0a" opacity="0.3"/>
                            <rect x="100" y="40" width="25" height="20" rx="3" fill="#30ab0a" opacity="0.3"/>
                            <rect x="35" y="75" width="25" height="20" rx="3" fill="#30ab0a" opacity="0.3"/>
                            <rect x="100" y="75" width="25" height="20" rx="3" fill="#30ab0a" opacity="0.3"/>
                            <rect x="35" y="110" width="25" height="20" rx="3" fill="#30ab0a" opacity="0.3"/>
                            <rect x="100" y="110" width="25" height="20" rx="3" fill="#30ab0a" opacity="0.3"/>
                            <rect x="35" y="145" width="25" height="20" rx="3" fill="#30ab0a" opacity="0.3"/>
                            <rect x="100" y="145" width="25" height="20" rx="3" fill="#30ab0a" opacity="0.3"/>
                            <!-- Elevator shaft -->
                            <rect x="65" y="35" width="30" height="180" rx="2" fill="#279208" opacity="0.15"/>
                            <!-- Elevator cab -->
                            <rect x="68" y="140" width="24" height="30" rx="2" fill="#30ab0a" opacity="0.9">
                                <animate attributeName="y" values="140;50;140" dur="4s" repeatCount="indefinite"/>
                            </rect>
                            <!-- Arrows -->
                            <path d="M80 25 L74 32 L86 32 Z" fill="#30ab0a"/>
                            <path d="M80 222 L74 215 L86 215 Z" fill="#30ab0a"/>
                            <!-- Door -->
                            <rect x="65" y="185" width="30" height="35" rx="3" fill="#606060" opacity="0.3"/>
                        </g>

                        <!-- Floating elements -->
                        <circle cx="320" cy="60" r="8" fill="#30ab0a" opacity="0.6">
                            <animate attributeName="cy" values="60;50;60" dur="2s" repeatCount="indefinite"/>
                        </circle>
                        <circle cx="80" cy="200" r="6" fill="#279208" opacity="0.6">
                            <animate attributeName="cy" values="200;210;200" dur="2.5s" repeatCount="indefinite"/>
                        </circle>
                        <circle cx="350" cy="180" r="10" fill="#606060" opacity="0.4">
                            <animate attributeName="cy" values="180;170;180" dur="3s" repeatCount="indefinite"/>
                        </circle>

                        <!-- Gradients definitions -->
                        <defs>
                            <linearGradient id="gradient1" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" style="stop-color:#30ab0a"/>
                                <stop offset="100%" style="stop-color:#279208"/>
                            </linearGradient>
                            <linearGradient id="gradient2" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" style="stop-color:#279208"/>
                                <stop offset="100%" style="stop-color:#30ab0a"/>
                            </linearGradient>
                        </defs>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Features Section -->
        <div class="features-section">
            <div class="feature-card">
                <div class="feature-icon" style="background: linear-gradient(135deg, #30ab0a, #279208);">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                        <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
                    </svg>
                </div>
                <h3>Mantenimiento Preventivo</h3>
                <p>Programas de mantenimiento regular para garantizar el optimo funcionamiento de sus equipos.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon" style="background: linear-gradient(135deg, #ba2831, #9e2229);">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    </svg>
                </div>
                <h3>Reparacion</h3>
                <p>Servicio tecnico especializado para la reparacion de ascensores y escaleras electricas.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon" style="background: linear-gradient(135deg, #279208, #1f7506);">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                        <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/>
                        <polyline points="17 6 23 6 23 12"/>
                    </svg>
                </div>
                <h3>Modernizacion</h3>
                <p>Actualizacion de equipos existentes con tecnologia de ultima generacion.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon" style="background: linear-gradient(135deg, #606060, #4a4a4a);">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                        <rect x="1" y="3" width="15" height="13"/>
                        <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/>
                        <circle cx="5.5" cy="18.5" r="2.5"/>
                        <circle cx="18.5" cy="18.5" r="2.5"/>
                    </svg>
                </div>
                <h3>Instalacion</h3>
                <p>Instalacion profesional de nuevos equipos de transporte vertical en edificios.</p>
            </div>
        </div>
    </div>
</template>

<script>
import { useAuth } from '@/stores/auth';
import statsService from '@/services/statsService.js';
import reportService from '@/services/reportService.js';

export default {
    name: 'Home',

    data() {
        return {
            stats: null,
            recentReports: [],
            statsLoading: true,
        };
    },

    computed: {
        isGuest() {
            const auth = useAuth();
            return auth.isAdmin();
        },
    },

    async created() {
        const auth = useAuth();
        if (auth.isInternal()) {
            try {
                const [statsRes, reportsRes] = await Promise.all([
                    statsService.overview(),
                    reportService.all({ limit: 5 })
                ]);
                this.stats = statsRes.data;
                this.recentReports = (reportsRes.data || []).slice(0, 5);
            } catch {} finally {
                this.statsLoading = false;
            }
        }
    },

    methods: {
        formatDate(dateStr) {
            if (!dateStr) return '-';
            const date = new Date(dateStr + 'T00:00:00');
            return date.toLocaleDateString('es-VE', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
            });
        },

        statusClass(status) {
            const map = {
                borrador: 'badge-secondary',
                firmado_tecnico: 'badge-warning',
                firmado_cliente: 'badge-info',
                cerrado: 'badge-success',
                anulado: 'badge-danger',
            };
            return map[status] || 'badge-secondary';
        },

        statusLabel(status) {
            const map = {
                borrador: 'Borrador',
                firmado_tecnico: 'Firmado Tec.',
                firmado_cliente: 'Firmado Cli.',
                cerrado: 'Cerrado',
                anulado: 'Anulado',
            };
            return map[status] || status;
        },

        typeClass(type) {
            const map = { RSTP: 'badge-rstp', RSTC: 'badge-rstc', RSTE: 'badge-rste' };
            return map[type] || 'badge-secondary';
        },
    },
}
</script>

<style scoped>
/* Upgrade Banner */
.upgrade-banner {
    background: linear-gradient(135deg, #e8f5e4, #d4edda);
    border: 1px solid #b7dfb2;
    border-radius: 12px;
    padding: 1rem 1.5rem;
    margin-bottom: 1.5rem;
}

.banner-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
}

.banner-text {
    font-size: 0.95rem;
    color: #1b5e10;
}

.banner-btn {
    white-space: nowrap;
    padding: 0.5rem 1.25rem;
    background: linear-gradient(135deg, #30ab0a, #279208);
    color: white;
    font-weight: 600;
    font-size: 0.85rem;
    border-radius: 8px;
    text-decoration: none;
    transition: all 0.2s;
}

.banner-btn:hover {
    transform: translateY(-1px);
    color: white;
    box-shadow: 0 4px 12px rgba(48, 171, 10, 0.3);
}

@media (max-width: 576px) {
    .banner-content {
        flex-direction: column;
        text-align: center;
    }
}

/* Dashboard Section */
.dashboard-section {
    margin-bottom: 2rem;
}

.dash-stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1.25rem;
    margin-bottom: 1.5rem;
}

.dash-stat-card {
    background: white;
    border-radius: 12px;
    padding: 1.25rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    border: 1px solid #e2e8f0;
}

.dash-stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    flex-shrink: 0;
}

.dash-icon-green { background: #e8f5e4; color: #30ab0a; }
.dash-icon-blue { background: #eff6ff; color: #2563eb; }
.dash-icon-orange { background: #fff7ed; color: #d97706; }
.dash-icon-red { background: #fef2f2; color: #ba2831; }

.dash-stat-info {
    display: flex;
    flex-direction: column;
}

.dash-stat-number {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1e293b;
    line-height: 1;
}

.dash-stat-label {
    font-size: 0.8rem;
    color: #64748b;
    font-weight: 500;
    margin-top: 0.25rem;
}

.dash-recent-card {
    background: white;
    border-radius: 12px;
    padding: 1.25rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    border: 1px solid #e2e8f0;
}

.dash-recent-title {
    font-size: 1rem;
    font-weight: 600;
    color: #1e293b;
    margin: 0 0 1rem;
}

.dash-recent-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.875rem;
}

.dash-recent-table thead th {
    background: #f8fafc;
    padding: 0.625rem 1rem;
    text-align: left;
    font-weight: 600;
    color: #64748b;
    border-bottom: 1px solid #e2e8f0;
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.03em;
}

.dash-recent-table tbody td {
    padding: 0.5rem 1rem;
    border-bottom: 1px solid #f1f5f9;
    color: #475569;
}

.dash-recent-table tbody tr:last-child td {
    border-bottom: none;
}

.fw-600 {
    font-weight: 600;
    color: #1e293b;
}

.dash-badge {
    display: inline-block;
    font-size: 0.7rem;
    font-weight: 600;
    padding: 0.2rem 0.5rem;
    border-radius: 6px;
    letter-spacing: 0.03em;
}

.badge-rstp { background: #e8f5e4; color: #30ab0a; }
.badge-rstc { background: #fef2f2; color: #ba2831; }
.badge-rste { background: #fff7ed; color: #d97706; }
.badge-secondary { background: #f1f5f9; color: #606060; }
.badge-warning { background: #fff7ed; color: #d97706; }
.badge-info { background: #eff6ff; color: #2563eb; }
.badge-success { background: #e8f5e4; color: #30ab0a; }
.badge-danger { background: #fef2f2; color: #ba2831; }

@media (max-width: 992px) {
    .dash-stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 576px) {
    .dash-stats-grid {
        grid-template-columns: 1fr;
    }
}

/* Hero Section */
.welcome-hero {
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border-radius: 20px;
    padding: 3rem;
    margin-bottom: 2rem;
    overflow: hidden;
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
    margin-bottom: 2rem;
}

.btn-start {
    display: inline-flex;
    align-items: center;
    padding: 0.875rem 2rem;
    background: linear-gradient(135deg, #30ab0a, #279208);
    color: white;
    font-weight: 600;
    border-radius: 12px;
    text-decoration: none;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(48, 171, 10, 0.4);
}

.btn-start:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(48, 171, 10, 0.5);
    color: white;
}

.hero-illustration {
    flex: 1;
    display: flex;
    justify-content: center;
}

.cards-illustration {
    width: 100%;
    max-width: 400px;
    height: auto;
}

/* Features Section */
.features-section {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
}

.feature-card {
    background: white;
    border-radius: 16px;
    padding: 1.75rem;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
    border: 1px solid #f1f5f9;
}

.feature-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
}

.feature-icon {
    width: 56px;
    height: 56px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1.25rem;
}

.feature-card h3 {
    font-size: 1.1rem;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 0.75rem;
}

.feature-card p {
    font-size: 0.95rem;
    color: #606060;
    line-height: 1.6;
    margin: 0;
}

/* Responsive */
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

    .cards-illustration {
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
