<template>
    <!-- Solo visible en móvil (ver media query). Navegación inferior por rol. -->
    <div>
        <nav class="role-bottom-nav">
            <router-link
                v-for="item in items"
                :key="item.to"
                :to="item.to"
                class="role-bottom-nav__item"
                :class="{ 'is-active': item.active() }"
            >
                <i :class="item.icon"></i>
                <span>{{ item.label }}</span>
            </router-link>

            <button
                v-if="moreItems.length"
                type="button"
                class="role-bottom-nav__item role-bottom-nav__more"
                :class="{ 'is-active': sheetOpen }"
                @click="sheetOpen = true"
            >
                <i class="fa fa-ellipsis-h"></i>
                <span>Más</span>
            </button>
        </nav>

        <!-- Hoja "Más" -->
        <div v-if="sheetOpen" class="role-sheet-overlay" @click.self="sheetOpen = false">
            <div class="role-sheet">
                <div class="role-sheet__handle"></div>
                <div class="role-sheet__title">Más opciones</div>

                <router-link
                    v-for="m in moreItems"
                    :key="m.to"
                    :to="m.to"
                    class="role-sheet__link"
                    @click="sheetOpen = false"
                >
                    <i :class="m.icon"></i>
                    <span>{{ m.label }}</span>
                </router-link>

                <button type="button" class="role-sheet__link role-sheet__logout" @click="logout">
                    <i class="fa fa-sign-out-alt"></i>
                    <span>Cerrar sesión</span>
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import { useAuth } from '@/stores/auth';

export default {
    name: 'RoleBottomNav',

    data() {
        return { sheetOpen: false };
    },

    computed: {
        auth() {
            return useAuth();
        },

        // Cliente externo (portal) vs personal interno (admin layout)
        isPortal() {
            return this.auth.isAdmin() && !this.auth.isMasterOrSuper();
        },

        items() {
            const path = this.$route.path;
            if (this.isPortal) {
                return [
                    { to: '/portal', icon: 'fa fa-home', label: 'Inicio', active: () => path === '/portal' },
                    { to: '/portal/equipos', icon: 'fa fa-building', label: 'Equipos', active: () => path.startsWith('/portal/equipos') },
                    { to: '/portal/reportes', icon: 'fa fa-file-alt', label: 'Reportes', active: () => path.startsWith('/portal/reportes') },
                    { to: '/portal/cronograma', icon: 'fa fa-calendar-alt', label: 'Agenda', active: () => path.startsWith('/portal/cronograma') },
                ];
            }
            const home = this.auth.isMasterOrSuper() ? '/admin' : '/';
            return [
                { to: home, icon: 'fa fa-home', label: 'Inicio', active: () => path === home },
                { to: '/clientes', icon: 'fa fa-building', label: 'Clientes', active: () => path.startsWith('/clientes') },
                { to: '/equipos', icon: 'fa fa-arrows-alt-v', label: 'Equipos', active: () => path.startsWith('/equipos') },
                { to: '/reportes', icon: 'fa fa-file-alt', label: 'Reportes', active: () => path.startsWith('/reportes') },
            ];
        },

        // Ítems secundarios (hoja "Más"), respetando el rol
        moreItems() {
            if (this.isPortal) {
                return [{ to: '/portal/perfil', icon: 'fa fa-user', label: 'Mi Perfil' }];
            }
            const out = [];
            if (this.auth.isMaster()) {
                out.push({ to: '/admin/usuarios', icon: 'fa fa-users', label: 'Usuarios' });
            }
            // Tarjetas: master, super, coordinator
            out.push({ to: '/tarjetas', icon: 'fa fa-id-card', label: 'Tarjetas' });
            out.push({ to: '/perfil', icon: 'fa fa-user', label: 'Mi Perfil' });
            return out;
        },
    },

    methods: {
        async logout() {
            this.sheetOpen = false;
            await this.auth.logout();
            this.$router.push('/login');
        },
    },
};
</script>

<style scoped>
.role-bottom-nav {
    display: none; /* visible solo en móvil */
}

/* Hoja "Más" */
.role-sheet-overlay {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.5);
    z-index: 1100;
    display: flex;
    align-items: flex-end;
    animation: rbn-fade 0.18s ease;
}

.role-sheet {
    width: 100%;
    background: #fff;
    border-radius: 16px 16px 0 0;
    padding: 0.75rem 1rem calc(1rem + env(safe-area-inset-bottom, 0));
    box-shadow: 0 -8px 24px rgba(0, 0, 0, 0.18);
    animation: rbn-slide 0.22s ease;
}

.role-sheet__handle {
    width: 40px;
    height: 4px;
    border-radius: 2px;
    background: #cbd5e1;
    margin: 0.25rem auto 0.75rem;
}

.role-sheet__title {
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #94a3b8;
    margin-bottom: 0.5rem;
}

.role-sheet__link {
    display: flex;
    align-items: center;
    gap: 0.85rem;
    width: 100%;
    padding: 0.9rem 0.5rem;
    border: none;
    background: none;
    text-align: left;
    font-size: 0.95rem;
    color: #1e293b;
    text-decoration: none;
    border-bottom: 1px solid #f1f5f9;
    cursor: pointer;
}

.role-sheet__link:last-child {
    border-bottom: none;
}

.role-sheet__link i {
    width: 22px;
    text-align: center;
    color: #279208;
    font-size: 1.05rem;
}

.role-sheet__logout {
    color: #ba2831;
    margin-top: 0.25rem;
}

.role-sheet__logout i {
    color: #ba2831;
}

@keyframes rbn-fade {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes rbn-slide {
    from { transform: translateY(100%); }
    to { transform: translateY(0); }
}

/* ───── Móvil: mostrar bottom nav ───── */
@media (max-width: 768px) {
    .role-bottom-nav {
        display: flex;
        align-items: center;
        justify-content: space-around;
        position: fixed;
        left: 0;
        right: 0;
        bottom: 0;
        height: 60px;
        background: #fff;
        border-top: 1px solid #e2e8f0;
        z-index: 1000;
        padding-bottom: env(safe-area-inset-bottom, 0);
        box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.06);
    }

    .role-bottom-nav__item {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 2px;
        flex: 1;
        min-width: 0;
        padding: 6px 2px;
        border: none;
        background: none;
        cursor: pointer;
        text-decoration: none;
        color: #94a3b8;
        font-size: 0.68rem;
        font-weight: 500;
    }

    .role-bottom-nav__item i {
        font-size: 1.2rem;
    }

    .role-bottom-nav__item span {
        max-width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .role-bottom-nav__item.is-active {
        color: #30ab0a;
    }
}
</style>
