import { reactive, computed } from 'vue';
import api from '@/services/api';

const state = reactive({
    user: null,
    token: null,
    permissions: [],
});

export function useAuth() {
    const isAuthenticated = computed(() => !!state.token);

    function hasRole(name) {
        const roles = state.user?.roles || [];
        return roles.some(r => r.name === name);
    }

    function hasPermission(name) {
        return state.permissions.includes(name);
    }

    function isMaster() {
        return hasRole('Master');
    }

    function can(permission) {
        return isMaster() || hasPermission(permission);
    }

    async function login(credentials) {
        const { data } = await api.post('/login', credentials);
        state.token = data.access_token;
        state.user = data.user;
        state.permissions = data.permissions || [];
        localStorage.setItem('auth_token', data.access_token);
        localStorage.setItem('auth_user', JSON.stringify(data.user));
        localStorage.setItem('auth_permissions', JSON.stringify(data.permissions || []));
        return data;
    }

    async function register(form) {
        const { data } = await api.post('/register', form);
        state.token = data.access_token;
        state.user = data.user;
        state.permissions = data.permissions || [];
        localStorage.setItem('auth_token', data.access_token);
        localStorage.setItem('auth_user', JSON.stringify(data.user));
        localStorage.setItem('auth_permissions', JSON.stringify(data.permissions || []));
        return data;
    }

    async function logout() {
        try {
            await api.post('/logout');
        } catch {
            // Si el token ya expiró, igual limpiar estado local
        }
        state.token = null;
        state.user = null;
        state.permissions = [];
        localStorage.removeItem('auth_token');
        localStorage.removeItem('auth_user');
        localStorage.removeItem('auth_permissions');
    }

    async function fetchUser() {
        try {
            const { data } = await api.get('/me');
            state.user = data.user;
            state.permissions = data.permissions || [];
            localStorage.setItem('auth_user', JSON.stringify(data.user));
            localStorage.setItem('auth_permissions', JSON.stringify(data.permissions || []));
            return data;
        } catch {
            state.token = null;
            state.user = null;
            state.permissions = [];
            localStorage.removeItem('auth_token');
            localStorage.removeItem('auth_user');
            localStorage.removeItem('auth_permissions');
            return null;
        }
    }

    async function loadFromStorage() {
        const token = localStorage.getItem('auth_token');
        const user = localStorage.getItem('auth_user');
        const permissions = localStorage.getItem('auth_permissions');
        if (token) {
            state.token = token;
            state.user = user ? JSON.parse(user) : null;
            state.permissions = permissions ? JSON.parse(permissions) : [];
            await fetchUser();
        }
    }

    return {
        state,
        isAuthenticated,
        hasRole,
        hasPermission,
        isMaster,
        can,
        login,
        register,
        logout,
        fetchUser,
        loadFromStorage,
    };
}
