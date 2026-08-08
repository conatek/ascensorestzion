#!/usr/bin/env node
/**
 * QA visual de la fase 2 del cronograma: la agenda del técnico (/tech/agenda y el
 * detalle de visita) y el cronograma del portal del cliente (/portal/cronograma,
 * lista y calendario), más la tarjeta "Próxima visita" del dashboard.
 *
 * Uso:
 *   1. php artisan serve, BD con visitas de esta semana, npm run build y SIN public/hot.
 *   2. node tools/qa/schedule-phase2-ui.js
 *
 * Variables: QA_BASE_URL, QA_CHROME_PATH, QA_PASSWORD, QA_OUT.
 */
const path = require('path');
const fs = require('fs');
const { chromium } = require('playwright-core');

const BASE = process.env.QA_BASE_URL || 'http://127.0.0.1:8000';
const CHROME = process.env.QA_CHROME_PATH || '/usr/bin/google-chrome-stable';
const PASSWORD = process.env.QA_PASSWORD || 'password';
const OUT = process.env.QA_OUT || path.join(__dirname, 'output-phase2');

const TECH_EMAIL = process.env.QA_TECH_EMAIL || 'tecnico1@ascensorestzion.com';
const CLIENT_EMAIL = process.env.QA_CLIENT_EMAIL || 'admin@ccoviedo.com';

const MOBILE = { viewport: { width: 390, height: 844 }, deviceScaleFactor: 2, isMobile: true, hasTouch: true };
const DESKTOP = { viewport: { width: 1366, height: 900 } };

const errors = [];

async function login(page, email) {
    await page.goto(`${BASE}/login`, { waitUntil: 'domcontentloaded' });
    await page.waitForSelector('#email', { timeout: 15000 });
    await page.fill('#email', email);
    await page.fill('#password', PASSWORD);
    await page.click('.btn-submit');
    await page.waitForURL((u) => !u.pathname.endsWith('/login'), { timeout: 20000 });
    await page.waitForTimeout(1200);
}

async function shot(page, name) {
    await page.screenshot({ path: path.join(OUT, `${name}.png`), fullPage: true });
    console.log(`✓ ${name}`);
}

function watch(page, tag) {
    page.on('console', (m) => {
        if (m.type() === 'error') errors.push(`[${tag}] ${m.text().slice(0, 200)}`);
    });
    page.on('pageerror', (e) => errors.push(`[${tag}] PAGEERROR ${e.message.slice(0, 200)}`));
}

(async () => {
    fs.mkdirSync(OUT, { recursive: true });

    const browser = await chromium.launch({
        executablePath: CHROME,
        headless: true,
        args: ['--no-sandbox', '--disable-dev-shm-usage'],
    });

    // ── Técnico (móvil) ──
    const techCtx = await browser.newContext(MOBILE);
    const tech = await techCtx.newPage();
    watch(tech, 'tech');

    await login(tech, TECH_EMAIL);
    await tech.goto(`${BASE}/tech/agenda`, { waitUntil: 'domcontentloaded' });
    await tech.waitForTimeout(2000);
    await shot(tech, 'tech-agenda-hoy');

    // Semana siguiente: es donde están las visitas programadas.
    const next = tech.locator('.agenda-nav-btn').last();
    await next.click();
    await tech.waitForTimeout(1500);
    await shot(tech, 'tech-agenda-semana-siguiente');

    // Primer día con visitas de esa semana.
    const withVisits = tech.locator('.agenda-day', { has: tech.locator('.agenda-day__dot:not(.is-empty)') });
    if (await withVisits.count()) {
        await withVisits.first().click();
        await tech.waitForTimeout(800);
        await shot(tech, 'tech-agenda-dia-con-visitas');

        await tech.locator('.agenda-item').first().click();
        await tech.waitForTimeout(2500);
        await shot(tech, 'tech-visita-detalle');
    } else {
        console.log('⚠ ningún día de la semana siguiente tiene visitas');
    }

    await techCtx.close();

    // ── Portal del cliente ──
    for (const [vpName, vp] of Object.entries({ desktop: DESKTOP, mobile: MOBILE })) {
        const ctx = await browser.newContext(vp);
        const page = await ctx.newPage();
        watch(page, `portal-${vpName}`);

        await login(page, CLIENT_EMAIL);
        await page.waitForTimeout(2000);
        await shot(page, `portal-dashboard-${vpName}`);

        await page.goto(`${BASE}/portal/cronograma`, { waitUntil: 'domcontentloaded' });
        await page.waitForTimeout(2000);
        await shot(page, `portal-cronograma-lista-${vpName}`);

        const calendar = page.locator('.view-toggle__btn', { hasText: /calendario/i });
        if (await calendar.count()) {
            await calendar.first().click();
            await page.waitForTimeout(1800);
            await shot(page, `portal-cronograma-calendario-${vpName}`);
        }

        await ctx.close();
    }

    await browser.close();

    if (errors.length) {
        console.log('\n⚠ Errores de consola:');
        errors.forEach((e) => console.log('  ' + e));
    } else {
        console.log('\n✓ Sin errores de consola');
    }
    console.log(`\nCapturas en ${OUT}`);
})();
