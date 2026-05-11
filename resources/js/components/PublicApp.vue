<template>
    <!-- Vistas públicas de tarjetas: sin layout de admin, siempre bare -->
    <router-view v-if="isPublicLayout" />

    <!-- Layout con sidebar/header para usuarios autenticados -->
    <div v-else-if="isAuthenticated" class="app-container app-theme-dark"
        :class="{
            'closed-sidebar': !isCollapsed,
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
            <button @click="openRightSettings()" type="button" id="TooltipDemo" class="btn-open-options btn" style="background-color: #e288f9">
                <i class="fa fa-cog fa-w-16 fa-spin fa-2x" style="color: black;"></i>
            </button>
            <div class="theme-settings__inner">
                <div class="scrollbar-container">
                </div>
            </div>
        </div>

        <div class="app-main">
            <main-sidebar :isCollapsed="isCollapsed" :sidebarStatus="sidebarStatus" @updateSidebar="toggleSidebar" />

            <div class="app-main__outer">

                <div class="app-main__inner">
                    <router-view />
                </div>

                <main-footer />
            </div>
        </div>

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

export default {
    data() {
        return {
            isCollapsed: true,
            sidebarStatus: false,
            isOpenRightDrawer: false,
            isOpenRightSettings: false,
            isOpenSidebarMobile: false,
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
    },
    methods: {
        sidebarVisualization() {
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
        },
        openSidebarMobile() {
            this.isOpenSidebarMobile = !this.isOpenSidebarMobile;
        },
    },
};
</script>

<style>
/* Layout base para el panel admin */
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

/* Corregir el margin/padding del sidebar que viene del base.css */
.app-sidebar {
    margin-top: 0 !important;
    padding-top: 0 !important;
    flex: none !important;
}

/* Evitar padding interno heredado de base.css */
.app-sidebar .sidebar-inner,
.app-sidebar .app-sidebar__inner {
    padding: 0 !important;
}

/* Contenedor del contenido principal - IMPORTANTE: quitar padding-left del base.css */
.app-main__outer {
    flex: 1;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    background: #f1f4f6;
    padding-left: 0 !important; /* Sobrescribir el padding-left: 280px del base.css */
}

/* Reducir padding del contenido */
.app-main .app-main__inner {
    padding: 1rem 1.25rem !important;
    flex: 1;
    overflow-y: auto;
}

/* Quitar margins negativos del page-title que pueden causar espacios */
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
        top: 60px;
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

/* Footer - dentro del flujo de app-main__outer */
.app-footer {
    position: relative !important;
    width: 100% !important;
    background-color: #ffffff !important;
    flex-shrink: 0;
}
</style>
