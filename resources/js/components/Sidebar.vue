<template>
    <div class="app-sidebar">
        <div class="sidebar-inner">
            <!-- Brand + Collapse toggle -->
            <div class="sidebar-brand">
                <span class="brand-text">Ascensores Tzion</span>
                <button type="button" class="collapse-btn" @click="toggleCollapse">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                        <polyline :points="isCollapsed ? '15 18 9 12 15 6' : '9 18 15 12 9 6'"/>
                    </svg>
                </button>
            </div>

            <!-- Navigation -->
            <nav class="sidebar-nav">
                <ul class="nav-menu">
                    <li class="nav-item" :class="{ 'active': isMaster ? $route.path === '/admin' : $route.path === '/' }">
                        <router-link :to="isMaster ? '/admin' : '/'" class="nav-link" :title="isMaster ? 'Panel Admin' : 'Panel de Control'">
                            <span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg></span>
                            <span class="nav-text">{{ isMaster ? 'Panel Admin' : 'Panel de Control' }}</span>
                        </router-link>
                    </li>
                    <li v-if="isInternal" class="nav-item" :class="{ 'active': $route.path.startsWith('/clientes') }">
                        <router-link to="/clientes" class="nav-link" title="Clientes">
                            <span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18"/><path d="M5 21V7l8-4v18"/><path d="M19 21V11l-6-4"/><path d="M9 9v.01M9 12v.01M9 15v.01M9 18v.01"/></svg></span>
                            <span class="nav-text">Clientes</span>
                        </router-link>
                    </li>
                    <li class="nav-item" :class="{ 'active': $route.path.startsWith('/equipos') }">
                        <router-link to="/equipos" class="nav-link" title="Equipos">
                            <span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="2" width="16" height="20" rx="2"/><line x1="12" y1="6" x2="12" y2="18"/><polyline points="8 10 12 6 16 10"/><polyline points="8 14 12 18 16 14"/></svg></span>
                            <span class="nav-text">{{ isAdmin ? 'Mis Equipos' : 'Equipos' }}</span>
                        </router-link>
                    </li>
                    <li class="nav-item" :class="{ 'active': $route.path.startsWith('/reportes') }">
                        <router-link to="/reportes" class="nav-link" title="Reportes">
                            <span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12h6M9 16h6M17 21H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg></span>
                            <span class="nav-text">{{ isAdmin ? 'Mis Reportes' : 'Reportes' }}</span>
                        </router-link>
                    </li>
                    <li v-if="isMaster" class="nav-separator"><span class="separator-text">Administración</span></li>
                    <li v-if="isMaster" class="nav-item" :class="{ 'active': $route.path.startsWith('/admin/usuarios') }">
                        <router-link to="/admin/usuarios" class="nav-link" title="Usuarios">
                            <span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg></span>
                            <span class="nav-text">Usuarios</span>
                        </router-link>
                    </li>
                    <li v-if="isMaster || isCoordinator" class="nav-separator"><span class="separator-text">Tarjetas Digitales</span></li>
                    <li v-if="isMaster || isCoordinator" class="nav-item" :class="{ 'active': $route.path.startsWith('/tarjetas') }">
                        <router-link to="/tarjetas" class="nav-link" title="Tarjetas Tzion">
                            <span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="9" cy="10" r="2"/><path d="M15 8h2M15 12h2M7 16h10"/></svg></span>
                            <span class="nav-text">Tarjetas Atzion</span>
                        </router-link>
                    </li>
                </ul>
            </nav>
            <div class="sidebar-decoration"><div class="decoration-circle circle-1"></div><div class="decoration-circle circle-2"></div></div>
        </div>
    </div>
</template>

<script>
import { useAuth } from '@/stores/auth';

export default {
    props: {
        isCollapsed: { type: Boolean, required: true },
        sidebarStatus: { type: Boolean, required: true },
    },
    computed: {
        isMaster() { return useAuth().isMaster(); },
        isCoordinator() { return useAuth().isCoordinator(); },
        isInternal() { return useAuth().isInternal(); },
        isAdmin() { return useAuth().isAdmin(); },
    },
    methods: {
        hoverSidebar(status) { this.$emit("updateSidebar", status); },
        toggleCollapse() { this.$emit("changeSidebarStatus"); },
    },
};
</script>

<style scoped>
.app-sidebar {
    height: 100%;
    background: linear-gradient(180deg, #1a2e14 0%, #1b5e10 50%, #227a0c 100%);
    position: relative;
    overflow: hidden;
    margin-top: 0 !important;
    padding-top: 0 !important;
}

.sidebar-inner {
    display: flex;
    flex-direction: column;
    height: 100%;
    position: relative;
    z-index: 1;
    padding: 0 !important;
}

.sidebar-brand {
    padding: 0 0.75rem;
    border-bottom: 1px solid rgba(255,255,255,0.1);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.5rem;
    height: 70px;
    flex-shrink: 0;
}

.brand-text {
    font-size: 1rem;
    font-weight: 700;
    color: white;
    white-space: nowrap;
    overflow: hidden;
}

.collapse-btn {
    width: 32px; height: 32px; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    background: rgba(255,255,255,0.15);
    border: 1px solid rgba(255,255,255,0.2);
    border-radius: 8px;
    color: rgba(255,255,255,0.7);
    cursor: pointer;
    transition: all 0.2s;
}
.collapse-btn:hover { background: rgba(255,255,255,0.25); color: white; }
.collapse-btn svg { width: 18px; height: 18px; }

.sidebar-nav { flex: 1; padding: 0.75rem 0.5rem; overflow-y: auto; overflow-x: hidden; }
.nav-menu { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 0.25rem; }
.nav-separator { padding: 0.75rem 0.75rem 0.25rem; margin-top: 0.25rem; overflow: hidden; }
.separator-text { font-size: 0.7rem; font-weight: 600; color: rgba(255,255,255,0.35); text-transform: uppercase; letter-spacing: 0.08em; white-space: nowrap; }
.nav-item { position: relative; }

.nav-link {
    display: flex; align-items: center; gap: 0.75rem;
    padding: 0.7rem 0.75rem; border-radius: 8px;
    color: rgba(255,255,255,0.7); text-decoration: none;
    transition: all 0.2s ease; position: relative; overflow: hidden;
    text-indent: 0 !important;
}
.nav-link::before {
    content: ''; position: absolute; inset: 0;
    background: linear-gradient(135deg, rgba(48,171,10,0.4), rgba(39,146,8,0.4));
    opacity: 0; transition: opacity 0.2s; border-radius: 8px;
}
.nav-link:hover { color: white; }
.nav-link:hover::before { opacity: 1; }
.nav-item.active .nav-link {
    color: white;
    background: linear-gradient(135deg, rgba(48,171,10,0.5), rgba(39,146,8,0.5));
    box-shadow: 0 4px 15px rgba(48,171,10,0.3);
}
.nav-item.active .nav-link::before { opacity: 0; }

.nav-icon { width: 22px; height: 22px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; position: relative; z-index: 1; }
.nav-icon svg { width: 100%; height: 100%; }
.nav-text { font-size: 0.9rem; font-weight: 500; white-space: nowrap; position: relative; z-index: 1; overflow: hidden; transition: opacity 0.25s ease; }

.sidebar-decoration { position: absolute; inset: 0; pointer-events: none; overflow: hidden; }
.decoration-circle { position: absolute; border-radius: 50%; background: linear-gradient(135deg, rgba(48,171,10,0.15), rgba(39,146,8,0.15)); }
.circle-1 { width: 200px; height: 200px; bottom: -50px; left: -50px; }
.circle-2 { width: 150px; height: 150px; top: 30%; right: -60px; }
.sidebar-nav::-webkit-scrollbar { width: 4px; }
.sidebar-nav::-webkit-scrollbar-track { background: transparent; }
.sidebar-nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); border-radius: 2px; }
</style>
