import api from './api.js';

const prefix = '/portal';

export default {
    dashboard() {
        return api.get(`${prefix}/dashboard`);
    },
    equipment(params = {}) {
        return api.get(`${prefix}/equipment`, { params });
    },
    equipmentShow(id) {
        return api.get(`${prefix}/equipment/${id}`);
    },
    reports(params = {}) {
        return api.get(`${prefix}/reports`, { params });
    },
    reportShow(id) {
        return api.get(`${prefix}/reports/${id}`);
    },
    /** Sin rango devuelve { upcoming, history }; con from/to, la lista del mes. */
    schedule(params = {}) {
        return api.get(`${prefix}/schedule`, { params });
    },
    scheduleShow(id) {
        return api.get(`${prefix}/schedule/${id}`);
    },
    /** Espacios libres del tecnico asignado para mover esta visita. */
    scheduleAvailability(visitId, params = {}) {
        return api.get(`${prefix}/schedule/${visitId}/availability`, { params });
    },
    requestReschedule(visitId, payload) {
        return api.post(`${prefix}/schedule/${visitId}/reschedule-request`, payload);
    },
};
