import api from './api.js';

export default {
    all(companyId) {
        return api.get(`/companies/${companyId}/products`);
    },

    get(companyId, productId) {
        return api.get(`/companies/${companyId}/products/${productId}`);
    },

    store(companyId, formData) {
        return api.post(`/companies/${companyId}/products`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
    },

    update(companyId, productId, formData) {
        formData.append('_method', 'PUT');
        return api.post(`/companies/${companyId}/products/${productId}`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
    },

    destroy(companyId, productId) {
        return api.delete(`/companies/${companyId}/products/${productId}`);
    },
};
