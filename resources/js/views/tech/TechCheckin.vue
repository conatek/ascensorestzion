<template>
    <div class="tech-checkin">
        <h1 class="tech-checkin__title">Registrar Llegada</h1>

        <!-- Tabs: QR / Manual -->
        <div class="checkin-tabs">
            <button
                class="checkin-tab"
                :class="{ 'is-active': activeTab === 'qr' }"
                @click="activeTab = 'qr'"
            >
                <i class="fa fa-qrcode"></i> Escanear QR
            </button>
            <button
                class="checkin-tab"
                :class="{ 'is-active': activeTab === 'manual' }"
                @click="activeTab = 'manual'"
            >
                <i class="fa fa-keyboard"></i> Código Manual
            </button>
        </div>

        <!-- Tab: QR Scanner -->
        <div v-if="activeTab === 'qr' && !checkinResult" class="checkin-panel">
            <QrScanner @scanned="onQrScanned" @error="onQrError" />
        </div>

        <!-- Tab: Manual Code -->
        <div v-if="activeTab === 'manual' && !checkinResult" class="checkin-panel">
            <div class="manual-input-group">
                <label class="manual-label">Código del equipo</label>
                <input
                    v-model="manualCode"
                    type="text"
                    class="manual-input"
                    placeholder="Ej: ASC-001"
                    @keyup.enter="submitManualCode"
                    spellcheck="false"
                    autocomplete="off"
                />
                <button
                    class="manual-submit-btn"
                    :disabled="!manualCode.trim() || loading"
                    @click="submitManualCode"
                >
                    <i v-if="loading" class="fa fa-spinner fa-spin"></i>
                    <template v-else>Buscar equipo</template>
                </button>
            </div>
        </div>

        <!-- Opciones de emergencia -->
        <div v-if="!checkinResult" class="emergency-section">
            <div class="emergency-toggle">
                <label class="toggle-label">
                    <span class="toggle-text">
                        <i class="fa fa-exclamation-triangle text-danger"></i>
                        Llamada de emergencia
                    </span>
                    <label class="switch">
                        <input type="checkbox" v-model="isEmergency">
                        <span class="slider"></span>
                    </label>
                </label>
            </div>

            <div v-if="isEmergency" class="emergency-fields">
                <label class="manual-label">Hora de recepción de llamada</label>
                <input
                    v-model="callReceivedAt"
                    type="datetime-local"
                    class="manual-input"
                />
            </div>
        </div>

        <!-- Geolocation status -->
        <div v-if="!checkinResult && geoStatus" class="geo-status" :class="geoStatusClass">
            <i :class="geoIcon"></i>
            <span>{{ geoStatus }}</span>
        </div>

        <!-- Error -->
        <div v-if="error" class="checkin-error">
            <i class="fa fa-exclamation-circle"></i>
            {{ error }}
        </div>

        <!-- Resultado exitoso -->
        <div v-if="checkinResult" class="checkin-success">
            <div class="success-icon">
                <i class="fa fa-check-circle"></i>
            </div>
            <h2 class="success-title">Check-in registrado</h2>
            <p class="success-time">{{ formatTime(checkinResult.checkin.checked_in_at) }}</p>

            <!-- Proximidad -->
            <div v-if="checkinResult.proximity" class="proximity-badge" :class="checkinResult.proximity.within_radius ? 'proximity-ok' : 'proximity-warn'">
                <i :class="checkinResult.proximity.within_radius ? 'fa fa-map-marker-alt' : 'fa fa-exclamation-triangle'"></i>
                <span v-if="checkinResult.proximity.within_radius">
                    Dentro del rango ({{ checkinResult.proximity.distance_meters }}m)
                </span>
                <span v-else>
                    Fuera del rango — {{ checkinResult.proximity.distance_meters }}m del sitio
                </span>
            </div>
            <div v-else-if="checkinResult.checkin.latitude" class="proximity-badge proximity-neutral">
                <i class="fa fa-info-circle"></i>
                <span>Ubicación registrada (la sede no tiene coordenadas para validar el rango).</span>
            </div>

            <!-- Tiempo de respuesta si emergencia -->
            <div v-if="checkinResult.checkin.is_emergency && checkinResult.checkin.response_time_minutes != null" class="response-time-badge">
                <i class="fa fa-clock"></i>
                Tiempo de respuesta: <strong>{{ checkinResult.checkin.response_time_minutes }} min</strong>
            </div>

            <!-- Botón continuar -->
            <router-link
                :to="{ name: 'tech.equipment', params: { id: checkinResult.equipment.id } }"
                class="continue-btn"
            >
                Ver ficha técnica <i class="fa fa-arrow-right"></i>
            </router-link>
        </div>
    </div>
</template>

<script>
import QrScanner from '@/components/tech/QrScanner.vue';
import checkinService from '@/services/checkinService.js';
import { getCurrentPosition } from '@/utils/useGeolocation.js';
import offlineManager from '@/utils/offlineManager.js';

export default {
    name: 'TechCheckin',
    components: { QrScanner },

    data() {
        return {
            activeTab: 'qr',
            manualCode: '',
            isEmergency: false,
            callReceivedAt: '',
            loading: false,
            error: null,
            checkinResult: null,
            geoStatus: null,
            geoStatusClass: '',
            geoIcon: 'fa fa-spinner fa-spin',
            position: null,
        };
    },

    async created() {
        // Obtener la ubicacion ANTES de cualquier check-in para que el QR
        // (que auto-registra al llegar) incluya las coordenadas y se valide la proximidad.
        await this.requestGeolocation();

        // Pre-cargar codigo de query params (viene del QR URL)
        const code = this.$route.query.code;
        if (code) {
            this.activeTab = 'manual';
            this.manualCode = code;
            this.submitManualCode();
        }
    },

    methods: {
        async requestGeolocation() {
            this.geoStatus = 'Obteniendo ubicación...';
            this.geoStatusClass = 'geo-loading';
            this.geoIcon = 'fa fa-spinner fa-spin';

            try {
                this.position = await getCurrentPosition();
                this.geoStatus = `Ubicación obtenida (±${Math.round(this.position.accuracy)}m)`;
                this.geoStatusClass = 'geo-ok';
                this.geoIcon = 'fa fa-map-marker-alt';
            } catch (err) {
                this.geoStatus = err.message;
                this.geoStatusClass = 'geo-warn';
                this.geoIcon = 'fa fa-exclamation-triangle';
            }
        },

        onQrScanned(code) {
            this.doCheckin(code, 'qr');
        },

        onQrError(errorMsg) {
            // El QrScanner ya muestra el error, no duplicar
        },

        submitManualCode() {
            if (!this.manualCode.trim()) return;
            this.doCheckin(this.manualCode.trim(), 'manual');
        },

        async doCheckin(code, method) {
            this.loading = true;
            this.error = null;

            const payload = {
                equipment_code: code,
                method,
                latitude: this.position?.latitude ?? null,
                longitude: this.position?.longitude ?? null,
                accuracy: this.position?.accuracy ?? null,
                is_emergency: this.isEmergency,
                call_received_at: this.callReceivedAt || null,
            };

            // Si estamos offline, encolar y navegar con datos cacheados
            if (!offlineManager.state.isOnline) {
                await offlineManager.queueCheckin(payload);
                const cached = await offlineManager.getCachedEquipmentByCode(code);
                if (cached) {
                    this.checkinResult = {
                        checkin: { ...payload, checked_in_at: new Date().toISOString() },
                        equipment: cached,
                        proximity: null,
                    };
                } else {
                    this.error = 'Sin conexión y equipo no encontrado en caché. Se procesará al reconectar.';
                }
                this.loading = false;
                return;
            }

            try {
                const { data } = await checkinService.store(payload);
                this.checkinResult = data;

                // Cachear equipo para uso offline futuro
                if (data.equipment) {
                    await offlineManager.cacheEquipment(data.equipment);
                }

                // Guardar checkin_id para uso en el reporte
                sessionStorage.setItem('current_checkin', JSON.stringify({
                    checkin_id: data.checkin.id,
                    equipment_id: data.equipment.id,
                    checked_in_at: data.checkin.checked_in_at,
                    is_emergency: data.checkin.is_emergency,
                    response_time_minutes: data.checkin.response_time_minutes,
                }));
            } catch (err) {
                if (err.response?.status === 404) {
                    this.error = 'Equipo no encontrado. Verifique el código.';
                } else if (err.response?.status === 422) {
                    this.error = err.response.data.message || 'Datos inválidos.';
                } else {
                    this.error = 'Error al registrar check-in. Intente nuevamente.';
                }
            } finally {
                this.loading = false;
            }
        },

        formatTime(dateStr) {
            if (!dateStr) return '';
            const d = new Date(dateStr);
            return d.toLocaleTimeString('es-CO', { hour: '2-digit', minute: '2-digit' })
                + ' — ' + d.toLocaleDateString('es-CO', { day: 'numeric', month: 'short', year: 'numeric' });
        },
    },
};
</script>

<style scoped>
.tech-checkin {
    padding: 1rem;
}

.tech-checkin__title {
    font-size: 1.25rem;
    font-weight: 700;
    color: #1e293b;
    margin: 0 0 1rem;
    text-align: center;
}

/* Tabs */
.checkin-tabs {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.checkin-tab {
    flex: 1;
    padding: 0.75rem;
    background: #f8fafc;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    font-size: 0.9rem;
    font-weight: 600;
    color: #64748b;
    cursor: pointer;
    transition: all 0.2s;
    min-height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.checkin-tab.is-active {
    background: #e8f5e4;
    border-color: #30ab0a;
    color: #30ab0a;
}

/* Panel */
.checkin-panel {
    margin-bottom: 1rem;
}

/* Manual input */
.manual-input-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.manual-label {
    font-size: 0.85rem;
    font-weight: 600;
    color: #475569;
}

.manual-input {
    padding: 0.75rem 1rem;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    font-size: 1rem;
    min-height: 48px;
    transition: border-color 0.2s;
}

.manual-input:focus {
    outline: none;
    border-color: #30ab0a;
}

.manual-submit-btn {
    padding: 0.75rem;
    background: linear-gradient(135deg, #30ab0a, #279208);
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    min-height: 48px;
    transition: opacity 0.2s;
}

.manual-submit-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* Emergency */
.emergency-section {
    background: white;
    border-radius: 12px;
    padding: 1rem;
    margin-bottom: 1rem;
    border: 1px solid #e2e8f0;
}

.emergency-toggle {
    display: flex;
    align-items: center;
}

.toggle-label {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    cursor: pointer;
}

.toggle-text {
    font-size: 0.9rem;
    font-weight: 600;
    color: #1e293b;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.text-danger {
    color: #ba2831;
}

/* Toggle switch */
.switch {
    position: relative;
    display: inline-block;
    width: 52px;
    height: 28px;
    flex-shrink: 0;
}

.switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #cbd5e1;
    border-radius: 28px;
    transition: 0.3s;
}

.slider::before {
    content: '';
    position: absolute;
    height: 22px;
    width: 22px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    border-radius: 50%;
    transition: 0.3s;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
}

.switch input:checked + .slider {
    background-color: #ba2831;
}

.switch input:checked + .slider::before {
    transform: translateX(24px);
}

.emergency-fields {
    margin-top: 0.75rem;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

/* Geo status */
.geo-status {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.6rem 1rem;
    border-radius: 10px;
    font-size: 0.8rem;
    font-weight: 500;
    margin-bottom: 1rem;
}

.geo-loading {
    background: #f1f5f9;
    color: #64748b;
}

.geo-ok {
    background: #e8f5e4;
    color: #279208;
}

.geo-warn {
    background: #fffbeb;
    color: #d97706;
}

/* Error */
.checkin-error {
    background: #fef2f2;
    color: #ba2831;
    padding: 0.75rem 1rem;
    border-radius: 10px;
    font-size: 0.85rem;
    font-weight: 500;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

/* Success */
.checkin-success {
    text-align: center;
    padding: 1.5rem 1rem;
}

.success-icon {
    font-size: 3rem;
    color: #30ab0a;
    margin-bottom: 0.75rem;
}

.success-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: #1e293b;
    margin: 0 0 0.25rem;
}

.success-time {
    font-size: 0.9rem;
    color: #64748b;
    margin: 0 0 1rem;
}

.proximity-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.4rem 0.85rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 500;
    margin-bottom: 0.75rem;
}

.proximity-ok {
    background: #e8f5e4;
    color: #279208;
}

.proximity-warn {
    background: #fffbeb;
    color: #d97706;
}

.proximity-neutral {
    background: #f1f5f9;
    color: #606060;
}

.response-time-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.4rem 0.85rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 500;
    background: #fef2f2;
    color: #ba2831;
    margin-bottom: 1rem;
}

.continue-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    width: 100%;
    padding: 0.85rem;
    background: linear-gradient(135deg, #30ab0a, #279208);
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 1rem;
    font-weight: 600;
    text-decoration: none;
    min-height: 48px;
    margin-top: 0.5rem;
    box-shadow: 0 4px 12px rgba(48, 171, 10, 0.3);
}
</style>
