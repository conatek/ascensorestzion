<template>
    <div class="portal-layout">
        <!-- Impersonation Banner -->
        <div v-if="isImpersonating" class="impersonate-banner">
            <div class="impersonate-banner-content">
                <i class="fa fa-user-secret"></i>
                <span>Estas viendo como <strong>{{ clientName }}</strong></span>
                <button class="impersonate-return-btn" @click="handleStopImpersonating">
                    <i class="fa fa-arrow-left me-1"></i> Volver a mi sesion
                </button>
            </div>
        </div>

        <!-- Header -->
        <header class="portal-header">
            <div class="header-left">
                <div class="header-logo">
                    <svg class="logo-icon" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect width="40" height="40" rx="10" fill="url(#portal-logo-gradient)"/>
                        <path d="M20 8v24M14 14l6-6 6 6M14 26l6 6 6-6" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <defs>
                            <linearGradient id="portal-logo-gradient" x1="0" y1="0" x2="40" y2="40">
                                <stop stop-color="#30ab0a"/>
                                <stop offset="1" stop-color="#279208"/>
                            </linearGradient>
                        </defs>
                    </svg>
                    <span class="logo-text">Portal Cliente</span>
                </div>
            </div>
            <div class="header-right">
                <div class="header-user-info">
                    <span class="client-name">{{ clientName }}</span>
                    <span class="user-name">{{ userName }}</span>
                </div>
                <button class="logout-btn" @click="handleLogout">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/>
                        <polyline points="16,17 21,12 16,7"/>
                        <line x1="21" y1="12" x2="9" y2="12"/>
                    </svg>
                    <span>Salir</span>
                </button>
            </div>
        </header>

        <!-- Navigation -->
        <nav class="portal-nav">
            <div class="nav-inner">
                <router-link to="/portal" class="nav-item" :class="{ active: $route.path === '/portal' }">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="7" height="7" rx="1"/>
                        <rect x="14" y="3" width="7" height="7" rx="1"/>
                        <rect x="3" y="14" width="7" height="7" rx="1"/>
                        <rect x="14" y="14" width="7" height="7" rx="1"/>
                    </svg>
                    <span>Dashboard</span>
                </router-link>
                <router-link to="/portal/equipos" class="nav-item" :class="{ active: $route.path.startsWith('/portal/equipos') }">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="4" y="2" width="16" height="20" rx="2"/>
                        <line x1="12" y1="6" x2="12" y2="18"/>
                        <line x1="8" y1="10" x2="16" y2="10"/>
                    </svg>
                    <span>Mis Equipos</span>
                </router-link>
                <router-link to="/portal/reportes" class="nav-item" :class="{ active: $route.path.startsWith('/portal/reportes') }">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="5" y="2" width="14" height="20" rx="2"/>
                        <line x1="9" y1="7" x2="15" y2="7"/>
                        <line x1="9" y1="11" x2="15" y2="11"/>
                        <line x1="9" y1="15" x2="13" y2="15"/>
                    </svg>
                    <span>Mis Reportes</span>
                </router-link>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="portal-main">
            <slot></slot>
        </main>

        <!-- Footer -->
        <footer class="portal-footer">
            Ascensores Tzion &copy; 2026
        </footer>
    </div>
</template>

<script>
import { useAuth } from '@/stores/auth';

export default {
    name: 'ClientPortalLayout',

    data() {
        return {
            auth: useAuth(),
        };
    },

    computed: {
        userName() {
            return this.auth.state.user?.name || 'Usuario';
        },
        clientName() {
            return this.auth.state.user?.client?.business_name || '';
        },
        isImpersonating() {
            return this.auth.isImpersonating.value;
        },
    },

    methods: {
        async handleLogout() {
            await this.auth.logout();
            this.$router.push('/login');
        },
        async handleStopImpersonating() {
            await this.auth.stopImpersonating();
            this.$router.push('/clientes');
        },
    },
};
</script>

<style scoped>
/* Impersonation Banner */
.impersonate-banner {
    background: linear-gradient(135deg, #7c3aed, #6d28d9);
    color: white;
    padding: 0.5rem 1.5rem;
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

.portal-layout {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    font-family: 'Poppins', sans-serif;
}

/* Header */
.portal-header {
    height: 60px;
    background: white;
    border-left: 4px solid #30ab0a;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 1.5rem;
    position: sticky;
    top: 0;
    z-index: 100;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
}

.header-left {
    display: flex;
    align-items: center;
}

.header-logo {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.logo-icon {
    width: 36px;
    height: 36px;
}

.logo-text {
    font-size: 1.1rem;
    font-weight: 700;
    color: #1e293b;
    letter-spacing: -0.01em;
}

.header-right {
    display: flex;
    align-items: center;
    gap: 1.25rem;
}

.header-user-info {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    line-height: 1.2;
}

.client-name {
    font-size: 0.85rem;
    font-weight: 600;
    color: #1e293b;
}

.user-name {
    font-size: 0.75rem;
    color: #64748b;
}

.logout-btn {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.45rem 0.85rem;
    background: linear-gradient(135deg, #fef2f2, #fee2e2);
    border: 1px solid #fecaca;
    border-radius: 8px;
    color: #ba2831;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.logout-btn:hover {
    background: linear-gradient(135deg, #fee2e2, #fecaca);
    box-shadow: 0 2px 8px rgba(186, 40, 49, 0.15);
}

.logout-btn svg {
    width: 16px;
    height: 16px;
}

/* Navigation */
.portal-nav {
    background: white;
    border-bottom: 1px solid #e2e8f0;
}

.nav-inner {
    display: flex;
    gap: 0;
    padding: 0 1.5rem;
}

.nav-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.875rem 1.25rem;
    color: #64748b;
    text-decoration: none;
    font-size: 0.9rem;
    font-weight: 500;
    border-bottom: 3px solid transparent;
    transition: all 0.2s;
}

.nav-item svg {
    width: 18px;
    height: 18px;
}

.nav-item:hover {
    color: #30ab0a;
    background: #f8faf7;
}

.nav-item.active {
    color: #30ab0a;
    border-bottom-color: #30ab0a;
    font-weight: 600;
}

/* Main */
.portal-main {
    flex: 1;
    padding: 1.5rem;
    background: #f8fafc;
}

/* Footer */
.portal-footer {
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: white;
    border-top: 1px solid #e2e8f0;
    font-size: 0.8rem;
    color: #94a3b8;
}

/* Responsive */
@media (max-width: 576px) {
    .portal-header {
        padding: 0 1rem;
    }

    .header-user-info {
        display: none;
    }

    .nav-inner {
        padding: 0 0.5rem;
    }

    .nav-item {
        padding: 0.75rem 0.75rem;
        font-size: 0.8rem;
    }

    .nav-item span {
        display: none;
    }

    .portal-main {
        padding: 1rem;
    }
}
</style>
