<template>
    <div>
        <!-- Cabecera -->
        <div class="app-page-title">
            <div class="page-title-wrapper">
                <div class="page-title-heading">
                    <div class="page-title-icon">
                        <i class="fa fa-calendar-week icon-gradient bg-mean-fruit"></i>
                    </div>
                    <div>
                        Cronograma
                        <div class="page-title-subheading text-muted">
                            Programa y reorganiza las visitas de mantenimiento
                        </div>
                    </div>
                </div>
                <div class="page-title-actions">
                    <button class="btn-holidays" @click="showHolidays = true">
                        <i class="fa fa-calendar-xmark me-2"></i> Días no laborables
                    </button>
                    <button class="btn-create" @click="openCreate()">
                        <i class="fa fa-plus me-2"></i> Programar visita
                    </button>
                </div>
            </div>
        </div>

        <!-- Solicitudes del cliente esperando decisión -->
        <div v-if="pendingRequests.length" class="resched-banner">
            <i class="fa fa-history"></i>
            <span>
                <strong>{{ pendingRequests.length }}</strong>
                {{ pendingRequests.length === 1 ? 'solicitud de reprogramación pendiente' : 'solicitudes de reprogramación pendientes' }}
            </span>
            <button class="resched-banner__btn" @click="showRequests = !showRequests">
                {{ showRequests ? 'Ocultar' : 'Revisar' }}
                <i :class="showRequests ? 'fa fa-chevron-up' : 'fa fa-arrow-right'" class="ms-1"></i>
            </button>
        </div>

        <RescheduleInbox
            v-if="showRequests"
            :requests="pendingRequests"
            :loading="loadingRequests"
            @approve="approveRequest"
            @reject="rejectRequest"
            @locate="locateRequest"
        />

        <!-- Controles -->
        <div class="filter-bar">
            <div class="filter-row">
                <div class="filter-group">
                    <label class="filter-label">Técnico</label>
                    <select v-model="filters.technician_id" class="filter-select" @change="onTechnicianChange">
                        <option value="">Todos (resumen del mes)</option>
                        <option v-for="t in technicians" :key="t.id" :value="t.id">{{ t.name }}</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label class="filter-label">Cliente</label>
                    <select v-model="filters.client_id" class="filter-select" @change="onClientFilter">
                        <option value="">Todos</option>
                        <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.business_name }}</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label class="filter-label">Sede</label>
                    <select v-model="filters.site_id" class="filter-select" :disabled="!filters.client_id" @change="load">
                        <option value="">Todas</option>
                        <option v-for="s in filteredSites" :key="s.id" :value="s.id">{{ s.name }}</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label class="filter-label">Estado</label>
                    <select v-model="filters.status" class="filter-select" @change="load">
                        <option value="">Todos</option>
                        <option v-for="(label, key) in statusLabels" :key="key" :value="key">{{ label }}</option>
                    </select>
                </div>
                <div v-if="hasFilters" class="filter-group filter-clear">
                    <button class="btn-clear" @click="clearFilters">
                        <i class="fa fa-times me-1"></i> Limpiar
                    </button>
                </div>
            </div>
        </div>

        <!-- Calendario -->
        <div class="schedule-card">
            <div v-if="loading" class="schedule-loading">
                <i class="fa fa-spinner fa-spin me-2"></i> Cargando cronograma…
            </div>

            <!-- Leyenda: el color dice el tipo de visita, no quién la atiende. -->
            <div v-show="!loading" class="schedule-legend">
                <span class="legend-item"><i class="legend-dot dot-preventivo"></i> Preventivo</span>
                <span class="legend-item"><i class="legend-dot dot-correctivo"></i> Correctivo</span>
                <span class="legend-item"><i class="legend-dot dot-especial"></i> Especial</span>
                <span class="legend-item"><i class="legend-ring ring-encurso"></i> En curso</span>
                <span class="legend-item"><i class="legend-ring ring-reprog"></i> Reprogramación solicitada</span>
                <span class="legend-item"><i class="legend-dot dot-cerrada"></i> Completada o cancelada</span>
                <span v-if="hasTechnician" class="legend-item"><i class="legend-dot dot-break"></i> Fuera de jornada o descanso (se puede agendar igual)</span>
            </div>

            <!-- Sin técnico elegido solo hay mes: el resumen de todos. Cada visita
                 lleva la insignia de su técnico; las fichas de abajo dicen de quién
                 es cada color y sirven de atajo para abrir su semana. -->
            <div v-if="!hasTechnician && !loading" class="tech-overview">
                <span class="tech-overview__hint">
                    <i class="fa fa-info-circle me-1"></i>
                    Resumen del mes con todos los técnicos. Elige uno para ver su semana o su día.
                </span>
                <div class="tech-overview__chips">
                    <button
                        v-for="t in overviewTechnicians"
                        :key="t.id"
                        type="button"
                        class="tech-chip"
                        :disabled="t.inactive"
                        :title="t.inactive ? `${t.name} ya no está activo` : `Ver la agenda de ${t.name}`"
                        @click="selectTechnician(t.id)"
                    >
                        <span class="tech-badge" :style="{ background: technicianColor(t.id) }">{{ initials(t.name) }}</span>
                        {{ t.name }}<span v-if="t.inactive" class="tech-chip__note">(inactivo)</span>
                    </button>
                </div>
            </div>

            <div v-show="!loading">
                <vue-cal
                    ref="cal"
                    class="tz-schedule"
                    locale="es"
                    :time-from="0"
                    :time-to="24 * 60"
                    :time-step="30"
                    :time-cell-height="30"
                    :disable-views="disabledViews"
                    :active-view="view"
                    :selected-date="selectedDate"
                    :special-hours="specialHours"
                    :events="events"
                    :editable-events="editableEvents"
                    :snap-to-time="15"
                    :on-event-click="openDrawer"
                    events-on-month-view="short"
                    @view-change="onViewChange"
                    @ready="onViewChange"
                    @cell-click="onCellClick"
                    @event-drop="onEventDrop"
                    @event-duration-change="onEventDrop"
                >
                    <template #event="{ event, view: evView }">
                        <!-- En el mes cada celda es un día entero: el bloque de
                             varias líneas desbordaba la casilla y deformaba la
                             rejilla, así que ahí va en una sola línea. -->
                        <div v-if="evView === 'month'" class="tz-event tz-event-compact" :title="event.tooltip">
                            <!-- En el resumen general el técnico va en la insignia;
                                 con uno elegido sobra, ya se sabe de quién es. -->
                            <span v-if="!hasTechnician" class="tech-badge tech-badge--sm" :style="{ background: event.technicianColor }">{{ event.technicianInitials }}</span>
                            {{ event.startTimeLabel }} · {{ event.equipmentCode }}
                        </div>
                        <div v-else class="tz-event">
                            <strong class="tz-event-code">{{ event.equipmentCode }}</strong>
                            <span class="tz-event-time">{{ event.timeLabel }}</span>
                            <span class="tz-event-meta">{{ event.siteName }}</span>
                        </div>
                    </template>
                </vue-cal>
            </div>
        </div>

        <!-- Modal crear / editar -->
        <VisitFormModal
            v-if="showForm"
            :visit="editingVisit"
            :prefill="prefill"
            :clients="clients"
            :sites="sites"
            :equipment="equipment"
            :technicians="technicians"
            :defaults="defaults"
            @close="closeForm"
            @saved="onSaved"
        />

        <!-- Drawer de detalle -->
        <VisitDrawer
            v-if="selectedVisit"
            :visit="selectedVisit"
            @close="selectedVisit = null"
            @edit="openEdit"
            @cancel="confirmCancel"
            @approve-reschedule="approveFromDrawer"
            @reject-reschedule="rejectFromDrawer"
        />

        <HolidaysModal
            v-if="showHolidays"
            @close="showHolidays = false"
            @changed="load"
        />
    </div>
</template>

<script>
import VueCal from 'vue-cal';
import 'vue-cal/dist/vuecal.css';
// El locale no se importa: vue-cal lo carga por su cuenta con la prop locale="es".
import dayjs from '@/utils/dayjs.js';

import scheduleService from '@/services/scheduleService.js';
import clientService from '@/services/clientService.js';
import equipmentService from '@/services/equipmentService.js';
import VisitFormModal from './VisitFormModal.vue';
import VisitDrawer from './VisitDrawer.vue';
import RescheduleInbox from '@/components/schedule/RescheduleInbox.vue';
import HolidaysModal from '@/components/schedule/HolidaysModal.vue';
import { STATUS_LABELS } from '@/utils/visitLabels.js';

/**
 * Colores de técnico para el resumen del mes. Distintos de los del tipo de visita
 * (verde, rojo, azul) porque el fondo del evento sigue diciendo el tipo.
 */
const TECHNICIAN_PALETTE = ['#7c3aed', '#ea580c', '#0891b2', '#db2777', '#4d7c0f', '#b45309', '#4f46e5', '#0f766e', '#9333ea', '#be123c'];

/** Estados que no se pueden arrastrar: la visita ya está cerrada. */
const LOCKED_STATUSES = ['completada', 'cancelada'];

export default {
    name: 'ScheduleBoard',
    components: { VueCal, VisitFormModal, VisitDrawer, RescheduleInbox, HolidaysModal },
    data() {
        return {
            loading: true,
            // Sin técnico elegido solo existe el mes (resumen de todos); semana y
            // día se abren al elegir uno.
            view: 'month',
            visits: [],
            technicians: [],
            defaults: {},
            clients: [],
            sites: [],
            equipment: [],
            // Rango visible; lo fija vue-cal en cada cambio de vista y es lo que
            // se le pide al backend (que exige from/to).
            range: { start: null, end: null },
            filters: {
                client_id: '',
                site_id: '',
                technician_id: '',
                status: '',
            },
            statusLabels: STATUS_LABELS,
            showForm: false,
            editingVisit: null,
            prefill: null,
            selectedVisit: null,
            // Bandeja de reprogramaciones: panel dentro del tablero, sin ruta
            // propia. El correo a coordinación llega con ?solicitudes=1.
            pendingRequests: [],
            loadingRequests: false,
            showRequests: false,
            // Fecha a la que salta el calendario al pulsar "Ver en calendario".
            selectedDate: null,
            showHolidays: false,
            scrollTimer: null,
        };
    },
    computed: {
        hasTechnician() {
            return !!this.filters.technician_id;
        },
        selectedTechnician() {
            return this.technicians.find(t => String(t.id) === String(this.filters.technician_id)) || null;
        },
        /**
         * Semana y día son por técnico: con todos a la vez las columnas no cabían
         * (en la semana solo se veían lunes y martes). Sin técnico, solo el mes.
         */
        disabledViews() {
            return this.hasTechnician ? ['years', 'year'] : ['years', 'year', 'week', 'day'];
        },
        /**
         * La rejilla va de 00:00 a 24:00: coordinación agenda a cualquier hora
         * (urgencias). La jornada del técnico se sigue pintando, rayada, como
         * referencia de lo que es su horario habitual, pero no bloquea nada.
         */
        specialHours() {
            const window = this.selectedTechnician?.working_window;
            if (!window) return {};

            const start = this.toMinutes(window.start) ?? 0;
            const end = this.toMinutes(window.end) ?? 24 * 60;
            const hours = {};
            for (let day = 1; day <= 7; day++) {
                if (!window.days?.includes(day)) {
                    hours[day] = { from: 0, to: 24 * 60, class: 'tz-closed' };
                    continue;
                }
                const ranges = [];
                if (start > 0) ranges.push({ from: 0, to: start, class: 'tz-closed' });
                if (window.break_start && window.break_end) {
                    ranges.push({
                        from: this.toMinutes(window.break_start),
                        to: this.toMinutes(window.break_end),
                        class: 'tz-break',
                    });
                }
                if (end < 24 * 60) ranges.push({ from: end, to: 24 * 60, class: 'tz-closed' });
                hours[day] = ranges;
            }
            return hours;
        },
        /**
         * Técnicos del resumen del mes: los activos más los que tengan visitas en
         * pantalla aunque ya no estén activos (si no, sus visitas saldrían con una
         * insignia que nadie explica).
         */
        overviewTechnicians() {
            const list = [...this.technicians];
            const known = new Set(list.map(t => String(t.id)));
            this.visits.forEach(v => {
                if (v.technician && !known.has(String(v.technician.id))) {
                    known.add(String(v.technician.id));
                    list.push({ id: v.technician.id, name: v.technician.name, inactive: true });
                }
            });
            return list;
        },
        /** Color fijo por técnico (según su posición en la lista) para el resumen del mes. */
        technicianColors() {
            const map = {};
            this.overviewTechnicians.forEach((t, i) => {
                map[t.id] = TECHNICIAN_PALETTE[i % TECHNICIAN_PALETTE.length];
            });
            return map;
        },
        events() {
            return this.visits.map(v => {
                const start = dayjs(v.scheduled_start);
                const end = dayjs(v.scheduled_end);
                return {
                    id: v.id,
                    start: start.format('YYYY-MM-DD HH:mm'),
                    end: end.format('YYYY-MM-DD HH:mm'),
                    title: v.equipment?.internal_code || 'Visita',
                    class: `tz-ev tz-ev-${v.visit_type} tz-ev-status-${v.status}`,
                    draggable: !LOCKED_STATUSES.includes(v.status),
                    resizable: !LOCKED_STATUSES.includes(v.status),
                    // Datos propios para el slot del evento y el drawer
                    equipmentCode: v.equipment?.internal_code || '—',
                    siteName: v.site?.name || '',
                    technicianName: v.technician?.name || '',
                    technicianInitials: this.initials(v.technician?.name),
                    technicianColor: this.technicianColor(v.technician_id),
                    tooltip: [
                        `${start.format('HH:mm')}–${end.format('HH:mm')}`,
                        v.equipment?.internal_code,
                        v.technician?.name || 'Sin técnico',
                        v.site?.name,
                    ].filter(Boolean).join(' · '),
                    timeLabel: `${start.format('HH:mm')}–${end.format('HH:mm')}`,
                    startTimeLabel: start.format('HH:mm'),
                    raw: v,
                };
            });
        },
        editableEvents() {
            return { title: false, drag: true, resize: true, delete: false, create: false };
        },
        filteredSites() {
            if (!this.filters.client_id) return [];
            return this.sites.filter(s => String(s.client_id) === String(this.filters.client_id));
        },
        hasFilters() {
            return Object.values(this.filters).some(Boolean);
        },
    },
    beforeUnmount() {
        clearTimeout(this.scrollTimer);
    },
    async created() {
        // El enlace del correo de solicitud trae ?solicitudes=1: quien viene de
        // ahí viene a decidir, no a mirar el calendario.
        this.showRequests = this.$route.query.solicitudes === '1';

        await Promise.all([this.loadCatalogs(), this.loadRequests()]);
    },
    methods: {
        technicianColor(id) {
            return this.technicianColors[id] || '#64748b';
        },

        initials(name) {
            if (!name) return '?';
            const parts = name.trim().split(/\s+/);
            return ((parts[0]?.[0] || '') + (parts[1]?.[0] || '')).toUpperCase();
        },

        toMinutes(hhmm) {
            if (!hhmm) return null;
            const [h, m] = hhmm.split(':');
            return Number(h) * 60 + Number(m || 0);
        },

        async loadCatalogs() {
            try {
                const [techRes, clientRes, equipRes] = await Promise.all([
                    scheduleService.technicians(),
                    clientService.all(),
                    equipmentService.all(),
                ]);
                this.technicians = techRes.data.technicians || [];
                this.defaults = techRes.data.defaults || {};
                this.clients = this.unwrap(clientRes.data);
                this.equipment = this.unwrap(equipRes.data);
                this.sites = this.deriveSites(this.equipment);
            } catch (err) {
                this.$swal.fire({
                    icon: 'error',
                    title: 'No se pudo cargar el cronograma',
                    text: err.response?.data?.message || err.message,
                    confirmButtonText: 'Aceptar',
                });
            }
        },

        /** Los índices del proyecto responden a veces {data: []} y a veces []. */
        unwrap(payload) {
            if (Array.isArray(payload)) return payload;
            return payload?.data ?? [];
        },

        /**
         * Las sedes salen del listado de equipos (que ya trae site con client_id) y
         * no de un endpoint propio: /sites está anidado bajo cliente y habría que
         * pedirlo cliente por cliente. Además este es justo el conjunto útil, porque
         * solo se puede agendar sobre un equipo.
         */
        deriveSites(equipment) {
            const byId = new Map();
            equipment.forEach(e => {
                if (e.site && !byId.has(e.site.id)) {
                    byId.set(e.site.id, {
                        id: e.site.id,
                        name: e.site.name,
                        client_id: e.site.client_id,
                    });
                }
            });
            return [...byId.values()].sort((a, b) => a.name.localeCompare(b.name));
        },

        /** vue-cal informa el rango visible al cambiar de vista o de fecha. */
        onViewChange(event) {
            if (!event?.startDate) return;
            this.range = {
                start: dayjs(event.startDate).format('YYYY-MM-DD 00:00'),
                end: dayjs(event.endDate).format('YYYY-MM-DD 23:59'),
            };
            this.view = event.view || this.view;
            this.load();
            this.scrollToWorkingHours();
        },

        /**
         * La rejilla tiene las 24 horas; al abrir semana o día se baja hasta el
         * inicio de la jornada para no aterrizar en la madrugada.
         */
        scrollToWorkingHours() {
            if (this.view === 'month') return;
            const start = this.toMinutes(this.selectedTechnician?.working_window?.start)
                ?? this.toMinutes(this.defaults.working_hours?.start)
                ?? 8 * 60;
            // vue-cal anima el cambio de vista y durante la transición conviven la
            // rejilla vieja y la nueva: hay que esperar a que acabe y tomar la que queda.
            clearTimeout(this.scrollTimer);
            this.scrollTimer = setTimeout(() => {
                const grids = this.$refs.cal?.$el?.querySelectorAll('.vuecal__bg');
                const bg = grids?.[grids.length - 1];
                // 30px por celda de 30 min; media hora de margen por encima.
                if (bg) bg.scrollTop = Math.max(0, start - 30);
            }, 400);
        },

        /**
         * Elegir técnico abre su semana (su día en el teléfono); quitarlo vuelve
         * al resumen del mes, que es la única vista con todos.
         */
        onTechnicianChange() {
            if (!this.hasTechnician) {
                this.view = 'month';
            } else if (this.view === 'month') {
                this.view = window.innerWidth < 768 ? 'day' : 'week';
            }
            this.load();
            this.scrollToWorkingHours();
        },

        selectTechnician(id) {
            this.filters.technician_id = id;
            this.onTechnicianChange();
        },

        async load() {
            if (!this.range.start) return;
            this.loading = true;
            try {
                const { data } = await scheduleService.visits({
                    from: this.range.start,
                    to: this.range.end,
                    ...this.cleanFilters(),
                });
                this.visits = data;
            } catch (err) {
                this.$swal.fire({
                    icon: 'error',
                    title: 'Error al cargar visitas',
                    text: err.response?.data?.message || err.message,
                    confirmButtonText: 'Aceptar',
                });
            } finally {
                this.loading = false;
            }
        },

        cleanFilters() {
            return Object.fromEntries(
                Object.entries(this.filters).filter(([, v]) => v !== '' && v !== null),
            );
        },

        onClientFilter() {
            this.filters.site_id = '';
            this.load();
        },

        clearFilters() {
            this.filters = { client_id: '', site_id: '', technician_id: '', status: '' };
            this.view = 'month';
            this.load();
        },

        // ── Crear / editar ──

        /**
         * Clic en un hueco libre: precarga fecha, hora y, si se está viendo por
         * técnico, la columna donde se hizo clic.
         */
        onCellClick(payload) {
            const date = payload?.date || payload;
            if (!date) return;
            // En vista mes el clic navega al día en vez de crear: crear a las 00:00
            // no tendría sentido.
            if (this.view === 'month') return;

            this.prefill = {
                start: dayjs(date).format('YYYY-MM-DD HH:mm'),
                technician_id: this.filters.technician_id || null,
            };
            this.editingVisit = null;
            this.showForm = true;
        },

        openCreate() {
            this.prefill = null;
            this.editingVisit = null;
            this.showForm = true;
        },

        openEdit(visit) {
            this.selectedVisit = null;
            this.prefill = null;
            this.editingVisit = visit;
            this.showForm = true;
        },

        closeForm() {
            this.showForm = false;
            this.editingVisit = null;
            this.prefill = null;
        },

        onSaved() {
            this.closeForm();
            this.load();
        },

        async openDrawer(event, e) {
            e?.stopPropagation();

            // Se pinta ya con lo que trae el calendario y se completa después: el
            // listado del mes no carga los recordatorios (serían cientos de filas
            // para enseñar unas pocas), así que el detalle los pide al abrirse.
            this.selectedVisit = event.raw;

            try {
                const { data } = await scheduleService.visit(event.raw.id);
                // Si mientras tanto se cerró o se abrió otra, no pisar nada.
                if (this.selectedVisit?.id === data.id) {
                    this.selectedVisit = data;
                }
            } catch {}
        },

        // ── Drag & drop / resize ──

        /**
         * Mover o redimensionar. El backend revalida jornada, descanso y solapes,
         * así que un rechazo se resuelve recargando: el evento vuelve a su sitio.
         */
        async onEventDrop({ event }) {
            const visit = event.raw;
            const start = dayjs(event.start).format('YYYY-MM-DD HH:mm');
            const end = dayjs(event.end).format('YYYY-MM-DD HH:mm');
            const technicianId = visit.technician_id;

            const confirmed = await this.$swal.fire({
                icon: 'question',
                title: '¿Reprogramar la visita?',
                html: `<b>${visit.equipment?.internal_code || 'Visita'}</b><br>`
                    + `${dayjs(event.start).format('dddd D [de] MMMM, HH:mm')} – ${dayjs(event.end).format('HH:mm')}`,
                showCancelButton: true,
                confirmButtonText: 'Reprogramar',
                cancelButtonText: 'Deshacer',
                confirmButtonColor: '#30ab0a',
            });

            if (!confirmed.isConfirmed) {
                this.load();
                return;
            }

            try {
                await scheduleService.update(visit.id, {
                    scheduled_start: start,
                    scheduled_end: end,
                    technician_id: technicianId,
                });
                this.$swal.fire({
                    icon: 'success',
                    title: 'Visita reprogramada',
                    timer: 1600,
                    showConfirmButton: false,
                });
            } catch (err) {
                const bag = err.response?.data?.errors;
                this.$swal.fire({
                    icon: 'error',
                    title: 'No se pudo mover',
                    html: bag
                        ? Object.values(bag).flat().join('<br>')
                        : (err.response?.data?.message || err.message),
                    confirmButtonText: 'Aceptar',
                });
            } finally {
                this.load();
            }
        },

        async confirmCancel(visit) {
            const { isConfirmed, value } = await this.$swal.fire({
                icon: 'warning',
                title: '¿Cancelar esta visita?',
                input: 'text',
                inputLabel: 'Motivo (opcional)',
                inputPlaceholder: 'Por ejemplo: el cliente pidió aplazarla',
                showCancelButton: true,
                confirmButtonText: 'Cancelar visita',
                cancelButtonText: 'Volver',
                confirmButtonColor: '#ba2831',
            });

            if (!isConfirmed) return;

            try {
                await scheduleService.cancel(visit.id, value || null);
                this.selectedVisit = null;
                this.load();
            } catch (err) {
                this.$swal.fire({
                    icon: 'error',
                    title: 'No se pudo cancelar',
                    text: err.response?.data?.message || err.message,
                    confirmButtonText: 'Aceptar',
                });
            }
        },

        // ── Bandeja de reprogramaciones ──

        async loadRequests() {
            this.loadingRequests = true;
            try {
                const { data } = await scheduleService.rescheduleRequests({ status: 'pendiente' });
                this.pendingRequests = data.requests || [];
            } catch {
                // Sin ruido: el tablero sigue siendo útil aunque la bandeja falle.
                this.pendingRequests = [];
            } finally {
                this.loadingRequests = false;
            }
        },

        async approveRequest(request, force = false) {
            // Sin dato de disponibilidad (por ejemplo desde el drawer) no se asume
            // conflicto: se confirma normal y, si lo hay, lo dice el 422 de abajo.
            const clash = request.availability ? !request.availability.technician_free : false;

            const { isConfirmed } = clash
                ? await this.$swal.fire({
                    icon: 'warning',
                    title: 'El horario ya no está libre',
                    html: `${(request.availability?.problems || []).join('<br>')}`
                        + '<br><br>Puedes aprobarla igual, pero el técnico quedará con dos visitas encima.',
                    showCancelButton: true,
                    confirmButtonText: 'Aprobar de todos modos',
                    cancelButtonText: 'Volver',
                    confirmButtonColor: '#ba2831',
                })
                : await this.$swal.fire({
                    icon: 'question',
                    title: '¿Aprobar la reprogramación?',
                    html: `<b>${request.scheduled_visit?.equipment?.internal_code || 'Visita'}</b><br>`
                        + `${dayjs(request.proposed_start).format('dddd D [de] MMMM, HH:mm')}`,
                    showCancelButton: true,
                    confirmButtonText: 'Aprobar y mover',
                    cancelButtonText: 'Volver',
                    confirmButtonColor: '#30ab0a',
                });

            if (!isConfirmed) return;

            try {
                await scheduleService.approveRescheduleRequest(request.id, { force: force || clash });
                this.$swal.fire({
                    icon: 'success',
                    title: 'Visita reprogramada',
                    timer: 1600,
                    showConfirmButton: false,
                });
                this.selectedVisit = null;
            } catch (err) {
                const bag = err.response?.data?.errors;

                // Carrera: el hueco se ocupó entre la carga de la bandeja y el
                // clic. Se ofrece forzar en vez de dejar el aviso en un callejón.
                if (bag?.scheduled_start && !force) {
                    const retry = await this.$swal.fire({
                        icon: 'warning',
                        title: 'El horario acaba de ocuparse',
                        html: bag.scheduled_start.join('<br>'),
                        showCancelButton: true,
                        confirmButtonText: 'Aprobar de todos modos',
                        cancelButtonText: 'Volver',
                        confirmButtonColor: '#ba2831',
                    });

                    if (retry.isConfirmed) {
                        await scheduleService.approveRescheduleRequest(request.id, { force: true });
                        this.selectedVisit = null;
                    }
                } else {
                    this.$swal.fire({
                        icon: 'error',
                        title: 'No se pudo aprobar',
                        html: bag
                            ? Object.values(bag).flat().join('<br>')
                            : (err.response?.data?.message || err.message),
                        confirmButtonText: 'Aceptar',
                    });
                }
            } finally {
                await Promise.all([this.loadRequests(), this.load()]);
            }
        },

        async rejectRequest(request) {
            const { isConfirmed, value } = await this.$swal.fire({
                icon: 'warning',
                title: '¿Rechazar la solicitud?',
                input: 'textarea',
                inputLabel: 'Motivo (se le enviará al cliente)',
                inputPlaceholder: 'Por ejemplo: ese día el técnico está en mantenimiento en otra sede',
                inputValidator: v => (v && v.trim() ? undefined : 'Explica al cliente por qué no se puede.'),
                showCancelButton: true,
                confirmButtonText: 'Rechazar',
                cancelButtonText: 'Volver',
                confirmButtonColor: '#ba2831',
            });

            if (!isConfirmed) return;

            try {
                await scheduleService.rejectRescheduleRequest(request.id, value.trim());
                this.selectedVisit = null;
                this.$swal.fire({
                    icon: 'success',
                    title: 'Solicitud rechazada',
                    text: 'Le avisamos al cliente con tu motivo.',
                    timer: 2200,
                    showConfirmButton: false,
                });
            } catch (err) {
                this.$swal.fire({
                    icon: 'error',
                    title: 'No se pudo rechazar',
                    text: err.response?.data?.message || err.message,
                    confirmButtonText: 'Aceptar',
                });
            } finally {
                await Promise.all([this.loadRequests(), this.load()]);
            }
        },

        /**
         * Desde el drawer se resuelve con los mismos métodos de la bandeja, pero
         * usando la versión enriquecida de la solicitud si ya está cargada: la del
         * detalle de la visita no trae el chequeo de disponibilidad.
         */
        approveFromDrawer(request) {
            return this.approveRequest(this.enrich(request));
        },

        rejectFromDrawer(request) {
            return this.rejectRequest(this.enrich(request));
        },

        enrich(request) {
            return this.pendingRequests.find(r => r.id === request.id) || request;
        },

        /** Salta al día propuesto para ver el hueco en contexto. */
        locateRequest(request) {
            this.showRequests = false;
            // El día es por técnico: se abre el del técnico de esa visita.
            const technicianId = request.scheduled_visit?.technician?.id || request.scheduled_visit?.technician_id;
            if (technicianId) this.filters.technician_id = technicianId;
            this.view = technicianId ? 'day' : 'month';
            this.selectedDate = dayjs(request.proposed_start).toDate();
        },
    },
};
</script>

<style scoped>
/* Cabecera y filtros: estas clases NO son globales, cada vista las define en su
   propio bloque scoped. Se replican aquí las del resto del panel para que el
   cronograma no desentone. */
.btn-holidays {
    display: inline-flex;
    align-items: center;
    padding: 0.625rem 1.1rem;
    margin-right: 0.5rem;
    background: #fffbeb;
    border: 1px solid #fde68a;
    color: #b45309;
    font-weight: 600;
    border-radius: 10px;
    cursor: pointer;
}

.btn-holidays:hover { background: #fef3c7; }

.btn-create {
    display: inline-flex;
    align-items: center;
    padding: 0.625rem 1.25rem;
    background: #279208;
    color: white;
    font-weight: 600;
    border-radius: 10px;
    text-decoration: none;
    transition: all 0.2s ease;
    box-shadow: 0 2px 8px rgba(39, 146, 8, 0.25);
    border: none;
    cursor: pointer;
}

.btn-create:hover {
    background: #1f7506;
    box-shadow: 0 4px 12px rgba(39, 146, 8, 0.35);
    color: white;
}

.filter-bar {
    background: white;
    border-radius: 12px;
    padding: 1.25rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    border: 1px solid #e2e8f0;
}

.filter-row {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 1rem;
    align-items: end;
}

.filter-label {
    display: block;
    font-size: 0.8rem;
    font-weight: 500;
    color: #64748b;
    margin-bottom: 0.375rem;
}

.filter-select {
    width: 100%;
    padding: 0.5rem 0.75rem;
    font-size: 0.9rem;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    background: white;
    transition: all 0.2s;
    color: #1e293b;
}

.filter-select:focus {
    outline: none;
    border-color: #279208;
    box-shadow: 0 0 0 3px rgba(39, 146, 8, 0.1);
}

.filter-select:disabled {
    background: #f8fafc;
    color: #94a3b8;
}

@media (max-width: 1200px) {
    .filter-row {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (max-width: 992px) {
    .filter-row {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 576px) {
    .filter-row {
        grid-template-columns: 1fr;
    }
}

.schedule-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 2px 12px rgba(15, 23, 42, 0.06);
    border: 1px solid #e2e8f0;
    padding: 1rem;
}

.schedule-loading {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 520px;
    color: #64748b;
    font-size: 0.95rem;
}

.schedule-legend {
    display: flex;
    flex-wrap: wrap;
    gap: 0.35rem 1.1rem;
    padding: 0 0.25rem 0.85rem;
    font-size: 0.78rem;
    color: #64748b;
}

.legend-item {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
}

/* Banner de solicitudes pendientes */
.resched-banner {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    background: #fffbeb;
    border: 1px solid #fde68a;
    border-radius: 12px;
    padding: 0.7rem 1rem;
    margin-bottom: 1rem;
    font-size: 0.9rem;
    color: #b45309;
}

.resched-banner > i {
    color: #f59e0b;
}

.resched-banner__btn {
    margin-left: auto;
    border: 0;
    border-radius: 9px;
    background: #f59e0b;
    color: #fff;
    font-size: 0.82rem;
    font-weight: 600;
    padding: 0.4rem 0.9rem;
    white-space: nowrap;
    cursor: pointer;
}

@media (max-width: 640px) {
    .resched-banner {
        flex-wrap: wrap;
    }

    .resched-banner__btn {
        margin-left: 0;
        width: 100%;
    }
}

.legend-dot {
    width: 11px;
    height: 11px;
    border-radius: 3px;
    display: inline-block;
}

.dot-preventivo { background: #30ab0a; }
.dot-correctivo { background: #ba2831; }
.dot-especial   { background: #2563eb; }
.dot-cerrada    { background: #94a3b8; }

/* El estado se pinta como anillo sobre el color del tipo, así que en la leyenda
   también va como anillo y no como punto lleno. */
.legend-ring {
    width: 11px;
    height: 11px;
    border-radius: 3px;
    display: inline-block;
    background: #fff;
}

.ring-encurso { box-shadow: 0 0 0 2px #0ea5e9; }
.ring-reprog  { box-shadow: 0 0 0 2px #f59e0b; }

.dot-break {
    background: repeating-linear-gradient(
        45deg,
        rgba(148, 163, 184, 0.5),
        rgba(148, 163, 184, 0.5) 3px,
        rgba(148, 163, 184, 0.15) 3px,
        rgba(148, 163, 184, 0.15) 6px
    );
}

.tech-overview {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    padding: 0 0.25rem 0.85rem;
}

.tech-overview__hint {
    font-size: 0.8rem;
    color: #475569;
}

.tech-overview__chips {
    display: flex;
    flex-wrap: wrap;
    gap: 0.4rem;
}

.tech-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    border: 1px solid #e2e8f0;
    background: #fff;
    border-radius: 999px;
    padding: 0.2rem 0.7rem 0.2rem 0.25rem;
    font-size: 0.78rem;
    font-weight: 600;
    color: #334155;
    cursor: pointer;
}

.tech-chip:disabled {
    cursor: default;
    opacity: 0.75;
}

.tech-chip__note {
    font-weight: 400;
    color: #94a3b8;
    margin-left: 0.25rem;
}

.tech-chip:not(:disabled):hover {
    border-color: #30ab0a;
    color: #227a0c;
}

.btn-clear {
    border: 0;
    background: #fef2f2;
    color: #ba2831;
    font-size: 0.82rem;
    font-weight: 600;
    padding: 0.5rem 0.9rem;
    border-radius: 9px;
    cursor: pointer;
}

.tz-event {
    display: flex;
    flex-direction: column;
    line-height: 1.25;
    overflow: hidden;
    padding: 2px 4px;
}

.tz-event-code {
    font-size: 0.76rem;
    font-weight: 700;
}

.tz-event-time,
.tz-event-meta {
    font-size: 0.68rem;
    opacity: 0.85;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.tech-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 22px;
    height: 22px;
    padding: 0 4px;
    border-radius: 999px;
    color: #fff;
    font-size: 0.66rem;
    font-weight: 700;
    letter-spacing: 0.02em;
}

/* Dentro del evento del mes: blanco alrededor para que se despegue del fondo
   del tipo de visita. */
.tech-badge--sm {
    min-width: 18px;
    height: 16px;
    font-size: 0.6rem;
    margin-right: 3px;
    box-shadow: 0 0 0 1.5px #fff;
    vertical-align: 1px;
}

.tz-event-compact {
    display: block;
    font-size: 0.7rem;
    font-weight: 600;
    line-height: 1.35;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
</style>

<style>
/* Sin scope: vue-cal renderiza su propio árbol y los estilos scoped no lo alcanzan.
   Todo va prefijado con .tz-schedule para no filtrarse a otras vistas. */
.tz-schedule {
    /* La rejilla tiene las 24 horas (48 filas de 30px): no cabe entera, así que
       el calendario scrollea por dentro y al abrir se coloca en el inicio de la
       jornada (scrollToWorkingHours). Con esta altura se ven unas 10 horas. */
    height: 730px;
    font-family: inherit;
}

@media (max-width: 768px) {
    .tz-schedule {
        height: 660px;
    }
}

.tz-schedule .vuecal__title-bar {
    background: #f8fafc;
    border-radius: 10px 10px 0 0;
    font-weight: 600;
    color: #1e293b;
}

/* Las pestañas Mes/Semana/Día son .vuecal__view-btn, no un <li> de un menú. */
.tz-schedule .vuecal__menu {
    background: #fff;
    border-bottom: 1px solid #e9edf2;
}

.tz-schedule .vuecal__view-btn {
    color: #64748b;
    font-size: 0.86rem;
    font-weight: 600;
    padding: 0.5rem 1.1rem;
    border-bottom: 2px solid transparent;
    transition: color 0.15s, border-color 0.15s;
}

.tz-schedule .vuecal__view-btn:hover {
    color: #227a0c;
}

.tz-schedule .vuecal__view-btn--active {
    color: #227a0c;
    border-bottom-color: #30ab0a;
}

.tz-schedule .vuecal__cell--today,
.tz-schedule .vuecal__cell--current {
    background: rgba(48, 171, 10, 0.05);
}

/* Vista mes: la casilla de un día es de altura fija, así que un día con cuatro
   visitas se desbordaba sobre la fila de abajo. Cada día scrollea lo suyo. */
.tz-schedule.vuecal--month-view .vuecal__cell-content {
    justify-content: flex-start;
    height: 100%;
}

.tz-schedule.vuecal--month-view .vuecal__cell-events {
    overflow-y: auto;
    width: 100%;
    max-height: calc(100% - 1.6em);
}

.tz-schedule.vuecal--month-view .vuecal__event {
    margin-bottom: 2px;
}

/* Fuera de jornada y descanso (orientativo, no bloquea) */
.tz-schedule .vuecal__cell-split .tz-break,
.tz-schedule .tz-break {
    background: repeating-linear-gradient(
        45deg,
        rgba(148, 163, 184, 0.14),
        rgba(148, 163, 184, 0.14) 6px,
        rgba(148, 163, 184, 0.05) 6px,
        rgba(148, 163, 184, 0.05) 12px
    );
}

/* Mismo rayado que el descanso: en la leyenda es una sola entrada. Es solo
   referencia de la jornada habitual; se puede agendar encima (urgencias). */
.tz-schedule .tz-closed {
    background: repeating-linear-gradient(
        45deg,
        rgba(148, 163, 184, 0.2),
        rgba(148, 163, 184, 0.2) 6px,
        rgba(148, 163, 184, 0.08) 6px,
        rgba(148, 163, 184, 0.08) 12px
    );
}

/* Eventos: color por tipo de visita, atenuados si ya están cerrados */
.tz-schedule .vuecal__event.tz-ev {
    border-radius: 7px;
    border-left: 3px solid;
    color: #fff;
    box-shadow: 0 1px 3px rgba(15, 23, 42, 0.18);
}

.tz-schedule .vuecal__event.tz-ev-preventivo {
    background: #30ab0a;
    border-left-color: #227a0c;
}

.tz-schedule .vuecal__event.tz-ev-correctivo {
    background: #ba2831;
    border-left-color: #8c1d24;
}

.tz-schedule .vuecal__event.tz-ev-especial {
    background: #2563eb;
    border-left-color: #1d4ed8;
}

.tz-schedule .vuecal__event.tz-ev-status-completada,
.tz-schedule .vuecal__event.tz-ev-status-cancelada,
.tz-schedule .vuecal__event.tz-ev-status-no_realizada {
    background: #94a3b8;
    border-left-color: #64748b;
    opacity: 0.75;
}

/* Anillo y no fondo: el fondo sigue diciendo el tipo de visita, que es la regla
   que explica la leyenda. */
.tz-schedule .vuecal__event.tz-ev-status-en_curso {
    box-shadow: 0 0 0 2px #0ea5e9;
}

.tz-schedule .vuecal__event.tz-ev-status-reprogramacion_solicitada {
    box-shadow: 0 0 0 2px #f59e0b;
}

/* Cabecera de día. */
.tz-schedule .vuecal__heading {
    font-size: 0.82rem;
}

/* Barra de vistas (Mes/Semana/Día) y título: separados del grid. */
.tz-schedule .vuecal__title-bar {
    padding: 0.35rem 0;
    font-size: 0.95rem;
}
</style>
