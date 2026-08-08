<template>
    <div class="tech-layout">
        <!-- Header -->
        <header class="tech-header">
            <div class="tech-header__left">
                <img :src="'/images/logo/logo-atzion.svg'" alt="Tzion" class="tech-header__logo" />
            </div>
            <div class="tech-header__center">
                <span class="tech-header__name">{{ userName }}</span>
            </div>
            <div class="tech-header__right">
                <NotificationBell />
                <ConnectionStatus to="tech.pending" persistent />
                <button class="tech-header__logout" @click="handleLogout" title="Cerrar sesion">
                    <i class="fa fa-sign-out-alt"></i>
                </button>
            </div>
        </header>

        <!-- Content -->
        <main class="tech-content">
            <router-view />
        </main>

        <!-- Bottom Navigation -->
        <nav class="tech-bottom-nav">
            <router-link
                to="/tech"
                class="tech-bottom-nav__item"
                :class="{ 'is-active': $route.path === '/tech' }"
            >
                <i class="fa fa-home"></i>
                <span>Inicio</span>
            </router-link>
            <router-link
                to="/tech/firmas-pendientes"
                class="tech-bottom-nav__item"
                :class="{ 'is-active': $route.path.startsWith('/tech/firmas-pendientes') }"
            >
                <span class="nav-icon-wrap">
                    <i class="fa fa-file-signature"></i>
                    <span v-if="pendingSignatures > 0" class="nav-badge">{{ pendingSignatures }}</span>
                </span>
                <span>Firmas</span>
            </router-link>
            <router-link
                to="/tech/checkin"
                class="tech-bottom-nav__item tech-bottom-nav__item--checkin"
                :class="{ 'is-active': $route.path === '/tech/checkin' }"
            >
                <div class="checkin-btn-circle">
                    <i class="fa fa-qrcode"></i>
                </div>
                <span>Check-in</span>
            </router-link>
            <!-- Sync salió de aquí y vive en el header (icono con estado): la
                 agenda se consulta varias veces al día y la cola solo cuando algo
                 va mal. -->
            <router-link
                to="/tech/agenda"
                class="tech-bottom-nav__item"
                :class="{ 'is-active': $route.path.startsWith('/tech/agenda') }"
            >
                <i class="fa fa-calendar-week"></i>
                <span>Agenda</span>
            </router-link>
            <router-link
                to="/tech/perfil"
                class="tech-bottom-nav__item"
                :class="{ 'is-active': $route.path.startsWith('/tech/perfil') }"
            >
                <i class="fa fa-user"></i>
                <span>Perfil</span>
            </router-link>
        </nav>
    </div>
</template>

<script>
import { useAuth } from '@/stores/auth';
import ConnectionStatus from '@/components/shared/ConnectionStatus.vue';
import NotificationBell from '@/components/shared/NotificationBell.vue';
import offlineManager from '@/utils/offlineManager.js';
import { loadPendingVisits } from '@/services/visitService.js';
import { warmCatalogs } from '@/utils/catalogCache.js';

export default {
    name: 'TechLayout',
    components: { ConnectionStatus, NotificationBell },
    data() {
        return {
            pendingSignatures: 0,
        };
    },
    computed: {
        userName() {
            const auth = useAuth();
            return auth.state.user?.name || 'Técnico';
        },
        offlineState() {
            return offlineManager.state;
        },
    },
    watch: {
        // Al navegar (p. ej. al terminar un reporte diferido) refrescar el contador
        $route: 'loadPendingSignatures',
        // Un ciclo de sync puede vaciar la cola de firmas
        'offlineState.lastSyncAt': 'loadPendingSignatures',
    },
    mounted() {
        this.loadPendingSignatures();
        window.addEventListener('tzion-visits-changed', this.onVisitsChanged);

        // Guardar las listas de revisión mientras hay cobertura: para cuando el
        // técnico llegue al sótano ya están, aunque nunca haya abierto un reporte
        // de ese tipo.
        warmCatalogs();
    },
    beforeUnmount() {
        window.removeEventListener('tzion-visits-changed', this.onVisitsChanged);
    },
    methods: {
        onVisitsChanged(e) {
            this.pendingSignatures = e.detail?.count ?? this.pendingSignatures;
        },
        async loadPendingSignatures() {
            try {
                // Cuenta servidor + cola local (offline las visitas viven en IndexedDB)
                const { visits } = await loadPendingVisits();
                this.pendingSignatures = visits.length;
            } catch {}
        },
        async handleLogout() {
            await useAuth().logout();
            this.$router.push('/login');
        },
    },
};
</script>

<style scoped>
.tech-layout {
    display: flex;
    flex-direction: column;
    height: 100vh;
    background: #f1f4f6;
}

/* Header */
.tech-header {
    height: auto;
    min-height: calc(56px + env(safe-area-inset-top, 0px));
    background: white;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: env(safe-area-inset-top, 0px) 1rem 0;
    flex-shrink: 0;
    position: sticky;
    top: 0;
    z-index: 100;
}

.tech-header__left {
    flex: 0 0 auto;
}

.tech-header__logo {
    height: 36px;
    width: auto;
}

.tech-header__center {
    flex: 1;
    /* min-width: 0 para que el nombre pueda encogerse: sin conexión la píldora de
       estado ocupa más y el nombre se partía en tres líneas. */
    min-width: 0;
    text-align: center;
}

.tech-header__name {
    display: block;
    font-size: 0.9rem;
    font-weight: 600;
    color: #1e293b;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.tech-header__right {
    flex: 0 0 auto;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.tech-header__logout {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: none;
    border: none;
    border-radius: 8px;
    color: #94a3b8;
    font-size: 1rem;
    cursor: pointer;
    transition: all 0.2s;
}

.tech-header__logout:hover {
    background: #fef2f2;
    color: #ba2831;
}

/* Content */
.tech-content {
    flex: 1;
    overflow-y: auto;
    -webkit-overflow-scrolling: touch;
    padding-bottom: env(safe-area-inset-bottom, 0);
}

/* Bottom Navigation */
.tech-bottom-nav {
    height: 64px;
    background: white;
    border-top: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: space-around;
    flex-shrink: 0;
    padding-bottom: env(safe-area-inset-bottom, 0);
    position: sticky;
    bottom: 0;
    z-index: 100;
}

.tech-bottom-nav__item {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 2px;
    text-decoration: none;
    color: #94a3b8;
    font-size: 0.7rem;
    font-weight: 500;
    padding: 6px 8px;
    border-radius: 12px;
    transition: all 0.2s;
    min-width: 56px;
}

.tech-bottom-nav__item i {
    font-size: 1.25rem;
}

.tech-bottom-nav__item.is-active {
    color: #30ab0a;
}

.tech-bottom-nav__item--checkin {
    position: relative;
}

/* Badges de la barra inferior */
.nav-icon-wrap {
    position: relative;
    display: inline-flex;
}

.nav-badge {
    position: absolute;
    top: -6px;
    right: -10px;
    min-width: 16px;
    height: 16px;
    padding: 0 4px;
    border-radius: 999px;
    background: #ba2831;
    color: white;
    font-size: 0.62rem;
    font-weight: 700;
    line-height: 16px;
    text-align: center;
}

.checkin-btn-circle {
    width: 48px;
    height: 48px;
    background: linear-gradient(135deg, #30ab0a, #279208);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.25rem;
    box-shadow: 0 4px 12px rgba(48, 171, 10, 0.35);
    margin-top: -20px;
    transition: transform 0.2s, box-shadow 0.2s;
}

.tech-bottom-nav__item--checkin.is-active .checkin-btn-circle {
    transform: scale(1.08);
    box-shadow: 0 6px 20px rgba(48, 171, 10, 0.45);
}

.tech-bottom-nav__item--checkin span {
    margin-top: 2px;
}

/* Desktop: contenido centrado */
@media (min-width: 769px) {
    .tech-content {
        max-width: 640px;
        margin: 0 auto;
        width: 100%;
    }

    .tech-bottom-nav {
        display: none;
    }

    .tech-header {
        height: 64px;
    }
}
</style>
