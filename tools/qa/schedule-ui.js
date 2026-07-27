#!/usr/bin/env node
/**
 * QA visual del cronograma (/cronograma) — vistas semana/mes/día en móvil y escritorio,
 * más el modal de creación y el drawer de detalle.
 *
 * Uso:
 *   1. php artisan serve, BD con visitas sembradas, npm run build y SIN public/hot.
 *   2. node tools/qa/schedule-ui.js
 *
 * Variables: QA_BASE_URL, QA_CHROME_PATH, QA_PASSWORD, QA_OUT.
 */
const path = require('path');
const fs = require('fs');
const { chromium } = require('playwright-core');

const BASE = process.env.QA_BASE_URL || 'http://127.0.0.1:8000';
const CHROME = process.env.QA_CHROME_PATH || '/usr/bin/google-chrome-stable';
const PASSWORD = process.env.QA_PASSWORD || 'password';
const OUT = process.env.QA_OUT || path.join(__dirname, 'output');
const EMAIL = process.env.QA_EMAIL || 'master@ascensorestzion.com';

const VIEWPORTS = {
    desktop: { viewport: { width: 1366, height: 900 } },
    mobile: { viewport: { width: 390, height: 844 }, deviceScaleFactor: 2, isMobile: true, hasTouch: true },
};

async function login(page) {
    await page.goto(`${BASE}/login`, { waitUntil: 'domcontentloaded' });
    await page.waitForSelector('#email', { timeout: 15000 });
    await page.fill('#email', EMAIL);
    await page.fill('#password', PASSWORD);
    await page.click('.btn-submit');
    await page.waitForURL((u) => !u.pathname.endsWith('/login'), { timeout: 20000 });
    await page.waitForTimeout(1200);
}

/**
 * Cambia de vista (Mes / Semana / Día). Los botones son `.vuecal__view-btn`;
 * con `.vuecal__menu li` el selector no encontraba nada y las capturas de mes y
 * día salían siendo la de semana sin avisar.
 */
async function switchView(page, label) {
    const item = page.locator('.vuecal__view-btn', { hasText: new RegExp(`^${label}$`, 'i') });
    if (!(await item.count())) {
        console.log(`⚠ no encontré la pestaña "${label}"`);
        return;
    }
    await item.first().click();
    await page.waitForTimeout(1800);
}

const errors = [];

(async () => {
    fs.mkdirSync(OUT, { recursive: true });

    const browser = await chromium.launch({
        executablePath: CHROME,
        headless: true,
        args: ['--no-sandbox', '--disable-dev-shm-usage'],
    });

    for (const [vpName, vp] of Object.entries(VIEWPORTS)) {
        const ctx = await browser.newContext(vp);
        const page = await ctx.newPage();

        page.on('console', (m) => {
            if (m.type() === 'error') errors.push(`[${vpName}] ${m.text().slice(0, 200)}`);
        });
        page.on('pageerror', (e) => errors.push(`[${vpName}] PAGEERROR ${e.message.slice(0, 200)}`));

        await login(page);

        await page.goto(`${BASE}/cronograma`, { waitUntil: 'domcontentloaded' });
        await page.waitForTimeout(3000);

        const shot = async (name, full = false) => {
            const file = path.join(OUT, `schedule-${name}-${vpName}.png`);
            await page.screenshot({ path: file, fullPage: full });
            console.log(`✓ ${path.basename(file)}`);
        };

        await shot('semana');
        await shot('semana-full', true);

        await switchView(page, 'Mes');
        await shot('mes');

        await switchView(page, 'Día');
        await shot('dia');

        await switchView(page, 'Semana');

        // Modal de creación
        const createBtn = page.locator('.btn-create');
        if (await createBtn.count()) {
            await createBtn.first().click();
            await page.waitForTimeout(900);
            await shot('modal');
            const cancel = page.locator('.visit-btn-ghost');
            if (await cancel.count()) await cancel.first().click();
            await page.waitForTimeout(500);
        }

        // Drawer de detalle: clic en el primer evento
        const ev = page.locator('.vuecal__event').first();
        if (await ev.count()) {
            await ev.click({ force: true });
            await page.waitForTimeout(900);
            await shot('drawer');
        }

        await ctx.close();
    }

    await browser.close();

    if (errors.length) {
        console.log(`\n── ${errors.length} errores de consola ──`);
        [...new Set(errors)].slice(0, 20).forEach((e) => console.log('   ' + e));
    } else {
        console.log('\nSin errores de consola.');
    }
    console.log(`Capturas en: ${OUT}`);
})().catch((e) => { console.error('FATAL', e); process.exit(2); });
