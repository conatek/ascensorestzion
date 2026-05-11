import { defineAsyncComponent } from 'vue';

const components = {
    'main-footer': defineAsyncComponent(() => import('./components/Footer.vue')),
    'main-sidebar': defineAsyncComponent(() => import('./components/Sidebar.vue')),
    'main-header': defineAsyncComponent(() => import('./components/Header.vue')),
};

export default components;
