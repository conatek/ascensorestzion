#!/usr/bin/env node
/**
 * Proxy delgado sobre `php artisan serve` que añade la cabecera
 * `Service-Worker-Allowed: /` a /build/sw.js.
 *
 * Sin ella el navegador rechaza registrar un worker de /build/ con scope /, así
 * que el modo sin conexión NO se puede probar contra el servidor de desarrollo:
 * en producción esa cabecera la pone Nginx.
 *
 * Uso: node tools/qa/sw-proxy.js  (escucha en 8012 y reenvía a 8011)
 */
const http = require('http');

const LISTEN = Number(process.env.PROXY_PORT || 8012);
const TARGET = Number(process.env.TARGET_PORT || 8011);

http.createServer((req, res) => {
    const proxied = http.request(
        { hostname: '127.0.0.1', port: TARGET, path: req.url, method: req.method, headers: req.headers },
        (up) => {
            const headers = { ...up.headers };
            if (req.url.startsWith('/build/sw.js')) headers['service-worker-allowed'] = '/';
            res.writeHead(up.statusCode, headers);
            up.pipe(res);
        },
    );
    proxied.on('error', (e) => { res.writeHead(502); res.end(String(e.message)); });
    req.pipe(proxied);
}).listen(LISTEN, () => console.log(`proxy :${LISTEN} → :${TARGET}`));
