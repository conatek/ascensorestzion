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
                <ConnectionStatus />
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
                to="/tech/checkin"
                class="tech-bottom-nav__item tech-bottom-nav__item--checkin"
                :class="{ 'is-active': $route.path === '/tech/checkin' }"
            >
                <div class="checkin-btn-circle">
                    <i class="fa fa-qrcode"></i>
                </div>
                <span>Check-in</span>
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

export default {
    name: 'TechLayout',
    components: { ConnectionStatus, NotificationBell },
    computed: {
        userName() {
            const auth = useAuth();
            return auth.state.user?.name || 'Técnico';
        },
    },
    methods: {
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
    height: 56px;
    background: white;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 1rem;
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
    text-align: center;
}

.tech-header__name {
    font-size: 0.9rem;
    font-weight: 600;
    color: #1e293b;
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
    padding: 6px 16px;
    border-radius: 12px;
    transition: all 0.2s;
    min-width: 64px;
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
