<template>
    <header class="app-header">
        <div class="header-left">
            <!-- Mobile Menu Button (solo visible en móvil) -->
            <button @click="openSidebarMobile()" type="button" class="mobile-toggle" :class="{ 'active': isOpenSidebarMobile }">
                <span class="toggle-bar"></span>
                <span class="toggle-bar"></span>
                <span class="toggle-bar"></span>
            </button>

            <!-- Logo -->
            <img :src="'/images/logo/logo-atzion.svg'" alt="Ascensores Tzion" class="header-logo-img" />
        </div>

        <div class="header-right">
            <!-- Notifications -->
            <NotificationBell />

            <!-- Connection Status -->
            <ConnectionStatus />

            <!-- User Menu -->
            <div class="user-menu" ref="userMenu">
                <button class="user-trigger" @click="toggleUserMenu">
                    <div class="user-avatar">
                        <img v-if="userAvatar" :src="userAvatar" alt="Avatar" class="user-avatar-img" />
                        <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <circle cx="12" cy="8" r="4"/>
                            <path d="M4 20c0-4 4-6 8-6s8 2 8 6"/>
                        </svg>
                    </div>
                    <div class="user-info">
                        <span class="user-name">{{ userName }}</span>
                        <span class="user-email">{{ userEmail }}</span>
                    </div>
                    <svg class="dropdown-arrow" :class="{ 'open': isUserMenuOpen }" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </button>

                <!-- Dropdown Menu -->
                <Transition name="dropdown">
                    <div v-if="isUserMenuOpen" class="user-dropdown">
                        <div class="dropdown-header">
                            <div class="dropdown-avatar">
                                <img v-if="userAvatar" :src="userAvatar" alt="Avatar" class="dropdown-avatar-img" />
                                <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <circle cx="12" cy="8" r="4"/>
                                    <path d="M4 20c0-4 4-6 8-6s8 2 8 6"/>
                                </svg>
                            </div>
                            <div class="dropdown-user-info">
                                <span class="dropdown-name">{{ userName }}</span>
                                <span class="dropdown-email">{{ userEmail }}</span>
                                <span class="role-badge" :class="roleBadgeClass">{{ userRole }}</span>
                            </div>
                        </div>

                        <div class="dropdown-divider"></div>

                        <div class="dropdown-body">
                            <router-link :to="{ name: 'profile' }" class="dropdown-item" @click="isUserMenuOpen = false">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="8" r="4"/>
                                    <path d="M4 20c0-4 4-6 8-6s8 2 8 6"/>
                                </svg>
                                <span>Mi Perfil</span>
                            </router-link>
                        </div>

                        <div class="dropdown-divider"></div>

                        <div class="dropdown-footer">
                            <button @click="handleLogout" class="logout-btn">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/>
                                    <polyline points="16,17 21,12 16,7"/>
                                    <line x1="21" y1="12" x2="9" y2="12"/>
                                </svg>
                                <span>Cerrar Sesion</span>
                            </button>
                        </div>
                    </div>
                </Transition>
            </div>
        </div>
    </header>
</template>

<script>
import { useAuth } from '@/stores/auth';
import ConnectionStatus from '@/components/shared/ConnectionStatus.vue';
import NotificationBell from '@/components/shared/NotificationBell.vue';

export default {
    components: { ConnectionStatus, NotificationBell },
    props: {
        isCollapsed: {
            type: Boolean,
            required: true,
        },
        isOpenSidebarMobile: {
            type: Boolean,
            required: false,
        },
    },
    data() {
        return {
            sidebarStatus: false,
            isUserMenuOpen: false,
        };
    },
    computed: {
        userName() {
            const auth = useAuth();
            return auth.state.user?.name || 'Usuario';
        },
        userAvatar() {
            return useAuth().state.user?.image_url || null;
        },
        userEmail() {
            const auth = useAuth();
            return auth.state.user?.email || '';
        },
        userRole() {
            const auth = useAuth();
            const roles = auth.state.user?.roles || [];
            const role = roles.length > 0 ? roles[0].name : '';
            const labels = { master: 'Master', coordinator: 'Coordinador', technician: 'Técnico', admin: 'Admin Cliente' };
            return labels[role] || role;
        },
        roleBadgeClass() {
            const auth = useAuth();
            const roles = auth.state.user?.roles || [];
            const role = roles.length > 0 ? roles[0].name : '';
            if (role === 'master') return 'role-badge-master';
            if (role === 'coordinator') return 'role-badge-admin';
            if (role === 'technician') return 'role-badge-admin';
            return 'role-badge-guest';
        },
    },
    mounted() {
        document.addEventListener('click', this.handleClickOutside);
    },
    beforeUnmount() {
        document.removeEventListener('click', this.handleClickOutside);
    },
    methods: {
        changeSidebarStatus() {
            this.sidebarStatus = !this.sidebarStatus;
            this.$emit('changeSidebarStatus');
        },
        openRightDrawer() {
            this.$emit('openRightDrawer');
        },
        openSidebarMobile() {
            this.$emit('openSidebarMobile');
        },
        toggleUserMenu() {
            this.isUserMenuOpen = !this.isUserMenuOpen;
        },
        handleClickOutside(event) {
            if (this.$refs.userMenu && !this.$refs.userMenu.contains(event.target)) {
                this.isUserMenuOpen = false;
            }
        },
        async handleLogout() {
            this.isUserMenuOpen = false;
            const auth = useAuth();
            await auth.logout();
            this.$router.push({ name: 'landing' });
        },
    },
};
</script>

<style scoped>
.app-header {
    height: auto;
    min-height: calc(70px + env(safe-area-inset-top, 0px));
    background: white;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: env(safe-area-inset-top, 0px) 1.5rem 0;
    position: sticky;
    top: 0;
    z-index: 100;
}

/* Header Left */
.header-left {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.header-logo-img {
    height: 60px;
    width: auto;
}

.sidebar-toggle,
.mobile-toggle {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    cursor: pointer;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 4px;
    padding: 8px;
    transition: all 0.2s;
}

.sidebar-toggle:hover,
.mobile-toggle:hover {
    background: #f1f5f9;
    border-color: #cbd5e1;
}

.toggle-bar {
    width: 18px;
    height: 2px;
    background: #64748b;
    border-radius: 1px;
    transition: all 0.3s ease;
}

.sidebar-toggle.active .toggle-bar:nth-child(1) {
    transform: translateY(6px) rotate(45deg);
}

.sidebar-toggle.active .toggle-bar:nth-child(2) {
    opacity: 0;
}

.sidebar-toggle.active .toggle-bar:nth-child(3) {
    transform: translateY(-6px) rotate(-45deg);
}

.mobile-toggle {
    display: none;
}

/* Header Right */
.header-right {
    display: flex;
    align-items: center;
    gap: 1rem;
}

/* User Menu */
.user-menu {
    position: relative;
}

.user-trigger {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.5rem 0.75rem;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.2s;
}

.user-trigger:hover {
    background: #f1f5f9;
    border-color: #cbd5e1;
}

.user-avatar {
    width: 36px;
    height: 36px;
    background: linear-gradient(135deg, #30ab0a, #606060);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    overflow: hidden;
    flex-shrink: 0;
}

.user-avatar svg {
    width: 20px;
    height: 20px;
}

.user-avatar-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.user-info {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    text-align: left;
}

.user-name {
    font-size: 0.875rem;
    font-weight: 600;
    color: #1e293b;
    line-height: 1.2;
}

.user-email {
    font-size: 0.75rem;
    color: #64748b;
    line-height: 1.2;
}

.dropdown-arrow {
    width: 16px;
    height: 16px;
    color: #94a3b8;
    transition: transform 0.2s;
    margin-left: 0.25rem;
}

.dropdown-arrow.open {
    transform: rotate(180deg);
}

/* Dropdown */
.user-dropdown {
    position: absolute;
    top: calc(100% + 8px);
    right: 0;
    width: 280px;
    background: white;
    border-radius: 16px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.12), 0 2px 10px rgba(0, 0, 0, 0.08);
    border: 1px solid #e2e8f0;
    overflow: hidden;
    z-index: 1000;
}

.dropdown-header {
    display: flex;
    align-items: center;
    gap: 0.875rem;
    padding: 1.25rem;
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
}

.dropdown-avatar {
    width: 48px;
    height: 48px;
    background: linear-gradient(135deg, #30ab0a, #606060);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    flex-shrink: 0;
    overflow: hidden;
}

.dropdown-avatar svg {
    width: 26px;
    height: 26px;
}

.dropdown-avatar-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.dropdown-user-info {
    display: flex;
    flex-direction: column;
    min-width: 0;
}

.dropdown-name {
    font-size: 0.95rem;
    font-weight: 600;
    color: #1e293b;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.dropdown-email {
    font-size: 0.8rem;
    color: #64748b;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Role Badge */
.role-badge {
    display: inline-block;
    font-size: 0.7rem;
    font-weight: 600;
    padding: 0.15rem 0.5rem;
    border-radius: 6px;
    margin-top: 0.25rem;
    letter-spacing: 0.02em;
    width: fit-content;
}

.role-badge-master {
    background: linear-gradient(135deg, #e8f5e4, #d4edda);
    color: #279208;
    border: 1px solid #b7dfb2;
}

.role-badge-admin {
    background: linear-gradient(135deg, #e0f2fe, #dbeafe);
    color: #2563eb;
    border: 1px solid #bfdbfe;
}

.role-badge-guest {
    background: linear-gradient(135deg, #f1f5f9, #e2e8f0);
    color: #64748b;
    border: 1px solid #cbd5e1;
}

.dropdown-divider {
    height: 1px;
    background: #e2e8f0;
}

.dropdown-body {
    padding: 0.5rem;
}

.dropdown-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    color: #475569;
    text-decoration: none;
    border-radius: 10px;
    transition: all 0.2s;
}

.dropdown-item:hover {
    background: #f8fafc;
    color: #30ab0a;
}

.dropdown-item svg {
    width: 18px;
    height: 18px;
    flex-shrink: 0;
}

.dropdown-item span {
    font-size: 0.9rem;
    font-weight: 500;
}

.dropdown-footer {
    padding: 0.75rem;
}

.logout-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    width: 100%;
    padding: 0.75rem;
    background: linear-gradient(135deg, #fef2f2, #fee2e2);
    border: 1px solid #fecaca;
    border-radius: 10px;
    color: #ba2831;
    font-size: 0.9rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.logout-btn:hover {
    background: linear-gradient(135deg, #fee2e2, #fecaca);
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.15);
}

.logout-btn svg {
    width: 18px;
    height: 18px;
}

/* Dropdown Animation */
.dropdown-enter-active,
.dropdown-leave-active {
    transition: all 0.2s ease;
}

.dropdown-enter-from,
.dropdown-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}

/* Responsive */
@media (max-width: 768px) {
    .sidebar-toggle {
        display: none;
    }

    .mobile-toggle {
        display: flex;
    }

    .user-info {
        display: none;
    }

    .user-trigger {
        padding: 0.5rem;
    }

    .dropdown-arrow {
        display: none;
    }
}
</style>
