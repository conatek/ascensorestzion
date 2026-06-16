import { createRouter, createWebHistory } from 'vue-router';
import Landing  from '../views/Landing.vue';
import About    from '../views/About.vue';
import Services from '../views/Services.vue';
import Contact  from '../views/Contact.vue';
import Login    from '../views/Login.vue';
import Home     from '../views/Home.vue';
import NotFound from '../views/NotFound.vue';
import Forbidden from '../js/views/errors/Forbidden.vue';

// Clientes
import ClientIndex  from '../js/views/client/ClientIndex.vue';
import ClientCreate from '../js/views/client/ClientCreate.vue';
import ClientShow   from '../js/views/client/ClientShow.vue';
import ClientEdit   from '../js/views/client/ClientEdit.vue';

// Sedes
import SiteCreate from '../js/views/site/SiteCreate.vue';
import SiteEdit   from '../js/views/site/SiteEdit.vue';

// Equipos
import EquipmentIndex  from '../js/views/equipment/EquipmentIndex.vue';
import EquipmentCreate from '../js/views/equipment/EquipmentCreate.vue';
import EquipmentEdit   from '../js/views/equipment/EquipmentEdit.vue';
import EquipmentDetail from '../js/views/equipment/EquipmentDetail.vue';

// Reportes de servicio
import ServiceReportIndex  from '../js/views/report/ServiceReportIndex.vue';
import ServiceReportForm   from '../js/views/report/ServiceReportForm.vue';
import ServiceReportDetail from '../js/views/report/ServiceReportDetail.vue';

// Portal Cliente
import PortalDashboard from '../js/views/portal/PortalDashboard.vue';
import PortalEquipment from '../js/views/portal/PortalEquipment.vue';
import PortalReports   from '../js/views/portal/PortalReports.vue';

// Empresas / Tarjetas (Ascensores Tzion)
import CompanyShow   from '../js/views/company/CompanyShow.vue';
import CompanyEdit   from '../js/views/company/CompanyEdit.vue';
import CardCreate from '../js/views/card/CardCreate.vue';
import CardEdit   from '../js/views/card/CardEdit.vue';
import ProductCreate from '../js/views/product/ProductCreate.vue';
import ProductEdit   from '../js/views/product/ProductEdit.vue';
import ServiceCreate from '../js/views/service/ServiceCreate.vue';
import ServiceEdit   from '../js/views/service/ServiceEdit.vue';
import TemplateEditor from '../js/views/settings/TemplateEditor.vue';

// Panel Admin (master)
import AdminDashboard from '../js/views/admin/AdminDashboard.vue';
import AdminUsers     from '../js/views/admin/AdminUsers.vue';
import AdminUserDetail from '../js/views/admin/AdminUserDetail.vue';
import AdminCompanies  from '../js/views/admin/AdminCompanies.vue';

// Confirmacion de recepcion (publica)
import ReportConfirmation from '../js/views/public/ReportConfirmation.vue';

// Vistas del técnico
import TechDashboard       from '../js/views/tech/TechDashboard.vue';
import TechCheckin         from '../js/views/tech/TechCheckin.vue';
import TechEquipmentCard   from '../js/views/tech/TechEquipmentCard.vue';
import TechReportTypeSelect from '../js/views/tech/TechReportTypeSelect.vue';

// Vistas publicas
import CompanyPublic from '../js/views/public/CompanyPublic.vue';
import CardPublic    from '../js/views/public/CardPublic.vue';

const routes = [
    // --- Landing ---
    {
        path: '/inicio',
        name: 'landing',
        component: Landing,
        meta: { layout: 'public' },
    },
    {
        path: '/nosotros',
        name: 'about',
        component: About,
        meta: { layout: 'public' },
    },
    {
        path: '/servicios',
        name: 'services',
        component: Services,
        meta: { layout: 'public' },
    },
    {
        path: '/contacto',
        name: 'contact',
        component: Contact,
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
        path: '/recuperar-contrasena',
        name: 'password.forgot',
        component: () => import('../js/views/auth/ForgotPassword.vue'),
        meta: { guest: true },
    },
    {
        path: '/restablecer/:token',
        name: 'password.reset',
        component: () => import('../js/views/auth/ResetPassword.vue'),
        meta: { guest: true },
    },

    // --- Panel ---
    {
        path: '/',
        name: 'home',
        component: Home,
        meta: { requiresAuth: true },
    },

    // --- Panel Admin (master y super) ---
    {
        path: '/admin',
        name: 'admin.dashboard',
        component: AdminDashboard,
        meta: { requiresAuth: true, roles: ['master', 'super'] },
    },
    // Gestión de usuarios: solo master
    {
        path: '/admin/usuarios',
        name: 'admin.users',
        component: AdminUsers,
        meta: { requiresAuth: true, roles: ['master'] },
    },
    {
        path: '/admin/usuarios/:id',
        name: 'admin.users.show',
        component: AdminUserDetail,
        meta: { requiresAuth: true, roles: ['master'] },
    },
    {
        path: '/admin/empresas',
        name: 'admin.companies',
        component: AdminCompanies,
        meta: { requiresAuth: true, roles: ['master', 'super'] },
    },

    // --- Acceso denegado ---
    {
        path: '/acceso-denegado',
        name: 'forbidden',
        component: Forbidden,
        meta: { requiresAuth: true },
    },

    // --- Clientes ---
    {
        path: '/clientes',
        name: 'clients.index',
        component: ClientIndex,
        meta: { requiresAuth: true },
    },
    {
        path: '/clientes/crear',
        name: 'clients.create',
        component: ClientCreate,
        meta: { requiresAuth: true, roles: ['master', 'super', 'coordinator'] },
    },
    {
        path: '/clientes/:id',
        name: 'clients.show',
        component: ClientShow,
        meta: { requiresAuth: true },
    },
    {
        path: '/clientes/:id/editar',
        name: 'clients.edit',
        component: ClientEdit,
        meta: { requiresAuth: true, roles: ['master', 'super', 'coordinator'] },
    },

    // --- Sedes ---
    {
        path: '/clientes/:clientId/sedes/crear',
        name: 'sites.create',
        component: SiteCreate,
        meta: { requiresAuth: true, roles: ['master', 'super', 'coordinator'] },
    },
    {
        path: '/clientes/:clientId/sedes/:siteId/editar',
        name: 'sites.edit',
        component: SiteEdit,
        meta: { requiresAuth: true, roles: ['master', 'super', 'coordinator'] },
    },

    // --- Equipos ---
    {
        path: '/equipos',
        name: 'equipment.index',
        component: EquipmentIndex,
        meta: { requiresAuth: true },
    },
    {
        path: '/equipos/crear',
        name: 'equipment.create',
        component: EquipmentCreate,
        meta: { requiresAuth: true, roles: ['master', 'super', 'coordinator'] },
    },
    {
        path: '/equipos/:id',
        name: 'equipment.show',
        component: EquipmentDetail,
        meta: { requiresAuth: true },
    },
    {
        path: '/equipos/:id/editar',
        name: 'equipment.edit',
        component: EquipmentEdit,
        meta: { requiresAuth: true, roles: ['master', 'super', 'coordinator'] },
    },

    // --- Reportes de Servicio ---
    {
        path: '/reportes',
        name: 'reports.index',
        component: ServiceReportIndex,
        meta: { requiresAuth: true },
    },
    {
        path: '/reportes/nuevo',
        name: 'reports.create',
        component: ServiceReportForm,
        meta: { requiresAuth: true, roles: ['master', 'super', 'coordinator', 'technician'] },
    },
    {
        path: '/reportes/:id',
        name: 'reports.show',
        component: ServiceReportDetail,
        meta: { requiresAuth: true },
    },
    {
        path: '/reportes/:id/editar',
        name: 'reports.edit',
        component: ServiceReportForm,
        meta: { requiresAuth: true },
    },

    // --- Portal Cliente ---
    {
        path: '/portal',
        name: 'portal.dashboard',
        component: PortalDashboard,
        meta: { requiresAuth: true, layout: 'portal', roles: ['admin'] },
    },
    {
        path: '/portal/equipos',
        name: 'portal.equipment',
        component: PortalEquipment,
        meta: { requiresAuth: true, layout: 'portal', roles: ['admin'] },
    },
    {
        path: '/portal/reportes',
        name: 'portal.reports',
        component: PortalReports,
        meta: { requiresAuth: true, layout: 'portal', roles: ['admin'] },
    },
    {
        path: '/portal/perfil',
        name: 'portal.profile',
        component: () => import('../js/views/Profile.vue'),
        meta: { requiresAuth: true, layout: 'portal', roles: ['admin'] },
    },

    // --- Mi Perfil (roles internos; portal y técnico usan sus propias rutas) ---
    {
        path: '/perfil',
        name: 'profile',
        component: () => import('../js/views/Profile.vue'),
        meta: { requiresAuth: true, layout: 'admin', roles: ['master', 'super', 'coordinator'] },
    },

    // --- Tarjetas Tzion ---
    {
        path: '/tarjetas',
        name: 'companies.show',
        component: CompanyShow,
        meta: { requiresAuth: true },
    },
    {
        path: '/tarjetas/editar',
        name: 'companies.edit',
        component: CompanyEdit,
        meta: { requiresAuth: true },
    },
    {
        path: '/tarjetas/crear',
        name: 'cards.create',
        component: CardCreate,
        meta: { requiresAuth: true },
    },
    {
        path: '/tarjetas/:cardId/editar',
        name: 'cards.edit',
        component: CardEdit,
        meta: { requiresAuth: true },
    },
    {
        path: '/tarjetas/productos/crear',
        name: 'products.create',
        component: ProductCreate,
        meta: { requiresAuth: true },
    },
    {
        path: '/tarjetas/productos/:productId/editar',
        name: 'products.edit',
        component: ProductEdit,
        meta: { requiresAuth: true },
    },
    {
        path: '/tarjetas/servicios/crear',
        name: 'services.create',
        component: ServiceCreate,
        meta: { requiresAuth: true },
    },
    {
        path: '/tarjetas/servicios/:serviceId/editar',
        name: 'services.edit',
        component: ServiceEdit,
        meta: { requiresAuth: true },
    },
    {
        path: '/tarjetas/plantilla',
        name: 'settings.editor',
        component: TemplateEditor,
        meta: { requiresAuth: true },
    },

    // --- Confirmacion de recepcion (publica) ---
    {
        path: '/confirmacion-reporte/:token',
        name: 'report.confirmation',
        component: ReportConfirmation,
        meta: { layout: 'public' },
    },

    // --- Técnico ---
    {
        path: '/tech',
        name: 'tech.dashboard',
        component: TechDashboard,
        meta: { requiresAuth: true, roles: ['technician'], layout: 'tech' },
    },
    {
        path: '/tech/checkin',
        name: 'tech.checkin',
        component: TechCheckin,
        meta: { requiresAuth: true, roles: ['technician'], layout: 'tech' },
    },
    {
        path: '/tech/equipo/:id',
        name: 'tech.equipment',
        component: TechEquipmentCard,
        meta: { requiresAuth: true, roles: ['technician'], layout: 'tech' },
    },
    {
        path: '/tech/reporte/tipo',
        name: 'tech.report.type',
        component: TechReportTypeSelect,
        meta: { requiresAuth: true, roles: ['technician'], layout: 'tech' },
    },
    {
        path: '/tech/reporte/nuevo',
        name: 'tech.report.create',
        component: () => import('../js/views/tech/TechReportForm.vue'),
        meta: { requiresAuth: true, roles: ['technician'], layout: 'tech' },
    },
    {
        path: '/tech/pendientes',
        name: 'tech.pending',
        component: () => import('../js/views/tech/TechPending.vue'),
        meta: { requiresAuth: true, roles: ['technician'], layout: 'tech' },
    },
    {
        path: '/tech/perfil',
        name: 'tech.profile',
        component: () => import('../js/views/Profile.vue'),
        meta: { requiresAuth: true, roles: ['technician'], layout: 'tech' },
    },

    // --- Redirects de rutas viejas ---
    { path: '/empresas/:id', redirect: '/tarjetas' },
    { path: '/empresas/:id/editar', redirect: '/tarjetas/editar' },
    { path: '/empresas/:companyId/tarjetas/crear', redirect: '/tarjetas/crear' },
    { path: '/empresas/:companyId/tarjetas/:cardId/editar', redirect: to => `/tarjetas/${to.params.cardId}/editar` },
    { path: '/empresas/:companyId/productos/crear', redirect: '/tarjetas/productos/crear' },
    { path: '/empresas/:companyId/productos/:productId/editar', redirect: to => `/tarjetas/productos/${to.params.productId}/editar` },
    { path: '/empresas/:companyId/servicios/crear', redirect: '/tarjetas/servicios/crear' },
    { path: '/empresas/:companyId/servicios/:serviceId/editar', redirect: to => `/tarjetas/servicios/${to.params.serviceId}/editar` },
    { path: '/empresas/:companyId/plantilla', redirect: '/tarjetas/plantilla' },

    // --- Vistas publicas de tarjetas ---
    {
        path: '/:cardSlug',
        name: 'public.card',
        component: CardPublic,
        meta: { layout: 'public' },
    },

    // Redirect de ruta vieja /:companySlug/:cardSlug
    { path: '/:companySlug/:cardSlug', redirect: to => `/${to.params.cardSlug}` },

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

    // Redirigir segun rol al acceder a home
    if (token && to.name === 'home') {
        const userData = localStorage.getItem('auth_user');
        const user = userData ? JSON.parse(userData) : null;
        const userRoles = (user?.roles || []).map(r => r.name);
        if (userRoles.includes('master') || userRoles.includes('super')) {
            return next({ name: 'admin.dashboard' });
        }
        if (userRoles.includes('technician')) {
            return next({ name: 'tech.dashboard' });
        }
        if (userRoles.includes('admin') && !userRoles.includes('master')) {
            return next({ name: 'portal.dashboard' });
        }
    }

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
