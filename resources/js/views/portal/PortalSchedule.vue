<template>
    <div>
        <!-- Cabecera -->
        <div class="app-page-title">
            <div class="page-title-wrapper">
                <div class="page-title-heading">
                    <div class="page-title-icon">
                        <i class="fa fa-calendar-alt icon-gradient bg-mean-fruit"></i>
                    </div>
                    <div>
                        Cronograma de Mantenimiento
                        <div class="page-title-subheading text-muted">
                            Tus próximas visitas programadas y el historial de las realizadas
                        </div>
                    </div>
                </div>
                <div class="page-title-actions">
                    <div class="view-toggle">
                        <button
                            class="view-toggle__btn"
                            :class="{ 'is-active': view === 'list' }"
                            @click="view = 'list'"
                        >
                            <i class="fa fa-list-ul"></i> Lista
                        </button>
                        <button
                            class="view-toggle__btn"
                            :class="{ 'is-active': view === 'calendar' }"
                            @click="showCalendar"
                        >
                            <i class="fa fa-calendar"></i> Calendario
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="loading" class="loading-state">
            <div class="spinner-border text-primary"></div>
            <p class="text-muted mt-3">Cargando cronograma...</p>
        </div>

        <!-- Vista lista -->
        <template v-else-if="view === 'list'">
            <section class="sched-section">
                <h2 class="sched-section__title">Próximas visitas</h2>

                <p v-if="!upcoming.length" class="sched-empty">
                    No hay visitas programadas por ahora.
                </p>

                <div v-else class="sched-list">
                    <article
                        v-for="visit in upcoming"
                        :key="visit.id"
                        class="sched-card"
                        :class="`is-${visit.status}`"
                    >
                        <div class="sched-card__date">
                            <span class="sched-card__weekday">{{ weekday(visit.scheduled_start) }}</span>
                            <span class="sched-card__day">{{ dayNumber(visit.scheduled_start) }}</span>
                            <span class="sched-card__month">{{ month(visit.scheduled_start) }}</span>
                        </div>
                        <div class="sched-card__body">
                            <div class="sched-card__top">
                                <span class="sched-card__code">{{ visit.equipment?.internal_code || 'Equipo' }}</span>
                                <span class="sched-status" :class="`status-${visit.status}`">
                                    {{ statusLabel(visit.status) }}
                                </span>
                            </div>
                            <p class="sched-card__site">{{ visit.site?.name || '—' }}</p>
                            <p class="sched-card__meta">
                                {{ timeRange(visit) }} · {{ visit.technician?.name || 'Técnico por asignar' }}
                            </p>
                            <p v-if="visit.pending_reschedule_request" class="sched-card__pending">
                                <i class="fa fa-clock me-1"></i>
                                Pediste moverla al {{ fullDateTime(visit.pending_reschedule_request.proposed_start) }},
                                pendiente de aprobación.
                            </p>
                        </div>
                        <div class="sched-card__aside">
                            <span class="sched-countdown">{{ countdown(visit.scheduled_start) }}</span>
                            <button
                                v-if="visit.can_request_reschedule"
                                class="sched-reschedule"
                                @click="reschedulingVisit = visit"
                            >
                                <i class="fa fa-calendar-day me-1"></i> Reprogramar
                            </button>
                        </div>
                    </article>
                </div>
            </section>

            <section class="sched-section">
                <h2 class="sched-section__title">Historial</h2>

                <p v-if="!history.length" class="sched-empty">
                    Todavía no hay visitas realizadas.
                </p>

                <div v-else class="table-card">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Equipo</th>
                                    <th>Sede</th>
                                    <th>Técnico</th>
                                    <th>Estado</th>
                                    <th class="th-actions">Reporte</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="visit in history" :key="visit.id">
                                    <td data-label="Fecha">{{ fullDate(visit.scheduled_start) }}</td>
                                    <td data-label="Equipo">
                                        <span class="code-text">{{ visit.equipment?.internal_code || '-' }}</span>
                                    </td>
                                    <td data-label="Sede">{{ visit.site?.name || '-' }}</td>
                                    <td data-label="Técnico">{{ visit.technician?.name || '-' }}</td>
                                    <td data-label="Estado">
                                        <span class="sched-status" :class="`status-${visit.status}`">
                                            {{ statusLabel(visit.status) }}
                                        </span>
                                    </td>
                                    <td class="cell-actions" data-label="Reporte">
                                        <router-link
                                            v-for="report in visit.service_reports || []"
                                            :key="report.id"
                                            :to="`/reportes/${report.id}`"
                                            class="sched-report-link"
                                        >
                                            {{ report.report_number }}
                                        </router-link>
                                        <span v-if="!(visit.service_reports || []).length" class="text-muted">—</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </template>

        <!-- Vista calendario -->
        <section v-else class="sched-calendar-wrap">
            <header class="sched-cal-head">
                <button class="sched-cal-btn" @click="shiftMonth(-1)" aria-label="Mes anterior">
                    <i class="fa fa-chevron-left"></i>
                </button>
                <h2 class="sched-cal-title">{{ monthLabel }}</h2>
                <button class="sched-cal-btn" @click="shiftMonth(1)" aria-label="Mes siguiente">
                    <i class="fa fa-chevron-right"></i>
                </button>
            </header>

            <div class="sched-cal-grid">
                <span v-for="name in weekdayNames" :key="name" class="sched-cal-dow">{{ name }}</span>

                <div
                    v-for="cell in calendarCells"
                    :key="cell.iso"
                    class="sched-cal-cell"
                    :class="{ 'is-outside': !cell.inMonth, 'is-today': cell.iso === todayIso }"
                >
                    <span class="sched-cal-number">{{ cell.number }}</span>
                    <div class="sched-cal-events">
                        <span
                            v-for="visit in cell.visits"
                            :key="visit.id"
                            class="sched-cal-event"
                            :class="`is-${visit.status}`"
                            :title="`${visit.equipment?.internal_code} · ${visit.site?.name || ''}`"
                        >
                            {{ time(visit.scheduled_start) }} {{ visit.equipment?.internal_code }}
                        </span>
                    </div>
                </div>
            </div>
        </section>

        <RescheduleRequestModal
            v-if="reschedulingVisit"
            :visit="reschedulingVisit"
            @close="reschedulingVisit = null"
            @submitted="onRescheduleSubmitted"
        />
    </div>
</template>

<script>
import dayjs from '@/utils/dayjs.js';
import portalService from '@/services/portalService.js';
import { statusLabel } from '@/utils/visitLabels.js';
// Import estatico: la vista ya es perezosa en el router, asi que el modal viaja
// en su mismo chunk y no engorda el bundle principal.
import RescheduleRequestModal from '@/components/schedule/RescheduleRequestModal.vue';

export default {
    name: 'PortalSchedule',

    components: { RescheduleRequestModal },

    data() {
        return {
            view: 'list',
            upcoming: [],
            history: [],
            monthVisits: [],
            monthStart: dayjs().startOf('month'),
            todayIso: dayjs().format('YYYY-MM-DD'),
            loading: true,
            reschedulingVisit: null,
        };
    },

    computed: {
        monthLabel() {
            const label = this.monthStart.format('MMMM YYYY');
            return label.charAt(0).toUpperCase() + label.slice(1);
        },

        weekdayNames() {
            return ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];
        },

        /**
         * Rejilla del mes, siempre semanas completas. Las visitas se agrupan con
         * dayjs y no por el texto de la fecha: la API serializa en UTC.
         */
        calendarCells() {
            const byDay = {};
            for (const visit of this.monthVisits) {
                const key = dayjs(visit.scheduled_start).format('YYYY-MM-DD');
                (byDay[key] ||= []).push(visit);
            }

            const first = this.monthStart.startOf('week');
            const last = this.monthStart.endOf('month').endOf('week');
            const cells = [];

            for (let day = first; day.isBefore(last); day = day.add(1, 'day')) {
                const iso = day.format('YYYY-MM-DD');
                cells.push({
                    iso,
                    number: day.format('D'),
                    inMonth: day.month() === this.monthStart.month(),
                    visits: (byDay[iso] || []).sort(
                        (a, b) => a.scheduled_start.localeCompare(b.scheduled_start),
                    ),
                });
            }

            return cells;
        },
    },

    async mounted() {
        await this.loadList();
    },

    methods: {
        statusLabel,

        time(value) {
            return dayjs(value).format('HH:mm');
        },
        weekday(value) {
            return dayjs(value).format('ddd').toUpperCase();
        },
        dayNumber(value) {
            return dayjs(value).format('D');
        },
        month(value) {
            return dayjs(value).format('MMM').toUpperCase();
        },
        fullDate(value) {
            return dayjs(value).format('D MMM YYYY');
        },
        fullDateTime(value) {
            return dayjs(value).format('D [de] MMMM [a las] HH:mm');
        },
        timeRange(visit) {
            return `${this.time(visit.scheduled_start)}–${this.time(visit.scheduled_end)}`;
        },

        countdown(value) {
            const days = dayjs(value).startOf('day').diff(dayjs().startOf('day'), 'day');
            if (days < 0) return '';
            if (days === 0) return 'Hoy';
            if (days === 1) return 'Mañana';
            return `En ${days} días`;
        },

        async loadList() {
            this.loading = true;
            try {
                const { data } = await portalService.schedule();
                this.upcoming = data.upcoming || [];
                this.history = data.history || [];
            } finally {
                this.loading = false;
            }
        },

        async loadMonth() {
            this.loading = true;
            try {
                const { data } = await portalService.schedule({
                    from: this.monthStart.startOf('week').format('YYYY-MM-DD'),
                    to: this.monthStart.endOf('month').endOf('week').format('YYYY-MM-DD'),
                });
                this.monthVisits = data || [];
            } finally {
                this.loading = false;
            }
        },

        showCalendar() {
            this.view = 'calendar';
            this.loadMonth();
        },

        shiftMonth(delta) {
            this.monthStart = this.monthStart.add(delta, 'month');
            this.loadMonth();
        },

        async onRescheduleSubmitted() {
            this.reschedulingVisit = null;

            this.$swal?.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: 'Solicitud enviada',
                text: 'Coordinación la revisará y te avisaremos por correo.',
                showConfirmButton: false,
                timer: 3500,
            });

            await this.loadList();
        },
    },
};
</script>

<style scoped>
/* Toggle lista / calendario */
.view-toggle {
    display: inline-flex;
    background: #f1f5f9;
    border-radius: 10px;
    padding: 3px;
    gap: 3px;
}

.view-toggle__btn {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    border: 0;
    background: none;
    border-radius: 8px;
    padding: 0.4rem 0.85rem;
    font-size: 0.82rem;
    font-weight: 600;
    color: #64748b;
    cursor: pointer;
}

.view-toggle__btn.is-active {
    background: white;
    color: #30ab0a;
    box-shadow: 0 1px 3px rgba(15, 23, 42, 0.1);
}

/* Secciones */
.sched-section {
    margin-bottom: 1.75rem;
}

.sched-section__title {
    font-size: 0.78rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: #94a3b8;
    margin: 0 0 0.75rem;
}

.sched-empty {
    background: white;
    border: 1px dashed #e2e8f0;
    border-radius: 12px;
    padding: 1.5rem;
    text-align: center;
    color: #94a3b8;
    font-size: 0.9rem;
    margin: 0;
}

.loading-state {
    text-align: center;
    padding: 3rem 1rem;
}

/* Tarjetas de próximas visitas */
.sched-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.sched-card {
    display: flex;
    align-items: stretch;
    gap: 1rem;
    background: white;
    border: 1px solid #e2e8f0;
    border-left: 4px solid #30ab0a;
    border-radius: 14px;
    padding: 0.9rem 1.1rem;
}

.sched-card.is-en_curso { border-left-color: #2563eb; }
.sched-card.is-reprogramacion_solicitada { border-left-color: #f59e0b; }

.sched-card__date {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-width: 58px;
    padding-right: 1rem;
    border-right: 1px solid #f1f5f9;
}

.sched-card__weekday,
.sched-card__month {
    font-size: 0.65rem;
    font-weight: 700;
    color: #94a3b8;
    letter-spacing: 0.04em;
}

.sched-card__day {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1e293b;
    line-height: 1.1;
}

.sched-card__body {
    flex: 1;
    min-width: 0;
}

.sched-card__top {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    flex-wrap: wrap;
}

.sched-card__code {
    font-size: 0.95rem;
    font-weight: 700;
    color: #1e293b;
}

.sched-card__site {
    margin: 0.15rem 0 0;
    font-size: 0.85rem;
    color: #475569;
}

.sched-card__meta {
    margin: 0.1rem 0 0;
    font-size: 0.78rem;
    color: #94a3b8;
}

.sched-card__pending {
    margin: 0.4rem 0 0;
    font-size: 0.78rem;
    font-weight: 600;
    color: #b45309;
}

.sched-card__aside {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    justify-content: center;
    gap: 0.5rem;
}

.sched-reschedule {
    border: 1px solid #e2e8f0;
    background: #fff;
    border-radius: 9px;
    padding: 0.35rem 0.75rem;
    font-size: 0.78rem;
    font-weight: 600;
    color: #64748b;
    white-space: nowrap;
    cursor: pointer;
}

.sched-reschedule:hover {
    border-color: #30ab0a;
    color: #30ab0a;
}

.sched-countdown {
    font-size: 0.75rem;
    font-weight: 700;
    color: #30ab0a;
    background: #eefbe9;
    border-radius: 999px;
    padding: 0.25rem 0.7rem;
    white-space: nowrap;
}

/* Chips de estado */
.sched-status {
    display: inline-block;
    font-size: 0.66rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    padding: 0.18rem 0.55rem;
    border-radius: 999px;
}

.status-programada { background: #eefbe9; color: #227a0c; }
.status-reprogramacion_solicitada { background: #fef6e7; color: #b45309; }
.status-en_curso { background: #e0f2fe; color: #0369a1; }
.status-completada { background: #f1f5f9; color: #475569; }
.status-no_realizada { background: #fef2f2; color: #ba2831; }

.sched-report-link {
    display: inline-block;
    margin-right: 0.5rem;
    color: #30ab0a;
    font-weight: 600;
    text-decoration: none;
    font-size: 0.85rem;
}

/* Tabla del historial */
.table-card {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    overflow: hidden;
}

.table-responsive {
    overflow-x: auto;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.86rem;
}

.data-table thead th {
    text-align: left;
    padding: 0.75rem 1rem;
    background: #f8fafc;
    color: #64748b;
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    border-bottom: 1px solid #e2e8f0;
    white-space: nowrap;
}

.data-table tbody td {
    padding: 0.75rem 1rem;
    border-bottom: 1px solid #f1f5f9;
    color: #334155;
}

.data-table tbody tr:last-child td {
    border-bottom: 0;
}

.code-text {
    font-weight: 700;
    color: #1e293b;
}

/* Calendario */
.sched-calendar-wrap {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 1rem;
}

.sched-cal-head {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1rem;
    margin-bottom: 0.9rem;
}

.sched-cal-title {
    font-size: 1.05rem;
    font-weight: 700;
    color: #1e293b;
    margin: 0;
    min-width: 180px;
    text-align: center;
}

.sched-cal-btn {
    width: 32px;
    height: 32px;
    border: 1px solid #e2e8f0;
    background: white;
    border-radius: 9px;
    color: #64748b;
    cursor: pointer;
}

.sched-cal-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 4px;
}

.sched-cal-dow {
    text-align: center;
    font-size: 0.68rem;
    font-weight: 700;
    color: #94a3b8;
    text-transform: uppercase;
    padding-bottom: 0.3rem;
}

.sched-cal-cell {
    min-height: 82px;
    /* Sin esto la celda crece con el texto del evento (min-width: auto) y los
       chips se salen de su columna. */
    min-width: 0;
    background: #f8fafc;
    border-radius: 8px;
    padding: 0.35rem;
    display: flex;
    flex-direction: column;
    gap: 3px;
}

.sched-cal-cell.is-outside {
    opacity: 0.45;
}

.sched-cal-cell.is-today {
    background: #eefbe9;
    outline: 1px solid #30ab0a;
}

.sched-cal-number {
    font-size: 0.72rem;
    font-weight: 700;
    color: #64748b;
}

.sched-cal-events {
    display: flex;
    flex-direction: column;
    gap: 2px;
    min-width: 0;
    overflow: hidden;
}

.sched-cal-event {
    font-size: 0.62rem;
    font-weight: 600;
    padding: 2px 4px;
    border-radius: 4px;
    background: #eefbe9;
    color: #227a0c;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.sched-cal-event.is-en_curso { background: #e0f2fe; color: #0369a1; }
.sched-cal-event.is-completada { background: #f1f5f9; color: #475569; }
.sched-cal-event.is-no_realizada { background: #fef2f2; color: #ba2831; }
.sched-cal-event.is-reprogramacion_solicitada { background: #fef6e7; color: #b45309; }

@media (max-width: 768px) {
    .sched-card {
        flex-wrap: wrap;
    }

    /* En movil la tarjeta se parte y el lateral pasa a ser una fila propia: en
       horizontal el countdown y el boton caben de sobra. */
    .sched-card__aside {
        width: 100%;
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
        padding-top: 0.6rem;
        border-top: 1px solid #f1f5f9;
    }

    .sched-cal-cell {
        min-height: 62px;
    }

    .sched-cal-event {
        font-size: 0.55rem;
    }
}
</style>
