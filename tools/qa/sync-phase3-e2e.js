#!/usr/bin/env node
/**
 * E2E del motor de sync — Fase 3 (adjuntos/fotos offline).
 *
 * Inyecta en IndexedDB (tzion-offline) un reporte pendiente + un adjunto enlazado
 * por report_client_uuid, dispara el evento `online` y deja que offlineManager →
 * syncEngine los suba al servidor con el token de la sesión. Verifica que las colas
 * quedan vacías. La verificación en BD (reporte creado + 1 adjunto) y la limpieza
 * la hace el wrapper en bash con tinker.
 *
 * Requisitos: php artisan serve en :8000, build vigente, chrome del sistema.
 *
 * Env: QA_BASE_URL, QA_CHROME_PATH, QA_PASSWORD, REPORT_UUID, ATT_UUID, EQUIPMENT_ID
 *
 * Imprime una línea JSON con el resultado: {"ok":bool,"queues":{...},"note":"..."}
 */
const { chromium } = require('playwright-core');

const BASE = process.env.QA_BASE_URL || 'http://127.0.0.1:8000';
const CHROME = process.env.QA_CHROME_PATH || '/usr/bin/google-chrome-stable';
const PASSWORD = process.env.QA_PASSWORD || 'password';
const EMAIL = process.env.QA_EMAIL || 'tecnico1@ascensorestzion.com';
const REPORT_UUID = process.env.REPORT_UUID;
const ATT_UUID = process.env.ATT_UUID;
const EQUIPMENT_ID = parseInt(process.env.EQUIPMENT_ID || '1', 10);

function out(obj) { console.log('RESULT ' + JSON.stringify(obj)); }

(async () => {
    const browser = await chromium.launch({
        executablePath: CHROME,
        headless: true,
        args: ['--no-sandbox', '--disable-dev-shm-usage'],
    });
    const ctx = await browser.newContext();
    const page = await ctx.newPage();
    const logs = [];
    page.on('console', (m) => logs.push(`[${m.type()}] ${m.text()}`));

    try {
        // 1) Login (deja el token en localStorage y carga el SPA → offlineManager)
        await page.goto(`${BASE}/login`, { waitUntil: 'domcontentloaded' });
        await page.waitForSelector('#email', { timeout: 15000 });
        await page.fill('#email', EMAIL);
        await page.fill('#password', PASSWORD);
        await page.click('.btn-submit');
        await page.waitForURL((u) => !u.pathname.endsWith('/login'), { timeout: 20000 });
        await page.waitForTimeout(1500);

        // 2) Inyectar reporte + adjunto en IndexedDB y disparar sync
        const injected = await page.evaluate(async ({ reportUuid, attUuid, equipmentId, attReportId }) => {
            const openDb = () => new Promise((res, rej) => {
                const r = indexedDB.open('tzion-offline');
                r.onsuccess = () => res(r.result);
                r.onerror = () => rej(r.error);
            });
            const db = await openDb();
            const addTo = (storeName, obj) => new Promise((res, rej) => {
                const tx = db.transaction(storeName, 'readwrite');
                const rq = tx.objectStore(storeName).add(obj);
                rq.onsuccess = () => res(rq.result);
                rq.onerror = () => rej(rq.error);
            });

            // Modo idempotencia: re-encolar SOLO el adjunto con report_id real ya puesto
            if (attReportId) {
                const blob2 = new Blob([new Uint8Array([255, 216, 255, 0, 1, 2, 3, 4])], { type: 'image/jpeg' });
                await addTo('attachments-pending', {
                    report_client_uuid: reportUuid,
                    report_id: attReportId,
                    client_uuid: attUuid,
                    blob: blob2,
                    type: 'otro', media_type: 'foto',
                    condition_key: 'iluminacion_cabina', activity_key: null, group_key: null,
                    filename: 'e2e-foto.jpg',
                    status: 'pending', attempts: 0, lastError: null, nextAttemptAt: 0,
                    queuedAt: Date.now(),
                });
                db.close();
                window.dispatchEvent(new Event('online'));
                return true;
            }

            // Reporte SIN firmas para aislar el camino de adjuntos (sin notificaciones)
            await addTo('reports-pending-sync', {
                client_uuid: reportUuid,
                payload: {
                    report_type: 'RSTC',
                    equipment_id: equipmentId,
                    service_date: '2026-06-15',
                },
                techSignature: null,
                custSignature: null,
                status: 'pending', attempts: 0, lastError: null, nextAttemptAt: 0,
                queuedAt: Date.now(),
            });

            // Adjunto enlazado al reporte por report_client_uuid (report_id se estampa
            // cuando el reporte se sincroniza)
            const blob = new Blob([new Uint8Array([255, 216, 255, 0, 1, 2, 3, 4])], { type: 'image/jpeg' });
            await addTo('attachments-pending', {
                report_client_uuid: reportUuid,
                report_id: null,
                client_uuid: attUuid,
                blob,
                type: 'otro',
                media_type: 'foto',
                condition_key: 'iluminacion_cabina',
                activity_key: null,
                group_key: null,
                filename: 'e2e-foto.jpg',
                status: 'pending', attempts: 0, lastError: null, nextAttemptAt: 0,
                queuedAt: Date.now(),
            });
            db.close();

            // Disparar el ciclo de sync (offlineManager escucha 'online')
            window.dispatchEvent(new Event('online'));
            return true;
        }, { reportUuid: REPORT_UUID, attUuid: ATT_UUID, equipmentId: EQUIPMENT_ID, attReportId: process.env.REPORT_ID ? parseInt(process.env.REPORT_ID, 10) : null });

        // 3) Poll hasta que las colas queden vacías (o timeout)
        const readQueues = () => page.evaluate(async () => {
            const openDb = () => new Promise((res, rej) => {
                const r = indexedDB.open('tzion-offline');
                r.onsuccess = () => res(r.result);
                r.onerror = () => rej(r.error);
            });
            const db = await openDb();
            const count = (s) => new Promise((res) => {
                const rq = db.transaction(s, 'readonly').objectStore(s).getAll();
                rq.onsuccess = () => res(rq.result);
            });
            const reports = await count('reports-pending-sync');
            const attachments = await count('attachments-pending');
            db.close();
            return {
                reports: reports.length,
                attachments: attachments.length,
                reportErr: reports.filter(r => r.status === 'error').map(r => r.lastError),
                attErr: attachments.filter(a => a.status === 'error').map(a => a.lastError),
                attPendingNoId: attachments.filter(a => !a.report_id).length,
            };
        });

        let q = null;
        const deadline = Date.now() + 25000;
        while (Date.now() < deadline) {
            await page.waitForTimeout(1500);
            q = await readQueues();
            if (q.reports === 0 && q.attachments === 0) break;
            // re-disparar por si el primer ciclo corrió antes de tener report_id
            await page.evaluate(() => window.dispatchEvent(new Event('online')));
        }

        const ok = q && q.reports === 0 && q.attachments === 0;
        out({ ok, injected, queues: q, note: ok ? 'colas vacías' : 'quedaron pendientes/errores', logs: logs.slice(-10) });
    } catch (e) {
        out({ ok: false, error: e.message.split('\n')[0], logs: logs.slice(-10) });
    } finally {
        await browser.close();
    }
})();
