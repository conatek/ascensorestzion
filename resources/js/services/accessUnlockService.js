import api from './api.js';

/**
 * Desbloqueo del ingreso de hoy a un equipo (bloqueo de llegada en preventivos).
 * Las rutas son solo de master: no llamarlas con otro rol, el 403 manda a
 * /acceso-denegado.
 */
export default {
    get(equipmentId) {
        return api.get(`/equipment/${equipmentId}/access-unlock`);
    },
    unlock(equipmentId, reason = null) {
        return api.post(`/equipment/${equipmentId}/access-unlock`, { reason });
    },
    relock(equipmentId) {
        return api.delete(`/equipment/${equipmentId}/access-unlock`);
    },
};
