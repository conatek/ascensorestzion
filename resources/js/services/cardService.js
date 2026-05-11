import api from './api.js';

export default {
    all(companyId) {
        return api.get(`/companies/${companyId}/cards`);
    },

    get(companyId, cardId) {
        return api.get(`/companies/${companyId}/cards/${cardId}`);
    },

    store(companyId, formData) {
        return api.post(`/companies/${companyId}/cards`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
    },

    update(companyId, cardId, formData) {
        formData.append('_method', 'PUT');
        return api.post(`/companies/${companyId}/cards/${cardId}`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
    },

    destroy(companyId, cardId) {
        return api.delete(`/companies/${companyId}/cards/${cardId}`);
    },
};
