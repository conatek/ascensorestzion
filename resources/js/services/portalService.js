import api from './api.js';

const prefix = '/portal';

export default {
    dashboard() {
        return api.get(`${prefix}/dashboard`);
    },
    equipment(params = {}) {
        return api.get(`${prefix}/equipment`, { params });
    },
    equipmentShow(id) {
        return api.get(`${prefix}/equipment/${id}`);
    },
    reports(params = {}) {
        return api.get(`${prefix}/reports`, { params });
    },
    reportShow(id) {
        return api.get(`${prefix}/reports/${id}`);
    },
};
