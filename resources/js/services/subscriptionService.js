import api from './api.js';

export default {
    current() {
        return api.get('/subscription');
    },
};
