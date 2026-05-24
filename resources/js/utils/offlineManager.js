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
});

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
        const checkins = await db.count(STORES.CHECKINS_PENDING);
        const reports = await db.count(STORES.REPORTS_PENDING_SYNC);
        const attachments = await db.count(STORES.ATTACHMENTS_PENDING);
        state.pendingCount = checkins + reports + attachments;
    } catch {
        state.pendingCount = 0;
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
        queuedAt: Date.now(),
    });
}

async function getPendingCheckins() {
    return getAll(STORES.CHECKINS_PENDING);
}

async function removePendingCheckin(localId) {
    await remove(STORES.CHECKINS_PENDING, localId);
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
    await put(STORES.REPORTS_PENDING_SYNC, {
        ...reportData,
        queuedAt: Date.now(),
    });
}

async function getPendingReports() {
    return getAll(STORES.REPORTS_PENDING_SYNC);
}

async function removePendingReport(localId) {
    await remove(STORES.REPORTS_PENDING_SYNC, localId);
}

// --- Attachments pendientes ---

async function queueAttachment(attachmentData) {
    await put(STORES.ATTACHMENTS_PENDING, {
        ...attachmentData,
        queuedAt: Date.now(),
    });
}

async function getPendingAttachments() {
    return getAll(STORES.ATTACHMENTS_PENDING);
}

async function removePendingAttachment(localId) {
    await remove(STORES.ATTACHMENTS_PENDING, localId);
}

// --- Sincronizacion ---

async function syncPending() {
    // La sincronizacion real se implementara en la Fase 3 cuando
    // existan los endpoints y servicios necesarios.
    // Por ahora solo refresca el conteo.
    await refreshPendingCount();
}

// Inicializar conteo al cargar
refreshPendingCount();

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

    // Borradores
    saveDraftReport,
    getDraftReport,
    removeDraftReport,
    getAllDraftReports,

    // Reportes pendientes
    queueReportForSync,
    getPendingReports,
    removePendingReport,

    // Attachments
    queueAttachment,
    getPendingAttachments,
    removePendingAttachment,

    // Sync
    syncPending,
    refreshPendingCount,

    // Low-level
    clear,
};
