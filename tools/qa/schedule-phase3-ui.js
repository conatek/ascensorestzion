#!/usr/bin/env node
/**
 * QA visual de la fase 3: la tarjeta de recordatorios en el perfil (técnico y
 * portal) y el historial de recordatorios en el drawer del cronograma.
 *
 * Uso: php artisan serve --port=8011, npm run build, y
 *      QA_BASE_URL=http://127.0.0.1:8011 node tools/qa/schedule-phase3-ui.js
 */
const path = require('path');
const fs = require('fs');
const { chromium } = require('playwright-core');

const BASE = process.env.QA_BASE_URL || 'http://127.0.0.1:8011';
const PASSWORD = process.env.QA_PASSWORD || 'password';
const OUT = process.env.QA_OUT || path.join(__dirname, 'output-phase3');

const MOBILE = { viewport: { width: 390, height: 844 }, deviceScaleFactor: 2, isMobile: true, hasTouch: true };
const DESKTOP = { viewport: { width: 1366, height: 900 } };
const errors = [];

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
    const browser = await chromium.launch({ executablePath: '/usr/bin/google-chrome-stable', headless: true, args: ['--no-sandbox','--disable-dev-shm-usage'] });

    // Perfil del técnico (móvil)
    let ctx = await browser.newContext(MOBILE);
    let p = await ctx.newPage();
    p.on('pageerror', e => errors.push('[tech] ' + e.message.slice(0, 160)));
    await login(p, 'tecnico1@ascensorestzion.com');
    await p.goto(`${BASE}/tech/perfil`, { waitUntil: 'domcontentloaded' });
    await p.waitForTimeout(2500);
    await shot(p, 'tech-perfil-recordatorios');
    await ctx.close();

    // Perfil del cliente (escritorio)
    ctx = await browser.newContext(DESKTOP);
    p = await ctx.newPage();
    p.on('pageerror', e => errors.push('[portal] ' + e.message.slice(0, 160)));
    await login(p, 'admin@ccoviedo.com');
    await p.goto(`${BASE}/portal/perfil`, { waitUntil: 'domcontentloaded' });
    await p.waitForTimeout(2500);
    await shot(p, 'portal-perfil-recordatorios');
    await ctx.close();

    // Drawer del cronograma con el historial de recordatorios
    ctx = await browser.newContext(DESKTOP);
    p = await ctx.newPage();
    p.on('pageerror', e => errors.push('[coord] ' + e.message.slice(0, 160)));
    await login(p, 'coordinador@ascensorestzion.com');
    await p.goto(`${BASE}/cronograma`, { waitUntil: 'domcontentloaded' });
    await p.waitForTimeout(4000);
    // A la semana siguiente: las visitas con recordatorios vivos son las futuras.
    const siguiente = p.locator('.vuecal__arrow--next');
    if (await siguiente.count()) { await siguiente.first().click(); await p.waitForTimeout(2500); }
    const evento = p.locator('.vuecal__event').first();
    if (await evento.count()) {
        await evento.click();
        await p.waitForTimeout(1800);
        await shot(p, 'cronograma-drawer-recordatorios');
    } else {
        console.log('⚠ no hay visitas visibles en el calendario');
    }
    await ctx.close();

    await browser.close();
    console.log(errors.length ? '\n⚠ ' + errors.join('\n  ') : '\n✓ sin errores de consola');
})();
