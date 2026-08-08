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
import fs from 'fs';
import crypto from 'crypto';

// Revisión del app shell. Cambia en cada build para que el service worker vuelva a
// pedir "/" y no sirva un documento que apunta a assets que ya no existen.
const shellRevision = String(Date.now());

/**
 * Ficheros de public/ sin los que la app se ve rota sin conexión: el logo y los
 * iconos. Van enumerados a mano porque `includeAssets` no sirve aquí —
 * laravel-vite-plugin desactiva el publicDir de Vite y el plugin no tiene dónde
 * buscarlos.
 *
 * Solo los .woff2 y sin los "brands": los .eot/.svg/.ttf son 2,5 MB de formatos
 * para navegadores que ningún técnico usa.
 */
const PUBLIC_SHELL_ASSETS = [
    'images/logo/logo-atzion.svg',
    'images/logo/logo-atzion-white.svg',
    'vendors/@fortawesome/fontawesome-free/css/all.min.css',
    'vendors/@fortawesome/fontawesome-free/webfonts/fa-solid-900.woff2',
    'vendors/@fortawesome/fontawesome-free/webfonts/fa-regular-400.woff2',
];

const publicShellEntries = PUBLIC_SHELL_ASSETS.map((file) => ({
    url: `/${file}`,
    revision: crypto.createHash('md5').update(fs.readFileSync(`public/${file}`)).digest('hex'),
}));

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
            includeAssets: ['images/favicon/favicon.ico', 'images/favicon/apple-touch-icon.png'],
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
                        src: '/images/favicon/android-chrome-192x192.png',
                        sizes: '192x192',
                        type: 'image/png',
                        purpose: 'any',
                    },
                    {
                        src: '/images/favicon/android-chrome-512x512.png',
                        sizes: '512x512',
                        type: 'image/png',
                        purpose: 'any',
                    },
                ],
            },
            workbox: {
                globPatterns: ['**/*.{js,css,html,ico,png,svg,woff,woff2}'],

                // El documento lo sirve Blade, así que en public/build no hay ningún
                // .html. El navigateFallback por defecto de vite-plugin-pwa apunta a
                // "index.html" y el service worker acababa registrando una ruta de
                // navegación atada a un fichero que no estaba precacheado: sin
                // conexión toda navegación moría (abrir la PWA desde el icono,
                // recargar, o volver a ella después de que el móvil la descartara
                // de memoria). Los chunks sí estaban en la caché; lo que faltaba era
                // el documento.
                //
                // Se precachea "/" —el catch-all de routes/web.php devuelve la SPA
                // sin OG tags— y se usa como shell de cualquier navegación.
                additionalManifestEntries: [
                    { url: '/', revision: shellRevision },
                    ...publicShellEntries,
                ],
                navigateFallback: '/',
                // La API, los assets y los ficheros subidos nunca son navegaciones
                // de la SPA: si se les sirviera el shell, un 401 se convertiría en
                // un HTML y axios reventaría al parsearlo.
                navigateFallbackDenylist: [/^\/api\//, /^\/build\//, /^\/storage\//],

                runtimeCaching: [
                    {
                        // El resto de public/ (fotos de equipos, hojas de estilo de
                        // los vendors, iconos sueltos): se guarda lo que se haya
                        // usado en línea. Precachearlo entero no tiene sentido —
                        // solo public/images ya son 12 MB.
                        urlPattern: ({ url, sameOrigin }) => sameOrigin
                            && /^\/(images|css|fonts|vendors|plugins|adminlte)\//.test(url.pathname),
                        handler: 'StaleWhileRevalidate',
                        options: {
                            cacheName: 'static-assets-cache',
                            expiration: { maxEntries: 200, maxAgeSeconds: 60 * 60 * 24 * 30 },
                            cacheableResponse: { statuses: [0, 200] },
                        },
                    },
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
