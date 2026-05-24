import api from './api.js';

export default {
    store(data) {
        return api.post('/tech/checkin', data);
    },

    index(params = {}) {
        return api.get('/tech/checkins', { params });
    },

    show(id) {
        return api.get(`/tech/checkins/${id}`);
    },
};
