<template>
    <div>
        <!-- Cabecera -->
        <div class="app-page-title">
            <div class="page-title-wrapper">
                <div class="page-title-heading">
                    <div class="page-title-icon">
                        <i class="fa icon-gradient bg-mean-fruit" :class="isEdit ? 'fa-edit' : 'fa-plus'"></i>
                    </div>
                    <div>
                        {{ isEdit ? 'Editar proveedor' : 'Nuevo proveedor' }}
                        <div class="page-title-subheading text-muted">
                            {{ isEdit ? form.business_name : 'Registra un proveedor con su sede y su asesor principal' }}
                        </div>
                    </div>
                </div>
                <div class="page-title-actions">
                    <router-link :to="backRoute" class="btn-action btn-back">
                        <i class="fa fa-arrow-left me-1"></i> Volver
                    </router-link>
                </div>
            </div>
        </div>

        <div v-if="loadingData" class="loading-state">
            <div class="spinner-border text-primary"></div>
            <p class="text-muted mt-3">Cargando proveedor...</p>
        </div>

        <form v-else @submit.prevent="submit">
            <div class="form-grid">
                <!-- Columna izquierda -->
                <div class="form-section">
                    <div class="section-card">
                        <div class="section-header">
                            <i class="fa fa-building section-icon"></i>
                            <span>Datos del proveedor</span>
                        </div>
                        <div class="section-body">
                            <div class="form-group">
                                <label class="form-label">Razón social <span class="required">*</span></label>
                                <input v-model="form.business_name" type="text" class="form-input" :class="{ 'has-error': errors.business_name }"
                                    placeholder="Ej: Repuestos Verticales S.A.S." />
                                <span v-if="errors.business_name" class="error-text">{{ errors.business_name[0] }}</span>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Nombre comercial</label>
                                    <input v-model="form.trade_name" type="text" class="form-input" placeholder="Como lo conoce el equipo" />
                                </div>
                                <div class="form-group">
                                    <label class="form-label">NIT</label>
                                    <input v-model="form.nit" type="text" class="form-input" :class="{ 'has-error': errors.nit }"
                                        placeholder="Ej: 900.123.456-7" />
                                    <span v-if="errors.nit" class="error-text">{{ errors.nit[0] }}</span>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Tipo <span class="required">*</span></label>
                                    <select v-model="form.supplier_type" class="form-input" :class="{ 'has-error': errors.supplier_type }">
                                        <option v-for="t in types" :key="t.value" :value="t.value">{{ t.label }}</option>
                                    </select>
                                    <span v-if="errors.supplier_type" class="error-text">{{ errors.supplier_type[0] }}</span>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Sitio web</label>
                                    <input v-model="form.website" type="text" class="form-input" placeholder="https://..." />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Qué vende</label>
                                <TagInput v-model="form.categories" :suggestions="categorySuggestions"
                                    placeholder="Ej: Tarjetas electrónicas (Enter para añadir)" />
                            </div>

                            <div class="form-group">
                                <label class="form-label">Marcas de ascensor que maneja</label>
                                <TagInput v-model="form.brands" :suggestions="brandSuggestions" placeholder="Ej: Mitsubishi (Enter para añadir)" />
                            </div>
                        </div>
                    </div>

                    <!-- Solo al crear: sede y asesor en el mismo paso -->
                    <div v-if="!isEdit" class="section-card">
                        <div class="section-header">
                            <i class="fa fa-map-marker-alt section-icon"></i>
                            <span>Sede principal</span>
                        </div>
                        <div class="section-body">
                            <p class="section-note">Opcional. Podrás añadir más sedes desde la ficha del proveedor.</p>
                            <div class="form-group">
                                <label class="form-label">Dirección</label>
                                <input v-model="form.main_branch.address" type="text" class="form-input" placeholder="Ej: Cra. 7 # 45-12" />
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Ciudad</label>
                                    <input v-model="form.main_branch.city" type="text" class="form-input" placeholder="Ej: Bogotá" />
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Departamento</label>
                                    <input v-model="form.main_branch.department" type="text" class="form-input" placeholder="Ej: Cundinamarca" />
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Teléfono fijo</label>
                                    <input v-model="form.main_branch.phone" type="text" class="form-input" placeholder="Ej: 601 123 4567" />
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Correo de la sede</label>
                                    <input v-model="form.main_branch.email" type="email" class="form-input"
                                        :class="{ 'has-error': errors['main_branch.email'] }" placeholder="ventas@proveedor.com" />
                                    <span v-if="errors['main_branch.email']" class="error-text">{{ errors['main_branch.email'][0] }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Columna derecha -->
                <div class="form-section">
                    <div v-if="!isEdit" class="section-card">
                        <div class="section-header">
                            <i class="fa fa-user-tie section-icon"></i>
                            <span>Asesor principal</span>
                        </div>
                        <div class="section-body">
                            <p class="section-note">Opcional. Es a quien apuntan los botones de llamar, WhatsApp y correo.</p>
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Nombre</label>
                                    <input v-model="form.primary_contact.name" type="text" class="form-input" placeholder="Ej: Laura Rincón" />
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Cargo</label>
                                    <input v-model="form.primary_contact.position" type="text" class="form-input" placeholder="Ej: Asesora comercial" />
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Celular / WhatsApp</label>
                                    <input v-model="form.primary_contact.mobile" type="text" class="form-input" placeholder="Ej: 310 123 4567" />
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Correo</label>
                                    <input v-model="form.primary_contact.email" type="email" class="form-input"
                                        :class="{ 'has-error': errors['primary_contact.email'] }" placeholder="asesor@proveedor.com" />
                                    <span v-if="errors['primary_contact.email']" class="error-text">{{ errors['primary_contact.email'][0] }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="section-card">
                        <div class="section-header">
                            <i class="fa fa-handshake section-icon"></i>
                            <span>Condiciones comerciales</span>
                        </div>
                        <div class="section-body">
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Forma de pago</label>
                                    <input v-model="form.payment_terms" type="text" class="form-input" placeholder="Ej: Crédito 30 días" />
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Días de crédito</label>
                                    <input v-model="form.credit_days" type="number" min="0" max="365" class="form-input" placeholder="0" />
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Moneda en que cotiza</label>
                                    <select v-model="form.default_currency" class="form-input">
                                        <option value="COP">COP · pesos</option>
                                        <option value="USD">USD · dólares</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Entrega habitual (días)</label>
                                    <input v-model="form.default_lead_time_days" type="number" min="0" max="365" class="form-input" placeholder="Ej: 3" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Valoración interna</label>
                                <div>
                                    <span class="rating-stars">
                                        <button v-for="n in 5" :key="n" type="button" class="rating-star" :class="{ on: n <= (form.rating || 0) }"
                                            :title="`${n} de 5`" @click="form.rating = n">
                                            <i class="fa fa-star"></i>
                                        </button>
                                    </span>
                                    <button v-if="form.rating" type="button" class="rating-clear" @click="form.rating = null">Quitar</button>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Notas</label>
                                <textarea v-model="form.notes" class="form-input form-textarea" rows="3"
                                    placeholder="Lo que conviene saber: cómo despacha, mínimos de compra, con quién hablar si hay urgencia..."></textarea>
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

                    <div v-if="generalError" class="error-alert">
                        <i class="fa fa-exclamation-circle"></i>
                        {{ generalError }}
                    </div>

                    <div class="form-actions">
                        <router-link :to="backRoute" class="btn-cancel">Cancelar</router-link>
                        <button type="submit" class="btn-submit" :disabled="loading">
                            <span v-if="loading" class="spinner"></span>
                            <i v-else class="fa fa-check"></i>
                            {{ loading ? 'Guardando...' : (isEdit ? 'Guardar cambios' : 'Guardar proveedor') }}
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</template>

<script>
import supplierService from '@/services/supplierService.js';
import api from '@/services/api.js';
import TagInput from '@/components/supplier/TagInput.vue';
import { SUPPLIER_TYPES, CATEGORY_SUGGESTIONS } from '@/services/supplierOptions.js';

// Crear y editar comparten formulario. Al crear se piden además la sede y el
// asesor principal, para dejar el proveedor utilizable en un solo paso.
export default {
    name: 'SupplierForm',
    components: { TagInput },

    data() {
        return {
            loading: false,
            loadingData: false,
            errors: {},
            generalError: null,
            types: SUPPLIER_TYPES,
            categorySuggestions: CATEGORY_SUGGESTIONS,
            brandSuggestions: [],
            loadedUpdatedAt: null,
            form: {
                business_name: '',
                trade_name: '',
                nit: '',
                supplier_type: 'distribuidor',
                website: '',
                categories: [],
                brands: [],
                payment_terms: '',
                credit_days: null,
                default_currency: 'COP',
                default_lead_time_days: null,
                rating: null,
                notes: '',
                active: true,
                main_branch: { address: '', city: '', department: '', phone: '', email: '' },
                primary_contact: { name: '', position: '', mobile: '', email: '' },
            },
        };
    },

    computed: {
        isEdit() {
            return !!this.$route.params.id;
        },
        backRoute() {
            return this.isEdit
                ? { name: 'suppliers.show', params: { id: this.$route.params.id } }
                : { name: 'suppliers.index' };
        },
    },

    async created() {
        this.loadBrands();
        if (this.isEdit) await this.loadSupplier();
    },

    methods: {
        async loadBrands() {
            try {
                const { data } = await api.get('/catalogs', { params: { scope: 'equipment', category: 'brand' } });
                this.brandSuggestions = data.map((c) => c.label);
            } catch { /* sin sugerencias */ }
        },

        async loadSupplier() {
            this.loadingData = true;
            try {
                const { data } = await supplierService.get(this.$route.params.id);
                Object.keys(this.form).forEach((key) => {
                    if (key in data && data[key] !== null) this.form[key] = data[key];
                });
                this.form.categories = data.categories || [];
                this.form.brands = data.brands || [];
                this.loadedUpdatedAt = data.updated_at;
            } catch {
                this.$router.replace({ name: 'suppliers.index' });
            } finally {
                this.loadingData = false;
            }
        },

        payload() {
            const { main_branch, primary_contact, ...supplier } = this.form;
            const clean = (v) => (v === '' ? null : v);
            const data = Object.fromEntries(Object.entries(supplier).map(([k, v]) => [k, clean(v)]));

            if (this.isEdit) {
                data.expected_updated_at = this.loadedUpdatedAt;
            } else {
                data.main_branch = main_branch;
                data.primary_contact = primary_contact;
            }
            return data;
        },

        async submit() {
            this.loading = true;
            this.errors = {};
            this.generalError = null;

            try {
                const { data } = this.isEdit
                    ? await supplierService.update(this.$route.params.id, this.payload())
                    : await supplierService.store(this.payload());
                this.$router.push({ name: 'suppliers.show', params: { id: data.id } });
            } catch (err) {
                const status = err.response?.status;
                if (status === 422) {
                    this.errors = err.response.data.errors;
                    this.generalError = 'Revisa los campos marcados.';
                } else if (status === 409) {
                    await this.onStale(err.response.data);
                } else {
                    this.generalError = err.response?.data?.message || 'No se pudo guardar el proveedor.';
                }
            } finally {
                this.loading = false;
            }
        },

        // Otro master guardó este proveedor mientras se editaba aquí.
        async onStale(info) {
            const res = await this.$swal.fire({
                icon: 'warning',
                title: 'Alguien más lo modificó',
                text: info.message,
                showCancelButton: true,
                confirmButtonText: 'Recargar sus cambios',
                cancelButtonText: 'Seguir editando',
                confirmButtonColor: '#279208',
            });
            if (res.isConfirmed) await this.loadSupplier();
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

.field-hint {
    font-size: 0.78rem;
    color: #64748b;
    margin: -0.25rem 0 0.6rem;
    line-height: 1.4;
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
.loading-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 4rem 2rem;
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border-radius: 20px;
}

select.form-input { cursor: pointer; }

.section-note {
    margin: -0.5rem 0 1rem;
    font-size: 0.8rem;
    color: #64748b;
    line-height: 1.45;
}

.rating-stars {
    display: inline-flex;
    gap: 0.15rem;
}

.rating-star {
    min-height: 0;
    padding: 0.1rem;
    border: none;
    background: none;
    font-size: 1.3rem;
    color: #cbd5e1;
    cursor: pointer;
    transition: color 0.15s;
}

.rating-star.on { color: #f59e0b; }
.rating-clear {
    min-height: 0;
    margin-left: 0.6rem;
    padding: 0;
    border: none;
    background: none;
    font-size: 0.78rem;
    color: #94a3b8;
    cursor: pointer;
}
</style>

