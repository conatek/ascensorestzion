/**
 * Motor de sincronización offline.
 *
 * Procesa las colas de IndexedDB (offlineManager) y las sube al servidor con
 * idempotencia (client_uuid). Fase 1: check-ins; Fase 2: reportes + firmas;
 * Fase 3: adjuntos/fotos (enlazados a su reporte por report_client_uuid).
 *
 * Clasificación de errores:
 *   - 401            → detiene el ciclo (token revocado); la cola se conserva.
 *   - 404 / 409 / 422 → error permanente: se marca y NO se reintenta en automático
 *                       (el usuario puede forzar reintento).
 *   - red / 5xx / 408 / 429 → transitorio: se reintenta con backoff exponencial.
 *
 * El lock anti-concurrencia vive en offlineManager.state.syncing (lo gestiona
 * offlineManager.syncPending, que es quien invoca este motor).
 */
import offlineManager from './offlineManager.js';
import checkinService from '@/services/checkinService.js';
import reportService from '@/services/reportService.js';

// Backoff por número de intento: 5s, 30s, 2m, 10m (tope)
const BACKOFF_MS = [5000, 30000, 120000, 600000];

function backoffMs(attempts) {
    return BACKOFF_MS[Math.min(attempts - 1, BACKOFF_MS.length - 1)] || BACKOFF_MS[0];
}

function isPermanent(statusCode) {
    return statusCode === 404 || statusCode === 409 || statusCode === 422;
}

function buildCheckinPayload(item) {
    return {
        equipment_code: item.equipment_code,
        method: item.method,
        latitude: item.latitude ?? null,
        longitude: item.longitude ?? null,
        accuracy: item.accuracy ?? null,
        is_emergency: item.is_emergency ?? false,
        call_received_at: item.call_received_at ?? null,
        client_uuid: item.client_uuid,
        checked_in_at: item.checked_in_at ?? null,
    };
}

async function syncCheckins({ force }) {
    const items = await offlineManager.getPendingCheckins();
    const now = Date.now();

    for (const item of items) {
        if (!force) {
            if (item.status === 'error') continue;
            if (item.nextAttemptAt && item.nextAttemptAt > now) continue;
        }

        try {
            const { data } = await checkinService.store(buildCheckinPayload(item));
            // Idempotente: el server responde 201 (creado) o 200 (ya existía).
            if (data?.equipment) {
                await offlineManager.cacheEquipment(data.equipment);
            }
            await offlineManager.removePendingCheckin(item.localId);
        } catch (err) {
            const statusCode = err?.response?.status;

            // Token revocado: detener el ciclo sin perder la cola
            if (statusCode === 401) {
                throw err;
            }

            item.attempts = (item.attempts || 0) + 1;
            item.lastError = err?.response?.data?.message || err.message || 'Error de red';

            if (isPermanent(statusCode)) {
                item.status = 'error';
            } else {
                item.status = 'pending';
                item.nextAttemptAt = Date.now() + backoffMs(item.attempts);
            }
            await offlineManager.updatePendingCheckin(item);
        }
    }
}

async function syncReports({ force }) {
    const items = await offlineManager.getPendingReports();
    const now = Date.now();

    for (const item of items) {
        if (!force) {
            if (item.status === 'error') continue;
            if (item.nextAttemptAt && item.nextAttemptAt > now) continue;
        }

        try {
            // 1) Crear el reporte (idempotente por client_uuid: 201 nuevo / 200 existente)
            const { data } = await reportService.store({ ...item.payload, client_uuid: item.client_uuid });
            const reportId = data.id;

            // 2) Firmas (idempotentes: el backend sobrescribe la firma)
            if (item.techSignature) {
                await reportService.signTechnician(reportId, { signature: item.techSignature });
            }
            if (item.custSignature) {
                await reportService.signCustomer(reportId, {
                    signature: item.custSignature,
                    signer_name: item.custName || '',
                    signer_document: item.custDoc || '',
                });
            }

            // Enlazar adjuntos pendientes de este reporte con su id real, antes de
            // quitar el reporte de la cola (así los adjuntos quedan autosuficientes
            // aunque su subida falle y se reintente en ciclos posteriores).
            await stampReportIdOnAttachments(item.client_uuid, reportId);

            await offlineManager.removePendingReport(item.localId);
        } catch (err) {
            const statusCode = err?.response?.status;
            if (statusCode === 401) {
                throw err;
            }
            item.attempts = (item.attempts || 0) + 1;
            item.lastError = err?.response?.data?.message || err.message || 'Error de red';
            if (isPermanent(statusCode)) {
                item.status = 'error';
            } else {
                item.status = 'pending';
                item.nextAttemptAt = Date.now() + backoffMs(item.attempts);
            }
            await offlineManager.updatePendingReport(item);
        }
    }
}

/**
 * Estampa el report_id real sobre los adjuntos pendientes enlazados a un reporte
 * (por report_client_uuid). A partir de aquí el adjunto puede subirse aunque su
 * reporte ya no esté en la cola.
 */
async function stampReportIdOnAttachments(reportClientUuid, reportId) {
    if (!reportClientUuid) return;
    const attachments = await offlineManager.getPendingAttachments();
    for (const att of attachments) {
        if (att.report_client_uuid === reportClientUuid && !att.report_id) {
            att.report_id = reportId;
            await offlineManager.updatePendingAttachment(att);
        }
    }
}

function buildAttachmentFormData(item) {
    const fd = new FormData();
    fd.append('file', item.blob, item.filename || 'foto.jpg');
    fd.append('type', item.type || 'otro');
    fd.append('media_type', item.media_type || 'foto');
    fd.append('client_uuid', item.client_uuid);
    if (item.condition_key) fd.append('condition_key', item.condition_key);
    if (item.activity_key) fd.append('activity_key', item.activity_key);
    if (item.group_key) fd.append('group_key', item.group_key);
    return fd;
}

async function syncAttachments({ force }) {
    const items = await offlineManager.getPendingAttachments();
    const now = Date.now();

    for (const item of items) {
        // Sin report_id el reporte aún no se ha sincronizado: el adjunto espera.
        if (!item.report_id) continue;

        if (!force) {
            if (item.status === 'error') continue;
            if (item.nextAttemptAt && item.nextAttemptAt > now) continue;
        }

        try {
            // Idempotente por client_uuid: 201 nuevo / 200 ya existente.
            await reportService.uploadAttachment(item.report_id, buildAttachmentFormData(item));
            await offlineManager.removePendingAttachment(item.localId);
        } catch (err) {
            const statusCode = err?.response?.status;
            if (statusCode === 401) {
                throw err;
            }
            item.attempts = (item.attempts || 0) + 1;
            item.lastError = err?.response?.data?.message || err.message || 'Error de red';
            if (isPermanent(statusCode)) {
                item.status = 'error';
            } else {
                item.status = 'pending';
                item.nextAttemptAt = Date.now() + backoffMs(item.attempts);
            }
            await offlineManager.updatePendingAttachment(item);
        }
    }
}

/**
 * Procesa todas las colas en orden. Lo invoca offlineManager.syncPending().
 * Orden: checkins → reportes (estampan report_id en sus adjuntos) → adjuntos.
 * @param {{force?: boolean}} opts
 */
export async function syncQueues({ force = false } = {}) {
    await syncCheckins({ force });
    await syncReports({ force });
    await syncAttachments({ force });
}

export default { syncQueues };
