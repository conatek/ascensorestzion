import api from './api.js';

export default {
    all() {
        return api.get('/plans');
    },
};
