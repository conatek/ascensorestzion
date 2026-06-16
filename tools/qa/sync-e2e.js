#!/usr/bin/env node
/**
 * E2E de sincronización offline — round-trip real con context.setOffline().
 *
 * A diferencia de los harness "motor" (que inyectan en IndexedDB), aquí se opera
 * la UI real del técnico estando offline y se verifica que al reconectar sube solo.
 *
 * Escenarios:
 *   A) Check-in offline por la UI → reconectar → se sincroniza (queda en BD).
 *   B) Robustez (Fase 4): dos check-ins offline, uno con código inválido y uno
 *      válido → al reconectar el inválido se marca "error" (404 permanente) y NO
 *      bloquea al válido, que sí sube.
 *
 * Requisitos: php artisan serve en :8000, build vigente (sin public/hot), chrome.
 * Salida: una línea `RESULT {...}` con ok global + detalle y `syncedUuids` (para
 * que el wrapper limpie en BD).
 */
const { chromium } = require('playwright-core');

const BASE = process.env.QA_BASE_URL || 'http://127.0.0.1:8000';
const CHROME = process.env.QA_CHROME_PATH || '/usr/bin/google-chrome-stable';
const PASSWORD = process.env.QA_PASSWORD || 'password';
const EMAIL = process.env.QA_EMAIL || 'tecnico1@ascensorestzion.com';
const GOOD_CODE = process.env.GOOD_CODE || 'TZ-2026-0001';
const BAD_CODE = 'NO-EXISTE-999';

function out(obj) { console.log('RESULT ' + JSON.stringify(obj)); }

// Helpers que corren EN la página (IndexedDB)
const PAGE_HELPERS = `
window.__q = {
  open: () => new Promise((res, rej) => { const r = indexedDB.open('tzion-offline'); r.onsuccess = () => res(r.result); r.onerror = () => rej(r.error); }),
  checkins: async () => { const db = await window.__q.open(); const out = await new Promise((res) => { const rq = db.transaction('checkins-pending','readonly').objectStore('checkins-pending').getAll(); rq.onsuccess = () => res(rq.result); }); db.close(); return out; },
  clearAll: async () => { const db = await window.__q.open(); for (const s of ['checkins-pending','reports-pending-sync','attachments-pending']) { await new Promise((res) => { const rq = db.transaction(s,'readwrite').objectStore(s).clear(); rq.onsuccess = () => res(); }); } db.close(); },
};
`;

async function submitCheckin(page, code) {
    await page.getByText('Código Manual').click();
    await page.fill('.manual-input', code);
    await page.click('.manual-submit-btn');
    await page.waitForTimeout(600);
}

async function pollCheckins(page, predicate, timeoutMs = 25000) {
    const deadline = Date.now() + timeoutMs;
    let items = null;
    while (Date.now() < deadline) {
        items = await page.evaluate(() => window.__q.checkins());
        if (predicate(items)) return items;
        await page.waitForTimeout(1200);
        // re-disparar por si el primer ciclo no alcanzó
        await page.evaluate(() => window.dispatchEvent(new Event('online')));
    }
    return items;
}

(async () => {
    const browser = await chromium.launch({
        executablePath: CHROME, headless: true,
        args: ['--no-sandbox', '--disable-dev-shm-usage'],
    });
    const ctx = await browser.newContext({
        viewport: { width: 390, height: 844 }, deviceScaleFactor: 2,
        geolocation: { latitude: 6.1934, longitude: -75.5586, accuracy: 20 },
        permissions: ['geolocation'],
    });
    const page = await ctx.newPage();
    await page.addInitScript(PAGE_HELPERS);
    const results = {};
    const syncedUuids = [];

    try {
        // Login
        await page.goto(`${BASE}/login`, { waitUntil: 'domcontentloaded' });
        await page.waitForSelector('#email', { timeout: 15000 });
        await page.fill('#email', EMAIL);
        await page.fill('#password', PASSWORD);
        await page.click('.btn-submit');
        await page.waitForURL((u) => !u.pathname.endsWith('/login'), { timeout: 20000 });
        await page.waitForTimeout(1000);
        await page.evaluate(() => window.__q.clearAll());

        // ── Escenario A: check-in offline → reconectar → sincroniza ──
        await page.goto(`${BASE}/tech/checkin`, { waitUntil: 'domcontentloaded' });
        await page.waitForSelector('.checkin-tabs', { timeout: 10000 });
        await ctx.setOffline(true);
        await page.waitForTimeout(400);
        await submitCheckin(page, GOOD_CODE);

        const queuedA = await page.evaluate(() => window.__q.checkins());
        const aUuid = queuedA[0]?.client_uuid;
        const aQueuedOk = queuedA.length === 1 && !!aUuid;

        await ctx.setOffline(false);
        await page.waitForTimeout(400);
        const afterA = await pollCheckins(page, (items) => items.length === 0);
        const aSyncedOk = afterA.length === 0;
        if (aUuid) syncedUuids.push(aUuid);
        results.A = { aQueuedOk, aSyncedOk, queued: queuedA.length, after: afterA.length };

        // ── Escenario B: robustez — inválido marca error, válido sube ──
        await page.evaluate(() => window.__q.clearAll());
        await page.goto(`${BASE}/tech/checkin`, { waitUntil: 'domcontentloaded' });
        await page.waitForSelector('.checkin-tabs', { timeout: 10000 });
        await ctx.setOffline(true);
        await page.waitForTimeout(400);
        await submitCheckin(page, BAD_CODE);
        await submitCheckin(page, GOOD_CODE);

        const queuedB = await page.evaluate(() => window.__q.checkins());
        const bQueuedOk = queuedB.length === 2;
        const goodUuidB = queuedB.find((c) => c.equipment_code === GOOD_CODE)?.client_uuid;

        await ctx.setOffline(false);
        await page.waitForTimeout(400);
        // Esperar a que el válido suba (queda solo el inválido marcado error)
        const afterB = await pollCheckins(page, (items) => items.length === 1 && items[0].status === 'error');
        const erroredItem = afterB.find((c) => c.equipment_code === BAD_CODE);
        const bOk = afterB.length === 1
            && erroredItem?.status === 'error'
            && !afterB.some((c) => c.equipment_code === GOOD_CODE); // el válido ya no está
        if (goodUuidB) syncedUuids.push(goodUuidB);
        results.B = {
            bQueuedOk, bOk, after: afterB.length,
            erroredStatus: erroredItem?.status, erroredMsg: erroredItem?.lastError,
        };

        // limpiar colas del dispositivo (el item con error)
        await page.evaluate(() => window.__q.clearAll());

        const ok = results.A.aQueuedOk && results.A.aSyncedOk
            && results.B.bQueuedOk && results.B.bOk;
        out({ ok, results, syncedUuids });
    } catch (e) {
        out({ ok: false, error: e.message.split('\n')[0], results, syncedUuids });
    } finally {
        await browser.close();
    }
})();
