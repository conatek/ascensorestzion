import api from './api.js';

export default {
    // Dashboard
    dashboard() {
        return api.get('/admin/dashboard');
    },

    // Users
    getUsers(params = {}) {
        return api.get('/admin/users', { params });
    },

    getUser(id) {
        return api.get(`/admin/users/${id}`);
    },

    createUser(data) {
        return api.post('/admin/users', data);
    },

    updateUser(id, data) {
        return api.put(`/admin/users/${id}`, data);
    },

    deleteUser(id) {
        return api.delete(`/admin/users/${id}`);
    },

    // Companies (admin view)
    getCompanies(params = {}) {
        return api.get('/admin/companies', { params });
    },
};
