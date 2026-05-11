import axios from 'axios';
import router from './../../router';

const api = axios.create({
    baseURL: '/api',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    },
});

// Interceptor de request: agregar token de autenticación
api.interceptors.request.use((config) => {
    const token = localStorage.getItem('auth_token');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

// Interceptor de response: manejar errores de autenticación
api.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response) {
            if (error.response.status === 401) {
                localStorage.removeItem('auth_token');
                localStorage.removeItem('auth_user');
                localStorage.removeItem('auth_permissions');
                router.push({ name: 'login' });
            } else if (error.response.status === 403) {
                router.push({ name: 'forbidden' });
            }
        }
        return Promise.reject(error);
    }
);

export default api;
