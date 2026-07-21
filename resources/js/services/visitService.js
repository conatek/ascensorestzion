import api from './api.js';
import offlineManager from '@/utils/offlineManager.js';

/**
 * Firma diferida: una visita agrupa los reportes de la misma sede y dia que
 * quedaron firmados solo por el tecnico, a la espera de una unica firma del cliente.
 */
export default {
    pendingSignature() {
        return api.get('/tech/visits/pending-signature');
    },
    signCustomer(data) {
        return api.post('/tech/visits/sign-customer', data);
    },
};

/**
 * Visitas pendientes de firma, combinando servidor y cola offline:
 * una visita puede tener reportes ya subidos y otros todavia sin sincronizar.
 * Las visitas con firma ya capturada salen aparte (esperan conexion).
 */
export async function loadPendingVisits() {
    const { reports, signatures } = await offlineManager.getAllPendingItems();

    // Visitas cuyos reportes siguen en la cola local
    const local = {};
    reports.forEach(r => {
        const meta = r.visitMeta;
        if (!meta?.visit_uuid) return;

        if (!local[meta.visit_uuid]) {
            local[meta.visit_uuid] = {
                visit_uuid: meta.visit_uuid,
                client_name: meta.client_name,
                site_name: meta.site_name,
                service_date: meta.service_date,
                reports: [],
                reports_count: 0,
                hasLocal: true,
            };
        }

        local[meta.visit_uuid].reports.push({
            id: `local-${r.localId}`,
            report_number: 'Pendiente de subir',
            report_type: meta.report_type,
            equipment_code: null,
        });
        local[meta.visit_uuid].reports_count++;
    });

    let server = [];
    if (offlineManager.state.isOnline) {
        try {
            const { data } = await api.get('/tech/visits/pending-signature');
            server = data || [];
        } catch (err) {
            // Sin servidor seguimos mostrando lo local
            console.error('pendingSignature:', err);
        }
    }

    const byUuid = new Map();
    server.forEach(v => byUuid.set(v.visit_uuid, { ...v, hasLocal: false }));
    Object.values(local).forEach(v => {
        const existing = byUuid.get(v.visit_uuid);
        if (existing) {
            existing.reports = [...existing.reports, ...v.reports];
            existing.reports_count += v.reports_count;
            existing.hasLocal = true;
        } else {
            byUuid.set(v.visit_uuid, v);
        }
    });

    // Una firma en error no bloquea la visita: vuelve a la lista para poder
    // recapturarla (al reencolar se reemplaza el registro fallido).
    const queuedSignatures = (signatures || []).filter(s => s.status !== 'error');
    const signed = new Set(queuedSignatures.map(s => s.visit_uuid));

    return {
        visits: [...byUuid.values()].filter(v => !signed.has(v.visit_uuid)),
        queuedSignatures,
    };
}

/**
 * Devuelve el uuid de la visita en curso para una sede y fecha, creandolo si no existe.
 * Se guarda en sessionStorage para que todos los reportes de la misma visita compartan
 * el mismo identificador aunque se recargue el formulario.
 */
export function currentVisitUuid(siteId, serviceDate) {
    // localStorage y no sessionStorage: la visita debe sobrevivir a que el técnico
    // cierre la PWA entre un equipo y otro. La clave sede+fecha descarta lo viejo.
    const stored = readCurrentVisit();
    if (stored && String(stored.site_id) === String(siteId) && stored.service_date === serviceDate) {
        return stored.visit_uuid;
    }

    const visitUuid = newUuid();
    localStorage.setItem(VISIT_KEY, JSON.stringify({ visit_uuid: visitUuid, site_id: siteId, service_date: serviceDate }));
    return visitUuid;
}

/**
 * Cierra la visita en curso. Recibe el uuid firmado para no borrar la visita
 * equivocada: el técnico puede firmar una visita vieja en mitad de otra.
 */
export function clearCurrentVisit(visitUuid = null) {
    const stored = readCurrentVisit();
    if (!stored) return;
    if (visitUuid && stored.visit_uuid !== visitUuid) return;
    localStorage.removeItem(VISIT_KEY);
}

const VISIT_KEY = 'current_visit';

function readCurrentVisit() {
    try {
        return JSON.parse(localStorage.getItem(VISIT_KEY) || 'null');
    } catch {
        return null;
    }
}

function newUuid() {
    if (typeof crypto !== 'undefined' && crypto.randomUUID) return crypto.randomUUID();
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, c => {
        const r = (Math.random() * 16) | 0;
        const v = c === 'x' ? r : (r & 0x3) | 0x8;
        return v.toString(16);
    });
}
