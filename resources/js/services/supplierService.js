import api from './api.js';

const resource = '/suppliers';

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

    // Sedes
    storeBranch(supplierId, data) {
        return api.post(`${resource}/${supplierId}/branches`, data);
    },
    updateBranch(supplierId, id, data) {
        return api.put(`${resource}/${supplierId}/branches/${id}`, data);
    },
    destroyBranch(supplierId, id) {
        return api.delete(`${resource}/${supplierId}/branches/${id}`);
    },

    // Asesores
    storeContact(supplierId, data) {
        return api.post(`${resource}/${supplierId}/contacts`, data);
    },
    updateContact(supplierId, id, data) {
        return api.put(`${resource}/${supplierId}/contacts/${id}`, data);
    },
    destroyContact(supplierId, id) {
        return api.delete(`${resource}/${supplierId}/contacts/${id}`);
    },

    // Productos y precios
    storeOffer(supplierId, data) {
        return api.post(`${resource}/${supplierId}/offers`, data);
    },
    updateOffer(supplierId, id, data) {
        return api.put(`${resource}/${supplierId}/offers/${id}`, data);
    },
    destroyOffer(supplierId, id) {
        return api.delete(`${resource}/${supplierId}/offers/${id}`);
    },
    offerHistory(supplierId, id) {
        return api.get(`${resource}/${supplierId}/offers/${id}/history`);
    },

    searchItems(q) {
        return api.get('/supply-items/search', { params: { q } });
    },
    itemUnits() {
        return api.get('/supply-items/units');
    },
};
