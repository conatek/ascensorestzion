import { reactive, computed } from 'vue';
import api from '@/services/api';

const state = reactive({
    user: null,
    token: null,
    permissions: [],
    impersonating: false,
    originalToken: null,
    originalUser: null,
    originalPermissions: [],
});

export function useAuth() {
    const isAuthenticated = computed(() => !!state.token);
    const isImpersonating = computed(() => state.impersonating);
    const companyId = computed(() => state.user?.company_id);

    function hasRole(name) {
        const roles = state.user?.roles || [];
        return roles.some(r => r.name === name);
    }

    function hasPermission(name) {
        return state.permissions.includes(name);
    }

    function isMaster()      { return hasRole('master'); }
    function isCoordinator() { return hasRole('coordinator'); }
    function isTechnician()  { return hasRole('technician'); }
    function isAdmin()       { return hasRole('admin'); }
    function isInternal()    { return isMaster() || isCoordinator() || isTechnician(); }

    function can(permission) {
        return isMaster() || hasPermission(permission);
    }

    async function login(credentials) {
        const { data } = await api.post('/login', credentials);
        state.token = data.access_token;
        state.user = data.user;
        state.permissions = data.permissions || [];
        state.impersonating = false;
        state.originalToken = null;
        state.originalUser = null;
        state.originalPermissions = [];
        localStorage.setItem('auth_token', data.access_token);
        localStorage.setItem('auth_user', JSON.stringify(data.user));
        localStorage.setItem('auth_permissions', JSON.stringify(data.permissions || []));
        localStorage.removeItem('impersonating');
        localStorage.removeItem('original_token');
        localStorage.removeItem('original_user');
        localStorage.removeItem('original_permissions');
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
        state.impersonating = false;
        state.originalToken = null;
        state.originalUser = null;
        state.originalPermissions = [];
        localStorage.removeItem('auth_token');
        localStorage.removeItem('auth_user');
        localStorage.removeItem('auth_permissions');
        localStorage.removeItem('impersonating');
        localStorage.removeItem('original_token');
        localStorage.removeItem('original_user');
        localStorage.removeItem('original_permissions');
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

            // Restaurar estado de impersonation
            const imp = localStorage.getItem('impersonating');
            if (imp === 'true') {
                state.impersonating = true;
                state.originalToken = localStorage.getItem('original_token');
                state.originalUser = JSON.parse(localStorage.getItem('original_user') || 'null');
                state.originalPermissions = JSON.parse(localStorage.getItem('original_permissions') || '[]');
            }

            await fetchUser();
        }
    }

    async function impersonate(clientId) {
        const { data } = await api.post(`/impersonate/${clientId}`);

        // Guardar datos originales del master
        state.originalToken = data.original_token;
        state.originalUser = data.original_user;
        state.originalPermissions = data.original_permissions || [];
        state.impersonating = true;

        // Cambiar a la sesión del usuario impersonado
        state.token = data.access_token;
        state.user = data.user;
        state.permissions = data.permissions || [];

        // Persistir
        localStorage.setItem('auth_token', data.access_token);
        localStorage.setItem('auth_user', JSON.stringify(data.user));
        localStorage.setItem('auth_permissions', JSON.stringify(data.permissions || []));
        localStorage.setItem('impersonating', 'true');
        localStorage.setItem('original_token', data.original_token);
        localStorage.setItem('original_user', JSON.stringify(data.original_user));
        localStorage.setItem('original_permissions', JSON.stringify(data.original_permissions || []));

        return data;
    }

    async function stopImpersonating() {
        // Eliminar token de impersonation en el server
        try {
            await api.post('/stop-impersonating');
        } catch {
            // Continuar restaurando aunque falle
        }

        // Restaurar sesión del master
        state.token = state.originalToken;
        state.user = state.originalUser;
        state.permissions = state.originalPermissions;
        state.impersonating = false;
        state.originalToken = null;
        state.originalUser = null;
        state.originalPermissions = [];

        // Persistir
        localStorage.setItem('auth_token', state.token);
        localStorage.setItem('auth_user', JSON.stringify(state.user));
        localStorage.setItem('auth_permissions', JSON.stringify(state.permissions));
        localStorage.removeItem('impersonating');
        localStorage.removeItem('original_token');
        localStorage.removeItem('original_user');
        localStorage.removeItem('original_permissions');

        // Refrescar datos del master
        await fetchUser();
    }

    return {
        state,
        isAuthenticated,
        isImpersonating,
        companyId,
        hasRole,
        hasPermission,
        isMaster,
        isCoordinator,
        isTechnician,
        isAdmin,
        isInternal,
        can,
        login,
        logout,
        fetchUser,
        loadFromStorage,
        impersonate,
        stopImpersonating,
    };
}
