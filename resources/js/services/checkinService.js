import api from './api.js';

export default {
    store(data) {
        return api.post('/tech/checkin', data);
    },

    /** Agenda del día para decidir el bloqueo de llegada sin conexión. */
    accessAgenda() {
        return api.get('/tech/access-agenda');
    },

    index(params = {}) {
        return api.get('/tech/checkins', { params });
    },

    show(id) {
        return api.get(`/tech/checkins/${id}`);
    },
};
