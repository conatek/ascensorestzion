import api from './api.js';

export default {
    forgot(email) {
        return api.post('/forgot-password', { email });
    },
    reset(payload) {
        // payload: { token, email, password, password_confirmation }
        return api.post('/reset-password', payload);
    },
};
