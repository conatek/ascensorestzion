import checkinService from '@/services/checkinService.js';
import offlineManager from '@/utils/offlineManager.js';
import { localDate } from '@/utils/localDate.js';

/**
 * Bloqueo de llegada sin conexión.
 *
 * El teléfono guarda la agenda del día (visitas abiertas del técnico y equipos
 * desbloqueados por el master) cada vez que la app abre con señal. Sin conexión
 * se aplica con ella la misma regla que en el servidor
 * (app/Services/EquipmentAccessService.php): si se cambia allá, cambiarla aquí.
 *
 * Se guarda en el store de catálogos ('access-agenda:<fecha>'): no hace falta
 * subir la versión de IndexedDB.
 */
const SCOPE = 'access-agenda';

export async function refreshAccessAgenda() {
    if (!offlineManager.state.isOnline) return null;
    try {
        const { data } = await checkinService.accessAgenda();
        await offlineManager.cacheCatalogs(SCOPE, data.date, data);
        return data;
    } catch {
        return null;
    }
}

export async function getCachedAccessAgenda(date = localDate()) {
    try {
        return await offlineManager.getCachedCatalogs(SCOPE, date);
    } catch {
        return null;
    }
}

const sameCode = (entry, code) => {
    const c = String(code).trim().toLowerCase();
    return [entry.internal_code, entry.customer_code]
        .filter(Boolean)
        .some(v => String(v).trim().toLowerCase() === c);
};

function timeLabel(iso) {
    return new Date(iso).toLocaleTimeString('es-CO', { hour: '2-digit', minute: '2-digit' });
}

/**
 * @returns {{ allowed: boolean, status: string, message: string|null, scheduledStart: string|null }}
 */
export function evaluateAccess(agenda, code, when = new Date(), emergency = false) {
    if (emergency) return { allowed: true, status: 'emergencia', message: null, scheduledStart: null };

    if ((agenda.unlocked || []).some(u => sameCode(u, code))) {
        return { allowed: true, status: 'desbloqueado', message: null, scheduledStart: null };
    }

    const visits = (agenda.visits || []).filter(v => sameCode(v, code));
    if (!visits.length) {
        return {
            allowed: false,
            status: 'sin_programar',
            message: 'Este equipo no tiene una visita programada para ti hoy. Solo un master puede desbloquear el ingreso.',
            scheduledStart: null,
        };
    }

    const open = visits.find(v => v.status === 'en_curso'
        || v.visit_type !== 'preventivo'
        || new Date(v.scheduled_start) <= when);
    if (open) return { allowed: true, status: 'a_tiempo', message: null, scheduledStart: open.scheduled_start };

    const next = visits[0];
    return {
        allowed: false,
        status: 'anticipado',
        // es-CO termina en "p. m.": sin punto final propio para no dejar "p. m.."
        message: `La visita está programada a las ${timeLabel(next.scheduled_start)}; podrás registrar la llegada a esa hora.`,
        scheduledStart: next.scheduled_start,
    };
}
