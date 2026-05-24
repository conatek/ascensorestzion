/**
 * Wrapper de navigator.geolocation para obtener la posicion actual.
 * Funciona sin internet (GPS es independiente de la conexion de datos).
 */

const DEFAULT_OPTIONS = {
    enableHighAccuracy: true,
    timeout: 15000,
    maximumAge: 60000,
};

/**
 * Obtiene la posicion actual del dispositivo.
 * @param {PositionOptions} options - Opciones de geolocation.
 * @returns {Promise<{latitude: number, longitude: number, accuracy: number}>}
 */
export function getCurrentPosition(options = {}) {
    return new Promise((resolve, reject) => {
        if (!navigator.geolocation) {
            reject(new GeolocationError('not_supported', 'Geolocalización no soportada en este navegador.'));
            return;
        }

        const mergedOptions = { ...DEFAULT_OPTIONS, ...options };

        navigator.geolocation.getCurrentPosition(
            (position) => {
                resolve({
                    latitude: position.coords.latitude,
                    longitude: position.coords.longitude,
                    accuracy: position.coords.accuracy,
                });
            },
            (error) => {
                let code = 'unknown';
                let message = 'Error desconocido al obtener ubicación.';

                switch (error.code) {
                    case error.PERMISSION_DENIED:
                        code = 'permission_denied';
                        message = 'Permiso de ubicación denegado. Actívelo en la configuración del navegador.';
                        break;
                    case error.POSITION_UNAVAILABLE:
                        code = 'position_unavailable';
                        message = 'Ubicación no disponible. Puede estar en un área sin señal GPS.';
                        break;
                    case error.TIMEOUT:
                        code = 'timeout';
                        message = 'Tiempo de espera agotado al obtener ubicación. Intente de nuevo.';
                        break;
                }

                reject(new GeolocationError(code, message));
            },
            mergedOptions,
        );
    });
}

/**
 * Calcula la distancia en metros entre dos coordenadas (formula Haversine).
 */
export function distanceBetween(lat1, lng1, lat2, lng2) {
    const R = 6371000; // Radio de la Tierra en metros
    const dLat = toRad(lat2 - lat1);
    const dLng = toRad(lng2 - lng1);
    const a =
        Math.sin(dLat / 2) * Math.sin(dLat / 2) +
        Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) *
        Math.sin(dLng / 2) * Math.sin(dLng / 2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    return R * c;
}

function toRad(deg) {
    return deg * (Math.PI / 180);
}

/**
 * Verifica si una posicion esta dentro del radio de un sitio.
 * @param {{latitude: number, longitude: number}} position
 * @param {{latitude: number, longitude: number, geo_radius_meters: number}} site
 * @returns {{withinRadius: boolean, distance: number}}
 */
export function checkProximity(position, site) {
    if (!site.latitude || !site.longitude) {
        return { withinRadius: true, distance: null }; // Sin coordenadas, asumir OK
    }

    const distance = distanceBetween(
        position.latitude, position.longitude,
        parseFloat(site.latitude), parseFloat(site.longitude),
    );

    const radius = site.geo_radius_meters || 500;

    return {
        withinRadius: distance <= radius,
        distance: Math.round(distance),
    };
}

class GeolocationError extends Error {
    constructor(code, message) {
        super(message);
        this.name = 'GeolocationError';
        this.code = code;
    }
}

export default {
    getCurrentPosition,
    distanceBetween,
    checkProximity,
};
