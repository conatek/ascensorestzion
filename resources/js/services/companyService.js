import api from './api.js';

export default {
    all() {
        return api.get('/companies');
    },

    get(id) {
        return api.get(`/companies/${id}`);
    },

    store(formData) {
        return api.post('/companies', formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
    },

    update(id, formData) {
        // Laravel no procesa FormData en PUT; usamos POST + _method spoofing
        formData.append('_method', 'PUT');
        return api.post(`/companies/${id}`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
    },

    destroy(id) {
        return api.delete(`/companies/${id}`);
    },

    theme(id) {
        return api.get(`/companies/${id}/theme`);
    },
};
