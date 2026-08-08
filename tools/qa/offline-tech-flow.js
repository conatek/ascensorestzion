#!/usr/bin/env node
/**
 * Prueba del flujo del técnico SIN CONEXIÓN, que es lo que no se podía comprobar
 * desde el escritorio: registra el service worker, corta la red y verifica que la
 * app sigue arrancando y que el formulario de reporte trae sus listas.
 *
 * Requiere `php artisan serve --port=8011` + `node tools/qa/sw-proxy.js`
 * (la cabecera Service-Worker-Allowed en local no la pone nadie más).
 */
const { chromium } = require('playwright-core');

const BASE = process.env.QA_BASE_URL || 'http://127.0.0.1:8012';
const EMAIL = process.env.QA_TECH_EMAIL || 'tecnico1@ascensorestzion.com';
const PASSWORD = process.env.QA_PASSWORD || 'password';

const results = [];
const check = (label, ok, detail = '') => {
    results.push({ label, ok, detail });
    console.log(`${ok ? '✓' : '✗'} ${label}${detail ? ` — ${detail}` : ''}`);
};

(async () => {
    const b = await chromium.launch({
        executablePath: '/usr/bin/google-chrome-stable',
        headless: true,
        args: ['--no-sandbox', '--disable-dev-shm-usage'],
    });
    const ctx = await b.newContext({ viewport: { width: 390, height: 844 }, deviceScaleFactor: 2, isMobile: true, hasTouch: true });
    const p = await ctx.newPage();

    // ── Con conexión: entrar y dejar que el SW precachee ──
    await p.goto(`${BASE}/login`, { waitUntil: 'load' });
    await p.fill('#email', EMAIL);
    await p.fill('#password', PASSWORD);
    await p.click('.btn-submit');
    await p.waitForURL(u => !u.pathname.endsWith('/login'), { timeout: 20000 });

    await p.waitForFunction(async () => (await navigator.serviceWorker.getRegistration())?.active?.state === 'activated', null, { timeout: 60000 })
        .then(() => check('el service worker se activa', true))
        .catch(() => check('el service worker se activa', false, 'nunca llegó a activated'));

    await p.waitForTimeout(10000); // precache + warmCatalogs

    const cached = await p.evaluate(async () => {
        const db = await new Promise((res, rej) => {
            const r = indexedDB.open('tzion-offline');
            r.onsuccess = () => res(r.result); r.onerror = () => rej(r.error);
        });
        return await new Promise(res => {
            const tx = db.transaction('catalogs', 'readonly').objectStore('catalogs').getAll();
            tx.onsuccess = () => res(tx.result.map(e => `${e.cacheKey}:${e.data?.length ?? 0}`));
            tx.onerror = () => res(['ERROR']);
        });
    }).catch(e => ['ERROR ' + e.message.slice(0, 60)]);
    check('los catálogos quedan guardados en IndexedDB', cached.length >= 4 && !cached.some(c => c.endsWith(':0')), cached.join(' '));

    // ── Modo avión ──
    await ctx.setOffline(true);
    console.log('\n--- modo avión ---');

    // 1. Arranque en frío: es abrir la PWA desde el icono
    try {
        await p.goto(`${BASE}/tech`, { waitUntil: 'domcontentloaded', timeout: 25000 });
        await p.waitForTimeout(3000);
        const txt = await p.locator('body').innerText();
        check('la app arranca sin conexión', txt.includes('Agenda') || txt.includes('Registrar Llegada'), txt.split('\n').slice(0, 2).join(' / '));
    } catch (e) {
        check('la app arranca sin conexión', false, e.message.split('\n')[0]);
    }

    // 2. El formulario de reporte y sus listas
    try {
        await p.goto(`${BASE}/tech/reporte/nuevo?type=RSTP&offline=1`, { waitUntil: 'domcontentloaded', timeout: 25000 });
        await p.waitForTimeout(4000);
        const items = await p.locator('.items-list > *').count();
        const missing = await p.locator('.catalogs-missing').count();
        check('el formulario RSTP pinta sus condiciones iniciales', items > 0, `${items} ítems, aviso=${missing}`);

        await p.locator('.step-bar__item').nth(1).click();
        await p.waitForTimeout(1500);
        const groups = await p.locator('.activity-group').count();
        check('el paso de actividades trae sus grupos', groups > 0, `${groups} grupos`);
        await p.screenshot({ path: 'tools/qa/output-phase2/offline-reporte-rstp.png', fullPage: true });
    } catch (e) {
        check('el formulario RSTP pinta sus condiciones iniciales', false, e.message.split('\n')[0]);
    }

    // 3. Logo e iconos: viven en public/, fuera del precache
    const assets = await p.evaluate(async () => {
        const probe = async (url) => {
            try { const r = await fetch(url); return r.ok; } catch { return false; }
        };
        return {
            logo: await probe('/images/logo/logo-atzion.svg'),
            faCss: await probe('/vendors/@fortawesome/fontawesome-free/css/all.min.css'),
            baseCss: await probe('/css/base.css'),
        };
    });
    // base.css (652 KB, sobre todo estilos del panel) no se precachea a propósito:
    // llega por la caché en tiempo de ejecución a partir de la segunda apertura.
    check('el logo y los iconos siguen disponibles', assets.logo && assets.faCss, `base.css (runtime) = ${assets.baseCss}`);

    // 4. La agenda (chunk perezoso + caché de localStorage)
    try {
        await p.goto(`${BASE}/tech/agenda`, { waitUntil: 'domcontentloaded', timeout: 25000 });
        await p.waitForTimeout(3000);
        const txt = await p.locator('body').innerText();
        check('la agenda abre sin conexión', txt.includes('Mi agenda'), txt.includes('Sin conexión') ? 'muestra el aviso de caché' : '');
    } catch (e) {
        check('la agenda abre sin conexión', false, e.message.split('\n')[0]);
    }

    // ── De vuelta con cobertura: que el service worker no rompa el uso normal ──
    await ctx.setOffline(false);
    console.log('\n--- con conexión otra vez ---');
    try {
        await p.goto(`${BASE}/tech`, { waitUntil: 'load', timeout: 25000 });
        await p.waitForTimeout(3000);
        const txt = await p.locator('body').innerText();
        check('la app sigue funcionando con red', txt.includes('Registrar Llegada'));
    } catch (e) {
        check('la app sigue funcionando con red', false, e.message.split('\n')[0]);
    }

    await b.close();
    const failed = results.filter(r => !r.ok);
    console.log(`\n${failed.length ? `✗ ${failed.length} fallo(s)` : '✓ todo bien'}`);
    process.exit(failed.length ? 1 : 0);
})();
