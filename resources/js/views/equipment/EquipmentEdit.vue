<template>
    <div>
        <!-- Estado de carga -->
        <div v-if="loadingData" class="loading-state">
            <div class="spinner-border text-primary"></div>
            <p class="text-muted mt-3">Cargando equipo...</p>
        </div>

        <template v-else>
            <!-- Cabecera -->
            <div class="app-page-title">
                <div class="page-title-wrapper">
                    <div class="page-title-heading">
                        <div class="page-title-icon">
                            <i class="fa fa-edit icon-gradient bg-mean-fruit"></i>
                        </div>
                        <div>
                            Editar Equipo
                            <div class="page-title-subheading text-muted">
                                {{ form.internal_code }}
                            </div>
                        </div>
                    </div>
                    <div class="page-title-actions">
                        <router-link :to="{ name: 'equipment.show', params: { id: $route.params.id } }" class="btn-action btn-back">
                            <i class="fa fa-arrow-left me-1"></i> Volver
                        </router-link>
                    </div>
                </div>
            </div>

            <form @submit.prevent="submit">
                <div class="form-grid">
                    <!-- Columna izquierda -->
                    <div class="form-section">
                        <!-- Seccion 1: Identificacion -->
                        <div class="section-card">
                            <div class="section-header">
                                <i class="fa fa-fingerprint section-icon"></i>
                                <span>Identificacion</span>
                            </div>
                            <div class="section-body">
                                <div class="form-row">
                                    <div class="form-group">
                                        <label class="form-label">Codigo interno <span class="required">*</span></label>
                                        <input v-model="form.internal_code" type="text" class="form-input"
                                               :class="{ 'has-error': errors.internal_code }"
                                               placeholder="Ej: ASC-001" />
                                        <span v-if="errors.internal_code" class="error-text">{{ errors.internal_code[0] }}</span>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Codigo del cliente</label>
                                        <input v-model="form.customer_code" type="text" class="form-input"
                                               :class="{ 'has-error': errors.customer_code }"
                                               placeholder="Codigo asignado por el cliente" />
                                        <span v-if="errors.customer_code" class="error-text">{{ errors.customer_code[0] }}</span>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Tipo de equipo <span class="required">*</span></label>
                                    <select v-model="form.equipment_type" class="form-input"
                                            :class="{ 'has-error': errors.equipment_type }">
                                        <option value="">Seleccionar tipo</option>
                                        <option value="ascensor">Ascensor</option>
                                        <option value="escalera_electrica">Escalera Electrica</option>
                                        <option value="montacargas">Montacargas</option>
                                        <option value="otro">Otro</option>
                                    </select>
                                    <span v-if="errors.equipment_type" class="error-text">{{ errors.equipment_type[0] }}</span>
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label class="form-label">Marca</label>
                                        <input v-model="form.brand" type="text" class="form-input"
                                               :class="{ 'has-error': errors.brand }"
                                               placeholder="Ej: Otis, Schindler" />
                                        <span v-if="errors.brand" class="error-text">{{ errors.brand[0] }}</span>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Modelo</label>
                                        <input v-model="form.model" type="text" class="form-input"
                                               :class="{ 'has-error': errors.model }"
                                               placeholder="Modelo del equipo" />
                                        <span v-if="errors.model" class="error-text">{{ errors.model[0] }}</span>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Numero de serie</label>
                                    <input v-model="form.serial_number" type="text" class="form-input"
                                           :class="{ 'has-error': errors.serial_number }"
                                           placeholder="Numero de serie del fabricante" />
                                    <span v-if="errors.serial_number" class="error-text">{{ errors.serial_number[0] }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Seccion 2: Ubicacion y Contrato -->
                        <div class="section-card">
                            <div class="section-header">
                                <i class="fa fa-map-marker-alt section-icon"></i>
                                <span>Ubicacion y Contrato</span>
                            </div>
                            <div class="section-body">
                                <div class="form-row">
                                    <div class="form-group">
                                        <label class="form-label">Cliente <span class="required">*</span></label>
                                        <select v-model="selectedClientId" class="form-input"
                                                :class="{ 'has-error': errors.site_id }"
                                                @change="onClientChange">
                                            <option value="">Seleccionar cliente</option>
                                            <option v-for="client in clients" :key="client.id" :value="client.id">
                                                {{ client.business_name }}
                                            </option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Sede <span class="required">*</span></label>
                                        <select v-model="form.site_id" class="form-input"
                                                :class="{ 'has-error': errors.site_id }"
                                                :disabled="!selectedClientId || loadingSites">
                                            <option value="">{{ loadingSites ? 'Cargando...' : 'Seleccionar sede' }}</option>
                                            <option v-for="site in sites" :key="site.id" :value="site.id">
                                                {{ site.name }}
                                            </option>
                                        </select>
                                        <span v-if="errors.site_id" class="error-text">{{ errors.site_id[0] }}</span>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Tipo de contrato</label>
                                    <select v-model="form.contract_type" class="form-input"
                                            :class="{ 'has-error': errors.contract_type }">
                                        <option value="">Sin contrato</option>
                                        <option value="mantenimiento">Mantenimiento</option>
                                        <option value="correctivo">Correctivo</option>
                                        <option value="integral">Integral</option>
                                    </select>
                                    <span v-if="errors.contract_type" class="error-text">{{ errors.contract_type[0] }}</span>
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label class="form-label">Inicio de contrato</label>
                                        <input v-model="form.contract_start" type="date" class="form-input"
                                               :class="{ 'has-error': errors.contract_start }" />
                                        <span v-if="errors.contract_start" class="error-text">{{ errors.contract_start[0] }}</span>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Fin de contrato</label>
                                        <input v-model="form.contract_end" type="date" class="form-input"
                                               :class="{ 'has-error': errors.contract_end }" />
                                        <span v-if="errors.contract_end" class="error-text">{{ errors.contract_end[0] }}</span>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Frecuencia de mantenimiento (dias)</label>
                                    <input v-model="form.maintenance_frequency_days" type="number" class="form-input"
                                           :class="{ 'has-error': errors.maintenance_frequency_days }"
                                           placeholder="Ej: 30" min="1" />
                                    <span v-if="errors.maintenance_frequency_days" class="error-text">{{ errors.maintenance_frequency_days[0] }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Columna derecha -->
                    <div class="form-section">
                        <!-- Seccion 3: Capacidad y Estado -->
                        <div class="section-card">
                            <div class="section-header">
                                <i class="fa fa-tachometer-alt section-icon"></i>
                                <span>Capacidad y Estado</span>
                            </div>
                            <div class="section-body">
                                <div class="form-row">
                                    <div class="form-group">
                                        <label class="form-label">Capacidad (kg)</label>
                                        <input v-model="form.capacity_kg" type="number" class="form-input"
                                               :class="{ 'has-error': errors.capacity_kg }"
                                               placeholder="Ej: 1000" min="0" step="1" />
                                        <span v-if="errors.capacity_kg" class="error-text">{{ errors.capacity_kg[0] }}</span>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Capacidad (personas)</label>
                                        <input v-model="form.capacity_persons" type="number" class="form-input"
                                               :class="{ 'has-error': errors.capacity_persons }"
                                               placeholder="Ej: 13" min="0" step="1" />
                                        <span v-if="errors.capacity_persons" class="error-text">{{ errors.capacity_persons[0] }}</span>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label class="form-label">Paradas</label>
                                        <input v-model="form.stops" type="number" class="form-input"
                                               :class="{ 'has-error': errors.stops }"
                                               placeholder="Ej: 10" min="0" step="1" />
                                        <span v-if="errors.stops" class="error-text">{{ errors.stops[0] }}</span>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Velocidad (m/s)</label>
                                        <input v-model="form.speed_mps" type="number" class="form-input"
                                               :class="{ 'has-error': errors.speed_mps }"
                                               placeholder="Ej: 1.5" min="0" step="0.01" />
                                        <span v-if="errors.speed_mps" class="error-text">{{ errors.speed_mps[0] }}</span>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Fecha de instalacion</label>
                                    <input v-model="form.installation_date" type="date" class="form-input"
                                           :class="{ 'has-error': errors.installation_date }" />
                                    <span v-if="errors.installation_date" class="error-text">{{ errors.installation_date[0] }}</span>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Estado <span class="required">*</span></label>
                                    <select v-model="form.status" class="form-input"
                                            :class="{ 'has-error': errors.status }">
                                        <option value="activo">Activo</option>
                                        <option value="fuera_servicio">Fuera de Servicio</option>
                                        <option value="modernizacion">Modernizacion</option>
                                        <option value="retirado">Retirado</option>
                                    </select>
                                    <span v-if="errors.status" class="error-text">{{ errors.status[0] }}</span>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Notas</label>
                                    <textarea v-model="form.notes" class="form-input form-textarea"
                                              :class="{ 'has-error': errors.notes }"
                                              placeholder="Observaciones adicionales..." rows="4"></textarea>
                                    <span v-if="errors.notes" class="error-text">{{ errors.notes[0] }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Error general y botones -->
                        <div v-if="generalError" class="error-alert">
                            <i class="fa fa-exclamation-circle"></i>
                            {{ generalError }}
                        </div>

                        <div class="form-actions">
                            <router-link :to="{ name: 'equipment.show', params: { id: $route.params.id } }" class="btn-cancel">
                                Cancelar
                            </router-link>
                            <button type="submit" class="btn-submit" :disabled="loading">
                                <span v-if="loading" class="spinner"></span>
                                <i v-else class="fa fa-check"></i>
                                {{ loading ? 'Guardando...' : 'Guardar cambios' }}
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </template>
    </div>
</template>

<script>
import equipmentService from '@/services/equipmentService.js';
import clientService from '@/services/clientService.js';
import siteService from '@/services/siteService.js';

export default {
    name: 'EquipmentEdit',

    data() {
        return {
            loading: false,
            loadingData: true,
            loadingSites: false,
            errors: {},
            generalError: null,
            clients: [],
            sites: [],
            selectedClientId: '',
            form: {
                internal_code: '',
                customer_code: '',
                equipment_type: '',
                brand: '',
                model: '',
                serial_number: '',
                site_id: '',
                contract_type: '',
                contract_start: '',
                contract_end: '',
                maintenance_frequency_days: '',
                capacity_kg: '',
                capacity_persons: '',
                stops: '',
                speed_mps: '',
                installation_date: '',
                status: 'activo',
                notes: '',
            },
        };
    },

    async created() {
        await this.loadData();
    },

    methods: {
        async loadData() {
            this.loadingData = true;
            try {
                const [equipmentRes, clientsRes] = await Promise.all([
                    equipmentService.get(this.$route.params.id),
                    clientService.all(),
                ]);

                this.clients = clientsRes.data;
                const eq = equipmentRes.data;

                // Populate form
                this.form.internal_code = eq.internal_code || '';
                this.form.customer_code = eq.customer_code || '';
                this.form.equipment_type = eq.equipment_type || '';
                this.form.brand = eq.brand || '';
                this.form.model = eq.model || '';
                this.form.serial_number = eq.serial_number || '';
                this.form.site_id = eq.site_id || '';
                this.form.contract_type = eq.contract_type || '';
                this.form.contract_start = eq.contract_start || '';
                this.form.contract_end = eq.contract_end || '';
                this.form.maintenance_frequency_days = eq.maintenance_frequency_days || '';
                this.form.capacity_kg = eq.capacity_kg || '';
                this.form.capacity_persons = eq.capacity_persons || '';
                this.form.stops = eq.stops || '';
                this.form.speed_mps = eq.speed_mps || '';
                this.form.installation_date = eq.installation_date || '';
                this.form.status = eq.status || 'activo';
                this.form.notes = eq.notes || '';

                // Set client from site relationship
                const clientId = eq.site?.client_id || eq.site?.client?.id || '';
                if (clientId) {
                    this.selectedClientId = clientId;
                    await this.loadSites(clientId);
                }
            } finally {
                this.loadingData = false;
            }
        },

        async loadSites(clientId) {
            this.loadingSites = true;
            try {
                const { data } = await siteService.all(clientId);
                this.sites = data;
            } catch {
                this.sites = [];
            } finally {
                this.loadingSites = false;
            }
        },

        async onClientChange() {
            this.form.site_id = '';
            this.sites = [];
            if (!this.selectedClientId) return;
            await this.loadSites(this.selectedClientId);
        },

        async submit() {
            this.loading = true;
            this.errors = {};
            this.generalError = null;

            const payload = new FormData();
            payload.append('_method', 'PUT');
            for (const [key, value] of Object.entries(this.form)) {
                if (value !== '' && value !== null && value !== undefined) {
                    payload.append(key, value);
                }
            }

            try {
                await equipmentService.update(this.$route.params.id, payload);
                this.$router.push({ name: 'equipment.show', params: { id: this.$route.params.id } });
            } catch (err) {
                if (err.response?.status === 422) {
                    this.errors = err.response.data.errors;
                } else {
                    this.generalError = err.response?.data?.message || 'Error al actualizar el equipo.';
                }
            } finally {
                this.loading = false;
            }
        },
    },
};
</script>

<style scoped>
/* Loading */
.loading-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 4rem 2rem;
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border-radius: 16px;
}

/* Action buttons */
.btn-action {
    display: inline-flex;
    align-items: center;
    padding: 0.5rem 1rem;
    font-size: 0.85rem;
    font-weight: 500;
    border-radius: 8px;
    text-decoration: none;
    transition: all 0.2s;
}

.btn-back {
    background: #f1f5f9;
    color: #475569;
    border: 1px solid #e2e8f0;
}

.btn-back:hover {
    background: #e2e8f0;
    color: #334155;
}

/* Form Grid */
.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
}

@media (max-width: 992px) {
    .form-grid {
        grid-template-columns: 1fr;
    }
}

/* Section Card */
.section-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    border: 1px solid #e2e8f0;
    overflow: hidden;
    margin-bottom: 1.5rem;
}

.section-header {
    padding: 1rem 1.25rem;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-weight: 600;
    color: #1e293b;
}

.section-icon {
    width: 32px;
    height: 32px;
    background: #e8f5e4;
    color: #279208;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.9rem;
}

.section-body {
    padding: 1.5rem;
}

/* Form Elements */
.form-group {
    margin-bottom: 1.25rem;
}

.form-group:last-child {
    margin-bottom: 0;
}

.form-label {
    display: block;
    font-size: 0.875rem;
    font-weight: 500;
    color: #374151;
    margin-bottom: 0.5rem;
}

.required {
    color: #ba2831;
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

.form-input.has-error {
    border-color: #ba2831;
}

.form-input::placeholder {
    color: #94a3b8;
}

.form-input:disabled {
    background: #f8fafc;
    color: #94a3b8;
    cursor: not-allowed;
}

.form-textarea {
    resize: vertical;
    min-height: 100px;
}

.error-text {
    display: block;
    font-size: 0.8rem;
    color: #ba2831;
    margin-top: 0.375rem;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

@media (max-width: 576px) {
    .form-row {
        grid-template-columns: 1fr;
    }
}

/* Error Alert */
.error-alert {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem;
    background: #fef2f2;
    border: 1px solid #fecaca;
    border-radius: 8px;
    color: #dc2626;
    font-size: 0.9rem;
    margin-bottom: 1.5rem;
}

/* Form Actions */
.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
}

.btn-cancel {
    padding: 0.625rem 1.25rem;
    font-size: 0.9rem;
    font-weight: 500;
    background: #f1f5f9;
    color: #475569;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.2s;
}

.btn-cancel:hover {
    background: #e2e8f0;
    color: #334155;
}

.btn-submit {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.625rem 1.25rem;
    font-size: 0.9rem;
    font-weight: 500;
    background: #279208;
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-submit:hover:not(:disabled) {
    background: #1f7506;
}

.btn-submit:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

.spinner {
    width: 16px;
    height: 16px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-top-color: white;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}
</style>
