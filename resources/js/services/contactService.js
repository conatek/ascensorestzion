import api from './api.js';

export default {
    send(data) {
        return api.post('/contact', data);
    },
};
