import { openDB } from 'idb';
import { reactive, readonly } from 'vue';

const DB_NAME = 'tzion-offline';
const DB_VERSION = 1;

const STORES = {
    CATALOGS: 'catalogs',
    EQUIPMENT: 'equipment',
    CHECKINS_PENDING: 'checkins-pending',
    REPORTS_DRAFT: 'reports-draft',
    REPORTS_PENDING_SYNC: 'reports-pending-sync',
    ATTACHMENTS_PENDING: 'attachments-pending',
};

let dbInstance = null;

/** Genera un UUID v4 con fallback si crypto.randomUUID no está disponible. */
function newUuid() {
    return (crypto.randomUUID && crypto.randomUUID())
        || `${Date.now()}-${Math.round(Math.random() * 1e9)}`;
}

async function getDb() {
    if (dbInstance) return dbInstance;

    dbInstance = await openDB(DB_NAME, DB_VERSION, {
        upgrade(db) {
            // Catalogos: scope+category como key
            if (!db.objectStoreNames.contains(STORES.CATALOGS)) {
                db.createObjectStore(STORES.CATALOGS, { keyPath: 'cacheKey' });
            }

            // Equipos cacheados para offline
            if (!db.objectStoreNames.contains(STORES.EQUIPMENT)) {
                const store = db.createObjectStore(STORES.EQUIPMENT, { keyPath: 'id' });
                store.createIndex('by-code', 'internal_code', { unique: true });
            }

            // Check-ins pendientes de sync
            if (!db.objectStoreNames.contains(STORES.CHECKINS_PENDING)) {
                db.createObjectStore(STORES.CHECKINS_PENDING, {
                    keyPath: 'localId',
                    autoIncrement: true,
                });
            }

            // Reportes en borrador (trabajo en progreso)
            if (!db.objectStoreNames.contains(STORES.REPORTS_DRAFT)) {
                db.createObjectStore(STORES.REPORTS_DRAFT, { keyPath: 'localId' });
            }

            // Reportes finalizados pendientes de upload
            if (!db.objectStoreNames.contains(STORES.REPORTS_PENDING_SYNC)) {
                db.createObjectStore(STORES.REPORTS_PENDING_SYNC, {
                    keyPath: 'localId',
                    autoIncrement: true,
                });
            }

            // Fotos/videos pendientes de upload
            if (!db.objectStoreNames.contains(STORES.ATTACHMENTS_PENDING)) {
                db.createObjectStore(STORES.ATTACHMENTS_PENDING, {
                    keyPath: 'localId',
                    autoIncrement: true,
                });
            }
        },
    });

    return dbInstance;
}

// --- Estado reactivo de conexion ---

const state = reactive({
    isOnline: navigator.onLine,
    pendingCount: 0,
    errorCount: 0,
    syncing: false,
    lastSyncAt: null,       // timestamp del último ciclo de sync completado (online)
    lastSyncResult: null,   // { uploaded, failed, at } del último ciclo
});

// --- Telemetría mínima: registro local de resultados de sync (para depurar en campo) ---

const SYNC_LOG_KEY = 'tzion-sync-log';

function logSyncEvent(entry) {
    try {
        const log = JSON.parse(localStorage.getItem(SYNC_LOG_KEY) || '[]');
        log.unshift({ ...entry, at: Date.now() });
        localStorage.setItem(SYNC_LOG_KEY, JSON.stringify(log.slice(0, 50)));
    } catch {}
}

function getSyncLog() {
    try {
        return JSON.parse(localStorage.getItem(SYNC_LOG_KEY) || '[]');
    } catch {
        return [];
    }
}

function updateOnlineStatus() {
    state.isOnline = navigator.onLine;
    if (state.isOnline) {
        syncPending();
    }
}

window.addEventListener('online', updateOnlineStatus);
window.addEventListener('offline', updateOnlineStatus);

// --- Conteo de pendientes ---

async function refreshPendingCount() {
    try {
        const db = await getDb();
        const checkins = await db.getAll(STORES.CHECKINS_PENDING);
        const reports = await db.getAll(STORES.REPORTS_PENDING_SYNC);
        const attachments = await db.getAll(STORES.ATTACHMENTS_PENDING);
        const pendingCheckins = checkins.filter(c => c.status !== 'error').length;
        const pendingReports = reports.filter(r => r.status !== 'error').length;
        const pendingAttachments = attachments.filter(a => a.status !== 'error').length;
        state.errorCount = (checkins.length - pendingCheckins)
            + (reports.length - pendingReports)
            + (attachments.length - pendingAttachments);
        state.pendingCount = pendingCheckins + pendingReports + pendingAttachments;
    } catch {
        state.pendingCount = 0;
        state.errorCount = 0;
    }
}

// --- Operaciones CRUD genericas ---

async function put(storeName, data) {
    const db = await getDb();
    await db.put(storeName, data);
    await refreshPendingCount();
}

async function getAll(storeName) {
    const db = await getDb();
    return db.getAll(storeName);
}

async function get(storeName, key) {
    const db = await getDb();
    return db.get(storeName, key);
}

async function remove(storeName, key) {
    const db = await getDb();
    await db.delete(storeName, key);
    await refreshPendingCount();
}

async function clear(storeName) {
    const db = await getDb();
    await db.clear(storeName);
    await refreshPendingCount();
}

// --- Catalogos ---

async function cacheCatalogs(scope, category, data) {
    const cacheKey = `${scope}:${category}`;
    await put(STORES.CATALOGS, { cacheKey, scope, category, data, cachedAt: Date.now() });
}

async function getCachedCatalogs(scope, category) {
    const cacheKey = `${scope}:${category}`;
    const entry = await get(STORES.CATALOGS, cacheKey);
    return entry ? entry.data : null;
}

// --- Equipos ---

async function cacheEquipment(equipment) {
    await put(STORES.EQUIPMENT, { ...equipment, cachedAt: Date.now() });
}

async function getCachedEquipment(id) {
    return get(STORES.EQUIPMENT, id);
}

async function getCachedEquipmentByCode(code) {
    const db = await getDb();
    return db.getFromIndex(STORES.EQUIPMENT, 'by-code', code);
}

// --- Check-ins pendientes ---

async function queueCheckin(checkinData) {
    await put(STORES.CHECKINS_PENDING, {
        ...checkinData,
        // Idempotencia + hora real del registro offline
        client_uuid: newUuid(),
        checked_in_at: checkinData.checked_in_at || new Date().toISOString(),
        // Control de sincronización
        status: 'pending',
        attempts: 0,
        lastError: null,
        nextAttemptAt: 0,
        queuedAt: Date.now(),
    });
}

async function getPendingCheckins() {
    return getAll(STORES.CHECKINS_PENDING);
}

async function removePendingCheckin(localId) {
    await remove(STORES.CHECKINS_PENDING, localId);
}

/** Actualiza un check-in pendiente (estado/intentos) sin tocar el resto. */
async function updatePendingCheckin(item) {
    const db = await getDb();
    await db.put(STORES.CHECKINS_PENDING, item);
    await refreshPendingCount();
}

// --- Reportes borrador ---

async function saveDraftReport(localId, reportData) {
    await put(STORES.REPORTS_DRAFT, {
        localId,
        ...reportData,
        savedAt: Date.now(),
    });
}

async function getDraftReport(localId) {
    return get(STORES.REPORTS_DRAFT, localId);
}

async function removeDraftReport(localId) {
    await remove(STORES.REPORTS_DRAFT, localId);
}

async function getAllDraftReports() {
    return getAll(STORES.REPORTS_DRAFT);
}

// --- Reportes pendientes de sync ---

async function queueReportForSync(reportData) {
    const client_uuid = newUuid();
    await put(STORES.REPORTS_PENDING_SYNC, {
        ...reportData,
        // Idempotencia + control de sincronización
        client_uuid,
        status: 'pending',
        attempts: 0,
        lastError: null,
        nextAttemptAt: 0,
        queuedAt: Date.now(),
    });
    // Devolver el uuid para enlazar los adjuntos (Fase 3) con este reporte.
    return client_uuid;
}

async function getPendingReports() {
    return getAll(STORES.REPORTS_PENDING_SYNC);
}

async function removePendingReport(localId) {
    await remove(STORES.REPORTS_PENDING_SYNC, localId);
}

/** Actualiza un reporte pendiente (estado/intentos) sin tocar el resto. */
async function updatePendingReport(item) {
    const db = await getDb();
    await db.put(STORES.REPORTS_PENDING_SYNC, item);
    await refreshPendingCount();
}

// --- Attachments pendientes ---

async function queueAttachment(attachmentData) {
    await put(STORES.ATTACHMENTS_PENDING, {
        ...attachmentData,
        // Idempotencia propia del adjunto + control de sincronización.
        // report_id se estampa cuando su reporte se sincroniza (ver syncEngine);
        // hasta entonces el adjunto espera (report_client_uuid lo enlaza al reporte).
        client_uuid: attachmentData.client_uuid || newUuid(),
        report_id: attachmentData.report_id ?? null,
        status: 'pending',
        attempts: 0,
        lastError: null,
        nextAttemptAt: 0,
        queuedAt: Date.now(),
    });
}

async function getPendingAttachments() {
    return getAll(STORES.ATTACHMENTS_PENDING);
}

async function removePendingAttachment(localId) {
    await remove(STORES.ATTACHMENTS_PENDING, localId);
}

/** Actualiza un adjunto pendiente (estado/intentos/report_id) sin tocar el resto. */
async function updatePendingAttachment(item) {
    const db = await getDb();
    await db.put(STORES.ATTACHMENTS_PENDING, item);
    await refreshPendingCount();
}

// --- Vista de pendientes (Fase 5) ---

const TYPE_STORE = {
    checkin: STORES.CHECKINS_PENDING,
    report: STORES.REPORTS_PENDING_SYNC,
    attachment: STORES.ATTACHMENTS_PENDING,
};

/** Devuelve todos los items en cola agrupados por tipo (para la UI del técnico). */
async function getAllPendingItems() {
    const [checkins, reports, attachments] = await Promise.all([
        getAll(STORES.CHECKINS_PENDING),
        getAll(STORES.REPORTS_PENDING_SYNC),
        getAll(STORES.ATTACHMENTS_PENDING),
    ]);
    return { checkins, reports, attachments };
}

/** Descarta un item de su cola (acción manual del técnico). */
async function discardPending(type, localId) {
    const store = TYPE_STORE[type];
    if (store) await remove(store, localId);
}

/** Reintenta un item: resetea su estado/backoff y dispara la sincronización. */
async function retryItem(type, item) {
    const store = TYPE_STORE[type];
    if (!store) return;
    item.status = 'pending';
    item.attempts = 0;
    item.lastError = null;
    item.nextAttemptAt = 0;
    const db = await getDb();
    await db.put(store, item);
    await refreshPendingCount();
    await syncPending(true);
}

// --- Sincronizacion ---

/**
 * Sube los registros pendientes al servidor. Delega en syncEngine.
 * @param {boolean} force - reintenta también los items marcados como 'error'.
 */
async function countAllItems() {
    const db = await getDb();
    const [c, r, a] = await Promise.all([
        db.count(STORES.CHECKINS_PENDING),
        db.count(STORES.REPORTS_PENDING_SYNC),
        db.count(STORES.ATTACHMENTS_PENDING),
    ]);
    return c + r + a;
}

async function syncPending(force = false) {
    if (!state.isOnline || state.syncing) {
        await refreshPendingCount();
        return;
    }
    state.syncing = true;
    const before = await countAllItems();
    try {
        // Import dinámico para evitar dependencia circular con syncEngine
        const { syncQueues } = await import('./syncEngine.js');
        await syncQueues({ force });
    } catch (e) {
        // No romper la app si el motor falla; el contador queda consistente
        // eslint-disable-next-line no-console
        console.error('syncPending error:', e);
    } finally {
        state.syncing = false;
        await refreshPendingCount();

        // Resumen del ciclo: lo subido = items que salieron de las colas.
        const after = await countAllItems();
        const uploaded = Math.max(0, before - after);
        state.lastSyncAt = Date.now();
        state.lastSyncResult = { uploaded, failed: state.errorCount, at: state.lastSyncAt };

        if (uploaded > 0 || state.errorCount > 0) {
            logSyncEvent({ uploaded, failed: state.errorCount });
        }
        if (uploaded > 0) {
            // Toast global: lo escucha ConnectionStatus.
            window.dispatchEvent(new CustomEvent('tzion-sync-complete', { detail: state.lastSyncResult }));
        }
    }
}

// Inicializar conteo al cargar e intentar sincronizar pendientes (no-op si la
// cola está vacía o no hay conexión).
refreshPendingCount().then(() => {
    if (navigator.onLine && state.pendingCount > 0) {
        setTimeout(() => syncPending(), 1500);
    }
});

export default {
    STORES,
    state: readonly(state),

    // Catalogos
    cacheCatalogs,
    getCachedCatalogs,

    // Equipos
    cacheEquipment,
    getCachedEquipment,
    getCachedEquipmentByCode,

    // Check-ins
    queueCheckin,
    getPendingCheckins,
    removePendingCheckin,
    updatePendingCheckin,

    // Borradores
    saveDraftReport,
    getDraftReport,
    removeDraftReport,
    getAllDraftReports,

    // Reportes pendientes
    queueReportForSync,
    getPendingReports,
    removePendingReport,
    updatePendingReport,

    // Attachments
    queueAttachment,
    getPendingAttachments,
    removePendingAttachment,
    updatePendingAttachment,

    // Sync
    syncPending,
    refreshPendingCount,

    // Vista de pendientes (Fase 5)
    getAllPendingItems,
    discardPending,
    retryItem,
    getSyncLog,

    // Low-level
    clear,
};
