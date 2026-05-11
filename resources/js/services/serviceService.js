import api from './api.js';

export default {
    all(companyId) {
        return api.get(`/companies/${companyId}/services`);
    },

    get(companyId, serviceId) {
        return api.get(`/companies/${companyId}/services/${serviceId}`);
    },

    store(companyId, formData) {
        return api.post(`/companies/${companyId}/services`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
    },

    update(companyId, serviceId, formData) {
        formData.append('_method', 'PUT');
        return api.post(`/companies/${companyId}/services/${serviceId}`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
    },

    destroy(companyId, serviceId) {
        return api.delete(`/companies/${companyId}/services/${serviceId}`);
    },
};
