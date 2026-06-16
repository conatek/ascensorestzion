#!/usr/bin/env node
/**
 * QA visual responsive — matriz rol × viewport × vista.
 *
 * Genera capturas de las vistas clave para cada rol en móvil y escritorio,
 * para detectar regresiones de responsive/PWA (bottom nav, tablas→cards,
 * gráficos, safe-area, etc.).
 *
 * Uso:
 *   1. Tener la app sirviendo (php artisan serve) y la BD con seeders.
 *      Para QA fiel a producción: `npm run build` y que NO exista `public/hot`
 *      (si corres `npm run dev`, el blade carga del dev server).
 *   2. node tools/qa/visual-matrix.js
 *
 * Variables de entorno (opcionales):
 *   QA_BASE_URL    (def: http://127.0.0.1:8000)
 *   QA_CHROME_PATH (def: /usr/bin/google-chrome-stable)
 *   QA_PASSWORD    (def: password)
 *   QA_OUT         (def: tools/qa/output)
 *   QA_ONLY        filtra roles, ej. "master,technician"
 *
 * Salida: PNG en QA_OUT/<rol>-<vista>-<viewport>.png + resumen en consola.
 * Exit code 1 si alguna vista falla (error de carga o redirección inesperada).
 */
const path = require('path');
const fs = require('fs');
const { chromium } = require('playwright-core');

const BASE = process.env.QA_BASE_URL || 'http://127.0.0.1:8000';
const CHROME = process.env.QA_CHROME_PATH || '/usr/bin/google-chrome-stable';
const PASSWORD = process.env.QA_PASSWORD || 'password';
const OUT = process.env.QA_OUT || path.join(__dirname, 'output');
const ONLY = (process.env.QA_ONLY || '').split(',').map((s) => s.trim()).filter(Boolean);

const VIEWPORTS = {
    mobile: { viewport: { width: 390, height: 844 }, deviceScaleFactor: 2, isMobile: true, hasTouch: true },
    desktop: { viewport: { width: 1366, height: 900 } },
};

// Cada ruta: [nombre, path]. Las rutas con :id usan datos sembrados (id 1).
const ROLES = {
    master: {
        email: 'master@ascensorestzion.com',
        routes: [
            ['dashboard', '/admin'],
            ['clientes', '/clientes'],
            ['cliente-detalle', '/clientes/1'],
            ['equipos', '/equipos'],
            ['equipo-detalle', '/equipos/1'],
            ['reportes', '/reportes'],
            ['usuarios', '/admin/usuarios'],
            ['empresas', '/admin/empresas'],
            ['tarjetas', '/tarjetas'],
        ],
    },
    super: {
        email: 'super@ascensorestzion.com',
        routes: [
            ['dashboard', '/admin'],
            ['clientes', '/clientes'],
            ['equipos', '/equipos'],
            ['reportes', '/reportes'],
            ['empresas', '/admin/empresas'],
        ],
    },
    coordinator: {
        email: 'coordinador@ascensorestzion.com',
        routes: [
            ['clientes', '/clientes'],
            ['equipos', '/equipos'],
            ['reportes', '/reportes'],
        ],
    },
    admin: { // portal cliente
        email: 'admin@ccoviedo.com',
        routes: [
            ['portal', '/portal'],
            ['portal-equipos', '/portal/equipos'],
            ['portal-reportes', '/portal/reportes'],
        ],
    },
    technician: {
        email: 'tecnico1@ascensorestzion.com',
        routes: [
            ['tech-inicio', '/tech'],
            ['tech-checkin', '/tech/checkin'],
        ],
    },
};

async function login(page, email) {
    await page.goto(`${BASE}/login`, { waitUntil: 'domcontentloaded' });
    await page.waitForSelector('#email', { timeout: 15000 });
    await page.fill('#email', email);
    await page.fill('#password', PASSWORD);
    await page.click('.btn-submit');
    await page.waitForURL((u) => !u.pathname.endsWith('/login'), { timeout: 20000 });
    await page.waitForTimeout(1500);
}

(async () => {
    fs.mkdirSync(OUT, { recursive: true });
    const roleNames = ONLY.length ? ONLY : Object.keys(ROLES);
    const results = [];

    const browser = await chromium.launch({
        executablePath: CHROME,
        headless: true,
        args: ['--no-sandbox', '--disable-dev-shm-usage'],
    });

    for (const role of roleNames) {
        const cfg = ROLES[role];
        if (!cfg) { console.log(`(omito rol desconocido: ${role})`); continue; }

        for (const [vpName, vp] of Object.entries(VIEWPORTS)) {
            const ctx = await browser.newContext({
                ...vp,
                geolocation: { latitude: 6.1934, longitude: -75.5586, accuracy: 20 },
                permissions: ['geolocation'],
            });
            const page = await ctx.newPage();
            try {
                await login(page, cfg.email);
            } catch (e) {
                console.log(`✗ LOGIN ${role}/${vpName}: ${e.message.split('\n')[0]}`);
                results.push({ role, vp: vpName, view: '(login)', ok: false, note: 'login falló' });
                await ctx.close();
                continue;
            }

            for (const [view, route] of cfg.routes) {
                const file = path.join(OUT, `${role}-${view}-${vpName}.png`);
                try {
                    await page.goto(`${BASE}${route}`, { waitUntil: 'domcontentloaded' });
                    await page.waitForTimeout(2600); // datos + charts
                    const finalPath = new URL(page.url()).pathname;
                    const redirected = finalPath.includes('acceso-denegado') || finalPath.endsWith('/login');
                    await page.screenshot({ path: file, fullPage: true });
                    results.push({ role, vp: vpName, view, ok: !redirected, note: redirected ? `redirigió a ${finalPath}` : '' });
                    console.log(`${redirected ? '⚠' : '✓'} ${role}/${vpName}/${view}${redirected ? ' → ' + finalPath : ''}`);
                } catch (e) {
                    results.push({ role, vp: vpName, view, ok: false, note: e.message.split('\n')[0] });
                    console.log(`✗ ${role}/${vpName}/${view}: ${e.message.split('\n')[0]}`);
                }
            }
            await ctx.close();
        }
    }

    await browser.close();

    const fail = results.filter((r) => !r.ok);
    console.log(`\n── Resumen ── ${results.length} vistas, ${fail.length} con problema`);
    if (fail.length) {
        fail.forEach((f) => console.log(`   ✗ ${f.role}/${f.vp}/${f.view} — ${f.note}`));
    }
    console.log(`Capturas en: ${OUT}`);
    process.exit(fail.length ? 1 : 0);
})().catch((e) => { console.error('FATAL', e); process.exit(2); });
