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
    // ── Excepciones de jornada: festivos, vacaciones, sabados sueltos ──

    /** Sin user_id devuelve todas; con user_id, las suyas mas las generales. */
    scheduleExceptions(params = {}) {
        return api.get(`${resource}/exceptions`, { params });
    },
    saveScheduleException(payload) {
        return api.post(`${resource}/exceptions`, payload);
    },
    deleteScheduleException(id) {
        return api.delete(`${resource}/exceptions/${id}`);
    },

    // ── Reprogramaciones ──

    /** Bandeja de solicitudes. Por defecto solo las pendientes. */
    rescheduleRequests(params = {}) {
        return api.get(`${resource}/reschedule-requests`, { params });
    },
    approveRescheduleRequest(id, payload = {}) {
        return api.post(`${resource}/reschedule-requests/${id}/approve`, payload);
    },
    rejectRescheduleRequest(id, notes) {
        return api.post(`${resource}/reschedule-requests/${id}/reject`, { resolution_notes: notes });
    },
    /** Espacios libres de una visita, sin la antelacion minima del portal. */
    visitAvailability(visitId, params = {}) {
        return api.get(`${resource}/visits/${visitId}/availability`, { params });
    },

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
