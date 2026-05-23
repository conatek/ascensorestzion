import api from './api.js';

const resource = '/service-reports';

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
    signTechnician(id, data) {
        return api.post(`${resource}/${id}/sign-technician`, data);
    },
    signCustomer(id, data) {
        return api.post(`${resource}/${id}/sign-customer`, data);
    },
    getPdf(id) {
        return api.get(`${resource}/${id}/pdf`, { responseType: 'blob' });
    },
    getCatalogs(scope, category) {
        return api.get('/catalogs', { params: { scope, category } });
    },
    sendEmail(id, data) {
        return api.post(`${resource}/${id}/send-email`, data);
    },
    exportCsv(params = {}) {
        return api.get('/service-reports-export', { params, responseType: 'blob' });
    },
};
