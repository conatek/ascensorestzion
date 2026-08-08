#!/usr/bin/env node
/**
 * QA visual de la fase 4: el cliente pide reprogramar desde el portal y
 * coordinación resuelve desde la bandeja del cronograma.
 *
 * Uso: php artisan serve --port=8011, npm run build (y sin public/hot), y
 *      QA_BASE_URL=http://127.0.0.1:8011 node tools/qa/schedule-phase4-ui.js
 *
 * Necesita al menos una visita programada del cliente admin@ccoviedo.com a más
 * de 24 h vista; si no, el botón "Reprogramar" no aparece y el script lo dice.
 */
const path = require('path');
const fs = require('fs');
const { chromium } = require('playwright-core');

const BASE = process.env.QA_BASE_URL || 'http://127.0.0.1:8011';
const CHROME = process.env.QA_CHROME_PATH || '/usr/bin/google-chrome-stable';
const PASSWORD = process.env.QA_PASSWORD || 'password';
const OUT = process.env.QA_OUT || path.join(__dirname, 'output-phase4');

const MOBILE = { viewport: { width: 390, height: 844 }, deviceScaleFactor: 2, isMobile: true, hasTouch: true };
const DESKTOP = { viewport: { width: 1366, height: 900 } };
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

/** El flujo del cliente: lista → modal paso 1 → paso 2. */
async function portalFlow(browser, viewport, suffix) {
    const ctx = await browser.newContext(viewport);
    const p = await ctx.newPage();
    watch(p, `portal-${suffix}`);

    await login(p, 'admin@ccoviedo.com');
    await p.goto(`${BASE}/portal/cronograma`, { waitUntil: 'domcontentloaded' });
    await p.waitForTimeout(2500);
    await shot(p, `portal-cronograma-${suffix}`);

    const boton = p.locator('.sched-reschedule').first();

    if (!(await boton.count())) {
        console.log(`⚠ [${suffix}] sin visitas reprogramables: carga una a más de 24 h vista`);
        await ctx.close();
        return;
    }

    await boton.click();
    await p.waitForSelector('.resch-modal', { timeout: 8000 });
    await p.waitForTimeout(2000);
    await shot(p, `portal-modal-slots-${suffix}`);

    // Primer horario libre y paso 2.
    const chip = p.locator('.slot-chip').first();
    if (await chip.count()) {
        await chip.click();
        await p.waitForTimeout(400);
        await p.locator('.resch-btn--primary').click();
        await p.waitForTimeout(700);
        await shot(p, `portal-modal-confirmar-${suffix}`);
    } else {
        console.log(`⚠ [${suffix}] el técnico no tiene ningún hueco en el próximo mes`);
    }

    await ctx.close();
}

(async () => {
    fs.mkdirSync(OUT, { recursive: true });
    const browser = await chromium.launch({
        executablePath: CHROME,
        headless: true,
        args: ['--no-sandbox', '--disable-dev-shm-usage'],
    });

    await portalFlow(browser, DESKTOP, 'desktop');
    await portalFlow(browser, MOBILE, 'mobile');

    // Coordinación: banner + bandeja. El enlace del correo trae ?solicitudes=1.
    let ctx = await browser.newContext(DESKTOP);
    let p = await ctx.newPage();
    watch(p, 'coord');
    await login(p, 'coordinador@ascensorestzion.com');
    await p.goto(`${BASE}/cronograma?solicitudes=1`, { waitUntil: 'domcontentloaded' });
    await p.waitForTimeout(4000);
    await shot(p, 'coord-banner-y-bandeja');

    if (!(await p.locator('.inbox-card').count())) {
        console.log('⚠ no hay solicitudes pendientes: manda una desde el portal primero');
    }

    // Drawer de una visita en ámbar: línea de tiempo y botones de decisión. La
    // solicitud suele ser de la semana que viene (24 h de antelación mínima), así
    // que hay que avanzar el calendario para encontrarla.
    const ambar = p.locator('.vuecal__event.tz-ev-status-reprogramacion_solicitada');
    for (let semana = 0; semana < 4 && !(await ambar.count()); semana++) {
        await p.locator('.vuecal__arrow--next').first().click();
        await p.waitForTimeout(2500);
    }

    if (await ambar.count()) {
        await ambar.first().click();
        await p.waitForTimeout(1800);
        await shot(p, 'coord-drawer-linea-de-tiempo');
    } else {
        console.log('⚠ ninguna visita ámbar en las próximas cuatro semanas');
    }
    await ctx.close();

    // Que master también entre a la bandeja (super comparte manage_schedule).
    ctx = await browser.newContext(DESKTOP);
    p = await ctx.newPage();
    watch(p, 'master');
    await login(p, 'master@ascensorestzion.com');
    await p.goto(`${BASE}/cronograma?solicitudes=1`, { waitUntil: 'domcontentloaded' });
    await p.waitForTimeout(4000);
    await shot(p, 'master-bandeja');
    await ctx.close();

    await browser.close();
    console.log(errors.length ? '\n⚠ ' + errors.join('\n  ') : '\n✓ sin errores de consola');
})();
