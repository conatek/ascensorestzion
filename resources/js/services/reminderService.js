import api from './api.js';

const resource = '/reminder-settings';

/**
 * Recordatorios de visita del usuario autenticado. Siempre los propios: el
 * backend no acepta un id de otro.
 */
export default {
    get() {
        return api.get(resource);
    },
    update(data) {
        return api.put(resource, data);
    },
    /** Vuelve a los momentos de fábrica del rol. */
    reset() {
        return api.delete(resource);
    },
};
