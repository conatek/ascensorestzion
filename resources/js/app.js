import './bootstrap';

import { createApp } from 'vue';
import router from './../router';
import { useAuth } from '@/stores/auth';

import Header from './components/Header.vue';
import Sidebar from './components/Sidebar.vue';
import Footer from './components/Footer.vue';

const loadApp = async () => {
    const auth = useAuth();
    await auth.loadFromStorage();

    const { default: App } = await import('./components/PublicApp.vue');

    const app = createApp(App);
    app.use(router);

    app.provide('auth', auth);

    app.component('main-header', Header);
    app.component('main-sidebar', Sidebar);
    app.component('main-footer', Footer);

    app.mount('#app');
};

loadApp();
