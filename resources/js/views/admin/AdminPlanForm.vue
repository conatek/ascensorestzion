<template>
    <div>
        <!-- Loading (edit mode) -->
        <div v-if="loadingPlan" class="loading-state">
            <div class="spinner-border text-primary"></div>
            <p class="text-muted mt-3">Cargando plan...</p>
        </div>

        <template v-else>
            <!-- Header -->
            <div class="app-page-title">
                <div class="page-title-wrapper">
                    <div class="page-title-heading">
                        <div class="page-title-icon">
                            <i class="fa fa-layer-group icon-gradient bg-mean-fruit"></i>
                        </div>
                        <div>
                            {{ isEdit ? 'Editar Plan' : 'Nuevo Plan' }}
                            <div class="page-title-subheading text-muted" v-if="isEdit">
                                {{ form.display_name }}
                            </div>
                        </div>
                    </div>
                    <div class="page-title-actions">
                        <router-link :to="{ name: 'admin.plans' }" class="btn-action btn-back">
                            <i class="fa fa-arrow-left me-1"></i> Volver
                        </router-link>
                    </div>
                </div>
            </div>

            <!-- Error -->
            <div v-if="generalError" class="error-alert">
                <i class="fa fa-exclamation-circle"></i>
                {{ generalError }}
            </div>

            <form @submit.prevent="submit">
                <div class="form-grid">
                    <!-- Col izquierda: Info general -->
                    <div class="form-section">
                        <div class="section-card">
                            <div class="section-header">
                                <i class="fa fa-info-circle section-icon"></i>
                                <span>Informacion general</span>
                            </div>
                            <div class="section-body">
                                <div class="form-group">
                                    <label class="form-label">Nombre interno <span class="required">*</span></label>
                                    <input v-model="form.name" type="text" class="form-input"
                                           :class="{ 'has-error': errors.name }"
                                           placeholder="ej: basico, pro, enterprise" />
                                    <span v-if="errors.name" class="error-text">{{ errors.name[0] }}</span>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Nombre visible <span class="required">*</span></label>
                                    <input v-model="form.display_name" type="text" class="form-input"
                                           :class="{ 'has-error': errors.display_name }"
                                           placeholder="ej: Básico, Pro, Enterprise" />
                                    <span v-if="errors.display_name" class="error-text">{{ errors.display_name[0] }}</span>
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label class="form-label">Orden</label>
                                        <input v-model.number="form.sort_order" type="number" class="form-input" min="0" />
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Estado</label>
                                        <div class="toggle-wrapper">
                                            <label class="toggle-label">
                                                <input type="checkbox" v-model="form.is_active" class="toggle-input" />
                                                <span class="toggle-switch-inline"></span>
                                                <span>{{ form.is_active ? 'Activo' : 'Inactivo' }}</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Marca de agua NexosCard</label>
                                    <div class="toggle-wrapper">
                                        <label class="toggle-label">
                                            <input type="checkbox" v-model="form.show_watermark" class="toggle-input" />
                                            <span class="toggle-switch-inline"></span>
                                            <span>{{ form.show_watermark ? 'Visible' : 'Oculta' }}</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Col derecha: Precios y limites -->
                    <div class="form-section">
                        <div class="section-card">
                            <div class="section-header">
                                <i class="fa fa-dollar-sign section-icon"></i>
                                <span>Precios y limites</span>
                            </div>
                            <div class="section-body">
                                <div class="form-row">
                                    <div class="form-group">
                                        <label class="form-label">Precio mensual (COP) <span class="required">*</span></label>
                                        <input v-model.number="form.price_monthly" type="number" class="form-input"
                                               :class="{ 'has-error': errors.price_monthly }"
                                               min="0" step="100" />
                                        <span v-if="errors.price_monthly" class="error-text">{{ errors.price_monthly[0] }}</span>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Precio anual (COP) <span class="required">*</span></label>
                                        <input v-model.number="form.price_yearly" type="number" class="form-input"
                                               :class="{ 'has-error': errors.price_yearly }"
                                               min="0" step="100" />
                                        <span v-if="errors.price_yearly" class="error-text">{{ errors.price_yearly[0] }}</span>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Max. tarjetas <span class="required">*</span></label>
                                    <input v-model.number="form.max_cards" type="number" class="form-input"
                                           :class="{ 'has-error': errors.max_cards }"
                                           min="1" />
                                    <span v-if="errors.max_cards" class="error-text">{{ errors.max_cards[0] }}</span>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Max. productos</label>
                                    <div class="limit-input">
                                        <input v-model.number="form.max_products" type="number" class="form-input"
                                               :disabled="unlimitedProducts" min="1"
                                               :class="{ 'input-disabled': unlimitedProducts }" />
                                        <label class="check-label">
                                            <input type="checkbox" v-model="unlimitedProducts" @change="onUnlimitedProducts" />
                                            <span>Ilimitado</span>
                                        </label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Max. servicios</label>
                                    <div class="limit-input">
                                        <input v-model.number="form.max_services" type="number" class="form-input"
                                               :disabled="unlimitedServices" min="1"
                                               :class="{ 'input-disabled': unlimitedServices }" />
                                        <label class="check-label">
                                            <input type="checkbox" v-model="unlimitedServices" @change="onUnlimitedServices" />
                                            <span>Ilimitado</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="form-actions">
                            <router-link :to="{ name: 'admin.plans' }" class="btn-cancel">Cancelar</router-link>
                            <button type="submit" class="btn-submit" :disabled="saving">
                                <span v-if="saving" class="spinner"></span>
                                <i v-else class="fa fa-check me-1"></i>
                                {{ saving ? 'Guardando...' : (isEdit ? 'Guardar cambios' : 'Crear plan') }}
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </template>
    </div>
</template>

<script>
import adminService from '@/services/adminService.js';

export default {
    name: 'AdminPlanForm',

    data() {
        return {
            form: {
                name: '',
                display_name: '',
                price_monthly: 0,
                price_yearly: 0,
                max_cards: 1,
                max_products: 3,
                max_services: 3,
                available_templates: null,
                show_watermark: false,
                is_active: true,
                sort_order: 0,
            },
            unlimitedProducts: false,
            unlimitedServices: false,
            errors: {},
            generalError: null,
            saving: false,
            loadingPlan: false,
        };
    },

    computed: {
        isEdit() {
            return !!this.$route.params.id;
        },
    },

    async created() {
        if (this.isEdit) {
            await this.loadPlan();
        }
    },

    watch: {
        '$route.params.id': {
            async handler(newId) {
                if (newId) {
                    await this.loadPlan();
                } else {
                    this.resetForm();
                }
            },
        },
    },

    methods: {
        async loadPlan() {
            this.loadingPlan = true;
            try {
                const { data } = await adminService.getPlans();
                const planId = parseInt(this.$route.params.id);
                const plan = data.find(p => p.id === planId);
                if (plan) {
                    this.form = {
                        name: plan.name,
                        display_name: plan.display_name,
                        price_monthly: Number(plan.price_monthly),
                        price_yearly: Number(plan.price_yearly),
                        max_cards: plan.max_cards,
                        max_products: plan.max_products,
                        max_services: plan.max_services,
                        available_templates: plan.available_templates,
                        show_watermark: plan.show_watermark,
                        is_active: plan.is_active,
                        sort_order: plan.sort_order,
                    };
                    this.unlimitedProducts = plan.max_products === null;
                    this.unlimitedServices = plan.max_services === null;
                } else {
                    this.generalError = 'Plan no encontrado.';
                }
            } catch {
                this.generalError = 'Error al cargar el plan.';
            } finally {
                this.loadingPlan = false;
            }
        },

        resetForm() {
            this.form = {
                name: '',
                display_name: '',
                price_monthly: 0,
                price_yearly: 0,
                max_cards: 1,
                max_products: 3,
                max_services: 3,
                available_templates: null,
                show_watermark: false,
                is_active: true,
                sort_order: 0,
            };
            this.unlimitedProducts = false;
            this.unlimitedServices = false;
            this.errors = {};
            this.generalError = null;
        },

        onUnlimitedProducts() {
            if (this.unlimitedProducts) {
                this.form.max_products = null;
            } else {
                this.form.max_products = 3;
            }
        },

        onUnlimitedServices() {
            if (this.unlimitedServices) {
                this.form.max_services = null;
            } else {
                this.form.max_services = 3;
            }
        },

        async submit() {
            this.saving = true;
            this.errors = {};
            this.generalError = null;

            try {
                if (this.isEdit) {
                    await adminService.updatePlan(this.$route.params.id, this.form);
                } else {
                    await adminService.storePlan(this.form);
                }
                this.$router.push({ name: 'admin.plans' });
            } catch (err) {
                if (err.response?.status === 422) {
                    this.errors = err.response.data.errors || {};
                } else {
                    this.generalError = err.response?.data?.message || 'Error al guardar el plan.';
                }
            } finally {
                this.saving = false;
            }
        },
    },
};
</script>

<style scoped>
.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
    align-items: start;
}

.section-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    border: 1px solid #e2e8f0;
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
    border-radius: 12px 12px 0 0;
}

.section-icon {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    background: #f3e8ff;
    color: #7c3aed;
}

.section-body { padding: 1.5rem; }

.form-group { margin-bottom: 1.25rem; }
.form-group:last-child { margin-bottom: 0; }

.form-label {
    display: block;
    font-size: 0.875rem;
    font-weight: 500;
    color: #374151;
    margin-bottom: 0.4rem;
}

.required { color: #ef4444; }

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
    border-color: #7c3aed;
    box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.1);
}

.form-input.has-error { border-color: #ef4444; }
.form-input.input-disabled { background: #f1f5f9; color: #94a3b8; }

.error-text {
    font-size: 0.8rem;
    color: #ef4444;
    margin-top: 0.25rem;
    display: block;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

/* Toggle */
.toggle-wrapper { margin-top: 0.25rem; }

.toggle-label {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
    font-size: 0.9rem;
    color: #475569;
}

.toggle-input { display: none; }

.toggle-switch-inline {
    width: 40px;
    height: 22px;
    background: #cbd5e1;
    border-radius: 11px;
    position: relative;
    transition: background 0.3s;
}

.toggle-switch-inline::after {
    content: '';
    width: 16px;
    height: 16px;
    background: white;
    border-radius: 50%;
    position: absolute;
    top: 3px;
    left: 3px;
    transition: transform 0.3s;
    box-shadow: 0 1px 2px rgba(0,0,0,0.2);
}

.toggle-input:checked + .toggle-switch-inline {
    background: linear-gradient(135deg, #8b5cf6, #7c3aed);
}

.toggle-input:checked + .toggle-switch-inline::after {
    transform: translateX(18px);
}

/* Limit input with checkbox */
.limit-input {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.limit-input .form-input { flex: 1; }

.check-label {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.85rem;
    color: #475569;
    white-space: nowrap;
    cursor: pointer;
}

.check-label input[type="checkbox"] {
    accent-color: #7c3aed;
}

/* Actions */
.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
}

.btn-cancel {
    padding: 0.625rem 1.25rem;
    background: #f1f5f9;
    color: #475569;
    border-radius: 8px;
    text-decoration: none;
    font-size: 0.95rem;
    font-weight: 500;
    transition: all 0.2s;
    border: 1px solid #e2e8f0;
}

.btn-cancel:hover { background: #e2e8f0; color: #334155; }

.btn-submit {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.625rem 1.5rem;
    background: linear-gradient(135deg, #7c3aed, #6d28d9);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 0.95rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    box-shadow: 0 2px 8px rgba(124, 58, 237, 0.25);
}

.btn-submit:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(124, 58, 237, 0.35);
}

.btn-submit:disabled { opacity: 0.7; cursor: not-allowed; }

.spinner {
    width: 16px; height: 16px;
    border: 2px solid rgba(255,255,255,0.3);
    border-top-color: white;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

@keyframes spin { to { transform: rotate(360deg); } }

/* Error alert */
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

/* Back button */
.btn-action.btn-back {
    padding: 0.5rem 1rem;
    background: #f1f5f9;
    color: #475569;
    border-radius: 8px;
    text-decoration: none;
    font-size: 0.9rem;
    font-weight: 500;
    transition: all 0.2s;
    border: 1px solid #e2e8f0;
}

.btn-action.btn-back:hover { background: #e2e8f0; }

.loading-state { text-align: center; padding: 4rem 0; }

@media (max-width: 768px) {
    .form-grid { grid-template-columns: 1fr; }
    .form-row { grid-template-columns: 1fr; }
}
</style>
