/*
 * Normaliza lo que el usuario pega en el campo de video de YouTube.
 * El cliente rara vez conoce el "ID": copia la URL de la barra del navegador o
 * del boton Compartir. Se acepta cualquiera de esas formas y se devuelve el ID
 * pelado, listo para armar la URL de /embed/.
 *
 * Se convierte al renderizar (no al guardar) para que los valores ya guardados
 * con la URL completa funcionen sin migracion.
 */

// El ID de YouTube son 11 caracteres de [A-Za-z0-9_-].
const ID = '([A-Za-z0-9_-]{11})'

const PATTERNS = [
    new RegExp(`youtu\\.be/${ID}`),                       // https://youtu.be/ID?si=...
    new RegExp(`[?&]v=${ID}`),                            // .../watch?v=ID&t=30s
    new RegExp(`/embed/${ID}`),                           // .../embed/ID
    new RegExp(`/shorts/${ID}`),                          // .../shorts/ID
    new RegExp(`/live/${ID}`),                            // .../live/ID
    new RegExp(`/v/${ID}`),                               // .../v/ID (formato antiguo)
]

/**
 * Extrae el ID de un video de YouTube desde una URL o desde el ID pelado.
 * Devuelve '' si el valor no contiene un ID reconocible.
 *
 * @param {string} value
 * @returns {string}
 */
export function extractYouTubeId(value) {
    const raw = (value || '').trim()
    if (!raw) return ''

    // El ID pelado: exactamente 11 caracteres validos y nada mas.
    if (new RegExp(`^${ID}$`).test(raw)) return raw

    for (const pattern of PATTERNS) {
        const match = raw.match(pattern)
        if (match) return match[1]
    }

    return ''
}

export default extractYouTubeId
