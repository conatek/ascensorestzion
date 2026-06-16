#!/usr/bin/env node
/**
 * UI test — Recuperación de contraseña (pending-task #2) + fix #3 (login).
 *
 * Rutas guest (sin login). Usa interceptación de red para ser determinista
 * (no depende de SMTP/Mailtrap).
 *
 * Requisitos: php artisan serve en :8000, build vigente (sin public/hot), chrome.
 */
const path = require('path');
const { chromium } = require('playwright-core');

const BASE = process.env.QA_BASE_URL || 'http://127.0.0.1:8000';
const CHROME = process.env.QA_CHROME_PATH || '/usr/bin/google-chrome-stable';
const OUT = path.join(__dirname, 'output');

function out(obj) { console.log('RESULT ' + JSON.stringify(obj)); }
const json = (status, body) => ({ status, contentType: 'application/json', body: JSON.stringify(body) });

(async () => {
    const browser = await chromium.launch({
        executablePath: CHROME, headless: true,
        args: ['--no-sandbox', '--disable-dev-shm-usage'],
    });
    const ctx = await browser.newContext({ viewport: { width: 420, height: 900 }, deviceScaleFactor: 2 });
    const page = await ctx.newPage();
    const res = {};

    try {
        // ── Login: enlace de recuperación presente, registro ausente ──
        await page.goto(`${BASE}/login`, { waitUntil: 'domcontentloaded' });
        await page.waitForSelector('.btn-submit', { timeout: 10000 });
        await page.waitForTimeout(400);
        const bodyHtml = await page.content();
        res.login = {
            hasForgotLink: /Olvidaste tu contrase/i.test(bodyHtml),
            // El enlace real era el texto "Regístrate aquí" → /register (no confundir
            // con build/registerSW.js de la PWA).
            hasRegisterLink: /reg[ií]strate/i.test(bodyHtml) || /to="\/register"/i.test(bodyHtml),
        };

        // ── Forgot: render + submit (intercept 200) → pantalla de éxito ──
        await page.route('**/api/forgot-password', (r) => r.fulfill(json(200, { message: 'Si el correo está registrado, te enviamos un enlace.' })));
        await page.goto(`${BASE}/recuperar-contrasena`, { waitUntil: 'domcontentloaded' });
        await page.waitForSelector('.pw-card', { timeout: 10000 });
        const forgotTitle = (await page.locator('.pw-title').textContent())?.trim();
        await page.fill('.pw-field input', 'alguien@correo.com');
        await page.click('.pw-btn');
        await page.waitForTimeout(800);
        const forgotSuccess = await page.locator('.pw-success').count();
        await page.screenshot({ path: path.join(OUT, 'recuperar-contrasena.png'), fullPage: true });

        res.forgot = { title: forgotTitle, successShown: forgotSuccess === 1 };

        // ── Reset: error 422 (token inválido) muestra banner ──
        await page.route('**/api/reset-password', (r) => r.fulfill(json(422, {
            message: 'El enlace es inválido o expiró. Solicita uno nuevo.',
            errors: { email: ['El enlace es inválido o expiró. Solicita uno nuevo.'] },
        })));
        await page.goto(`${BASE}/restablecer/tok123?email=alguien@correo.com`, { waitUntil: 'domcontentloaded' });
        await page.waitForSelector('.pw-card', { timeout: 10000 });
        const showsEmail = (await page.locator('.pw-subtitle').textContent())?.includes('alguien@correo.com');
        await page.locator('.pw-field input').nth(0).fill('nuevaclave123');
        await page.locator('.pw-field input').nth(1).fill('nuevaclave123');
        await page.click('.pw-btn');
        await page.waitForTimeout(800);
        const bannerErr = (await page.locator('.pw-banner-err').textContent().catch(() => ''))?.trim();
        await page.screenshot({ path: path.join(OUT, 'restablecer-error.png'), fullPage: true });

        // ── Reset: éxito (intercept 200) → swal → redirige a /login ──
        await page.unroute('**/api/reset-password');
        await page.route('**/api/reset-password', (r) => r.fulfill(json(200, { message: 'Contraseña restablecida. Ya puedes iniciar sesión.' })));
        await page.goto(`${BASE}/restablecer/tok123?email=alguien@correo.com`, { waitUntil: 'domcontentloaded' });
        await page.waitForSelector('.pw-card', { timeout: 10000 });
        await page.locator('.pw-field input').nth(0).fill('nuevaclave123');
        await page.locator('.pw-field input').nth(1).fill('nuevaclave123');
        await page.click('.pw-btn');
        await page.waitForSelector('.swal2-confirm', { timeout: 5000 });
        await page.click('.swal2-confirm');
        await page.waitForTimeout(1000);
        const finalPath = new URL(page.url()).pathname;

        res.reset = {
            showsEmail: !!showsEmail,
            bannerErrOk: /inválido o expiró/i.test(bannerErr || ''),
            redirectedToLogin: finalPath === '/login',
        };

        const ok = res.login.hasForgotLink && !res.login.hasRegisterLink
            && res.forgot.successShown && res.reset.showsEmail
            && res.reset.bannerErrOk && res.reset.redirectedToLogin;
        out({ ok, res });
    } catch (e) {
        out({ ok: false, error: e.message.split('\n')[0], res });
    } finally {
        await browser.close();
    }
})();
