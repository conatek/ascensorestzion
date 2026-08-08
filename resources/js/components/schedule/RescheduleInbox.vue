<template>
    <section class="inbox">
        <p v-if="loading" class="inbox-loading">
            <i class="fa fa-spinner fa-spin me-2"></i> Cargando solicitudes…
        </p>

        <p v-else-if="!requests.length" class="inbox-empty">
            No hay solicitudes pendientes.
        </p>

        <article v-for="request in requests" v-else :key="request.id" class="inbox-card">
            <header class="inbox-card__head">
                <span class="inbox-card__client">{{ clientName(request) }}</span>
                <span class="inbox-card__sep">·</span>
                <span class="inbox-card__code">{{ equipmentCode(request) }}</span>
                <span class="inbox-card__sep">·</span>
                <span class="inbox-card__site">{{ siteName(request) }}</span>
            </header>

            <p class="inbox-card__move">
                <span class="inbox-card__from">{{ when(request.original_start) }}</span>
                <i class="fa fa-arrow-right mx-2"></i>
                <span class="inbox-card__to">{{ when(request.proposed_start) }}</span>
            </p>

            <p class="inbox-card__meta">
                <span v-if="request.reason" class="inbox-card__reason">"{{ request.reason }}"</span>
                <span>Solicitó {{ request.requester?.name || 'el cliente' }} el {{ asked(request.created_at) }}</span>
            </p>

            <!-- Lo que decide en un vistazo: si el hueco propuesto sigue libre. -->
            <p
                class="inbox-card__check"
                :class="request.availability?.technician_free ? 'is-free' : 'is-clash'"
            >
                <i :class="request.availability?.technician_free ? 'fa fa-check-circle' : 'fa fa-exclamation-circle'"></i>
                <span>{{ technicianName(request) }}</span>
                <template v-if="request.availability?.technician_free">
                    — libre en el nuevo horario
                </template>
                <template v-else>
                    — {{ (request.availability?.problems || []).join(' ') || 'ya no está libre.' }}
                </template>
            </p>

            <footer class="inbox-card__actions">
                <button class="inbox-btn inbox-btn--ghost" @click="$emit('locate', request)">
                    <i class="fa fa-calendar me-1"></i> Ver en calendario
                </button>
                <button class="inbox-btn inbox-btn--reject" @click="$emit('reject', request)">
                    <i class="fa fa-times me-1"></i> Rechazar
                </button>
                <button class="inbox-btn inbox-btn--approve" @click="$emit('approve', request)">
                    <i class="fa fa-check me-1"></i> Aprobar
                </button>
            </footer>
        </article>
    </section>
</template>

<script>
import dayjs from '@/utils/dayjs.js';

/**
 * Bandeja de solicitudes de reprogramacion. Componente tonto: el tablero es quien
 * llama a la API y quien pide confirmacion.
 *
 * Estilos propios con prefijo inbox-, como el resto de piezas del cronograma: los
 * de la vista anfitriona son scoped y no llegan hasta aqui.
 */
export default {
    name: 'RescheduleInbox',

    props: {
        requests: { type: Array, default: () => [] },
        loading: { type: Boolean, default: false },
    },

    emits: ['approve', 'reject', 'locate'],

    methods: {
        clientName(request) {
            return request.scheduled_visit?.client?.business_name || 'Cliente';
        },
        equipmentCode(request) {
            return request.scheduled_visit?.equipment?.internal_code || '—';
        },
        siteName(request) {
            return request.scheduled_visit?.site?.name || '—';
        },
        technicianName(request) {
            return request.scheduled_visit?.technician?.name || 'Sin técnico';
        },

        when(value) {
            return dayjs(value).format('ddd D MMM, HH:mm');
        },

        // Formato absoluto y no "hace 2 h": dayjs va sin el plugin relativeTime y
        // cargarlo solo para esta linea no compensa.
        asked(value) {
            return dayjs(value).format('D MMM [a las] HH:mm');
        },
    },
};
</script>

<style scoped>
.inbox {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    margin-bottom: 1.25rem;
}

.inbox-loading,
.inbox-empty {
    margin: 0;
    padding: 1rem;
    background: #fff;
    border: 1px dashed #e2e8f0;
    border-radius: 12px;
    text-align: center;
    font-size: 0.88rem;
    color: #94a3b8;
}

.inbox-card {
    background: #fff;
    border: 1px solid #fde68a;
    border-left: 4px solid #f59e0b;
    border-radius: 14px;
    padding: 0.9rem 1.1rem;
}

.inbox-card__head {
    display: flex;
    flex-wrap: wrap;
    align-items: baseline;
    gap: 0.35rem;
    font-size: 0.88rem;
    color: #475569;
}

.inbox-card__client {
    font-weight: 700;
    color: #1e293b;
}

.inbox-card__code {
    font-weight: 700;
    color: #30ab0a;
}

.inbox-card__sep {
    color: #cbd5e1;
}

.inbox-card__move {
    margin: 0.5rem 0 0;
    font-size: 0.95rem;
    color: #1e293b;
}

.inbox-card__from {
    color: #94a3b8;
    text-decoration: line-through;
}

.inbox-card__to {
    font-weight: 700;
}

.inbox-card__meta {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin: 0.35rem 0 0;
    font-size: 0.78rem;
    color: #94a3b8;
}

.inbox-card__reason {
    font-style: italic;
    color: #475569;
}

.inbox-card__check {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.35rem;
    margin: 0.6rem 0 0;
    font-size: 0.82rem;
    font-weight: 600;
}

.inbox-card__check.is-free {
    color: #227a0c;
}

.inbox-card__check.is-clash {
    color: #ba2831;
}

.inbox-card__actions {
    display: flex;
    flex-wrap: wrap;
    justify-content: flex-end;
    gap: 0.5rem;
    margin-top: 0.75rem;
}

.inbox-btn {
    border: 0;
    border-radius: 9px;
    padding: 0.42rem 0.9rem;
    font-size: 0.82rem;
    font-weight: 600;
    cursor: pointer;
}

.inbox-btn--ghost {
    background: #f1f5f9;
    color: #64748b;
}

.inbox-btn--reject {
    background: #fef2f2;
    color: #ba2831;
}

.inbox-btn--approve {
    background: linear-gradient(135deg, #30ab0a, #37c20c);
    color: #fff;
}

@media (max-width: 640px) {
    .inbox-card__actions {
        justify-content: stretch;
    }

    .inbox-btn {
        flex: 1;
    }
}
</style>
