import { createRouter, createWebHistory } from 'vue-router';
import Landing  from '../views/Landing.vue';
import Login    from '../views/Login.vue';
import Register from '../views/Register.vue';
import Home     from '../views/Home.vue';
import NotFound from '../views/NotFound.vue';
import Forbidden from '../js/views/errors/Forbidden.vue';

// Empresas (admin)
import CompanyIndex  from '../js/views/company/CompanyIndex.vue';
import CompanyCreate from '../js/views/company/CompanyCreate.vue';
import CompanyShow   from '../js/views/company/CompanyShow.vue';
import CompanyEdit   from '../js/views/company/CompanyEdit.vue';

// Tarjetas (admin)
import CardCreate from '../js/views/card/CardCreate.vue';
import CardEdit   from '../js/views/card/CardEdit.vue';

// Productos (admin)
import ProductCreate from '../js/views/product/ProductCreate.vue';
import ProductEdit   from '../js/views/product/ProductEdit.vue';

// Servicios (admin)
import ServiceCreate from '../js/views/service/ServiceCreate.vue';
import ServiceEdit   from '../js/views/service/ServiceEdit.vue';

// Editor de plantillas (admin)
import TemplateEditor from '../js/views/settings/TemplateEditor.vue';

// Panel Admin (Master)
import AdminDashboard          from '../js/views/admin/AdminDashboard.vue';
import AdminPlans              from '../js/views/admin/AdminPlans.vue';
import AdminPlanForm           from '../js/views/admin/AdminPlanForm.vue';
import AdminSubscriptions      from '../js/views/admin/AdminSubscriptions.vue';
import AdminSubscriptionDetail from '../js/views/admin/AdminSubscriptionDetail.vue';
import AdminPayments           from '../js/views/admin/AdminPayments.vue';
import AdminPaymentDetail      from '../js/views/admin/AdminPaymentDetail.vue';
import AdminUsers              from '../js/views/admin/AdminUsers.vue';
import AdminUserDetail         from '../js/views/admin/AdminUserDetail.vue';
import AdminCompanies          from '../js/views/admin/AdminCompanies.vue';

// Suscripcion y pagos
import Plans            from '../js/views/subscription/Plans.vue';
import Checkout         from '../js/views/subscription/Checkout.vue';
import PaymentResult    from '../js/views/subscription/PaymentResult.vue';
import MySubscription   from '../js/views/subscription/MySubscription.vue';

// Vistas publicas
import CompanyPublic from '../js/views/public/CompanyPublic.vue';
import CardPublic    from '../js/views/public/CardPublic.vue';

const routes = [
    // --- Landing (pagina publica de inicio) ---
    {
        path: '/inicio',
        name: 'landing',
        component: Landing,
        meta: { layout: 'public' },
    },

    // --- Auth ---
    {
        path: '/login',
        name: 'login',
        component: Login,
        meta: { guest: true },
    },
    {
        path: '/register',
        name: 'register',
        component: Register,
        meta: { guest: true },
    },

    // --- Panel ---
    {
        path: '/',
        name: 'home',
        component: Home,
        meta: { requiresAuth: true },
    },

    // --- Panel Admin (Master) ---
    {
        path: '/admin',
        name: 'admin.dashboard',
        component: AdminDashboard,
        meta: { requiresAuth: true, roles: ['Master'] },
    },
    {
        path: '/admin/planes',
        name: 'admin.plans',
        component: AdminPlans,
        meta: { requiresAuth: true, roles: ['Master'] },
    },
    {
        path: '/admin/planes/crear',
        name: 'admin.plans.create',
        component: AdminPlanForm,
        meta: { requiresAuth: true, roles: ['Master'] },
    },
    {
        path: '/admin/planes/:id/editar',
        name: 'admin.plans.edit',
        component: AdminPlanForm,
        meta: { requiresAuth: true, roles: ['Master'] },
    },
    {
        path: '/admin/suscripciones',
        name: 'admin.subscriptions',
        component: AdminSubscriptions,
        meta: { requiresAuth: true, roles: ['Master'] },
    },
    {
        path: '/admin/suscripciones/:id',
        name: 'admin.subscriptions.show',
        component: AdminSubscriptionDetail,
        meta: { requiresAuth: true, roles: ['Master'] },
    },
    {
        path: '/admin/pagos',
        name: 'admin.payments',
        component: AdminPayments,
        meta: { requiresAuth: true, roles: ['Master'] },
    },
    {
        path: '/admin/pagos/:id',
        name: 'admin.payments.show',
        component: AdminPaymentDetail,
        meta: { requiresAuth: true, roles: ['Master'] },
    },
    {
        path: '/admin/usuarios',
        name: 'admin.users',
        component: AdminUsers,
        meta: { requiresAuth: true, roles: ['Master'] },
    },
    {
        path: '/admin/usuarios/:id',
        name: 'admin.users.show',
        component: AdminUserDetail,
        meta: { requiresAuth: true, roles: ['Master'] },
    },
    {
        path: '/admin/empresas',
        name: 'admin.companies',
        component: AdminCompanies,
        meta: { requiresAuth: true, roles: ['Master'] },
    },

    // --- Acceso denegado ---
    {
        path: '/acceso-denegado',
        name: 'forbidden',
        component: Forbidden,
        meta: { requiresAuth: true },
    },

    // --- Empresas ---
    {
        path: '/empresas',
        name: 'companies.index',
        component: CompanyIndex,
        meta: { requiresAuth: true },
    },
    {
        path: '/empresas/crear',
        name: 'companies.create',
        component: CompanyCreate,
        meta: { requiresAuth: true, roles: ['Master'] },
    },
    {
        path: '/empresas/:id',
        name: 'companies.show',
        component: CompanyShow,
        meta: { requiresAuth: true },
    },
    {
        path: '/empresas/:id/editar',
        name: 'companies.edit',
        component: CompanyEdit,
        meta: { requiresAuth: true },
    },

    // --- Tarjetas (anidadas bajo empresa) ---
    {
        path: '/empresas/:companyId/tarjetas/crear',
        name: 'cards.create',
        component: CardCreate,
        meta: { requiresAuth: true },
    },
    {
        path: '/empresas/:companyId/tarjetas/:cardId/editar',
        name: 'cards.edit',
        component: CardEdit,
        meta: { requiresAuth: true },
    },

    // --- Productos (anidados bajo empresa) ---
    {
        path: '/empresas/:companyId/productos/crear',
        name: 'products.create',
        component: ProductCreate,
        meta: { requiresAuth: true },
    },
    {
        path: '/empresas/:companyId/productos/:productId/editar',
        name: 'products.edit',
        component: ProductEdit,
        meta: { requiresAuth: true },
    },

    // --- Servicios (anidados bajo empresa) ---
    {
        path: '/empresas/:companyId/servicios/crear',
        name: 'services.create',
        component: ServiceCreate,
        meta: { requiresAuth: true },
    },
    {
        path: '/empresas/:companyId/servicios/:serviceId/editar',
        name: 'services.edit',
        component: ServiceEdit,
        meta: { requiresAuth: true },
    },

    // --- Editor de Plantillas ---
    {
        path: '/empresas/:companyId/plantilla',
        name: 'settings.editor',
        component: TemplateEditor,
        meta: { requiresAuth: true },
    },

    // --- Suscripcion y pagos ---
    {
        path: '/planes',
        name: 'subscription.plans',
        component: Plans,
        meta: { requiresAuth: true },
    },
    {
        path: '/checkout/:planId',
        name: 'subscription.checkout',
        component: Checkout,
        meta: { requiresAuth: true },
    },
    {
        path: '/pago/resultado',
        name: 'subscription.result',
        component: PaymentResult,
        meta: { layout: 'public' },
    },
    {
        path: '/mi-suscripcion',
        name: 'subscription.my',
        component: MySubscription,
        meta: { requiresAuth: true },
    },

    // --- Vistas publicas de tarjetas (al final, antes del 404) ---
    {
        path: '/:companySlug',
        name: 'public.company',
        component: CompanyPublic,
        meta: { layout: 'public' },
    },
    {
        path: '/:companySlug/:cardSlug',
        name: 'public.card',
        component: CardPublic,
        meta: { layout: 'public' },
    },

    // --- 404 ---
    {
        path: '/:pathMatch(.*)*',
        name: 'not-found',
        component: NotFound,
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach((to, from, next) => {
    const token = localStorage.getItem('auth_token');

    if (to.meta.requiresAuth && !token) {
        return next({ name: 'landing' });
    }

    if (to.meta.guest && token) {
        return next({ name: 'home' });
    }

    // Verificar roles si la ruta los requiere
    if (to.meta.roles && token) {
        const userData = localStorage.getItem('auth_user');
        const user = userData ? JSON.parse(userData) : null;
        const userRoles = (user?.roles || []).map(r => r.name);

        const hasRequiredRole = to.meta.roles.some(role => userRoles.includes(role));
        if (!hasRequiredRole) {
            return next({ name: 'forbidden' });
        }
    }

    next();
});

export default router;
