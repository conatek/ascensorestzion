<template>
    <div>
        <!-- Cabecera -->
        <div class="app-page-title">
            <div class="page-title-wrapper">
                <div class="page-title-heading">
                    <div class="page-title-icon">
                        <i class="fa fa-edit icon-gradient bg-mean-fruit"></i>
                    </div>
                    <div>
                        Editar Sede
                        <div class="page-title-subheading text-muted">
                            Modifica la informacion de la sede
                        </div>
                    </div>
                </div>
                <div class="page-title-actions">
                    <router-link :to="{ name: 'clients.show', params: { id: clientId } }" class="btn-action btn-back">
                        <i class="fa fa-arrow-left me-1"></i> Volver
                    </router-link>
                </div>
            </div>
        </div>

        <!-- Loading -->
        <div v-if="loadingData" class="loading-container">
            <span class="spinner-lg"></span>
            <p>Cargando datos de la sede...</p>
        </div>

        <form v-else @submit.prevent="submit">
            <div class="form-grid">
                <!-- Columna izquierda: Informacion de la Sede -->
                <div class="form-section">
                    <div class="section-card">
                        <div class="section-header">
                            <i class="fa fa-map-marker-alt section-icon"></i>
                            <span>Informacion de la Sede</span>
                        </div>
                        <div class="section-body">
                            <div class="form-group">
                                <label class="form-label">Nombre de la sede <span class="required">*</span></label>
                                <input v-model="form.name" type="text" class="form-input" :class="{ 'has-error': errors.name }"
                                    placeholder="Ej: Sede Central" />
                                <span v-if="errors.name" class="error-text">{{ errors.name[0] }}</span>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Direccion</label>
                                <input v-model="form.address" type="text" class="form-input" :class="{ 'has-error': errors.address }"
                                    placeholder="Ej: Av. Principal #123" />
                                <span v-if="errors.address" class="error-text">{{ errors.address[0] }}</span>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Ciudad</label>
                                    <input v-model="form.city" type="text" class="form-input" :class="{ 'has-error': errors.city }"
                                        placeholder="Ej: Ciudad de Panama" />
                                    <span v-if="errors.city" class="error-text">{{ errors.city[0] }}</span>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Departamento</label>
                                    <input v-model="form.department" type="text" class="form-input" :class="{ 'has-error': errors.department }"
                                        placeholder="Ej: Panama" />
                                    <span v-if="errors.department" class="error-text">{{ errors.department[0] }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Columna derecha: Contacto en Sitio -->
                <div class="form-section">
                    <div class="section-card">
                        <div class="section-header">
                            <i class="fa fa-address-book section-icon"></i>
                            <span>Contacto en Sitio</span>
                        </div>
                        <div class="section-body">
                            <div class="form-group">
                                <label class="form-label">Nombre del contacto</label>
                                <input v-model="form.contact_name_onsite" type="text" class="form-input" :class="{ 'has-error': errors.contact_name_onsite }"
                                    placeholder="Ej: Juan Perez" />
                                <span v-if="errors.contact_name_onsite" class="error-text">{{ errors.contact_name_onsite[0] }}</span>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Telefono del contacto</label>
                                    <input v-model="form.contact_phone_onsite" type="text" class="form-input" :class="{ 'has-error': errors.contact_phone_onsite }"
                                        placeholder="Ej: +507 6000-0000" />
                                    <span v-if="errors.contact_phone_onsite" class="error-text">{{ errors.contact_phone_onsite[0] }}</span>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Email del contacto</label>
                                    <input v-model="form.contact_email_onsite" type="email" class="form-input" :class="{ 'has-error': errors.contact_email_onsite }"
                                        placeholder="Ej: contacto@empresa.com" />
                                    <span v-if="errors.contact_email_onsite" class="error-text">{{ errors.contact_email_onsite[0] }}</span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Notas</label>
                                <textarea v-model="form.notes" class="form-input form-textarea" :class="{ 'has-error': errors.notes }"
                                    placeholder="Observaciones adicionales sobre la sede..." rows="4"></textarea>
                                <span v-if="errors.notes" class="error-text">{{ errors.notes[0] }}</span>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Estado</label>
                                <div class="toggle-wrapper">
                                    <label class="toggle">
                                        <input v-model="form.active" type="checkbox" class="toggle-input" />
                                        <span class="toggle-slider"></span>
                                    </label>
                                    <span class="toggle-label">{{ form.active ? 'Activa' : 'Inactiva' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Error general y botones -->
                    <div v-if="generalError" class="error-alert">
                        <i class="fa fa-exclamation-circle"></i>
                        {{ generalError }}
                    </div>

                    <div class="form-actions">
                        <router-link :to="{ name: 'clients.show', params: { id: clientId } }" class="btn-cancel">
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
    </div>
</template>

<script>
import siteService from '@/services/siteService.js';

export default {
    name: 'SiteEdit',

    data() {
        return {
            loading: false,
            loadingData: true,
            errors: {},
            generalError: null,
            clientId: this.$route.params.clientId,
            siteId: this.$route.params.siteId,
            form: {
                name: '',
                address: '',
                city: '',
                department: '',
                contact_name_onsite: '',
                contact_phone_onsite: '',
                contact_email_onsite: '',
                notes: '',
                active: true,
            },
        };
    },

    async mounted() {
        try {
            const { data } = await siteService.get(this.clientId, this.siteId);
            this.form.name = data.name || '';
            this.form.address = data.address || '';
            this.form.city = data.city || '';
            this.form.department = data.department || '';
            this.form.contact_name_onsite = data.contact_name_onsite || '';
            this.form.contact_phone_onsite = data.contact_phone_onsite || '';
            this.form.contact_email_onsite = data.contact_email_onsite || '';
            this.form.notes = data.notes || '';
            this.form.active = data.active ?? true;
        } catch (err) {
            this.generalError = err.response?.data?.message || 'Error al cargar los datos de la sede.';
        } finally {
            this.loadingData = false;
        }
    },

    methods: {
        async submit() {
            this.loading = true;
            this.errors = {};
            this.generalError = null;

            try {
                await siteService.update(this.clientId, this.siteId, this.form);
                this.$router.push({ name: 'clients.show', params: { id: this.clientId } });
            } catch (err) {
                if (err.response?.status === 422) {
                    this.errors = err.response.data.errors;
                } else {
                    this.generalError = err.response?.data?.message || 'Error al actualizar la sede.';
                }
            } finally {
                this.loading = false;
            }
        },
    },
};
</script>

<style scoped>
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

/* Loading */
.loading-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 3rem;
    color: #64748b;
}

.loading-container p {
    margin-top: 1rem;
    font-size: 0.95rem;
}

.spinner-lg {
    width: 36px;
    height: 36px;
    border: 3px solid #e2e8f0;
    border-top-color: #30ab0a;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
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

.form-textarea {
    resize: vertical;
    min-height: 100px;
    font-family: inherit;
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

/* Toggle */
.toggle-wrapper {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.toggle {
    position: relative;
    display: inline-block;
    width: 44px;
    height: 24px;
    margin: 0;
}

.toggle-input {
    opacity: 0;
    width: 0;
    height: 0;
}

.toggle-slider {
    position: absolute;
    cursor: pointer;
    inset: 0;
    background: #cbd5e1;
    border-radius: 24px;
    transition: all 0.2s;
}

.toggle-slider::before {
    content: '';
    position: absolute;
    height: 18px;
    width: 18px;
    left: 3px;
    bottom: 3px;
    background: white;
    border-radius: 50%;
    transition: all 0.2s;
}

.toggle-input:checked + .toggle-slider {
    background: #30ab0a;
}

.toggle-input:checked + .toggle-slider::before {
    transform: translateX(20px);
}

.toggle-label {
    font-size: 0.9rem;
    color: #475569;
    font-weight: 500;
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
