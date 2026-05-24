import api from './api.js';

export default {
    index(params = {}) {
        return api.get('/notifications', { params });
    },

    unreadCount() {
        return api.get('/notifications/unread-count');
    },

    markAsRead(id) {
        return api.post(`/notifications/${id}/read`);
    },

    markAllAsRead() {
        return api.post('/notifications/read-all');
    },
};
