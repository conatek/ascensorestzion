<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Rutas de Node y Chrome para Browsershot
    |--------------------------------------------------------------------------
    |
    | Browsershot lanza Chromium a través de Node. En producción el usuario web
    | (www-data) no tiene estos binarios en su PATH, así que hay que darle las
    | rutas absolutas. Se leen aquí (dentro de un fichero config) y NO con env()
    | directo en runtime, para que sigan funcionando con `config:cache`.
    |
    | Dev (Linux + nvm + puppeteer): p.ej.
    |   BROWSERSHOT_NODE_PATH=/home/tu-usuario/.nvm/versions/node/vXX/bin/node
    |   BROWSERSHOT_CHROME_PATH=/home/tu-usuario/.cache/puppeteer/chrome/.../chrome
    | Producción (Ubuntu + google-chrome-stable):
    |   BROWSERSHOT_NODE_PATH=/usr/bin/node
    |   BROWSERSHOT_CHROME_PATH=/usr/bin/google-chrome-stable
    |
    | Si quedan en null, Browsershot usa las rutas por defecto del PATH.
    |
    */

    'node_path' => env('BROWSERSHOT_NODE_PATH'),
    'chrome_path' => env('BROWSERSHOT_CHROME_PATH'),
    'npm_path' => env('BROWSERSHOT_NPM_PATH'),

    // Segundos máximos que Browsershot espera a que Chrome genere el PDF antes de
    // abortar. Evita que un request quede colgado si una imagen remota no responde.
    'timeout' => (int) env('BROWSERSHOT_TIMEOUT', 60),
];
