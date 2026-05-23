import api from './api.js';

const resource = '/equipment';

export default {
    all(params = {}) {
        return api.get(resource, { params });
    },
    get(id) {
        return api.get(`${resource}/${id}`);
    },
    store(formData) {
        return api.post(resource, formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
    },
    update(id, formData) {
        formData.append('_method', 'PUT');
        return api.post(`${resource}/${id}`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
    },
    destroy(id) {
        return api.delete(`${resource}/${id}`);
    },
    getHistory(id, params = {}) {
        return api.get(`${resource}/${id}/history`, { params });
    },
    uploadAttachment(id, formData) {
        return api.post(`${resource}/${id}/attachments`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
    },
    deleteAttachment(id, attachmentId) {
        return api.delete(`${resource}/${id}/attachments/${attachmentId}`);
    },
};
