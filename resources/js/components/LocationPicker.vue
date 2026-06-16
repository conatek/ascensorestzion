<template>
    <div class="location-picker">
        <div class="lp-fields">
            <div class="lp-field">
                <label class="form-label">Latitud</label>
                <input
                    :value="latitude"
                    @input="onInputLat($event.target.value)"
                    type="number" step="any" inputmode="decimal"
                    class="form-input" placeholder="Ej: 8.9824" />
            </div>
            <div class="lp-field">
                <label class="form-label">Longitud</label>
                <input
                    :value="longitude"
                    @input="onInputLng($event.target.value)"
                    type="number" step="any" inputmode="decimal"
                    class="form-input" placeholder="Ej: -79.5199" />
            </div>
            <div class="lp-field lp-field-radius">
                <label class="form-label">Radio (m)</label>
                <input
                    :value="radius"
                    @input="onInputRadius($event.target.value)"
                    type="number" min="10" max="10000" step="10"
                    class="form-input" placeholder="500" />
            </div>
        </div>

        <div class="lp-actions">
            <button type="button" class="lp-btn-geo" @click="useMyLocation" :disabled="geoLoading">
                <i :class="geoLoading ? 'fa fa-spinner fa-spin' : 'fa fa-crosshairs'" class="me-1"></i>
                {{ geoLoading ? 'Obteniendo...' : 'Usar mi ubicación actual' }}
            </button>
            <button v-if="hasCoords" type="button" class="lp-btn-clear" @click="clearLocation">
                <i class="fa fa-times me-1"></i> Quitar
            </button>
            <span v-if="geoStatus" class="lp-status" :class="geoStatusClass">{{ geoStatus }}</span>
        </div>

        <div ref="map" class="lp-map"></div>

        <p class="lp-hint">
            <i class="fa fa-info-circle me-1"></i>
            Toca el mapa o arrastra el marcador para fijar la ubicación exacta de la sede. El círculo indica el radio de validación del check-in.
        </p>
    </div>
</template>

<script>
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import { getCurrentPosition } from '@/utils/useGeolocation.js';

const DEFAULT_CENTER = [8.9824, -79.5199]; // Ciudad de Panamá
const MARKER_ICON = L.divIcon({
    className: 'lp-pin',
    html: '<i class="fa fa-map-marker-alt"></i>',
    iconSize: [32, 32],
    iconAnchor: [16, 30],
});

export default {
    name: 'LocationPicker',

    props: {
        latitude: { type: [Number, String], default: null },
        longitude: { type: [Number, String], default: null },
        radius: { type: [Number, String], default: 500 },
    },

    emits: ['update:latitude', 'update:longitude', 'update:radius'],

    data() {
        return {
            map: null,
            marker: null,
            circle: null,
            geoLoading: false,
            geoStatus: '',
            geoStatusClass: '',
            internalUpdate: false,
        };
    },

    computed: {
        numLat() {
            const n = parseFloat(this.latitude);
            return Number.isFinite(n) ? n : null;
        },
        numLng() {
            const n = parseFloat(this.longitude);
            return Number.isFinite(n) ? n : null;
        },
        numRadius() {
            const n = parseInt(this.radius, 10);
            return Number.isFinite(n) && n > 0 ? n : 500;
        },
        hasCoords() {
            return this.numLat !== null && this.numLng !== null;
        },
    },

    watch: {
        latitude() { this.syncFromProps(); },
        longitude() { this.syncFromProps(); },
        radius() {
            if (this.circle) this.circle.setRadius(this.numRadius);
        },
    },

    mounted() {
        this.initMap();
    },

    beforeUnmount() {
        if (this.map) {
            this.map.remove();
            this.map = null;
        }
    },

    methods: {
        initMap() {
            const center = this.hasCoords ? [this.numLat, this.numLng] : DEFAULT_CENTER;
            const zoom = this.hasCoords ? 16 : 12;

            this.map = L.map(this.$refs.map).setView(center, zoom);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap',
                maxZoom: 19,
            }).addTo(this.map);

            this.map.on('click', (e) => this.setPosition(e.latlng.lat, e.latlng.lng));

            if (this.hasCoords) this.drawMarker(this.numLat, this.numLng);

            // El contenedor puede renderizar con tamaño 0 al montar
            this.$nextTick(() => setTimeout(() => this.map && this.map.invalidateSize(), 200));
        },

        drawMarker(lat, lng) {
            if (!this.map) return;

            if (!this.marker) {
                this.marker = L.marker([lat, lng], { icon: MARKER_ICON, draggable: true }).addTo(this.map);
                this.marker.on('dragend', () => {
                    const p = this.marker.getLatLng();
                    this.setPosition(p.lat, p.lng, false);
                });
                this.circle = L.circle([lat, lng], {
                    radius: this.numRadius,
                    color: '#30ab0a',
                    fillColor: '#30ab0a',
                    fillOpacity: 0.12,
                    weight: 2,
                }).addTo(this.map);
            } else {
                this.marker.setLatLng([lat, lng]);
                this.circle.setLatLng([lat, lng]);
            }
        },

        setPosition(lat, lng, recenter = true) {
            const rLat = Math.round(lat * 1e7) / 1e7;
            const rLng = Math.round(lng * 1e7) / 1e7;

            this.drawMarker(rLat, rLng);
            if (recenter && this.map) this.map.panTo([rLat, rLng]);

            this.internalUpdate = true;
            this.$emit('update:latitude', rLat);
            this.$emit('update:longitude', rLng);
            this.$nextTick(() => { this.internalUpdate = false; });
        },

        syncFromProps() {
            if (this.internalUpdate) return;
            if (this.hasCoords) {
                this.drawMarker(this.numLat, this.numLng);
            } else if (this.marker) {
                this.map.removeLayer(this.marker);
                this.map.removeLayer(this.circle);
                this.marker = null;
                this.circle = null;
            }
        },

        onInputLat(val) {
            this.$emit('update:latitude', val === '' ? null : val);
        },
        onInputLng(val) {
            this.$emit('update:longitude', val === '' ? null : val);
        },
        onInputRadius(val) {
            this.$emit('update:radius', val === '' ? null : parseInt(val, 10));
        },

        clearLocation() {
            this.$emit('update:latitude', null);
            this.$emit('update:longitude', null);
        },

        async useMyLocation() {
            this.geoLoading = true;
            this.geoStatus = '';
            try {
                const pos = await getCurrentPosition();
                this.setPosition(pos.latitude, pos.longitude);
                if (this.map) this.map.setView([pos.latitude, pos.longitude], 17);
                this.geoStatus = `Ubicación fijada (±${Math.round(pos.accuracy)}m)`;
                this.geoStatusClass = 'lp-ok';
            } catch (err) {
                this.geoStatus = err.message || 'No se pudo obtener la ubicación.';
                this.geoStatusClass = 'lp-warn';
            } finally {
                this.geoLoading = false;
            }
        },
    },
};
</script>

<style scoped>
.location-picker {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.lp-fields {
    display: grid;
    grid-template-columns: 1fr 1fr auto;
    gap: 1rem;
}

.lp-field-radius {
    min-width: 110px;
}

.form-label {
    display: block;
    font-size: 0.875rem;
    font-weight: 500;
    color: #374151;
    margin-bottom: 0.5rem;
}

.form-input {
    width: 100%;
    padding: 0.625rem 0.875rem;
    font-size: 0.95rem;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    background: white;
    transition: all 0.2s;
}

.form-input:focus {
    outline: none;
    border-color: #279208;
    box-shadow: 0 0 0 3px rgba(39, 146, 8, 0.1);
}

.lp-actions {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.lp-btn-geo {
    display: inline-flex;
    align-items: center;
    padding: 0.5rem 1rem;
    font-size: 0.85rem;
    font-weight: 500;
    background: #279208;
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s;
}

.lp-btn-geo:hover:not(:disabled) {
    background: #1f7506;
}

.lp-btn-geo:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

.lp-btn-clear {
    display: inline-flex;
    align-items: center;
    padding: 0.5rem 0.875rem;
    font-size: 0.85rem;
    font-weight: 500;
    background: #f1f5f9;
    color: #475569;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    cursor: pointer;
}

.lp-btn-clear:hover {
    background: #e2e8f0;
}

.lp-status {
    font-size: 0.8rem;
}

.lp-ok {
    color: #279208;
}

.lp-warn {
    color: #d97706;
}

.lp-map {
    height: 320px;
    width: 100%;
    border-radius: 10px;
    border: 1px solid #e2e8f0;
    z-index: 0;
}

.lp-hint {
    font-size: 0.8rem;
    color: #64748b;
    margin: 0;
}

@media (max-width: 576px) {
    .lp-fields {
        grid-template-columns: 1fr 1fr;
    }

    .lp-field-radius {
        grid-column: span 2;
    }
}
</style>

<style>
/* Marcador (no scoped: leaflet inserta el divIcon fuera del scope del componente) */
.lp-pin {
    color: #ba2831;
    font-size: 30px;
    line-height: 1;
    text-align: center;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.4);
}
</style>
