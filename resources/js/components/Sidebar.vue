<template>
    <div class="app-sidebar" :class="{ 'collapsed': !isCollapsed }" @mouseover="hoverSidebar(true)" @mouseleave="hoverSidebar(false)">
        <div class="sidebar-inner">
            <!-- Logo Section -->
            <div class="sidebar-brand">
                <div class="brand-logo">
                    <svg class="logo-icon" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect width="40" height="40" rx="10" fill="url(#sidebar-logo-gradient)"/>
                        <path d="M12 20C12 15.58 15.58 12 20 12C24.42 12 28 15.58 28 20C28 24.42 24.42 28 20 28" stroke="white" stroke-width="2.5" stroke-linecap="round"/>
                        <circle cx="20" cy="20" r="4" fill="white"/>
                        <defs>
                            <linearGradient id="sidebar-logo-gradient" x1="0" y1="0" x2="40" y2="40">
                                <stop stop-color="#a855f7"/>
                                <stop offset="1" stop-color="#ec4899"/>
                            </linearGradient>
                        </defs>
                    </svg>
                    <span class="brand-text">NexosCard</span>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="sidebar-nav">
                <ul class="nav-menu">
                    <!-- Panel de Control (todos) / Panel Admin (Master) -->
                    <li class="nav-item" :class="{ 'active': isMaster ? $route.path === '/admin' : $route.path === '/' }">
                        <router-link :to="isMaster ? '/admin' : '/'" class="nav-link">
                            <span class="nav-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="3" width="7" height="7" rx="1"/>
                                    <rect x="14" y="3" width="7" height="7" rx="1"/>
                                    <rect x="3" y="14" width="7" height="7" rx="1"/>
                                    <rect x="14" y="14" width="7" height="7" rx="1"/>
                                </svg>
                            </span>
                            <span class="nav-text">{{ isMaster ? 'Panel Admin' : 'Panel de Control' }}</span>
                        </router-link>
                    </li>

                    <!-- Empresas -->
                    <li class="nav-item" :class="{ 'active': $route.path.startsWith('/empresas') || (isMaster && $route.path === '/admin/empresas') }">
                        <router-link :to="isMaster ? '/admin/empresas' : companyRoute" class="nav-link">
                            <span class="nav-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M3 21h18"/>
                                    <path d="M5 21V7l8-4v18"/>
                                    <path d="M19 21V11l-6-4"/>
                                    <path d="M9 9v.01M9 12v.01M9 15v.01M9 18v.01"/>
                                </svg>
                            </span>
                            <span class="nav-text">{{ isMaster ? 'Empresas' : 'Mi Empresa' }}</span>
                        </router-link>
                    </li>

                    <!-- Separador Admin -->
                    <li v-if="isMaster" class="nav-separator">
                        <span class="separator-text">Administracion</span>
                    </li>

                    <!-- Planes (solo Master) -->
                    <li v-if="isMaster" class="nav-item" :class="{ 'active': $route.path.startsWith('/admin/planes') }">
                        <router-link to="/admin/planes" class="nav-link">
                            <span class="nav-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z"/>
                                </svg>
                            </span>
                            <span class="nav-text">Planes</span>
                        </router-link>
                    </li>

                    <!-- Suscripciones (solo Master) -->
                    <li v-if="isMaster" class="nav-item" :class="{ 'active': $route.path.startsWith('/admin/suscripciones') }">
                        <router-link to="/admin/suscripciones" class="nav-link">
                            <span class="nav-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M9 12h6M9 16h6M17 21H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </span>
                            <span class="nav-text">Suscripciones</span>
                        </router-link>
                    </li>

                    <!-- Pagos (solo Master) -->
                    <li v-if="isMaster" class="nav-item" :class="{ 'active': $route.path.startsWith('/admin/pagos') }">
                        <router-link to="/admin/pagos" class="nav-link">
                            <span class="nav-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="1" y="4" width="22" height="16" rx="2"/>
                                    <line x1="1" y1="10" x2="23" y2="10"/>
                                </svg>
                            </span>
                            <span class="nav-text">Pagos</span>
                        </router-link>
                    </li>

                    <!-- Usuarios (solo Master) -->
                    <li v-if="isMaster" class="nav-item" :class="{ 'active': $route.path.startsWith('/admin/usuarios') }">
                        <router-link to="/admin/usuarios" class="nav-link">
                            <span class="nav-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
                                    <circle cx="9" cy="7" r="4"/>
                                    <path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>
                                </svg>
                            </span>
                            <span class="nav-text">Usuarios</span>
                        </router-link>
                    </li>

                    <!-- Mi Plan (solo Admin/Guest) -->
                    <li v-if="!isMaster" class="nav-item" :class="{ 'active': $route.path.startsWith('/planes') || $route.path.startsWith('/mi-suscripcion') }">
                        <router-link to="/mi-suscripcion" class="nav-link">
                            <span class="nav-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z"/>
                                </svg>
                            </span>
                            <span class="nav-text">Mi Plan</span>
                        </router-link>
                    </li>
                </ul>
            </nav>

            <!-- Decorative Elements -->
            <div class="sidebar-decoration">
                <div class="decoration-circle circle-1"></div>
                <div class="decoration-circle circle-2"></div>
            </div>
        </div>
    </div>
</template>

<script>
import { useAuth } from '@/stores/auth';

export default {
    props: {
        isCollapsed: {
            type: Boolean,
            required: true,
        },
        sidebarStatus: {
            type: Boolean,
            required: true,
        },
    },
    computed: {
        isMaster() {
            const auth = useAuth();
            return auth.isMaster();
        },
        companyRoute() {
            if (this.isMaster) return '/empresas';
            const auth = useAuth();
            const companyId = auth.state.user?.company_id;
            return companyId ? `/empresas/${companyId}` : '/empresas';
        },
    },
    methods: {
        hoverSidebar(status) {
            this.$emit("updateSidebar", status);
        },
    },
};
</script>

<style scoped>
.app-sidebar {
    width: 280px;
    min-width: 280px;
    height: 100%;
    background: linear-gradient(180deg, #1e1b4b 0%, #312e81 50%, #4c1d95 100%);
    position: relative;
    overflow: hidden;
    transition: all 0.3s ease;
    flex-shrink: 0;
    /* Override base.css */
    margin-top: 0 !important;
    padding-top: 0 !important;
    flex: none !important;
}

.app-sidebar.collapsed {
    width: 80px;
    min-width: 80px;
}

.app-sidebar.collapsed:hover {
    width: 280px;
    min-width: 280px;
}

.sidebar-inner {
    display: flex;
    flex-direction: column;
    height: 100%;
    position: relative;
    z-index: 1;
    width: 100%;
    padding: 0 !important;
}

/* Brand Section */
.sidebar-brand {
    padding: 1rem 1rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.brand-logo {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.logo-icon {
    width: 40px;
    height: 40px;
    flex-shrink: 0;
}

.brand-text {
    font-size: 1.25rem;
    font-weight: 700;
    color: white;
    white-space: nowrap;
    transition: opacity 0.2s;
}

.app-sidebar.collapsed .brand-text {
    opacity: 0;
    width: 0;
}

.app-sidebar.collapsed:hover .brand-text {
    opacity: 1;
    width: auto;
}

/* Navigation */
.sidebar-nav {
    flex: 1;
    padding: 0.75rem 0.5rem;
    overflow-y: auto;
}

.nav-menu {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

/* Separator */
.nav-separator {
    padding: 0.75rem 0.75rem 0.25rem;
    margin-top: 0.25rem;
}

.separator-text {
    font-size: 0.7rem;
    font-weight: 600;
    color: rgba(255, 255, 255, 0.35);
    text-transform: uppercase;
    letter-spacing: 0.08em;
}

.app-sidebar.collapsed .separator-text {
    opacity: 0;
}

.app-sidebar.collapsed:hover .separator-text {
    opacity: 1;
}

.nav-item {
    position: relative;
}

.nav-link {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 0.75rem;
    border-radius: 8px;
    color: rgba(255, 255, 255, 0.7);
    text-decoration: none;
    transition: all 0.2s ease;
    position: relative;
    overflow: hidden;
}

.nav-link::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, rgba(168, 85, 247, 0.4), rgba(236, 72, 153, 0.4));
    opacity: 0;
    transition: opacity 0.2s;
    border-radius: 10px;
}

.nav-link:hover {
    color: white;
}

.nav-link:hover::before {
    opacity: 1;
}

.nav-item.active .nav-link {
    color: white;
    background: linear-gradient(135deg, rgba(168, 85, 247, 0.5), rgba(236, 72, 153, 0.5));
    box-shadow: 0 4px 15px rgba(168, 85, 247, 0.3);
}

.nav-item.active .nav-link::before {
    opacity: 0;
}

.nav-icon {
    width: 22px;
    height: 22px;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    z-index: 1;
}

.nav-icon svg {
    width: 100%;
    height: 100%;
}

.nav-text {
    font-size: 0.95rem;
    font-weight: 500;
    white-space: nowrap;
    position: relative;
    z-index: 1;
    transition: opacity 0.2s;
}

/* Collapsed State */
.app-sidebar.collapsed .nav-link {
    justify-content: center;
    padding: 0.75rem;
}

.app-sidebar.collapsed .nav-text {
    opacity: 0;
    width: 0;
    overflow: hidden;
}

.app-sidebar.collapsed:hover .nav-link {
    justify-content: flex-start;
    padding: 0.75rem 0.75rem;
}

.app-sidebar.collapsed:hover .nav-text {
    opacity: 1;
    width: auto;
}

/* Decorative Elements */
.sidebar-decoration {
    position: absolute;
    inset: 0;
    pointer-events: none;
    overflow: hidden;
}

.decoration-circle {
    position: absolute;
    border-radius: 50%;
    background: linear-gradient(135deg, rgba(168, 85, 247, 0.15), rgba(236, 72, 153, 0.15));
}

.circle-1 {
    width: 200px;
    height: 200px;
    bottom: -50px;
    left: -50px;
}

.circle-2 {
    width: 150px;
    height: 150px;
    top: 30%;
    right: -60px;
}

/* Scrollbar */
.sidebar-nav::-webkit-scrollbar {
    width: 4px;
}

.sidebar-nav::-webkit-scrollbar-track {
    background: transparent;
}

.sidebar-nav::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.2);
    border-radius: 2px;
}

.sidebar-nav::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.3);
}
</style>
