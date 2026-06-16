import './bootstrap';

import { createApp } from 'vue';
import router from './../router';
import { useAuth } from '@/stores/auth';
import VueApexCharts from 'vue3-apexcharts';
import VueSweetalert2 from 'vue-sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';

import Header from './components/Header.vue';
import Sidebar from './components/Sidebar.vue';
import Footer from './components/Footer.vue';
import ClientPortalLayout from './components/ClientPortalLayout.vue';
import TechLayout from './components/TechLayout.vue';
import RoleBottomNav from './components/shared/RoleBottomNav.vue';

const loadApp = async () => {
    const auth = useAuth();
    await auth.loadFromStorage();

    const { default: App } = await import('./components/PublicApp.vue');

    const app = createApp(App);
    app.use(router);
    app.use(VueApexCharts);
    app.use(VueSweetalert2, {
        confirmButtonColor: '#30ab0a',
        cancelButtonColor: '#64748b',
        reverseButtons: true,
    });

    app.provide('auth', auth);

    app.component('main-header', Header);
    app.component('main-sidebar', Sidebar);
    app.component('main-footer', Footer);
    app.component('client-portal-layout', ClientPortalLayout);
    app.component('tech-layout', TechLayout);
    app.component('role-bottom-nav', RoleBottomNav);

    app.mount('#app');
};

loadApp();
