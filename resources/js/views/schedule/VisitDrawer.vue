<template>
    <!-- Teleport a body: dentro del contenedor de contenido del layout, el
         position:fixed se resolvía contra ese contenedor y no contra la ventana,
         así que la cabecera del panel quedaba tapada por el header de la app. -->
    <Teleport to="body">
    <div class="drawer-backdrop" @click.self="$emit('close')">
        <aside class="drawer">
            <header class="drawer-head">
                <div>
                    <span class="drawer-status" :class="`status-${visit.status}`">{{ statusLabel }}</span>
                    <h4 class="drawer-title">{{ visit.equipment?.internal_code || 'Visita' }}</h4>
                    <p class="drawer-sub">{{ typeLabel }}</p>
                </div>
                <button class="drawer-close" @click="$emit('close')"><i class="fa fa-times"></i></button>
            </header>

            <div class="drawer-body">
                <section class="drawer-block">
                    <h5 class="drawer-block-title">Cuándo</h5>
                    <p class="drawer-when">{{ whenLabel }}</p>
                    <p class="drawer-duration">{{ durationLabel }}</p>
                </section>

                <section class="drawer-block">
                    <h5 class="drawer-block-title">Dónde</h5>
                    <dl class="drawer-dl">
                        <dt>Cliente</dt>
                        <dd>{{ visit.client?.business_name || '—' }}</dd>
                        <dt>Sede</dt>
                        <dd>{{ visit.site?.name || '—' }}</dd>
                        <dd v-if="visit.site?.address" class="drawer-address">{{ visit.site.address }}</dd>
                        <dt>Equipo</dt>
                        <dd>
                            <router-link v-if="visit.equipment_id" :to="`/equipos/${visit.equipment_id}`" class="drawer-link">
                                {{ visit.equipment?.internal_code }}
                                <i class="fa fa-external-link-alt ms-1"></i>
                            </router-link>
                            <span v-else>—</span>
                        </dd>
                    </dl>
                </section>

                <section class="drawer-block">
                    <h5 class="drawer-block-title">Quién</h5>
                    <dl class="drawer-dl">
                        <dt>Técnico</dt>
                        <dd>{{ visit.technician?.name || '—' }}</dd>
                        <dt>Programada por</dt>
                        <dd>{{ visit.creator?.name || '—' }}</dd>
                    </dl>
                </section>

                <section v-if="visit.notes" class="drawer-block">
                    <h5 class="drawer-block-title">Notas</h5>
                    <p class="drawer-notes">{{ visit.notes }}</p>
                </section>

                <section v-if="visit.status === 'cancelada'" class="drawer-block drawer-block-muted">
                    <h5 class="drawer-block-title">Cancelación</h5>
                    <p class="drawer-notes">{{ visit.cancel_reason || 'Sin motivo registrado.' }}</p>
                </section>
            </div>

            <footer v-if="canEdit" class="drawer-foot">
                <button class="drawer-btn drawer-btn-ghost" @click="$emit('edit', visit)">
                    <i class="fa fa-pen me-2"></i> Editar
                </button>
                <button class="drawer-btn drawer-btn-danger" @click="$emit('cancel', visit)">
                    <i class="fa fa-ban me-2"></i> Cancelar visita
                </button>
            </footer>
        </aside>
    </div>
    </Teleport>
</template>

<script>
import dayjs from '@/utils/dayjs.js';

const STATUS_LABELS = {
    programada: 'Programada',
    reprogramacion_solicitada: 'Reprogramación solicitada',
    en_curso: 'En curso',
    completada: 'Completada',
    no_realizada: 'No realizada',
    cancelada: 'Cancelada',
};

const TYPE_LABELS = {
    preventivo: 'Mantenimiento preventivo',
    correctivo: 'Mantenimiento correctivo',
    especial: 'Servicio especial',
};

export default {
    name: 'VisitDrawer',
    props: {
        visit: { type: Object, required: true },
    },
    emits: ['close', 'edit', 'cancel'],
    computed: {
        statusLabel() {
            return STATUS_LABELS[this.visit.status] || this.visit.status;
        },
        typeLabel() {
            return TYPE_LABELS[this.visit.visit_type] || this.visit.visit_type;
        },
        whenLabel() {
            const start = dayjs(this.visit.scheduled_start);
            const end = dayjs(this.visit.scheduled_end);
            return `${this.capitalize(start.format('dddd D [de] MMMM'))} · ${start.format('HH:mm')}–${end.format('HH:mm')}`;
        },
        durationLabel() {
            const mins = dayjs(this.visit.scheduled_end).diff(dayjs(this.visit.scheduled_start), 'minute');
            const h = Math.floor(mins / 60);
            const rest = mins % 60;
            if (!h) return `${rest} minutos`;
            return rest ? `${h} h ${rest} min` : `${h} horas`;
        },
        /** Una visita cerrada ya no se toca desde el cronograma. */
        canEdit() {
            return !['completada', 'cancelada'].includes(this.visit.status);
        },
    },
    methods: {
        capitalize(s) {
            return s.charAt(0).toUpperCase() + s.slice(1);
        },
    },
};
</script>

<style scoped>
.drawer-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.45);
    z-index: 9998;
    display: flex;
    justify-content: flex-end;
}

.drawer {
    width: 100%;
    max-width: 400px;
    background: #fff;
    height: 100%;
    display: flex;
    flex-direction: column;
    box-shadow: -12px 0 32px rgba(15, 23, 42, 0.18);
    animation: drawer-in 0.18s ease-out;
}

@keyframes drawer-in {
    from { transform: translateX(24px); opacity: 0; }
    to   { transform: translateX(0); opacity: 1; }
}

.drawer-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    padding: 1.25rem 1.4rem 1rem;
    border-bottom: 1px solid #e9edf2;
}

.drawer-status {
    display: inline-block;
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    padding: 0.18rem 0.55rem;
    border-radius: 999px;
    margin-bottom: 0.5rem;
}

.status-programada { background: #eefbe9; color: #227a0c; }
.status-reprogramacion_solicitada { background: #fef6e7; color: #b45309; }
.status-en_curso { background: #e0f2fe; color: #0369a1; }
.status-completada { background: #f1f5f9; color: #475569; }
.status-no_realizada { background: #fef2f2; color: #ba2831; }
.status-cancelada { background: #f1f5f9; color: #94a3b8; }

.drawer-title {
    margin: 0;
    font-size: 1.2rem;
    font-weight: 700;
    color: #1e293b;
}

.drawer-sub {
    margin: 0.15rem 0 0;
    font-size: 0.85rem;
    color: #64748b;
}

.drawer-close {
    border: 0;
    background: #f1f5f9;
    color: #64748b;
    width: 32px;
    height: 32px;
    border-radius: 9px;
    cursor: pointer;
    flex-shrink: 0;
}

.drawer-body {
    flex: 1;
    overflow-y: auto;
    padding: 1.1rem 1.4rem;
}

.drawer-block {
    margin-bottom: 1.4rem;
}

.drawer-block-muted {
    background: #f8fafc;
    border-radius: 12px;
    padding: 0.9rem 1rem;
}

.drawer-block-title {
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #94a3b8;
    margin-bottom: 0.55rem;
}

.drawer-when {
    margin: 0;
    font-size: 0.98rem;
    font-weight: 600;
    color: #1e293b;
}

.drawer-duration {
    margin: 0.15rem 0 0;
    font-size: 0.82rem;
    color: #64748b;
}

.drawer-dl {
    margin: 0;
    display: grid;
    grid-template-columns: 96px 1fr;
    gap: 0.4rem 0.75rem;
    font-size: 0.88rem;
}

.drawer-dl dt {
    color: #94a3b8;
    font-weight: 500;
}

.drawer-dl dd {
    margin: 0;
    color: #1e293b;
}

.drawer-address {
    grid-column: 2;
    color: #64748b !important;
    font-size: 0.8rem;
}

.drawer-link {
    color: #30ab0a;
    text-decoration: none;
    font-weight: 600;
}

.drawer-notes {
    margin: 0;
    font-size: 0.88rem;
    color: #475569;
    white-space: pre-wrap;
}

.drawer-foot {
    display: flex;
    gap: 0.6rem;
    padding: 1rem 1.4rem;
    border-top: 1px solid #e9edf2;
    background: #fafcfe;
}

.drawer-btn {
    flex: 1;
    border: 0;
    border-radius: 10px;
    padding: 0.6rem 0.9rem;
    font-size: 0.86rem;
    font-weight: 600;
    cursor: pointer;
}

.drawer-btn-ghost {
    background: #eef2f6;
    color: #475569;
}

.drawer-btn-danger {
    background: #fef2f2;
    color: #ba2831;
}

.drawer-btn-danger:hover {
    background: #fee2e2;
}
</style>
