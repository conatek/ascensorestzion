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
                    <label class="filter-label">Agrupar por</label>
                    <div class="group-toggle">
                        <button :class="{ active: groupBy === 'technician' }" @click="setGroupBy('technician')">
                            <i class="fa fa-user-cog me-1"></i> Técnico
                        </button>
                        <button :class="{ active: groupBy === 'equipment' }" @click="setGroupBy('equipment')">
                            <i class="fa fa-arrows-alt-v me-1"></i> Equipo
                        </button>
                    </div>
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
                    <label class="filter-label">Técnico</label>
                    <select v-model="filters.technician_id" class="filter-select" @change="load">
                        <option value="">Todos</option>
                        <option v-for="t in technicians" :key="t.id" :value="t.id">{{ t.name }}</option>
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
                <span class="legend-item"><i class="legend-dot dot-break"></i> No agendable (descanso o día no laborable)</span>
            </div>

            <div v-show="!loading">
                <vue-cal
                    ref="cal"
                    class="tz-schedule"
                    locale="es"
                    :time-from="timeFrom"
                    :time-to="timeTo"
                    :time-step="30"
                    :time-cell-height="30"
                    :disable-views="['years', 'year']"
                    :active-view="view"
                    :selected-date="selectedDate"
                    :split-days="splits"
                    :sticky-split-labels="useSplits"
                    :min-split-width="minSplitWidth"
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
                        <div v-if="evView === 'month'" class="tz-event tz-event-compact">
                            {{ event.startTimeLabel }} · {{ event.equipmentCode }}
                        </div>
                        <div v-else class="tz-event">
                            <strong class="tz-event-code">{{ event.equipmentCode }}</strong>
                            <span class="tz-event-time">{{ event.timeLabel }}</span>
                            <!-- Con columnas por técnico el nombre ya está en la
                                 cabecera; sin ellas hay que decir de quién es. -->
                            <span v-if="!useSplits" class="tz-event-meta">{{ event.technicianName }}</span>
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

/** Estados que no se pueden arrastrar: la visita ya está cerrada. */
const LOCKED_STATUSES = ['completada', 'cancelada'];

export default {
    name: 'ScheduleBoard',
    components: { VueCal, VisitFormModal, VisitDrawer, RescheduleInbox, HolidaysModal },
    data() {
        return {
            loading: true,
            // En un teléfono la semana con columnas por técnico obliga a barrer
            // de lado para ver cualquier cosa; el día entra completo.
            view: window.innerWidth < 768 ? 'day' : 'week',
            groupBy: 'technician',
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
        };
    },
    computed: {
        /** Los splits por técnico solo tienen sentido en día y semana. */
        useSplits() {
            return this.groupBy === 'technician' && ['day', 'week'].includes(this.view);
        },
        splits() {
            if (!this.useSplits) return [];
            return this.visibleTechnicians.map(t => ({
                id: t.id,
                label: t.name,
                class: `tz-split-${t.id}`,
            }));
        },
        visibleTechnicians() {
            if (!this.filters.technician_id) return this.technicians;
            return this.technicians.filter(t => String(t.id) === String(this.filters.technician_id));
        },
        /**
         * vue-cal usa un único time-from/time-to para todo el grid, así que se toma
         * el mínimo y el máximo de las jornadas visibles; las franjas fuera de la
         * jornada de cada técnico se marcan como muertas vía special-hours.
         */
        timeFrom() {
            const mins = this.visibleTechnicians
                .map(t => this.toMinutes(t.working_window?.start))
                .filter(m => m !== null);
            if (!mins.length) return this.toMinutes(this.defaults.working_hours?.start) ?? 8 * 60;
            return Math.min(...mins);
        },
        timeTo() {
            const mins = this.visibleTechnicians
                .map(t => this.toMinutes(t.working_window?.end))
                .filter(m => m !== null);
            if (!mins.length) return this.toMinutes(this.defaults.working_hours?.end) ?? 18 * 60;
            return Math.max(...mins);
        },
        /**
         * Franjas no agendables: el descanso y, cuando se ve un solo técnico, las
         * horas fuera de su jornada. Con varios técnicos a la vez se pinta solo el
         * descanso global, que es lo común a todos.
         */
        specialHours() {
            const window = this.visibleTechnicians.length === 1
                ? this.visibleTechnicians[0].working_window
                : {
                    days: this.defaults.working_days || [1, 2, 3, 4, 5],
                    break_start: this.defaults.working_break?.start,
                    break_end: this.defaults.working_break?.end,
                };

            const hours = {};
            for (let day = 1; day <= 7; day++) {
                if (!window?.days?.includes(day)) {
                    // Día no laborable: todo el día muerto.
                    hours[day] = { from: this.timeFrom, to: this.timeTo, class: 'tz-closed' };
                } else if (window.break_start && window.break_end) {
                    hours[day] = {
                        from: this.toMinutes(window.break_start),
                        to: this.toMinutes(window.break_end),
                        class: 'tz-break',
                    };
                }
            }
            return hours;
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
                    split: this.useSplits ? v.technician_id : undefined,
                    draggable: !LOCKED_STATUSES.includes(v.status),
                    resizable: !LOCKED_STATUSES.includes(v.status),
                    // Datos propios para el slot del evento y el drawer
                    equipmentCode: v.equipment?.internal_code || '—',
                    siteName: v.site?.name || '',
                    technicianName: v.technician?.name || '',
                    timeLabel: `${start.format('HH:mm')}–${end.format('HH:mm')}`,
                    startTimeLabel: start.format('HH:mm'),
                    raw: v,
                };
            });
        },
        editableEvents() {
            return { title: false, drag: true, resize: true, delete: false, create: false };
        },
        /**
         * Ancho mínimo de cada columna de técnico. En la semana son 7 días × N
         * técnicos: se deja estrecho y el contenedor hace scroll horizontal, que
         * es preferible a espachurrar las columnas hasta que no se lea nada.
         */
        minSplitWidth() {
            if (!this.useSplits) return 0;
            return this.view === 'day' ? 0 : 130;
        },
        filteredSites() {
            if (!this.filters.client_id) return [];
            return this.sites.filter(s => String(s.client_id) === String(this.filters.client_id));
        },
        hasFilters() {
            return Object.values(this.filters).some(Boolean);
        },
    },
    async created() {
        // El enlace del correo de solicitud trae ?solicitudes=1: quien viene de
        // ahí viene a decidir, no a mirar el calendario.
        this.showRequests = this.$route.query.solicitudes === '1';

        await Promise.all([this.loadCatalogs(), this.loadRequests()]);
    },
    methods: {
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

        setGroupBy(mode) {
            this.groupBy = mode;
        },

        onClientFilter() {
            this.filters.site_id = '';
            this.load();
        },

        clearFilters() {
            this.filters = { client_id: '', site_id: '', technician_id: '', status: '' };
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
                technician_id: this.useSplits ? payload?.split : null,
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
            const technicianId = event.split || visit.technician_id;

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
            this.view = 'day';
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

.group-toggle {
    display: inline-flex;
    background: #eef2f6;
    border-radius: 10px;
    padding: 3px;
    gap: 3px;
}

.group-toggle button {
    border: 0;
    background: transparent;
    color: #64748b;
    font-size: 0.82rem;
    font-weight: 600;
    padding: 0.4rem 0.75rem;
    border-radius: 8px;
    cursor: pointer;
}

.group-toggle button.active {
    background: #fff;
    color: #227a0c;
    box-shadow: 0 1px 3px rgba(15, 23, 42, 0.12);
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
    /* Una jornada de 08:00 a 18:00 en pasos de 30 min son 20 filas. Con celdas de
       30px son 600px de rejilla, así que con esta altura el día entra entero y no
       hay que hacer scroll dentro del calendario para ver la tarde (antes se
       cortaba a mediodía). Quien scrollea es la página, que es lo esperable. */
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

/* Franjas no agendables */
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

/* Mismo rayado que el descanso: en la leyenda es una sola entrada, y para quien
   agenda ambas cosas significan lo mismo (aquí no se puede poner una visita). */
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

/* Los nombres largos ("Andrés Felipe Cardona") se salían de su columna y pisaban
   la de al lado. Sin el contenedor .vuecal__split-days-headers delante, porque ese
   solo existe en la vista de día con etiquetas fijas: en la semana las cabeceras
   de técnico cuelgan de la cabecera del día. */
.tz-schedule .day-split-header {
    font-size: 0.74rem;
    font-weight: 600;
    color: #475569;
    padding: 0.35rem 0.25rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    display: block;
    max-width: 100%;
}

/* Cabecera de día. Sin tocarle la altura: con columnas por técnico vue-cal mete
   los nombres dentro y fijársela los recortaba por abajo. */
.tz-schedule .vuecal__heading {
    font-size: 0.82rem;
}

/* Barra de vistas (Mes/Semana/Día) y título: separados del grid. */
.tz-schedule .vuecal__title-bar {
    padding: 0.35rem 0;
    font-size: 0.95rem;
}
</style>
