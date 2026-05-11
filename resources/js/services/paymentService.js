import api from './api.js';

export default {
    checkout(planId, billingPeriod) {
        return api.post('/payments/checkout', {
            plan_id: planId,
            billing_period: billingPeriod,
        });
    },

    history() {
        return api.get('/payments/history');
    },

    result(referenceCode, transactionState) {
        return api.get('/payments/result', {
            params: { referenceCode, transactionState },
        });
    },
};
