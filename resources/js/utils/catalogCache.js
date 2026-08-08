import reportService from '@/services/reportService.js';
import offlineManager from '@/utils/offlineManager.js';

/**
 * Catálogos del formulario de reporte: las condiciones iniciales, las actividades
 * del RSTP y los trabajos del RSTE.
 *
 * Existe porque el formulario los pedía a la API a secas: sin señal la petición
 * fallaba, el catch solo escribía en consola y el técnico se quedaba mirando un
 * formulario sin una sola casilla que marcar. Aquí se guardan en IndexedDB al
 * pasar por ellos y se recuperan cuando la red no está.
 *
 * El service worker también cachea /api/catalogs, pero solo la URL exacta que ya
 * se pidió alguna vez estando en línea; esto además los precarga todos.
 */
const CATALOGS = [
    ['RSTP', 'initial_condition'],
    ['RSTC', 'initial_condition'],
    ['RSTP', 'rstp_activity'],
    ['RSTE', 'rste_work'],
];

/**
 * Red primero y, si falla, lo último que se guardó. Devuelve [] si nunca se pudo
 * descargar: quien llame decide qué avisar.
 */
export async function loadCatalog(scope, category) {
    try {
        const { data } = await reportService.getCatalogs(scope, category);
        const list = data || [];

        // Una respuesta vacía no pisa lo guardado: seria cambiar un catalogo bueno
        // por uno vacio si el backend responde raro.
        if (list.length) {
            await offlineManager.cacheCatalogs(scope, category, list);
        }

        return list;
    } catch {
        return (await offlineManager.getCachedCatalogs(scope, category)) || [];
    }
}

/**
 * Baja los cuatro catálogos mientras hay cobertura. Se llama al entrar al layout
 * del técnico para que, cuando llegue al sótano, ya estén guardados aunque nunca
 * haya abierto un reporte de ese tipo.
 */
export async function warmCatalogs() {
    if (!navigator.onLine) return;

    await Promise.all(CATALOGS.map(([scope, category]) => loadCatalog(scope, category)));
}
