#!/usr/bin/env node
/**
 * QA visual de la fase 4b: la tarjeta de jornada y excepciones en la ficha del
 * técnico, y el modal de días no laborables del cronograma.
 *
 * Uso: php artisan serve --port=8011, npm run build (y sin public/hot), y
 *      QA_BASE_URL=http://127.0.0.1:8011 node tools/qa/schedule-phase4b-ui.js
 */
const path = require('path');
const fs = require('fs');
const { chromium } = require('playwright-core');

const BASE = process.env.QA_BASE_URL || 'http://127.0.0.1:8011';
const CHROME = process.env.QA_CHROME_PATH || '/usr/bin/google-chrome-stable';
const PASSWORD = process.env.QA_PASSWORD || 'password';
const OUT = process.env.QA_OUT || path.join(__dirname, 'output-phase4b');

const DESKTOP = { viewport: { width: 1366, height: 900 } };
const MOBILE = { viewport: { width: 390, height: 844 }, deviceScaleFactor: 2, isMobile: true, hasTouch: true };
const errors = [];

function watch(page, tag) {
    page.on('pageerror', e => errors.push(`[${tag}] ${e.message.slice(0, 160)}`));
    page.on('console', m => {
        if (m.type() === 'error') errors.push(`[${tag}] console: ${m.text().slice(0, 160)}`);
    });
}

async function login(page, email) {
    await page.goto(`${BASE}/login`, { waitUntil: 'domcontentloaded' });
    await page.waitForSelector('#email', { timeout: 15000 });
    await page.fill('#email', email);
    await page.fill('#password', PASSWORD);
    await page.click('.btn-submit');
    await page.waitForURL(u => !u.pathname.endsWith('/login'), { timeout: 20000 });
    await page.waitForTimeout(1500);
}

const shot = async (p, name) => {
    await p.screenshot({ path: path.join(OUT, `${name}.png`), fullPage: true });
    console.log('✓ ' + name);
};

(async () => {
    fs.mkdirSync(OUT, { recursive: true });
    const browser = await chromium.launch({
        executablePath: CHROME,
        headless: true,
        args: ['--no-sandbox', '--disable-dev-shm-usage'],
    });

    // Ficha del técnico: jornada + excepciones. Solo master entra a /admin.
    for (const [viewport, suffix] of [[DESKTOP, 'desktop'], [MOBILE, 'mobile']]) {
        const ctx = await browser.newContext(viewport);
        const p = await ctx.newPage();
        watch(p, `admin-${suffix}`);

        await login(p, 'master@ascensorestzion.com');
        await p.goto(`${BASE}/admin/usuarios`, { waitUntil: 'domcontentloaded' });
        await p.waitForTimeout(2500);

        // Fila de un técnico → su ficha (el ojo abre un modal rápido, no la ficha).
        const fila = p.locator('tr', { has: p.locator('text=/t[eé]cnico/i') }).first();
        const ficha = fila.locator('a[href*="/admin/usuarios/"]').first();

        if (!(await ficha.count())) {
            console.log(`⚠ [${suffix}] no hay técnicos en /admin/usuarios`);
            await ctx.close();
            continue;
        }

        await ficha.click();
        await p.waitForSelector('.jor-card', { timeout: 10000 });
        await p.waitForTimeout(2000);
        await shot(p, `tecnico-jornada-y-excepciones-${suffix}`);

        // El formulario de excepción desplegado.
        const anadir = p.locator('.jor-block__head .jor-btn--sm').first();
        if (await anadir.count()) {
            await anadir.click();
            await p.waitForTimeout(500);
            await shot(p, `tecnico-form-excepcion-${suffix}`);
        }

        await ctx.close();
    }

    // Modal de días no laborables, desde el cronograma.
    const ctx = await browser.newContext(DESKTOP);
    const p = await ctx.newPage();
    watch(p, 'coord');

    await login(p, 'coordinador@ascensorestzion.com');
    await p.goto(`${BASE}/cronograma`, { waitUntil: 'domcontentloaded' });
    await p.waitForTimeout(4000);
    await shot(p, 'coord-boton-dias-no-laborables');

    await p.locator('.btn-holidays').click();
    await p.waitForSelector('.hol-modal', { timeout: 8000 });
    await p.waitForTimeout(1800);
    await shot(p, 'coord-modal-dias-no-laborables');

    await ctx.close();
    await browser.close();
    console.log(errors.length ? '\n⚠ ' + errors.join('\n  ') : '\n✓ sin errores de consola');
})();
