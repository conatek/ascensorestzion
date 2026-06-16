import api from './api.js';

export default {
    update(data) {
        return api.put('/profile', data);
    },
    updatePassword(data) {
        return api.put('/profile/password', data);
    },
    updateAvatar(formData) {
        return api.post('/profile/avatar', formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
    },
    deleteAvatar() {
        return api.delete('/profile/avatar');
    },
};
