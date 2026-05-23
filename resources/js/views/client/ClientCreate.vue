<template>
    <div>
        <!-- Cabecera -->
        <div class="app-page-title">
            <div class="page-title-wrapper">
                <div class="page-title-heading">
                    <div class="page-title-icon">
                        <i class="fa fa-plus icon-gradient bg-mean-fruit"></i>
                    </div>
                    <div>
                        Nuevo Cliente
                        <div class="page-title-subheading text-muted">
                            Registra un nuevo cliente en el sistema
                        </div>
                    </div>
                </div>
                <div class="page-title-actions">
                    <router-link :to="{ name: 'clients.index' }" class="btn-action btn-back">
                        <i class="fa fa-arrow-left me-1"></i> Volver
                    </router-link>
                </div>
            </div>
        </div>

        <form @submit.prevent="submit">
            <div class="form-grid">
                <!-- Columna izquierda: Informacion General -->
                <div class="form-section">
                    <div class="section-card">
                        <div class="section-header">
                            <i class="fa fa-building section-icon"></i>
                            <span>Informacion General</span>
                        </div>
                        <div class="section-body">
                            <div class="form-group">
                                <label class="form-label">Razon Social <span class="required">*</span></label>
                                <input v-model="form.business_name" type="text" class="form-input" :class="{ 'has-error': errors.business_name }"
                                    placeholder="Ej: Empresa S.A.S." />
                                <span v-if="errors.business_name" class="error-text">{{ errors.business_name[0] }}</span>
                            </div>

                            <div class="form-group">
                                <label class="form-label">NIT <span class="required">*</span></label>
                                <input v-model="form.nit" type="text" class="form-input" :class="{ 'has-error': errors.nit }"
                                    placeholder="Ej: 900.123.456-7" />
                                <span v-if="errors.nit" class="error-text">{{ errors.nit[0] }}</span>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Direccion</label>
                                <input v-model="form.address" type="text" class="form-input" :class="{ 'has-error': errors.address }"
                                    placeholder="Ej: Cra. 7 #45-12, Oficina 301" />
                                <span v-if="errors.address" class="error-text">{{ errors.address[0] }}</span>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Ciudad</label>
                                    <input v-model="form.city" type="text" class="form-input" :class="{ 'has-error': errors.city }"
                                        placeholder="Ej: Bogota" />
                                    <span v-if="errors.city" class="error-text">{{ errors.city[0] }}</span>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Departamento</label>
                                    <input v-model="form.department" type="text" class="form-input" :class="{ 'has-error': errors.department }"
                                        placeholder="Ej: Cundinamarca" />
                                    <span v-if="errors.department" class="error-text">{{ errors.department[0] }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Columna derecha: Contacto -->
                <div class="form-section">
                    <div class="section-card">
                        <div class="section-header">
                            <i class="fa fa-address-book section-icon"></i>
                            <span>Contacto</span>
                        </div>
                        <div class="section-body">
                            <div class="form-group">
                                <label class="form-label">Nombre de contacto</label>
                                <input v-model="form.contact_name" type="text" class="form-input" :class="{ 'has-error': errors.contact_name }"
                                    placeholder="Ej: Juan Perez" />
                                <span v-if="errors.contact_name" class="error-text">{{ errors.contact_name[0] }}</span>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Email de contacto</label>
                                    <input v-model="form.contact_email" type="email" class="form-input" :class="{ 'has-error': errors.contact_email }"
                                        placeholder="correo@empresa.com" />
                                    <span v-if="errors.contact_email" class="error-text">{{ errors.contact_email[0] }}</span>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Telefono de contacto</label>
                                    <input v-model="form.contact_phone" type="text" class="form-input" :class="{ 'has-error': errors.contact_phone }"
                                        placeholder="Ej: 310 123 4567" />
                                    <span v-if="errors.contact_phone" class="error-text">{{ errors.contact_phone[0] }}</span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Notas</label>
                                <textarea v-model="form.notes" class="form-input form-textarea" :class="{ 'has-error': errors.notes }"
                                    rows="3" placeholder="Observaciones o notas adicionales sobre el cliente..."></textarea>
                                <span v-if="errors.notes" class="error-text">{{ errors.notes[0] }}</span>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Estado</label>
                                <div class="toggle-wrapper">
                                    <label class="toggle-switch">
                                        <input type="checkbox" v-model="form.active" />
                                        <span class="toggle-slider"></span>
                                    </label>
                                    <span class="toggle-label">{{ form.active ? 'Activo' : 'Inactivo' }}</span>
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
                        <router-link :to="{ name: 'clients.index' }" class="btn-cancel">
                            Cancelar
                        </router-link>
                        <button type="submit" class="btn-submit" :disabled="loading">
                            <span v-if="loading" class="spinner"></span>
                            <i v-else class="fa fa-check"></i>
                            {{ loading ? 'Guardando...' : 'Guardar Cliente' }}
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</template>

<script>
import clientService from '@/services/clientService.js';

export default {
    name: 'ClientCreate',

    data() {
        return {
            loading: false,
            errors: {},
            generalError: null,
            form: {
                business_name: '',
                nit: '',
                address: '',
                city: '',
                department: '',
                contact_name: '',
                contact_email: '',
                contact_phone: '',
                notes: '',
                active: true,
            },
        };
    },

    methods: {
        async submit() {
            this.loading = true;
            this.errors = {};
            this.generalError = null;

            try {
                const { data } = await clientService.store(this.form);
                this.$router.push({ name: 'clients.show', params: { id: data.id } });
            } catch (err) {
                if (err.response?.status === 422) {
                    this.errors = err.response.data.errors;
                } else {
                    this.generalError = err.response?.data?.message || 'Error al crear el cliente.';
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
    min-height: 80px;
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

/* Toggle Switch */
.toggle-wrapper {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.toggle-switch {
    position: relative;
    display: inline-block;
    width: 44px;
    height: 24px;
    cursor: pointer;
}

.toggle-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.toggle-slider {
    position: absolute;
    inset: 0;
    background: #cbd5e1;
    border-radius: 24px;
    transition: all 0.3s;
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
    transition: all 0.3s;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
}

.toggle-switch input:checked + .toggle-slider {
    background: #30ab0a;
}

.toggle-switch input:checked + .toggle-slider::before {
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
