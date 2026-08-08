<template>
    <!-- Vistas públicas de tarjetas: sin layout de admin, siempre bare -->
    <router-view v-if="isPublicLayout" />

    <!-- Portal Cliente: layout con sidebar -->
    <div v-else-if="isPortalLayout && isAuthenticated" class="portal-container">
        <!-- Impersonation Banner -->
        <div v-if="isImpersonating" class="impersonate-banner">
            <div class="impersonate-banner-content">
                <i class="fa fa-user-secret"></i>
                <span>Estas viendo como <strong>{{ portalClientName }}</strong></span>
                <button class="impersonate-return-btn" @click="handleStopImpersonating">
                    <i class="fa fa-arrow-left me-1"></i> Volver a mi sesion
                </button>
            </div>
        </div>

        <!-- Portal Header -->
        <header class="portal-top-header">
            <div class="portal-header-left">
                <button type="button" class="portal-mobile-toggle" @click="portalMobileOpen = !portalMobileOpen">
                    <span class="toggle-bar"></span>
                    <span class="toggle-bar"></span>
                    <span class="toggle-bar"></span>
                </button>
                <img :src="'/images/logo/logo-atzion.svg'" alt="Ascensores Tzion" class="portal-header-logo" />
            </div>
            <div class="portal-header-right">
                <div class="portal-user-info">
                    <span class="portal-client-name">{{ portalClientName }}</span>
                    <span class="portal-user-name">{{ portalUserName }}</span>
                </div>
                <button class="portal-logout-btn" @click="handlePortalLogout">
                    <i class="fa fa-sign-out-alt"></i> Salir
                </button>
            </div>
        </header>

        <!-- Portal Body: sidebar + content -->
        <div class="portal-body" :class="{ 'portal-mobile-open': portalMobileOpen }">
            <!-- Portal Sidebar -->
            <aside class="portal-sidebar" :class="{ 'portal-sidebar-collapsed': portalCollapsed }">
                <div class="portal-sidebar-inner">
                    <!-- Brand -->
                    <div class="portal-brand">
                        <span class="portal-brand-text" v-show="!portalCollapsed">Portal Cliente</span>
                        <button type="button" class="portal-collapse-btn" @click="portalCollapsed = !portalCollapsed">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                                <polyline :points="portalCollapsed ? '9 18 15 12 9 6' : '15 18 9 12 15 6'"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Nav -->
                    <nav class="portal-sidebar-nav">
                        <router-link to="/portal" class="portal-sb-link" :class="{ active: $route.path === '/portal' }" @click="portalMobileOpen = false">
                            <span class="portal-sb-icon"><i class="fa fa-th-large"></i></span>
                            <span class="portal-sb-text">Dashboard</span>
                        </router-link>
                        <router-link to="/portal/equipos" class="portal-sb-link" :class="{ active: $route.path.startsWith('/portal/equipos') }" @click="portalMobileOpen = false">
                            <span class="portal-sb-icon"><i class="fa fa-building"></i></span>
                            <span class="portal-sb-text">Mis Equipos</span>
                        </router-link>
                        <router-link to="/portal/reportes" class="portal-sb-link" :class="{ active: $route.path.startsWith('/portal/reportes') }" @click="portalMobileOpen = false">
                            <span class="portal-sb-icon"><i class="fa fa-file-alt"></i></span>
                            <span class="portal-sb-text">Mis Reportes</span>
                        </router-link>
                        <router-link to="/portal/cronograma" class="portal-sb-link" :class="{ active: $route.path.startsWith('/portal/cronograma') }" @click="portalMobileOpen = false">
                            <span class="portal-sb-icon"><i class="fa fa-calendar-alt"></i></span>
                            <span class="portal-sb-text">Cronograma</span>
                        </router-link>
                    </nav>

                    <!-- Decorative -->
                    <div class="portal-sb-decoration">
                        <div class="portal-sb-circle portal-sb-circle-1"></div>
                        <div class="portal-sb-circle portal-sb-circle-2"></div>
                    </div>
                </div>
            </aside>

            <!-- Portal Main -->
            <div class="portal-main-area">
                <div class="portal-main-inner">
                    <router-view />
                </div>
                <footer class="portal-bottom-footer">
                    <img :src="'/images/logo/logo-atzion.svg'" alt="Ascensores Tzion" style="height: 22px; width: auto; opacity: 0.5;" />
            <span style="margin-left: 8px;">&copy; {{ new Date().getFullYear() }}</span>
                </footer>
            </div>
        </div>

        <!-- Navegación inferior (solo móvil) -->
        <role-bottom-nav />
    </div>

    <!-- Layout técnico: mobile-first, sin sidebar -->
    <tech-layout v-else-if="isTechLayout && isAuthenticated" />

    <!-- Layout con sidebar/header para usuarios autenticados -->
    <div v-else-if="isAuthenticated" class="app-container app-theme-dark"
        :class="{
            'admin-collapsed': !isCollapsed,
            'sidebar-mobile-open': isOpenSidebarMobile
        }">
        <main-header
            :isCollapsed="isCollapsed"
            :sidebarStatus="sidebarStatus"
            :isOpenSidebarMobile="isOpenSidebarMobile"
            @changeSidebarStatus="sidebarVisualization"
            @openRightDrawer="openRightDrawer"
            @openSidebarMobile="openSidebarMobile"
        />

        <div class="ui-theme-settings" :class="{ 'settings-open': isOpenRightSettings }">
            <button @click="openRightSettings()" type="button" id="TooltipDemo" class="btn-open-options btn" style="background-color: #ba2831">
                <i class="fa fa-cog fa-w-16 fa-spin fa-2x" style="color: #ffffff;"></i>
            </button>
            <div class="theme-settings__inner">
                <div class="scrollbar-container">
                    <div class="quick-panel">
                        <!-- Header -->
                        <div class="quick-panel-header">
                            <div class="quick-panel-user">
                                <div class="quick-panel-avatar">
                                    <img v-if="authUser?.image_url" :src="authUser.image_url" alt="Avatar" class="quick-panel-avatar-img" />
                                    <i v-else class="fa fa-user"></i>
                                </div>
                                <div>
                                    <div class="quick-panel-name">{{ authUser?.name }}</div>
                                    <div class="quick-panel-role">{{ currentRoleLabel }}</div>
                                </div>
                            </div>
                            <button class="quick-panel-close" @click="openRightSettings()">
                                <i class="fa fa-times"></i>
                            </button>
                        </div>

                        <!-- Loading -->
                        <div v-if="quickPanelLoading" class="quick-panel-loading">
                            <div class="spinner-border spinner-border-sm text-success"></div>
                            <span>Cargando...</span>
                        </div>

                        <template v-else>
                            <!-- KPIs -->
                            <div class="quick-panel-section">
                                <div class="quick-panel-section-title">Resumen del Mes</div>
                                <div class="qp-stats-grid">
                                    <div class="qp-stat-card qp-stat-green">
                                        <div class="qp-stat-value">{{ quickStats.active_equipment || 0 }}</div>
                                        <div class="qp-stat-label">Equipos Activos</div>
                                    </div>
                                    <div class="qp-stat-card qp-stat-blue">
                                        <div class="qp-stat-value">{{ quickStats.reports_this_month || 0 }}</div>
                                        <div class="qp-stat-label">Reportes del Mes</div>
                                    </div>
                                    <div class="qp-stat-card" :class="quickStats.maintenance_compliance_percent >= 80 ? 'qp-stat-green' : quickStats.maintenance_compliance_percent >= 50 ? 'qp-stat-amber' : 'qp-stat-red'">
                                        <div class="qp-stat-value">{{ quickStats.maintenance_compliance_percent || 0 }}%</div>
                                        <div class="qp-stat-label">Cumplimiento</div>
                                    </div>
                                    <div class="qp-stat-card qp-stat-red">
                                        <div class="qp-stat-value">{{ quickStats.rstc_open || 0 }}</div>
                                        <div class="qp-stat-label">RSTC Abiertos</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Mini grafico de reportes por mes -->
                            <div v-if="quickReportsByMonth.length" class="quick-panel-section">
                                <div class="quick-panel-section-title">Reportes Ultimos 6 Meses</div>
                                <apexchart
                                    v-if="quickPanelChartsReady"
                                    type="area"
                                    height="160"
                                    :options="quickChartOptions"
                                    :series="quickChartSeries"
                                />
                            </div>

                            <!-- Indicadores extra Master/Coordinator -->
                            <div v-if="authIsMaster || authIsSuper || authIsCoordinator" class="quick-panel-section">
                                <div class="quick-panel-section-title">Indicadores</div>
                                <div class="qp-indicator">
                                    <span class="qp-indicator-label">Total Reportes</span>
                                    <span class="qp-indicator-value">{{ quickStats.total_reports || 0 }}</span>
                                </div>
                                <div class="qp-indicator">
                                    <span class="qp-indicator-label">RSTC Cerrados</span>
                                    <span class="qp-indicator-value">{{ quickStats.rstc_closed || 0 }}</span>
                                </div>
                                <div class="qp-indicator">
                                    <span class="qp-indicator-label">Tiempo Resp. RSTC</span>
                                    <span class="qp-indicator-value">{{ quickStats.avg_response_time_minutes != null ? quickStats.avg_response_time_minutes + ' min' : '-' }}</span>
                                </div>
                            </div>

                            <!-- Info de sesion -->
                            <div class="quick-panel-section">
                                <div class="quick-panel-section-title">Sesion</div>
                                <div class="quick-panel-info">
                                    <i class="fa fa-envelope"></i> {{ authUser?.email }}
                                </div>
                                <div v-if="authUser?.company?.name" class="quick-panel-info">
                                    <i class="fa fa-building"></i> {{ authUser.company.name }}
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <div class="app-main">
            <main-sidebar :isCollapsed="isCollapsed" :sidebarStatus="sidebarStatus" @updateSidebar="toggleSidebar" @changeSidebarStatus="sidebarVisualization" />

            <!-- Backdrop para cerrar sidebar en mobile -->
            <div v-if="isOpenSidebarMobile" class="sidebar-mobile-backdrop" @click="isOpenSidebarMobile = false"></div>

            <div class="app-main__outer">

                <div class="app-main__inner">
                    <router-view />
                </div>

                <main-footer />
            </div>
        </div>

        <!-- Navegación inferior (solo móvil) -->
        <role-bottom-nav />

        <div class="app-drawer-wrapper" :class="{ 'drawer-open': isOpenRightDrawer }">
            <div class="drawer-nav-btn">
                <button type="button" @click="closeRightDrawer()" class="hamburger hamburger--elastic is-active">
                    <span class="hamburger-box">
                        <span class="hamburger-inner"></span>
                    </span>
                </button>
            </div>
            <div class="drawer-content-wrapper">

            </div>
        </div>

        <div @click="closeRightDrawer()" class="app-drawer-overlay animated fadeIn" :class="{ 'd-none': !isOpenRightDrawer }"></div>
    </div>

    <!-- Sin layout para páginas de auth (login/register) -->
    <router-view v-else />
</template>

<script>
import { useAuth } from '@/stores/auth';
import statsService from '@/services/statsService.js';

export default {
    data() {
        return {
            isCollapsed: true,
            sidebarStatus: false,
            isOpenRightDrawer: false,
            isOpenRightSettings: false,
            isOpenSidebarMobile: false,
            portalMobileOpen: false,
            portalCollapsed: false,
            quickPanelLoading: false,
            quickPanelLoaded: false,
            quickPanelChartsReady: false,
            quickStats: {},
            quickReportsByMonth: [],
        };
    },
    computed: {
        isAuthenticated() {
            const auth = useAuth();
            return auth.isAuthenticated.value;
        },
        isPublicLayout() {
            return this.$route.meta?.layout === 'public';
        },
        isTechLayout() {
            return this.$route.meta?.layout === 'tech';
        },
        isPortalLayout() {
            if (this.$route.meta?.layout === 'portal') return true;
            const auth = useAuth();
            return auth.isAdmin() && !auth.isMaster();
        },
        portalUserName() {
            const auth = useAuth();
            return auth.state.user?.name || 'Usuario';
        },
        portalClientName() {
            const auth = useAuth();
            return auth.state.user?.client?.business_name || '';
        },
        isImpersonating() {
            const auth = useAuth();
            return auth.isImpersonating.value;
        },
        authUser() {
            const auth = useAuth();
            return auth.state.user;
        },
        authIsMaster() {
            return useAuth().isMaster();
        },
        authIsSuper() {
            return useAuth().isSuper();
        },
        authIsCoordinator() {
            return useAuth().isCoordinator();
        },
        authIsTechnician() {
            return useAuth().isTechnician();
        },
        currentRoleLabel() {
            const auth = useAuth();
            if (auth.isMaster()) return 'Master';
            if (auth.isSuper()) return 'Super';
            if (auth.isCoordinator()) return 'Coordinador';
            if (auth.isTechnician()) return 'Tecnico';
            if (auth.isAdmin()) return 'Administrador';
            return 'Usuario';
        },
        quickChartOptions() {
            return {
                chart: { type: 'area', sparkline: { enabled: true }, toolbar: { show: false } },
                stroke: { curve: 'smooth', width: 2 },
                fill: {
                    type: 'gradient',
                    gradient: { opacityFrom: 0.4, opacityTo: 0.05 },
                },
                colors: ['#30ab0a', '#ba2831', '#d97706'],
                xaxis: { categories: this.quickReportsByMonth.map(m => m.month) },
                tooltip: {
                    fixed: { enabled: false },
                    y: { formatter: (val) => val + ' reportes' },
                },
                legend: { show: true, position: 'top', fontSize: '11px', labels: { colors: '#64748b' } },
            };
        },
        quickChartSeries() {
            return [
                { name: 'RSTP', data: this.quickReportsByMonth.map(m => m.rstp || 0) },
                { name: 'RSTC', data: this.quickReportsByMonth.map(m => m.rstc || 0) },
                { name: 'RSTE', data: this.quickReportsByMonth.map(m => m.rste || 0) },
            ];
        },
    },
    watch: {
        '$route'() {
            this.isOpenSidebarMobile = false;
            this.portalMobileOpen = false;
        },
    },
    methods: {
        sidebarVisualization() {
            // En móvil, el botón del sidebar cierra el overlay (no colapsa a iconos)
            if (this.isOpenSidebarMobile || window.innerWidth <= 768) {
                this.isOpenSidebarMobile = false;
                return;
            }
            this.isCollapsed = !this.isCollapsed;
        },
        toggleSidebar(status) {
            this.sidebarStatus = status;
        },
        openRightDrawer() {
            this.isOpenRightDrawer = true;
        },
        closeRightDrawer() {
            this.isOpenRightDrawer = false;
        },
        openRightSettings() {
            this.isOpenRightSettings = !this.isOpenRightSettings;
            if (this.isOpenRightSettings && !this.quickPanelLoaded) {
                this.loadQuickPanel();
            }
        },
        async loadQuickPanel() {
            this.quickPanelLoading = true;
            this.quickPanelChartsReady = false;
            try {
                const [overviewRes, byMonthRes] = await Promise.all([
                    statsService.overview(),
                    statsService.reportsByMonth(),
                ]);
                this.quickStats = overviewRes.data?.data || overviewRes.data || {};
                this.quickReportsByMonth = byMonthRes.data?.data || byMonthRes.data || [];
                this.quickPanelLoaded = true;
            } catch {
                this.quickStats = {};
                this.quickReportsByMonth = [];
            } finally {
                this.quickPanelLoading = false;
                this.$nextTick(() => { this.quickPanelChartsReady = true; });
            }
        },
        openSidebarMobile() {
            this.isOpenSidebarMobile = !this.isOpenSidebarMobile;
        },
        async handlePortalLogout() {
            const auth = useAuth();
            await auth.logout();
            this.$router.push('/login');
        },
        async handleStopImpersonating() {
            const auth = useAuth();
            await auth.stopImpersonating();
            this.$router.push('/clientes');
        },
    },
};
</script>

<style>
/* ═══ Impersonation Banner ═══ */
.impersonate-banner {
    background: linear-gradient(135deg, #7c3aed, #6d28d9);
    color: white;
    padding: 0.5rem 1.5rem;
    flex-shrink: 0;
    z-index: 101;
}

.impersonate-banner-content {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.85rem;
    font-weight: 500;
}

.impersonate-return-btn {
    margin-left: auto;
    padding: 0.3rem 0.85rem;
    background: rgba(255, 255, 255, 0.2);
    border: 1px solid rgba(255, 255, 255, 0.3);
    border-radius: 6px;
    color: white;
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.impersonate-return-btn:hover {
    background: rgba(255, 255, 255, 0.35);
}

/* ═══ Quick Panel ═══ */
.quick-panel {
    padding: 0;
    height: 100vh;
    overflow-y: auto;
}

.quick-panel-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.25rem 1.5rem;
    background: linear-gradient(135deg, #30ab0a, #279208);
    color: white;
}

.quick-panel-user {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.quick-panel-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: rgba(255,255,255,0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    overflow: hidden;
    flex-shrink: 0;
}

.quick-panel-avatar-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.quick-panel-name {
    font-weight: 600;
    font-size: 0.95rem;
}

.quick-panel-role {
    font-size: 0.8rem;
    opacity: 0.85;
}

.quick-panel-close {
    background: rgba(255,255,255,0.15);
    border: none;
    color: white;
    width: 32px;
    height: 32px;
    border-radius: 8px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.2s;
}

.quick-panel-close:hover {
    background: rgba(255,255,255,0.3);
}

.quick-panel-section {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #f1f5f9;
}

.quick-panel-section-title {
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #94a3b8;
    margin-bottom: 0.5rem;
}

.quick-panel-info {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.4rem 0;
    font-size: 0.85rem;
    color: #64748b;
}

.quick-panel-info i {
    width: 18px;
    text-align: center;
    font-size: 0.8rem;
}

.quick-panel-loading {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 3rem 1rem;
    color: #64748b;
    font-size: 0.9rem;
}

/* Stat cards grid */
.qp-stats-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.75rem;
}

.qp-stat-card {
    border-radius: 10px;
    padding: 0.85rem 1rem;
    text-align: center;
}

.qp-stat-green {
    background: #e8f5e4;
}

.qp-stat-blue {
    background: #eff6ff;
}

.qp-stat-amber {
    background: #fef3c7;
}

.qp-stat-red {
    background: #fef2f2;
}

.qp-stat-value {
    font-size: 1.5rem;
    font-weight: 700;
    line-height: 1;
    color: #1e293b;
}

.qp-stat-green .qp-stat-value { color: #279208; }
.qp-stat-blue .qp-stat-value { color: #2563eb; }
.qp-stat-amber .qp-stat-value { color: #d97706; }
.qp-stat-red .qp-stat-value { color: #ba2831; }

.qp-stat-label {
    font-size: 0.7rem;
    color: #64748b;
    font-weight: 500;
    margin-top: 0.25rem;
}

/* Indicators */
.qp-indicator {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.5rem 0;
    border-bottom: 1px solid #f1f5f9;
    font-size: 0.85rem;
}

.qp-indicator:last-child {
    border-bottom: none;
}

.qp-indicator-label {
    color: #64748b;
}

.qp-indicator-value {
    font-weight: 600;
    color: #1e293b;
}

/* ═══ Portal Layout with Sidebar ═══ */
.portal-container {
    display: flex;
    flex-direction: column;
    height: 100vh;
    overflow: hidden;
}

.portal-top-header {
    height: auto;
    min-height: calc(70px + env(safe-area-inset-top, 0px));
    background: #fff;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: env(safe-area-inset-top, 0px) 1.5rem 0;
    position: sticky;
    top: 0;
    z-index: 100;
    flex-shrink: 0;
}

.portal-header-left { display: flex; align-items: center; gap: 1rem; }
.portal-header-logo { height: 60px; width: auto; }
.portal-header-right { display: flex; align-items: center; gap: 1.25rem; }
.portal-user-info { display: flex; flex-direction: column; align-items: flex-end; line-height: 1.2; }
.portal-client-name { font-size: 0.85rem; font-weight: 600; color: #1e293b; }
.portal-user-name { font-size: 0.75rem; color: #64748b; }

.portal-logout-btn {
    display: flex; align-items: center; gap: 0.4rem;
    padding: 0.45rem 0.85rem;
    background: linear-gradient(135deg, #fef2f2, #fee2e2);
    border: 1px solid #fecaca; border-radius: 8px;
    color: #ba2831; font-size: 0.85rem; font-weight: 600;
    cursor: pointer; transition: all 0.2s;
}
.portal-logout-btn:hover { background: linear-gradient(135deg, #fee2e2, #fecaca); }

.portal-mobile-toggle {
    width: 36px; height: 36px; border-radius: 8px;
    background: #f8fafc; border: 1px solid #e2e8f0;
    cursor: pointer; display: none; flex-direction: column;
    align-items: center; justify-content: center; gap: 4px; padding: 8px;
}
.portal-mobile-toggle .toggle-bar {
    width: 18px; height: 2px; background: #64748b; border-radius: 1px;
}

/* Body: sidebar + main */
.portal-body {
    flex: 1; display: flex; overflow: hidden;
}

/* Sidebar */
.portal-sidebar {
    width: 250px; min-width: 250px;
    background: linear-gradient(180deg, #1a2e14 0%, #1b5e10 50%, #227a0c 100%);
    position: relative; overflow: hidden; flex-shrink: 0;
    transition: width 0.3s ease, min-width 0.3s ease;
}

.portal-sidebar-collapsed {
    width: 68px !important; min-width: 68px !important;
}
.portal-sidebar-collapsed .portal-sb-text { display: none; }
.portal-sidebar-collapsed .portal-brand { justify-content: center; }
.portal-sidebar-collapsed .portal-sb-link { justify-content: center; padding: 0.75rem; }

.portal-collapse-btn {
    width: 28px; height: 28px; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.2);
    border-radius: 6px; color: rgba(255,255,255,0.7); cursor: pointer; transition: all 0.2s;
}
.portal-collapse-btn:hover { background: rgba(255,255,255,0.25); color: white; }
.portal-collapse-btn svg { width: 16px; height: 16px; }

.portal-sidebar-inner {
    display: flex; flex-direction: column; height: 100%;
    position: relative; z-index: 1;
}

.portal-brand {
    padding: 0 0.75rem; border-bottom: 1px solid rgba(255,255,255,0.1);
    display: flex; align-items: center; justify-content: space-between;
    height: 70px; flex-shrink: 0; gap: 0.5rem;
}
.portal-sidebar-collapsed .portal-brand { justify-content: center; }
.portal-brand-logo { height: 60px; width: auto; flex-shrink: 0; filter: brightness(0) invert(1); }
.portal-brand-text { font-size: 1.1rem; font-weight: 700; color: #fff; white-space: nowrap; }

.portal-sidebar-nav {
    flex: 1; padding: 0.75rem 0.5rem; overflow-y: auto;
    display: flex; flex-direction: column; gap: 0.25rem;
}

.portal-sb-link {
    display: flex; align-items: center; gap: 0.75rem;
    padding: 0.75rem; border-radius: 8px;
    color: rgba(255,255,255,0.7); text-decoration: none;
    transition: all 0.2s ease; position: relative; overflow: hidden;
}
.portal-sb-link:hover { color: #fff; background: rgba(48,171,10,0.3); }
.portal-sb-link.active {
    color: #fff;
    background: linear-gradient(135deg, rgba(48,171,10,0.5), rgba(39,146,8,0.5));
    box-shadow: 0 4px 15px rgba(48,171,10,0.3);
}

.portal-sb-icon { width: 20px; text-align: center; flex-shrink: 0; }
.portal-sb-text { font-size: 0.95rem; font-weight: 500; white-space: nowrap; }

.portal-sb-decoration { position: absolute; inset: 0; pointer-events: none; overflow: hidden; }
.portal-sb-circle {
    position: absolute; border-radius: 50%;
    background: linear-gradient(135deg, rgba(48,171,10,0.15), rgba(39,146,8,0.15));
}
.portal-sb-circle-1 { width: 180px; height: 180px; bottom: -40px; left: -40px; }
.portal-sb-circle-2 { width: 120px; height: 120px; top: 40%; right: -50px; }

/* Main area */
.portal-main-area {
    flex: 1; display: flex; flex-direction: column; overflow: hidden;
    background: #f1f4f6;
}
.portal-main-inner {
    flex: 1; padding: 1rem 1.25rem; overflow-y: auto;
}

.portal-bottom-footer {
    height: 40px; display: flex; align-items: center; justify-content: center;
    background: #fff; border-top: 1px solid #e2e8f0;
    font-size: 0.8rem; color: #94a3b8; flex-shrink: 0;
}

/* Mobile */
@media (max-width: 768px) {
    .portal-user-info { display: none; }

    /* En móvil el bottom nav reemplaza la barra lateral del portal */
    .portal-mobile-toggle { display: none; }
    .portal-sidebar { display: none; }
    .portal-bottom-footer { display: none; }

    .portal-main-inner {
        padding: 0.75rem;
        padding-bottom: calc(72px + env(safe-area-inset-bottom, 0));
    }
}

/* ═══ Admin Layout ═══ */
.app-container {
    display: flex;
    flex-direction: column;
    height: 100vh;
    overflow: hidden;
}

.app-main {
    flex: 1;
    display: flex;
    overflow: hidden;
    padding-top: 0 !important;
}

.app-sidebar {
    margin-top: 0 !important;
    padding-top: 0 !important;
    flex: none !important;
}

.app-sidebar .sidebar-inner,
.app-sidebar .app-sidebar__inner {
    padding: 0 !important;
}

.app-main__outer {
    flex: 1;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    background: #f1f4f6;
    padding-left: 0 !important;
}

.app-main .app-main__inner {
    padding: 1rem 1.25rem !important;
    flex: 1;
    overflow-y: auto;
}

.app-page-title {
    margin: 0 0 1rem 0 !important;
    padding: 0 !important;
}

@media (max-width: 768px) {
    .app-container {
        padding: 0;
    }

    .app-header .app-header__logo {
        order: 2;
        background: transparent !important;
        border: 0 !important;
    }

    .app-header .app-header__logo .header__pane {
        display: none;
    }

    .app-header__logo {
        padding: 0;
        height: 60px;
        width: 100%;
        display: flex;
        align-items: center;
        transition: width .2s;
    }

    .app-header__logo .logo-src {
        height: 30px;
        width: 100%;
        background: url("../../images/nexos-logo.png");
        background-size: contain;
        background-repeat: no-repeat;
    }

    .app-sidebar {
        position: fixed;
        top: 70px;
        left: 0;
        z-index: 1000;
        transform: translateX(-100%);
        transition: transform 0.3s ease-in-out !important;
    }

    .sidebar-mobile-open .app-sidebar {
        transform: translateX(0);
    }

    .app-main .app-main__inner {
        padding: 0.75rem !important;
        /* espacio para el bottom nav fijo (60px + safe-area) */
        padding-bottom: calc(72px + env(safe-area-inset-bottom, 0)) !important;
    }

    /* En móvil el bottom nav reemplaza al footer */
    .app-footer {
        display: none !important;
    }

    /* El engranaje flotante (panel rápido) estorba el bottom nav en móvil */
    .ui-theme-settings {
        display: none !important;
    }
}

@media (min-width: 769px) {
    .app-header__logo {
        padding: 0 1.5rem;
        height: 60px;
        width: 280px;
        display: flex;
        align-items: center;
        transition: width .2s;
    }

    .app-header__logo .logo-src {
        height: 50px;
        width: 100%;
        background: url("../../images/nexos-logo.png");
        background-size: contain;
        background-repeat: no-repeat;
    }
}

.app-footer {
    position: relative !important;
    width: 100% !important;
    background-color: #ffffff !important;
    flex-shrink: 0;
}

/* Sidebar mobile backdrop */
.sidebar-mobile-backdrop {
    display: none;
}

@media (max-width: 768px) {
    .sidebar-mobile-backdrop {
        display: block;
        position: fixed;
        top: calc(70px + env(safe-area-inset-top, 0px));
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.4);
        z-index: 999;
        animation: fadeIn 0.2s ease;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
}
</style>
