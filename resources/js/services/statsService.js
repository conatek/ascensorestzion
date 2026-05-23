import api from './api.js';

const prefix = '/stats';

export default {
    overview(params = {}) {
        return api.get(`${prefix}/overview`, { params });
    },
    reportsByMonth(params = {}) {
        return api.get(`${prefix}/reports-by-month`, { params });
    },
    topEquipmentFaults(params = {}) {
        return api.get(`${prefix}/top-equipment-faults`, { params });
    },
    faultCodesFrequency(params = {}) {
        return api.get(`${prefix}/fault-codes-frequency`, { params });
    },
    technicianWorkload(params = {}) {
        return api.get(`${prefix}/technician-workload`, { params });
    },
    maintenanceCompliance(params = {}) {
        return api.get(`${prefix}/maintenance-compliance`, { params });
    },
};
