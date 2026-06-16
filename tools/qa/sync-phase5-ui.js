#!/usr/bin/env node
/**
 * UI test — Fase 5 (vista de pendientes/errores del técnico).
 *
 * Inyecta en IndexedDB un check-in con error + un reporte pendiente + un adjunto
 * pendiente, abre /tech/pendientes y verifica render/contadores. Luego prueba
 * discardPending vía API del offlineManager. Limpia las colas al final.
 *
 * Requisitos: php artisan serve en :8000, build vigente (sin public/hot), chrome.
 */
const path = require('path');
const { chromium } = require('playwright-core');

const BASE = process.env.QA_BASE_URL || 'http://127.0.0.1:8000';
const CHROME = process.env.QA_CHROME_PATH || '/usr/bin/google-chrome-stable';
const PASSWORD = process.env.QA_PASSWORD || 'password';
const EMAIL = process.env.QA_EMAIL || 'tecnico1@ascensorestzion.com';
const OUT = path.join(__dirname, 'output');

function out(obj) { console.log('RESULT ' + JSON.stringify(obj)); }

(async () => {
    const browser = await chromium.launch({
        executablePath: CHROME, headless: true,
        args: ['--no-sandbox', '--disable-dev-shm-usage'],
    });
    const ctx = await browser.newContext({ viewport: { width: 390, height: 844 }, deviceScaleFactor: 2 });
    const page = await ctx.newPage();
    // Ruido de entorno irrelevante para la vista: SW scope (PWA), WebSocket de
    // Reverb apagado, y el 422/sync del auto-sync que intenta subir lo inyectado.
    const BENIGN = [/scope.*not under the max scope/i, /websocket connection/i, /localhost:8080/i, /status of 422/i, /Failed to load resource/i];
    const logs = [];
    page.on('console', (m) => {
        if (m.type() !== 'error') return;
        const t = m.text();
        if (!BENIGN.some(rx => rx.test(t))) logs.push(t);
    });

    try {
        // Login
        await page.goto(`${BASE}/login`, { waitUntil: 'domcontentloaded' });
        await page.waitForSelector('#email', { timeout: 15000 });
        await page.fill('#email', EMAIL);
        await page.fill('#password', PASSWORD);
        await page.click('.btn-submit');
        await page.waitForURL((u) => !u.pathname.endsWith('/login'), { timeout: 20000 });
        await page.waitForTimeout(1200);

        // Inyectar items (clear primero para aislar)
        await page.evaluate(async () => {
            const openDb = () => new Promise((res, rej) => {
                const r = indexedDB.open('tzion-offline');
                r.onsuccess = () => res(r.result); r.onerror = () => rej(r.error);
            });
            const db = await openDb();
            const clear = (s) => new Promise((res) => { const rq = db.transaction(s, 'readwrite').objectStore(s).clear(); rq.onsuccess = () => res(); });
            const add = (s, o) => new Promise((res, rej) => { const rq = db.transaction(s, 'readwrite').objectStore(s).add(o); rq.onsuccess = () => res(); rq.onerror = () => rej(rq.error); });
            await clear('checkins-pending'); await clear('reports-pending-sync'); await clear('attachments-pending');

            await add('checkins-pending', {
                client_uuid: 'ph5-checkin', equipment_code: 'TZ-2026-0001', method: 'qr',
                status: 'error', attempts: 3, lastError: 'Equipo retirado (422)', nextAttemptAt: 0, queuedAt: Date.now() - 7200000,
            });
            await add('reports-pending-sync', {
                client_uuid: 'ph5-report', payload: { report_type: 'RSTC', equipment_id: 1, service_date: '2026-06-15' },
                techSignature: null, custSignature: null,
                status: 'pending', attempts: 0, lastError: null, nextAttemptAt: 0, queuedAt: Date.now() - 600000,
            });
            const blob = new Blob([new Uint8Array([1, 2, 3])], { type: 'image/jpeg' });
            await add('attachments-pending', {
                report_client_uuid: 'ph5-report', report_id: null, client_uuid: 'ph5-att', blob,
                type: 'otro', media_type: 'foto', condition_key: 'iluminacion_cabina',
                status: 'pending', attempts: 0, lastError: null, nextAttemptAt: 0, queuedAt: Date.now() - 300000,
            });
            db.close();
            // refrescar contadores reactivos del offlineManager
            window.dispatchEvent(new Event('focus'));
        });

        // Abrir la vista de pendientes
        await page.goto(`${BASE}/tech/pendientes`, { waitUntil: 'domcontentloaded' });
        await page.waitForSelector('.tech-pending', { timeout: 10000 });
        await page.waitForTimeout(1200);

        const itemCount = await page.locator('.item').count();
        const errorItems = await page.locator('.item--error').count();
        const pendingNum = (await page.locator('.count .count__num').first().textContent())?.trim();
        const hasErrorCount = await page.locator('.count--error').count();
        const errorText = (await page.locator('.item--error .item__error').first().textContent().catch(() => ''))?.trim();
        const retryBtns = await page.locator('.act--retry').count();

        await page.screenshot({ path: path.join(OUT, 'tech-pendientes-mobile.png'), fullPage: true });

        // Probar "Descartar" por la UI: click → confirmar en el modal swal → el item desaparece
        await page.locator('.item--error .act--discard').click();
        await page.waitForSelector('.swal2-confirm', { timeout: 5000 });
        await page.locator('.swal2-confirm').click();
        await page.waitForTimeout(800);
        const itemCountAfterDiscard = await page.locator('.item').count();

        const ok = itemCount === 3 && errorItems === 1 && pendingNum === '2'
            && hasErrorCount === 1 && retryBtns === 1 && itemCountAfterDiscard === 2 && logs.length === 0;
        out({ ok, itemCount, errorItems, pendingNum, hasErrorCount, errorText, retryBtns, itemCountAfterDiscard, jsErrors: logs.slice(-5) });

        // Limpiar colas
        await page.evaluate(async () => {
            const openDb = () => new Promise((res) => { const r = indexedDB.open('tzion-offline'); r.onsuccess = () => res(r.result); });
            const db = await openDb();
            for (const s of ['checkins-pending', 'reports-pending-sync', 'attachments-pending']) {
                await new Promise((res) => { const rq = db.transaction(s, 'readwrite').objectStore(s).clear(); rq.onsuccess = () => res(); });
            }
            db.close();
        });
    } catch (e) {
        out({ ok: false, error: e.message.split('\n')[0], jsErrors: logs.slice(-5) });
    } finally {
        await browser.close();
    }
})();
