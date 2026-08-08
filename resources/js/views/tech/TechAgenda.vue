<template>
    <div class="agenda">
        <!-- Cabecera: semana visible -->
        <header class="agenda-head">
            <div class="agenda-head__title">
                <h1>Mi agenda</h1>
                <p>{{ weekLabel }}</p>
            </div>
            <div class="agenda-head__nav">
                <button class="agenda-nav-btn" @click="shiftWeek(-1)" aria-label="Semana anterior">
                    <i class="fa fa-chevron-left"></i>
                </button>
                <button class="agenda-nav-btn agenda-nav-btn--today" @click="goToday">Hoy</button>
                <button class="agenda-nav-btn" @click="shiftWeek(1)" aria-label="Semana siguiente">
                    <i class="fa fa-chevron-right"></i>
                </button>
            </div>
        </header>

        <!-- Tira semanal -->
        <div class="agenda-strip">
            <button
                v-for="day in week"
                :key="day.iso"
                class="agenda-day"
                :class="{ 'is-selected': day.iso === selectedIso, 'is-today': day.iso === todayIso }"
                @click="selectedIso = day.iso"
            >
                <span class="agenda-day__name">{{ day.name }}</span>
                <span class="agenda-day__number">{{ day.number }}</span>
                <span class="agenda-day__dot" :class="{ 'is-empty': !day.count }">
                    {{ day.count || '' }}
                </span>
            </button>
        </div>

        <div v-if="fromCache" class="agenda-cache-note">
            <i class="fa fa-wifi-slash"></i>
            Sin conexión — mostrando la última agenda descargada.
        </div>

        <!-- Timeline del día -->
        <div v-if="loading" class="agenda-loading">
            <i class="fa fa-spinner fa-spin"></i>
        </div>

        <div v-else-if="!dayVisits.length" class="agenda-empty">
            <i class="fa fa-calendar-check"></i>
            <p>Sin visitas {{ selectedIso === todayIso ? 'hoy' : 'este día' }}</p>
        </div>

        <div v-else class="agenda-timeline">
            <router-link
                v-for="visit in dayVisits"
                :key="visit.id"
                :to="{ name: 'tech.visit', params: { id: visit.id } }"
                class="agenda-item"
                :class="`is-${visit.status}`"
            >
                <div class="agenda-item__time">
                    <span class="agenda-item__start">{{ time(visit.scheduled_start) }}</span>
                    <span class="agenda-item__end">{{ time(visit.scheduled_end) }}</span>
                </div>
                <div class="agenda-item__body">
                    <div class="agenda-item__top">
                        <span class="agenda-item__code">{{ visit.equipment?.internal_code || 'Equipo' }}</span>
                        <span class="agenda-item__status">{{ statusLabel(visit.status) }}</span>
                    </div>
                    <p class="agenda-item__site">{{ visit.site?.name || '—' }}</p>
                    <p class="agenda-item__client">{{ visit.client?.business_name || '' }}</p>
                </div>
                <i class="fa fa-chevron-right agenda-item__arrow"></i>
            </router-link>
        </div>
    </div>
</template>

<script>
import dayjs from '@/utils/dayjs.js';
import scheduleService from '@/services/scheduleService.js';
import { statusLabel } from '@/utils/visitLabels.js';

// La agenda es lo primero que mira el técnico al llegar a una sede, muchas veces
// en un sótano sin señal: se guarda la última semana descargada para poder
// responder "¿qué me toca?" aunque la petición falle.
const CACHE_KEY = 'tzion-tech-agenda';

export default {
    name: 'TechAgenda',

    data() {
        const today = dayjs();
        return {
            weekStart: today.startOf('week'),
            selectedIso: today.format('YYYY-MM-DD'),
            todayIso: today.format('YYYY-MM-DD'),
            visits: [],
            loading: true,
            fromCache: false,
        };
    },

    computed: {
        weekLabel() {
            const end = this.weekStart.add(6, 'day');
            const sameMonth = this.weekStart.month() === end.month();
            const left = sameMonth ? this.weekStart.format('D') : this.weekStart.format('D MMM');
            return `${left} – ${end.format('D MMM YYYY')}`;
        },

        /**
         * Visitas agrupadas por día. La agrupación pasa por dayjs y no por el texto
         * de la fecha porque la API serializa en UTC: comparar la cadena metería en
         * el día siguiente todo lo de después de las 19:00.
         */
        visitsByDay() {
            const map = {};

            for (const visit of this.visits) {
                const key = dayjs(visit.scheduled_start).format('YYYY-MM-DD');
                (map[key] ||= []).push(visit);
            }

            Object.values(map).forEach(list => list.sort(
                (a, b) => a.scheduled_start.localeCompare(b.scheduled_start),
            ));

            return map;
        },

        week() {
            return Array.from({ length: 7 }, (_, i) => {
                const day = this.weekStart.add(i, 'day');
                const iso = day.format('YYYY-MM-DD');
                const short = day.format('dd');

                return {
                    iso,
                    name: short.charAt(0).toUpperCase() + short.slice(1),
                    number: day.format('D'),
                    count: (this.visitsByDay[iso] || []).length,
                };
            });
        },

        dayVisits() {
            return this.visitsByDay[this.selectedIso] || [];
        },
    },

    created() {
        this.load();
    },

    methods: {
        statusLabel,

        time(value) {
            return dayjs(value).format('HH:mm');
        },

        shiftWeek(delta) {
            this.weekStart = this.weekStart.add(delta, 'week');
            // Al cambiar de semana el día seleccionado deja de existir en la tira.
            this.selectedIso = this.weekStart.format('YYYY-MM-DD');
            this.load();
        },

        goToday() {
            const today = dayjs();
            this.weekStart = today.startOf('week');
            this.selectedIso = today.format('YYYY-MM-DD');
            this.load();
        },

        async load() {
            this.loading = true;
            this.fromCache = false;

            const from = this.weekStart.format('YYYY-MM-DD');
            const to = this.weekStart.add(7, 'day').format('YYYY-MM-DD');

            try {
                const { data } = await scheduleService.techAgenda(from, to);
                this.visits = data || [];
                this.cache(from, this.visits);
            } catch {
                this.visits = this.cached(from);
                this.fromCache = true;
            } finally {
                this.loading = false;
            }
        },

        cache(from, visits) {
            try {
                localStorage.setItem(CACHE_KEY, JSON.stringify({ from, visits }));
            } catch {}
        },

        cached(from) {
            try {
                const entry = JSON.parse(localStorage.getItem(CACHE_KEY) || 'null');
                // Solo sirve si es la misma semana: enseñar otra sería peor que nada.
                return entry?.from === from ? entry.visits : [];
            } catch {
                return [];
            }
        },
    },
};
</script>

<style scoped>
.agenda {
    padding: 1rem;
}

/* Cabecera */
.agenda-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 0.75rem;
    margin-bottom: 0.9rem;
}

.agenda-head__title h1 {
    font-size: 1.4rem;
    font-weight: 700;
    color: #1e293b;
    margin: 0;
}

.agenda-head__title p {
    font-size: 0.8rem;
    color: #64748b;
    margin: 0.1rem 0 0;
}

.agenda-head__nav {
    display: flex;
    align-items: center;
    gap: 0.3rem;
    flex-shrink: 0;
}

.agenda-nav-btn {
    min-width: 32px;
    height: 32px;
    border: 1px solid #e2e8f0;
    background: white;
    border-radius: 9px;
    color: #64748b;
    font-size: 0.8rem;
    cursor: pointer;
}

.agenda-nav-btn--today {
    padding: 0 0.7rem;
    font-weight: 600;
    color: #30ab0a;
}

/* Tira semanal */
.agenda-strip {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 0.3rem;
    margin-bottom: 1rem;
}

.agenda-day {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 2px;
    padding: 0.45rem 0.1rem 0.35rem;
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    cursor: pointer;
}

.agenda-day__name {
    font-size: 0.65rem;
    color: #94a3b8;
    text-transform: uppercase;
    font-weight: 600;
}

.agenda-day__number {
    font-size: 1rem;
    font-weight: 700;
    color: #1e293b;
    line-height: 1.1;
}

.agenda-day__dot {
    min-width: 16px;
    height: 16px;
    border-radius: 999px;
    background: #30ab0a;
    color: white;
    font-size: 0.6rem;
    font-weight: 700;
    line-height: 16px;
}

.agenda-day__dot.is-empty {
    background: transparent;
}

.agenda-day.is-today .agenda-day__number {
    color: #30ab0a;
}

.agenda-day.is-selected {
    background: #30ab0a;
    border-color: #30ab0a;
}

.agenda-day.is-selected .agenda-day__name,
.agenda-day.is-selected .agenda-day__number {
    color: white;
}

.agenda-day.is-selected .agenda-day__dot {
    background: white;
    color: #30ab0a;
}

.agenda-day.is-selected .agenda-day__dot.is-empty {
    background: transparent;
}

/* Avisos y vacíos */
.agenda-cache-note {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.55rem 0.9rem;
    background: #fffbeb;
    border: 1px solid #fde68a;
    border-radius: 10px;
    color: #d97706;
    font-size: 0.78rem;
    margin-bottom: 0.9rem;
}

.agenda-loading,
.agenda-empty {
    text-align: center;
    padding: 2.5rem 1rem;
    color: #94a3b8;
}

.agenda-empty i {
    font-size: 1.8rem;
    opacity: 0.5;
}

.agenda-empty p {
    margin: 0.6rem 0 0;
    font-size: 0.9rem;
}

/* Timeline */
.agenda-timeline {
    display: flex;
    flex-direction: column;
    gap: 0.6rem;
}

.agenda-item {
    display: flex;
    align-items: stretch;
    gap: 0.75rem;
    padding: 0.75rem;
    background: white;
    border: 1px solid #e2e8f0;
    border-left: 4px solid #30ab0a;
    border-radius: 12px;
    text-decoration: none;
    min-height: 68px;
}

.agenda-item:active {
    background: #f8fafc;
}

.agenda-item.is-en_curso { border-left-color: #2563eb; }
.agenda-item.is-completada { border-left-color: #64748b; }
.agenda-item.is-no_realizada { border-left-color: #ba2831; }
.agenda-item.is-reprogramacion_solicitada { border-left-color: #f59e0b; }

.agenda-item__time {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    min-width: 48px;
    border-right: 1px solid #f1f5f9;
    padding-right: 0.75rem;
}

.agenda-item__start {
    font-size: 0.95rem;
    font-weight: 700;
    color: #1e293b;
}

.agenda-item__end {
    font-size: 0.72rem;
    color: #94a3b8;
}

.agenda-item__body {
    flex: 1;
    min-width: 0;
}

.agenda-item__top {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.agenda-item__code {
    font-size: 0.88rem;
    font-weight: 700;
    color: #1e293b;
}

.agenda-item__status {
    font-size: 0.62rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    color: #94a3b8;
}

.agenda-item__site,
.agenda-item__client {
    margin: 0.1rem 0 0;
    font-size: 0.78rem;
    color: #64748b;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.agenda-item__client {
    color: #94a3b8;
    font-size: 0.72rem;
}

.agenda-item__arrow {
    align-self: center;
    color: #cbd5e1;
    font-size: 0.8rem;
}
</style>
