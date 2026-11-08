/**
 * Fecha local YYYY-MM-DD. No usar toISOString().split('T')[0]: es UTC y en
 * Colombia, desde las 7 p. m., devuelve el día siguiente.
 */
export function localDate(d = new Date()) {
    const pad = n => String(n).padStart(2, '0');
    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
}

/** HH:mm en 24 h, el formato que espera el servidor para time_in / time_out. */
export function localTime(d = new Date()) {
    return d.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' });
}
