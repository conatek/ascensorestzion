import axios from 'axios';

// Cliente sin token de autenticación — acceso público
const publicApi = axios.create({
    baseURL: '/api/public',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    },
});

export default {
    company(companySlug) {
        return publicApi.get(`/${companySlug}`);
    },

    card(companySlug, cardSlug) {
        return publicApi.get(`/${companySlug}/${cardSlug}`);
    },
};
