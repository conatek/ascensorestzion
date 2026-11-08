/**
 * Etiquetas de una visita programada. Viven aparte porque las pintan tres sitios
 * distintos —el tablero de coordinación, la agenda del técnico y el portal— y una
 * visita no puede llamarse "En curso" en una pantalla y "En proceso" en otra.
 *
 * Las clases CSS de color sí son de cada vista: el mismo estado se ve como chip en
 * el móvil y como borde del evento en el calendario.
 */
export const STATUS_LABELS = {
    programada: 'Programada',
    reprogramacion_solicitada: 'Reprogramación solicitada',
    en_curso: 'En curso',
    completada: 'Completada',
    no_realizada: 'No realizada',
    cancelada: 'Cancelada',
};

export const TYPE_LABELS = {
    preventivo: 'Mantenimiento preventivo',
    correctivo: 'Mantenimiento correctivo',
    especial: 'Servicio especial',
};

export function statusLabel(status) {
    return STATUS_LABELS[status] || status;
}

export function typeLabel(type) {
    return TYPE_LABELS[type] || type;
}

/** Cómo se decidió el ingreso del técnico (bloqueo de llegada en preventivos). */
export const ACCESS_LABELS = {
    a_tiempo: 'A la hora',
    emergencia: 'Emergencia',
    desbloqueado: 'Desbloqueado por master',
    anticipado: 'Antes de la hora',
    sin_programar: 'Sin visita programada',
    reloj_inconsistente: 'Reloj del teléfono inconsistente',
    previo_bloqueo: 'Anterior al bloqueo de llegada',
};
