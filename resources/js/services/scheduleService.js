import api from './api.js';

const resource = '/schedule';

/**
 * Cronograma de visitas (coordinación). La agenda del técnico y la del portal
 * cuelgan de otros prefijos, con su propio scoping.
 */
export default {
    /** El calendario siempre acota: from/to son obligatorios en el backend. */
    visits(params = {}) {
        return api.get(`${resource}/visits`, { params });
    },
    visit(id) {
        return api.get(`${resource}/visits/${id}`);
    },
    store(data) {
        return api.post(`${resource}/visits`, data);
    },
    /** Sirve para editar y para drag & drop: manda solo lo que cambia. */
    update(id, data) {
        return api.put(`${resource}/visits/${id}`, data);
    },
    cancel(id, cancelReason = null) {
        return api.post(`${resource}/visits/${id}/cancel`, { cancel_reason: cancelReason });
    },

    /** Técnicos activos + color + jornada resuelta (el grid la necesita). */
    technicians() {
        return api.get(`${resource}/technicians`);
    },
    technicianSchedule(userId) {
        return api.get(`${resource}/technicians/${userId}/schedule`);
    },
    saveTechnicianSchedule(userId, data) {
        return api.put(`${resource}/technicians/${userId}/schedule`, data);
    },
    resetTechnicianSchedule(userId) {
        return api.delete(`${resource}/technicians/${userId}/schedule`);
    },

    /** Duración sugerida al elegir equipo en el modal. */
    equipmentDuration(equipmentId) {
        return api.get(`${resource}/equipment/${equipmentId}/duration`);
    },

    // ── Agenda del técnico (solo sus visitas, scoping en el backend) ──

    techAgenda(from, to) {
        return api.get('/tech/schedule', { params: { from, to } });
    },
    techVisit(id) {
        return api.get(`/tech/schedule/${id}`);
    },
};
