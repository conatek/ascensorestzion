<template>
    <div class="visit-detail">
        <button class="visit-back" @click="$router.back()">
            <i class="fa fa-arrow-left"></i> Agenda
        </button>

        <div v-if="loading" class="visit-loading">
            <i class="fa fa-spinner fa-spin"></i>
        </div>

        <div v-else-if="error" class="visit-error">
            <i class="fa fa-exclamation-triangle"></i>
            <p>{{ error }}</p>
        </div>

        <template v-else-if="visit">
            <!-- Cabecera -->
            <header class="visit-head" :class="`is-${visit.status}`">
                <span class="visit-status">{{ statusLabel(visit.status) }}</span>
                <h1>{{ visit.equipment?.internal_code || 'Visita' }}</h1>
                <p class="visit-when">{{ whenLabel }}</p>
                <p class="visit-type">{{ typeLabel(visit.visit_type) }}</p>
            </header>

            <!-- Acciones -->
            <div class="visit-actions">
                <router-link
                    v-if="canCheckin"
                    class="visit-btn visit-btn--primary"
                    :to="{ name: 'tech.checkin', query: { code: visit.equipment?.internal_code } }"
                >
                    <i class="fa fa-qrcode"></i> Hacer check-in
                </router-link>
                <a v-if="mapsUrl" class="visit-btn visit-btn--ghost" :href="mapsUrl" target="_blank" rel="noopener">
                    <i class="fa fa-directions"></i> Cómo llegar
                </a>
            </div>

            <!-- Sede y contacto -->
            <section class="visit-card">
                <h2 class="visit-card__title">Dónde</h2>
                <p class="visit-site">{{ visit.site?.name || '—' }}</p>
                <p class="visit-address">{{ addressLabel }}</p>
                <p class="visit-client">{{ visit.client?.business_name || '' }}</p>

                <div v-if="visit.site?.contact_name_onsite || visit.site?.contact_phone_onsite" class="visit-contact">
                    <div>
                        <span class="visit-contact__label">Contacto en sitio</span>
                        <span class="visit-contact__name">{{ visit.site?.contact_name_onsite || '—' }}</span>
                    </div>
                    <a
                        v-if="visit.site?.contact_phone_onsite"
                        class="visit-call"
                        :href="`tel:${visit.site.contact_phone_onsite}`"
                    >
                        <i class="fa fa-phone"></i>
                        {{ visit.site.contact_phone_onsite }}
                    </a>
                </div>

                <div v-if="hasGeo" ref="map" class="visit-map"></div>
            </section>

            <!-- Ficha técnica -->
            <section class="visit-card">
                <h2 class="visit-card__title">Equipo</h2>
                <dl class="visit-dl">
                    <dt>Tipo</dt>
                    <dd>{{ visit.equipment?.equipment_type || '—' }}</dd>
                    <dt>Marca</dt>
                    <dd>{{ visit.equipment?.brand || '—' }}</dd>
                    <dt>Modelo</dt>
                    <dd>{{ visit.equipment?.model || '—' }}</dd>
                    <dt>Serie</dt>
                    <dd>{{ visit.equipment?.serial_number || '—' }}</dd>
                    <dt>Paradas</dt>
                    <dd>{{ visit.equipment?.stops ?? '—' }}</dd>
                    <dt>Capacidad</dt>
                    <dd>{{ capacityLabel }}</dd>
                    <dt>Contrato</dt>
                    <dd>{{ visit.equipment?.contract_type || '—' }}</dd>
                </dl>

                <router-link
                    v-if="visit.equipment_id"
                    class="visit-link"
                    :to="{ name: 'tech.equipment', params: { id: visit.equipment_id } }"
                >
                    Ver ficha completa <i class="fa fa-chevron-right"></i>
                </router-link>
            </section>

            <!-- Último reporte -->
            <section class="visit-card">
                <h2 class="visit-card__title">Último reporte</h2>
                <router-link
                    v-if="lastReport"
                    class="visit-report"
                    :to="{ name: 'reports.show', params: { id: lastReport.id } }"
                >
                    <span class="visit-report__type" :class="`type-${lastReport.report_type}`">
                        {{ lastReport.report_type }}
                    </span>
                    <span class="visit-report__info">
                        <span class="visit-report__number">{{ lastReport.report_number }}</span>
                        <span class="visit-report__meta">
                            {{ date(lastReport.service_date) }} · {{ lastReport.technician?.name || '—' }}
                        </span>
                    </span>
                    <i class="fa fa-chevron-right"></i>
                </router-link>
                <p v-else class="visit-empty">Este equipo aún no tiene reportes.</p>
            </section>

            <section v-if="visit.notes" class="visit-card">
                <h2 class="visit-card__title">Notas de coordinación</h2>
                <p class="visit-notes">{{ visit.notes }}</p>
            </section>
        </template>
    </div>
</template>

<script>
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import dayjs from '@/utils/dayjs.js';
import scheduleService from '@/services/scheduleService.js';
import { statusLabel, typeLabel } from '@/utils/visitLabels.js';

const MARKER_ICON = L.divIcon({
    className: 'visit-map-marker',
    html: '<i class="fa fa-map-marker-alt"></i>',
    iconSize: [28, 28],
    iconAnchor: [14, 28],
});

export default {
    name: 'TechVisitDetail',

    data() {
        return {
            visit: null,
            lastReport: null,
            loading: true,
            error: '',
            map: null,
        };
    },

    computed: {
        whenLabel() {
            const start = dayjs(this.visit.scheduled_start);
            const end = dayjs(this.visit.scheduled_end);
            const day = start.format('dddd D [de] MMMM');
            return `${day.charAt(0).toUpperCase() + day.slice(1)} · ${start.format('HH:mm')}–${end.format('HH:mm')}`;
        },

        addressLabel() {
            const site = this.visit.site || {};
            return [site.address, site.city].filter(Boolean).join(', ') || '—';
        },

        capacityLabel() {
            const eq = this.visit.equipment || {};
            const parts = [];
            if (eq.capacity_kg) parts.push(`${eq.capacity_kg} kg`);
            if (eq.capacity_persons) parts.push(`${eq.capacity_persons} personas`);
            return parts.join(' · ') || '—';
        },

        hasGeo() {
            return !!(this.visit?.site?.latitude && this.visit?.site?.longitude);
        },

        /** Abre la app de mapas del teléfono: es lo que de verdad usa en la calle. */
        mapsUrl() {
            const site = this.visit?.site;
            if (!site) return '';
            if (site.latitude && site.longitude) {
                return `https://www.google.com/maps/search/?api=1&query=${site.latitude},${site.longitude}`;
            }
            return site.address
                ? `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(site.address)}`
                : '';
        },

        /** Ya ejecutada o cancelada no se vuelve a abrir desde aquí. */
        canCheckin() {
            return ['programada', 'reprogramacion_solicitada', 'en_curso'].includes(this.visit?.status);
        },
    },

    created() {
        this.load();
    },

    beforeUnmount() {
        this.map?.remove();
    },

    methods: {
        statusLabel,
        typeLabel,

        date(value) {
            // La API entrega la fecha ya en ISO completo: cortar por la T evita
            // el "Invalid Date" de concatenarle una hora.
            return value ? dayjs(String(value).split('T')[0]).format('D MMM YYYY') : '—';
        },

        async load() {
            this.loading = true;
            try {
                const { data } = await scheduleService.techVisit(this.$route.params.id);
                this.visit = data.visit;
                this.lastReport = data.last_report;
            } catch {
                this.error = 'No se pudo cargar la visita.';
            } finally {
                this.loading = false;
            }

            // Después de bajar loading: mientras está arriba el template pinta el
            // spinner y el contenedor del mapa todavía no existe en el DOM.
            await this.$nextTick();
            this.renderMap();
        },

        renderMap() {
            if (!this.hasGeo || !this.$refs.map) return;

            const point = [Number(this.visit.site.latitude), Number(this.visit.site.longitude)];

            this.map = L.map(this.$refs.map, {
                zoomControl: false,
                // Sin scroll-zoom: dentro de una vista que se desplaza, la rueda o el
                // dedo acabarían haciendo zoom en vez de bajar por la página.
                scrollWheelZoom: false,
            }).setView(point, 16);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap',
                maxZoom: 19,
            }).addTo(this.map);

            L.marker(point, { icon: MARKER_ICON }).addTo(this.map);
        },
    },
};
</script>

<style scoped>
.visit-detail {
    padding: 1rem;
}

.visit-back {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    background: none;
    border: 0;
    color: #64748b;
    font-size: 0.85rem;
    font-weight: 600;
    padding: 0 0 0.75rem;
    cursor: pointer;
}

.visit-loading,
.visit-error {
    text-align: center;
    padding: 3rem 1rem;
    color: #94a3b8;
}

.visit-error i {
    font-size: 1.6rem;
    color: #ba2831;
}

/* Cabecera */
.visit-head {
    background: white;
    border: 1px solid #e2e8f0;
    border-left: 4px solid #30ab0a;
    border-radius: 14px;
    padding: 1rem 1.1rem;
    margin-bottom: 0.85rem;
}

.visit-head.is-en_curso { border-left-color: #2563eb; }
.visit-head.is-completada { border-left-color: #64748b; }
.visit-head.is-no_realizada { border-left-color: #ba2831; }
.visit-head.is-reprogramacion_solicitada { border-left-color: #f59e0b; }

.visit-status {
    display: inline-block;
    font-size: 0.65rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: #94a3b8;
}

.visit-head h1 {
    font-size: 1.3rem;
    font-weight: 700;
    color: #1e293b;
    margin: 0.2rem 0 0;
}

.visit-when {
    margin: 0.3rem 0 0;
    font-size: 0.88rem;
    font-weight: 600;
    color: #30ab0a;
}

.visit-type {
    margin: 0.1rem 0 0;
    font-size: 0.78rem;
    color: #64748b;
}

/* Acciones */
.visit-actions {
    display: flex;
    gap: 0.6rem;
    margin-bottom: 0.85rem;
}

.visit-btn {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.45rem;
    padding: 0.75rem 0.5rem;
    border-radius: 12px;
    text-decoration: none;
    font-size: 0.85rem;
    font-weight: 600;
    min-height: 46px;
}

.visit-btn--primary {
    background: linear-gradient(135deg, #30ab0a, #279208);
    color: white;
    box-shadow: 0 4px 12px rgba(48, 171, 10, 0.28);
}

.visit-btn--ghost {
    background: white;
    border: 1px solid #e2e8f0;
    color: #475569;
}

/* Tarjetas */
.visit-card {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 0.95rem 1.1rem;
    margin-bottom: 0.85rem;
}

.visit-card__title {
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #94a3b8;
    margin: 0 0 0.6rem;
}

.visit-site {
    margin: 0;
    font-size: 1rem;
    font-weight: 700;
    color: #1e293b;
}

.visit-address {
    margin: 0.15rem 0 0;
    font-size: 0.82rem;
    color: #64748b;
}

.visit-client {
    margin: 0.1rem 0 0;
    font-size: 0.78rem;
    color: #94a3b8;
}

.visit-contact {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    margin-top: 0.85rem;
    padding-top: 0.85rem;
    border-top: 1px solid #f1f5f9;
}

.visit-contact__label {
    display: block;
    font-size: 0.68rem;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    font-weight: 600;
}

.visit-contact__name {
    font-size: 0.88rem;
    color: #1e293b;
    font-weight: 600;
}

.visit-call {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.5rem 0.8rem;
    background: #eefbe9;
    border-radius: 10px;
    color: #227a0c;
    font-size: 0.82rem;
    font-weight: 700;
    text-decoration: none;
    white-space: nowrap;
}

.visit-map {
    height: 160px;
    border-radius: 12px;
    margin-top: 0.85rem;
    z-index: 0;
}

/* Ficha técnica */
.visit-dl {
    margin: 0;
    display: grid;
    grid-template-columns: 96px 1fr;
    gap: 0.35rem 0.75rem;
    font-size: 0.85rem;
}

.visit-dl dt {
    color: #94a3b8;
    font-weight: 500;
}

.visit-dl dd {
    margin: 0;
    color: #1e293b;
    text-transform: capitalize;
}

.visit-link {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    margin-top: 0.85rem;
    color: #30ab0a;
    font-size: 0.82rem;
    font-weight: 700;
    text-decoration: none;
}

/* Último reporte */
.visit-report {
    display: flex;
    align-items: center;
    gap: 0.7rem;
    text-decoration: none;
    color: #cbd5e1;
}

.visit-report__type {
    font-size: 0.68rem;
    font-weight: 700;
    padding: 0.2rem 0.5rem;
    border-radius: 6px;
}

.type-RSTP { background: #e8f5e4; color: #30ab0a; }
.type-RSTC { background: #fef2f2; color: #ba2831; }
.type-RSTE { background: #fffbeb; color: #d97706; }

.visit-report__info {
    flex: 1;
    min-width: 0;
}

.visit-report__number {
    display: block;
    font-size: 0.85rem;
    font-weight: 600;
    color: #1e293b;
}

.visit-report__meta {
    display: block;
    font-size: 0.75rem;
    color: #94a3b8;
}

.visit-empty {
    margin: 0;
    font-size: 0.85rem;
    color: #94a3b8;
}

.visit-notes {
    margin: 0;
    font-size: 0.85rem;
    color: #475569;
    white-space: pre-wrap;
}
</style>

<style>
/* El icono del marcador lo inyecta Leaflet fuera del árbol del componente, así
   que el scoped no lo alcanzaría. */
.visit-map-marker {
    color: #ba2831;
    font-size: 1.6rem;
    text-align: center;
    line-height: 1;
}
</style>
