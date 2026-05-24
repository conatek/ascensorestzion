// import { defineConfig } from 'vite';
// import laravel from 'laravel-vite-plugin';
// import vue from '@vitejs/plugin-vue';
// import path from 'path';

// export default defineConfig({
//     plugins: [
//         vue(),
//         laravel({
//             input: [
//                 'resources/sass/app.scss',
//                 'resources/css/app.css',
//                 'resources/js/app.js',
//             ],
//             refresh: true,
//         }),
//     ],
//     resolve: {
//         alias: {
//             'vue': 'vue/dist/vue.esm-bundler',
//             '@': path.resolve(__dirname, './resources/js'),
//         }
//     },
//     build: {
//         manifest: true,
//         outDir: 'public/build',
//         rollupOptions: {
//             external: [
//                 'node_modules/@fortawesome/fontawesome-svg-core',
//                 'node_modules/@fortawesome/free-solid-svg-icons',
//                 'node_modules/@fortawesome/free-regular-svg-icons',
//                 'node_modules/@fortawesome/vue-fontawesome',
//             ],
//         }
//       }
// });

// --------------------------------------------

// import { defineConfig } from 'vite';
// import vue from '@vitejs/plugin-vue';
// import path from 'path';


// export default defineConfig({
//     plugins: [vue()],
//     resolve: {
//         alias: {
//             '@': path.resolve(__dirname, 'resources/js'),
//         },
//     },
//     server: {
//         historyApiFallback: true, // Permite recargar páginas sin perder el enrutamiento
//     },
// });

// --------------------------------------------

import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import { VitePWA } from 'vite-plugin-pwa';
import path from 'path';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/js/app.js'],
            refresh: true,
        }),
        vue(),
        VitePWA({
            registerType: 'autoUpdate',
            scope: '/',
            devOptions: { enabled: false },
            includeAssets: ['images/favicon.ico', 'images/logo.png'],
            manifest: {
                name: 'Ascensores Tzion',
                short_name: 'Atzion',
                description: 'Gestión de equipos de transporte vertical',
                theme_color: '#30ab0a',
                background_color: '#ffffff',
                display: 'standalone',
                orientation: 'portrait-primary',
                start_url: '/',
                scope: '/',
                icons: [
                    {
                        src: '/images/pwa/icon-192.png',
                        sizes: '192x192',
                        type: 'image/png',
                    },
                    {
                        src: '/images/pwa/icon-512.png',
                        sizes: '512x512',
                        type: 'image/png',
                    },
                    {
                        src: '/images/pwa/icon-512.png',
                        sizes: '512x512',
                        type: 'image/png',
                        purpose: 'maskable',
                    },
                ],
            },
            workbox: {
                globPatterns: ['**/*.{js,css,html,ico,png,svg,woff,woff2}'],
                runtimeCaching: [
                    {
                        urlPattern: /^https:\/\/fonts\.googleapis\.com\/.*/i,
                        handler: 'CacheFirst',
                        options: {
                            cacheName: 'google-fonts-cache',
                            expiration: { maxEntries: 10, maxAgeSeconds: 60 * 60 * 24 * 365 },
                            cacheableResponse: { statuses: [0, 200] },
                        },
                    },
                    {
                        urlPattern: /^https:\/\/fonts\.gstatic\.com\/.*/i,
                        handler: 'CacheFirst',
                        options: {
                            cacheName: 'gstatic-fonts-cache',
                            expiration: { maxEntries: 10, maxAgeSeconds: 60 * 60 * 24 * 365 },
                            cacheableResponse: { statuses: [0, 200] },
                        },
                    },
                    {
                        urlPattern: /\/api\/catalogs.*/i,
                        handler: 'StaleWhileRevalidate',
                        options: {
                            cacheName: 'catalogs-cache',
                            expiration: { maxEntries: 50, maxAgeSeconds: 60 * 60 * 24 * 7 },
                            cacheableResponse: { statuses: [0, 200] },
                        },
                    },
                    {
                        urlPattern: /\/api\/equipment.*/i,
                        handler: 'NetworkFirst',
                        options: {
                            cacheName: 'equipment-cache',
                            expiration: { maxEntries: 100, maxAgeSeconds: 60 * 60 * 24 },
                            cacheableResponse: { statuses: [0, 200] },
                            networkTimeoutSeconds: 5,
                        },
                    },
                ],
            },
        }),
    ],
    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'resources/js'),
        },
    },
    server: {
        historyApiFallback: true,
    },
});
