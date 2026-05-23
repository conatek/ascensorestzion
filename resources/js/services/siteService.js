import api from './api.js';

export default {
    all(clientId) {
        return api.get(`/clients/${clientId}/sites`);
    },
    get(clientId, siteId) {
        return api.get(`/clients/${clientId}/sites/${siteId}`);
    },
    store(clientId, data) {
        return api.post(`/clients/${clientId}/sites`, data);
    },
    update(clientId, siteId, data) {
        return api.put(`/clients/${clientId}/sites/${siteId}`, data);
    },
    destroy(clientId, siteId) {
        return api.delete(`/clients/${clientId}/sites/${siteId}`);
    },
};
