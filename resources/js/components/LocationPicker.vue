<template>
    <div class="location-picker">
        <!-- Buscador de direcciones -->
        <div class="lp-search" ref="searchBox">
            <label class="form-label">Buscar dirección</label>
            <div class="lp-search-row">
                <div class="lp-search-input">
                    <i class="fa fa-search lp-search-icon"></i>
                    <input
                        v-model="query"
                        @keydown.enter.prevent="search"
                        @keydown.esc="resultsOpen = false"
                        type="text"
                        class="form-input"
                        placeholder="Ej: Calle 50, Ciudad de Panamá" />
                </div>
                <button type="button" class="lp-btn-search" @click="search" :disabled="searching">
                    <i :class="searching ? 'fa fa-spinner fa-spin' : 'fa fa-search'" class="me-1"></i>
                    {{ searching ? 'Buscando...' : 'Buscar' }}
                </button>
            </div>

            <button
                v-if="formAddress && formAddress !== query"
                type="button"
                class="lp-use-address"
                @click="useFormAddress">
                <i class="fa fa-level-up-alt fa-rotate-90 me-1"></i>
                Usar la dirección del formulario
            </button>

            <span v-if="searchStatus" class="lp-status" :class="searchStatusClass">{{ searchStatus }}</span>

            <ul v-if="resultsOpen && results.length" class="lp-results">
                <li v-for="r in results" :key="r.place_id">
                    <button type="button" class="lp-result" @click="selectResult(r)">
                        <i class="fa fa-map-marker-alt lp-result-icon"></i>
                        <span class="lp-result-text">
                            <strong>{{ r.mainLabel }}</strong>
                            <small v-if="r.subLabel">{{ r.subLabel }}</small>
                        </span>
                    </button>
                </li>
            </ul>
        </div>

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

        <div class="lp-map-wrap">
            <div ref="map" class="lp-map"></div>
            <div class="lp-basemap">
                <button
                    v-for="opt in basemapOptions"
                    :key="opt.key"
                    type="button"
                    class="lp-basemap-btn"
                    :class="{ 'is-active': basemap === opt.key }"
                    @click="setBasemap(opt.key)">
                    {{ opt.label }}
                </button>
            </div>
        </div>

        <p class="lp-hint">
            <i class="fa fa-info-circle me-1"></i>
            Busca la dirección, o toca el mapa y arrastra el marcador para fijar la ubicación exacta de la sede. El círculo indica el radio de validación del check-in.
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

// Nominatim es gratuito y sin clave, pero pide un máximo de una petición por
// segundo: por eso se busca al pulsar Enter o el botón, nunca al teclear.
const NOMINATIM_URL = 'https://nominatim.openstreetmap.org/search';
const PAIS = 'pa';

export default {
    name: 'LocationPicker',

    props: {
        latitude: { type: [Number, String], default: null },
        longitude: { type: [Number, String], default: null },
        radius: { type: [Number, String], default: 500 },
        // Los del formulario de la sede: alimentan el atajo "usar la dirección
        // del formulario", que es el caso normal al dar de alta una sede.
        address: { type: String, default: '' },
        city: { type: String, default: '' },
        department: { type: String, default: '' },
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
            query: '',
            searching: false,
            searchStatus: '',
            searchStatusClass: '',
            results: [],
            resultsOpen: false,
            controller: null,
            basemap: 'mapa',
            layers: {},
            basemapOptions: [
                { key: 'mapa', label: 'Mapa' },
                { key: 'satelite', label: 'Satélite' },
                { key: 'hibrido', label: 'Híbrido' },
            ],
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
        formAddress() {
            return [this.address, this.city, this.department]
                .map((p) => (p || '').trim())
                .filter(Boolean)
                .join(', ');
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
        document.addEventListener('click', this.onDocumentClick);
    },

    beforeUnmount() {
        document.removeEventListener('click', this.onDocumentClick);
        if (this.controller) this.controller.abort();
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

            this.layers = this.buildBaseLayers();
            this.layers[this.basemap].addTo(this.map);

            this.map.on('click', (e) => this.setPosition(e.latlng.lat, e.latlng.lng));

            if (this.hasCoords) this.drawMarker(this.numLat, this.numLng);

            // El contenedor puede renderizar con tamaño 0 al montar
            this.$nextTick(() => setTimeout(() => this.map && this.map.invalidateSize(), 200));
        },

        buildBaseLayers() {
            const callejero = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap',
                maxZoom: 19,
            });

            // Ortofoto de Esri: el eje Y va antes que el X, al revés que en OSM.
            const orto = () => L.tileLayer(
                'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
                { attribution: 'Imágenes &copy; Esri', maxZoom: 19 },
            );

            // Solo rotulos, sobre la ortofoto. Los de Esri apenas traen calles a
            // este nivel de zoom; los de OSM en Panama si estan.
            const etiquetas = L.tileLayer(
                'https://{s}.basemaps.cartocdn.com/light_only_labels/{z}/{x}/{y}{r}.png',
                {
                    attribution: '&copy; OpenStreetMap, &copy; CARTO',
                    subdomains: 'abcd',
                    maxZoom: 19,
                },
            );

            return {
                mapa: callejero,
                satelite: orto(),
                hibrido: L.layerGroup([orto(), etiquetas]),
            };
        },

        setBasemap(key) {
            if (!this.map || key === this.basemap || !this.layers[key]) return;

            this.map.removeLayer(this.layers[this.basemap]);
            this.layers[key].addTo(this.map);
            this.basemap = key;
        },

        useFormAddress() {
            this.query = this.formAddress;
            this.search();
        },

        async search() {
            const q = this.query.trim();

            if (q.length < 3) {
                this.results = [];
                this.resultsOpen = false;
                this.setSearchStatus('Escribe al menos 3 caracteres.', 'lp-warn');
                return;
            }

            if (this.controller) this.controller.abort();
            this.controller = new AbortController();

            this.searching = true;
            this.setSearchStatus('', '');
            this.results = [];

            try {
                // Primero acotado a Panamá, que es lo normal; si no sale nada,
                // se repite sin acotar antes de dar por perdida la búsqueda.
                let found = await this.fetchPlaces(q, PAIS);
                if (!found.length) found = await this.fetchPlaces(q, null);

                if (!found.length) {
                    this.resultsOpen = false;
                    this.setSearchStatus('Sin resultados. Prueba con menos detalle o ubica el punto en el mapa.', 'lp-warn');
                    return;
                }

                this.results = found.map((r) => {
                    const parts = (r.display_name || '').split(', ');
                    // El primer trozo suele ser el numero de portal ("50, Calle B"):
                    // solo no dice nada, asi que se junta con el siguiente.
                    const corte = /^\d+$/.test(parts[0]) && parts.length > 1 ? 2 : 1;
                    return {
                        ...r,
                        mainLabel: parts.slice(0, corte).join(', ') || r.display_name,
                        subLabel: parts.slice(corte).join(', '),
                    };
                });
                this.resultsOpen = true;
            } catch (err) {
                if (err.name === 'AbortError') return;
                this.resultsOpen = false;
                this.setSearchStatus('No se pudo buscar. Revisa la conexión e inténtalo de nuevo.', 'lp-warn');
            } finally {
                this.searching = false;
            }
        },

        /** Petición cruda con fetch: axios lleva el token de Sanctum en las cabeceras. */
        async fetchPlaces(q, countryCodes) {
            const params = new URLSearchParams({
                format: 'jsonv2',
                addressdetails: '1',
                limit: '5',
                'accept-language': 'es',
                q,
            });
            if (countryCodes) params.set('countrycodes', countryCodes);

            // Sesga (que no limita) los resultados a lo que se ve en pantalla:
            // sin esto, "Calle 50" cae en cualquier barrio del pais.
            if (this.map) {
                const b = this.map.getBounds();
                params.set('viewbox', [b.getWest(), b.getNorth(), b.getEast(), b.getSouth()].join(','));
                params.set('bounded', '0');
            }

            const res = await fetch(`${NOMINATIM_URL}?${params}`, {
                signal: this.controller.signal,
                headers: { Accept: 'application/json' },
            });
            if (!res.ok) throw new Error(`Nominatim respondió ${res.status}`);

            const data = await res.json();
            return Array.isArray(data) ? data : [];
        },

        selectResult(r) {
            const lat = parseFloat(r.lat);
            const lng = parseFloat(r.lon);
            if (!Number.isFinite(lat) || !Number.isFinite(lng)) return;

            this.setPosition(lat, lng, false);

            if (this.map) {
                // boundingbox llega como [sur, norte, oeste, este] en cadenas.
                const bb = (r.boundingbox || []).map(parseFloat);
                if (bb.length === 4 && bb.every(Number.isFinite)) {
                    this.map.fitBounds([[bb[0], bb[2]], [bb[1], bb[3]]], { maxZoom: 18 });
                } else {
                    this.map.setView([lat, lng], 17);
                }
            }

            this.resultsOpen = false;
            this.setSearchStatus('Ubicación fijada. Ajústala en el mapa si hace falta.', 'lp-ok');
        },

        setSearchStatus(text, cls) {
            this.searchStatus = text;
            this.searchStatusClass = cls;
        },

        onDocumentClick(e) {
            const box = this.$refs.searchBox;
            if (box && !box.contains(e.target)) this.resultsOpen = false;
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

/* Buscador */
.lp-search {
    position: relative;
}

.lp-search-row {
    display: flex;
    gap: 0.5rem;
    align-items: stretch;
}

.lp-search-input {
    position: relative;
    flex: 1;
    min-width: 0;
}

.lp-search-input .form-input {
    padding-left: 2.25rem;
}

.lp-search-icon {
    position: absolute;
    left: 0.875rem;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    font-size: 0.85rem;
    pointer-events: none;
}

.lp-btn-search {
    display: inline-flex;
    align-items: center;
    white-space: nowrap;
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

.lp-btn-search:hover:not(:disabled) {
    background: #1f7506;
}

.lp-btn-search:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

.lp-use-address {
    display: inline-flex;
    align-items: center;
    margin-top: 0.5rem;
    padding: 0;
    font-size: 0.8rem;
    color: #279208;
    background: none;
    border: none;
    cursor: pointer;
    text-align: left;
}

.lp-use-address:hover {
    text-decoration: underline;
}

.lp-results {
    position: absolute;
    z-index: 1100;
    left: 0;
    right: 0;
    top: 100%;
    margin: 0.35rem 0 0;
    padding: 0.25rem;
    list-style: none;
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    box-shadow: 0 8px 24px rgba(15, 23, 42, 0.12);
    max-height: 260px;
    overflow-y: auto;
}

.lp-result {
    display: flex;
    align-items: flex-start;
    gap: 0.625rem;
    width: 100%;
    padding: 0.5rem 0.625rem;
    background: none;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    text-align: left;
}

.lp-result:hover {
    background: #f1f5f9;
}

.lp-result-icon {
    color: #ba2831;
    font-size: 0.85rem;
    margin-top: 0.2rem;
    flex-shrink: 0;
}

.lp-result-text {
    display: flex;
    flex-direction: column;
    min-width: 0;
}

.lp-result-text strong {
    font-size: 0.875rem;
    font-weight: 600;
    color: #1e293b;
}

.lp-result-text small {
    font-size: 0.78rem;
    color: #64748b;
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
    display: inline-block;
    margin-top: 0.4rem;
    font-size: 0.8rem;
}

.lp-ok {
    color: #279208;
}

.lp-warn {
    color: #d97706;
}

.lp-map-wrap {
    position: relative;
}

.lp-map {
    height: 320px;
    width: 100%;
    border-radius: 10px;
    border: 1px solid #e2e8f0;
    z-index: 0;
}

/* Selector de capa: encima del mapa, a la derecha para no tapar el zoom. */
.lp-basemap {
    position: absolute;
    top: 10px;
    right: 10px;
    z-index: 1000;
    display: flex;
    padding: 3px;
    background: rgba(255, 255, 255, 0.95);
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.15);
}

.lp-basemap-btn {
    padding: 0.3rem 0.6rem;
    font-size: 0.78rem;
    font-weight: 500;
    color: #475569;
    background: none;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    white-space: nowrap;
}

.lp-basemap-btn:hover {
    background: #f1f5f9;
}

.lp-basemap-btn.is-active {
    background: #279208;
    color: white;
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

    .lp-basemap-btn {
        padding: 0.3rem 0.5rem;
        font-size: 0.72rem;
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
