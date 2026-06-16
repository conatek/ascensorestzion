#!/usr/bin/env node
/**
 * UI test — Mi Perfil (pending-task #1).
 *
 * master (layout admin): carga datos, error de contraseña actual incorrecta,
 *   y happy path (cambia teléfono → persiste → revierte al original).
 * technician (layout tech): la vista /tech/perfil renderiza.
 *
 * Requisitos: php artisan serve en :8000, build vigente (sin public/hot), chrome.
 */
const path = require('path');
const { chromium } = require('playwright-core');

const BASE = process.env.QA_BASE_URL || 'http://127.0.0.1:8000';
const CHROME = process.env.QA_CHROME_PATH || '/usr/bin/google-chrome-stable';
const PASSWORD = process.env.QA_PASSWORD || 'password';
const OUT = path.join(__dirname, 'output');

function out(obj) { console.log('RESULT ' + JSON.stringify(obj)); }

async function login(page, email) {
    await page.goto(`${BASE}/login`, { waitUntil: 'domcontentloaded' });
    await page.waitForSelector('#email', { timeout: 15000 });
    await page.fill('#email', email);
    await page.fill('#password', PASSWORD);
    await page.click('.btn-submit');
    await page.waitForURL((u) => !u.pathname.endsWith('/login'), { timeout: 20000 });
    await page.waitForTimeout(1000);
}

(async () => {
    const browser = await chromium.launch({
        executablePath: CHROME, headless: true,
        args: ['--no-sandbox', '--disable-dev-shm-usage'],
    });
    const res = {};
    try {
        // ── master: layout admin ──
        {
            const ctx = await browser.newContext({ viewport: { width: 1366, height: 900 } });
            const page = await ctx.newPage();
            await login(page, 'master@ascensorestzion.com');
            await page.goto(`${BASE}/perfil`, { waitUntil: 'domcontentloaded' });
            await page.waitForSelector('.profile-page', { timeout: 10000 });
            await page.waitForTimeout(600);

            const nameVal = await page.inputValue('input[type="text"]');
            const emailVal = await page.inputValue('input[type="email"]');
            const roleChip = (await page.locator('.role-chip').textContent())?.trim();

            // Error: contraseña actual incorrecta (no muta nada)
            const pwInputs = page.locator('input[type="password"]');
            await pwInputs.nth(0).fill('contraseña-incorrecta');
            await pwInputs.nth(1).fill('nuevaclave12345');
            await pwInputs.nth(2).fill('nuevaclave12345');
            await page.locator('.card', { hasText: 'Cambiar contraseña' }).locator('.btn-primary').click();
            await page.waitForTimeout(1500);
            const pwErr = (await page.locator('.card', { hasText: 'Cambiar contraseña' }).locator('.err').first().textContent().catch(() => ''))?.trim();

            await page.screenshot({ path: path.join(OUT, 'perfil-master-desktop.png'), fullPage: true });

            // Happy path: cambiar teléfono y revertir
            const phoneInput = page.locator('.field', { hasText: 'Teléfono' }).locator('input');
            const origPhone = await phoneInput.inputValue();
            const testPhone = '3009998877';
            await phoneInput.fill(testPhone);
            await page.locator('.card', { hasText: 'Datos de la cuenta' }).locator('.btn-primary').click();
            await page.waitForTimeout(1500);
            // recargar y verificar persistencia
            await page.reload({ waitUntil: 'domcontentloaded' });
            await page.waitForSelector('.profile-page', { timeout: 10000 });
            await page.waitForTimeout(800);
            const phoneAfter = await page.locator('.field', { hasText: 'Teléfono' }).locator('input').inputValue();
            // revertir
            await page.locator('.field', { hasText: 'Teléfono' }).locator('input').fill(origPhone);
            await page.locator('.card', { hasText: 'Datos de la cuenta' }).locator('.btn-primary').click();
            await page.waitForTimeout(1200);

            res.master = {
                nameVal, emailVal, roleChip,
                pwErrOk: /actual es incorrecta/i.test(pwErr || ''),
                phonePersisted: phoneAfter === testPhone,
                origPhone,
            };
            await ctx.close();
        }

        // ── technician: layout tech ──
        {
            const ctx = await browser.newContext({ viewport: { width: 390, height: 844 }, deviceScaleFactor: 2 });
            const page = await ctx.newPage();
            await login(page, 'tecnico1@ascensorestzion.com');
            await page.goto(`${BASE}/tech/perfil`, { waitUntil: 'domcontentloaded' });
            await page.waitForSelector('.profile-page', { timeout: 10000 });
            await page.waitForTimeout(600);
            const techName = await page.inputValue('input[type="text"]');
            const hasTechChrome = await page.locator('.tech-bottom-nav').count();
            await page.screenshot({ path: path.join(OUT, 'perfil-tecnico-mobile.png'), fullPage: true });
            res.technician = { rendered: !!techName, techName, hasTechChrome };
            await ctx.close();
        }

        const ok = res.master.nameVal && res.master.emailVal && res.master.pwErrOk
            && res.master.phonePersisted && res.technician.rendered && res.technician.hasTechChrome === 1;
        out({ ok, res });
    } catch (e) {
        out({ ok: false, error: e.message.split('\n')[0], res });
    } finally {
        await browser.close();
    }
})();
