import api from './api.js';

export default {
    // Dashboard
    dashboard() {
        return api.get('/admin/dashboard');
    },

    // Plans CRUD
    getPlans() {
        return api.get('/admin/plans');
    },

    storePlan(data) {
        return api.post('/admin/plans', data);
    },

    updatePlan(id, data) {
        return api.put(`/admin/plans/${id}`, data);
    },

    togglePlan(id) {
        return api.patch(`/admin/plans/${id}/toggle`);
    },

    // Subscriptions
    getSubscriptions(params = {}) {
        return api.get('/admin/subscriptions', { params });
    },

    getSubscription(id) {
        return api.get(`/admin/subscriptions/${id}`);
    },

    updateSubscription(id, data) {
        return api.put(`/admin/subscriptions/${id}`, data);
    },

    extendSubscription(id, days) {
        return api.post(`/admin/subscriptions/${id}/extend`, { days });
    },

    cancelSubscription(id) {
        return api.post(`/admin/subscriptions/${id}/cancel`);
    },

    // Payments
    getPayments(params = {}) {
        return api.get('/admin/payments', { params });
    },

    getPayment(id) {
        return api.get(`/admin/payments/${id}`);
    },

    approvePayment(id) {
        return api.post(`/admin/payments/${id}/approve`);
    },

    refundPayment(id) {
        return api.post(`/admin/payments/${id}/refund`);
    },

    // Users
    getUsers(params = {}) {
        return api.get('/admin/users', { params });
    },

    getUser(id) {
        return api.get(`/admin/users/${id}`);
    },

    updateUser(id, data) {
        return api.put(`/admin/users/${id}`, data);
    },

    // Companies (admin view)
    getCompanies(params = {}) {
        return api.get('/admin/companies', { params });
    },
};
