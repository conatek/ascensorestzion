import api from './api.js';

const resource = '/clients';

export default {
    all(params = {}) {
        return api.get(resource, { params });
    },
    get(id) {
        return api.get(`${resource}/${id}`);
    },
    store(data) {
        return api.post(resource, data);
    },
    update(id, data) {
        return api.put(`${resource}/${id}`, data);
    },
    destroy(id) {
        return api.delete(`${resource}/${id}`);
    },
};
