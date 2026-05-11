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
import laravel from 'laravel-vite-plugin'; // Importa el plugin de Laravel
import path from 'path';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/js/app.js'], // Archivos de entrada
            refresh: true, // Recarga automática en desarrollo
        }),
        vue(),
    ],
    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'resources/js'),
        },
    },
    server: {
        historyApiFallback: true, // Permite recargar páginas sin perder el enrutamiento
    },
});
