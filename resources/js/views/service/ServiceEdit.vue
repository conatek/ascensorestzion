<template>
    <div>
        <!-- Loading state -->
        <div v-if="loading" class="loading-container">
            <div class="spinner"></div>
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
                            Editar Servicio
                            <div class="page-title-subheading text-muted">
                                {{ form.name }}
                            </div>
                        </div>
                    </div>
                    <div class="page-title-actions">
                        <router-link :to="{ name: 'companies.show', params: { id: $route.params.companyId } }" class="btn-action btn-back">
                            <i class="fa fa-arrow-left me-1"></i> Volver
                        </router-link>
                    </div>
                </div>
            </div>

            <form @submit.prevent="submit">
                <div class="form-grid">
                    <!-- Columna izquierda: Informacion del servicio -->
                    <div class="form-section">
                        <div class="section-card">
                            <div class="section-header">
                                <i class="fa fa-concierge-bell section-icon"></i>
                                <span>Informacion del servicio</span>
                            </div>
                            <div class="section-body">
                                <div class="form-group">
                                    <label class="form-label">Nombre <span class="required">*</span></label>
                                    <input v-model="form.name" type="text" class="form-input" :class="{ 'has-error': errors.name }" />
                                    <span v-if="errors.name" class="error-text">{{ errors.name[0] }}</span>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Descripcion</label>
                                    <textarea v-model="form.description" class="form-input" rows="4"
                                        placeholder="Descripcion del servicio..."></textarea>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Imagen del servicio</label>
                                    <div class="file-upload">
                                        <input ref="fileInput" type="file" class="file-input" accept="image/*" @change="onFileSelected" />
                                        <div class="file-upload-content">
                                            <i class="fa fa-cloud-upload-alt"></i>
                                            <span>Seleccionar imagen</span>
                                        </div>
                                    </div>
                                    <div v-if="imagePreview || form.image_path" class="image-preview">
                                        <img :src="imagePreview || form.image_path" class="preview-image" />
                                        <button type="button" class="btn-crop" @click="openCropper">
                                            <i class="fa fa-crop"></i> Recortar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Columna derecha: Opciones -->
                    <div class="form-section">
                        <div class="section-card">
                            <div class="section-header">
                                <i class="fa fa-cog section-icon icon-orange"></i>
                                <span>Opciones</span>
                            </div>
                            <div class="section-body">
                                <div class="form-row">
                                    <div class="form-group">
                                        <label class="form-label">Orden</label>
                                        <input v-model="form.order" type="number" min="0" class="form-input" placeholder="0" />
                                        <span class="help-text">Menor = aparece primero</span>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">&nbsp;</label>
                                        <label class="toggle-switch">
                                            <input type="checkbox" v-model="form.is_active" />
                                            <span class="toggle-slider"></span>
                                            <span class="toggle-label">Servicio activo</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Success alert -->
                        <div v-if="success" class="success-alert">
                            <i class="fa fa-check-circle"></i>
                            Servicio actualizado correctamente.
                        </div>

                        <!-- Error general y botones -->
                        <div v-if="generalError" class="error-alert">
                            <i class="fa fa-exclamation-circle"></i>
                            {{ generalError }}
                        </div>

                        <div class="form-actions">
                            <router-link :to="{ name: 'companies.show', params: { id: $route.params.companyId } }" class="btn-cancel">
                                Cancelar
                            </router-link>
                            <button type="submit" class="btn-submit" :disabled="saving">
                                <span v-if="saving" class="spinner"></span>
                                <i v-else class="fa fa-check"></i>
                                {{ saving ? 'Guardando...' : 'Guardar cambios' }}
                            </button>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Modal de recorte -->
            <Teleport to="body">
                <div v-if="cropperOpen" class="cropper-modal-overlay" @click.self="cancelCrop">
                    <div class="cropper-modal-container">
                        <div class="cropper-modal-header">
                            <h4>Recortar imagen del servicio</h4>
                            <button type="button" class="cropper-modal-close" @click="cancelCrop">
                                <i class="fa fa-times"></i>
                            </button>
                        </div>

                        <div class="cropper-modal-ratios">
                            <button
                                v-for="r in ratios"
                                :key="r.label"
                                type="button"
                                :class="['cropper-modal-ratio-btn', { active: selectedRatio === r.value }]"
                                @click="selectedRatio = r.value"
                            >
                                {{ r.label }}
                            </button>
                        </div>

                        <div class="cropper-modal-canvas">
                            <Cropper
                                ref="cropper"
                                :src="cropperSrc"
                                :stencil-props="{ aspectRatio: selectedRatio }"
                                class="cropper"
                            />
                        </div>

                        <div class="cropper-modal-actions">
                            <button type="button" class="btn-cancel" @click="cancelCrop">Cancelar</button>
                            <button type="button" class="btn-submit" @click="confirmCrop">
                                <i class="fa fa-check"></i> Aplicar recorte
                            </button>
                        </div>
                    </div>
                </div>
            </Teleport>
        </template>
    </div>
</template>

<script>
import { Cropper } from 'vue-advanced-cropper';
import 'vue-advanced-cropper/dist/style.css';
import serviceService from '@/services/serviceService.js';

export default {
    name: 'ServiceEdit',

    components: { Cropper },

    data() {
        return {
            loading: true,
            saving: false,
            errors: {},
            generalError: null,
            success: false,
            imagePreview: null,
            imageFile: null,
            cropperOpen: false,
            cropperSrc: null,
            selectedRatio: 1,
            ratios: [
                { label: '1:1', value: 1 },
                { label: '2:1', value: 2 },
                { label: '3:1', value: 3 },
            ],
            form: {},
        };
    },

    async created() {
        const { data } = await serviceService.get(
            this.$route.params.companyId,
            this.$route.params.serviceId
        );
        this.form = data;
        this.loading = false;
    },

    methods: {
        onFileSelected(e) {
            const file = e.target.files[0];
            if (!file) return;
            this.cropperSrc = URL.createObjectURL(file);
            this.cropperOpen = true;
        },

        openCropper() {
            this.cropperSrc = this.imagePreview || this.form.image_path;
            this.cropperOpen = true;
        },

        confirmCrop() {
            const { canvas } = this.$refs.cropper.getResult();
            canvas.toBlob((blob) => {
                this.imageFile = new File([blob], 'image.png', { type: 'image/png' });
                if (this.imagePreview) URL.revokeObjectURL(this.imagePreview);
                this.imagePreview = URL.createObjectURL(blob);
                this.cropperOpen = false;
            }, 'image/png');
        },

        cancelCrop() {
            this.cropperOpen = false;
            if (!this.imageFile && !this.form.image_path) {
                this.cropperSrc = null;
                this.$refs.fileInput.value = '';
            }
        },

        async submit() {
            this.saving = true;
            this.errors = {};
            this.generalError = null;
            this.success = false;

            const payload = new FormData();
            const fields = ['name', 'description', 'order', 'is_active'];
            fields.forEach(k => {
                if (this.form[k] != null) payload.append(k, this.form[k]);
            });
            if (this.imageFile) payload.append('image', this.imageFile);

            try {
                await serviceService.update(
                    this.$route.params.companyId,
                    this.$route.params.serviceId,
                    payload
                );
                this.success = true;
                window.scrollTo(0, 0);
            } catch (err) {
                if (err.response?.status === 422) {
                    this.errors = err.response.data.errors;
                } else {
                    this.generalError = err.response?.data?.message || 'Error al guardar.';
                }
            } finally {
                this.saving = false;
            }
        },
    },
};
</script>

<style scoped>
/* Loading */
.loading-container {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 4rem 0;
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
    background: #f3e8ff;
    color: #7c3aed;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.9rem;
}

.section-icon.icon-orange {
    background: #fef3c7;
    color: #d97706;
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
    color: #ef4444;
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
    border-color: #7c3aed;
    box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.1);
}

.form-input.has-error {
    border-color: #ef4444;
}

.form-input::placeholder {
    color: #94a3b8;
}

textarea.form-input {
    resize: vertical;
    min-height: 100px;
}

.help-text {
    display: block;
    font-size: 0.8rem;
    color: #64748b;
    margin-top: 0.375rem;
}

.error-text {
    display: block;
    font-size: 0.8rem;
    color: #ef4444;
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

/* File Upload */
.file-upload {
    position: relative;
}

.file-input {
    position: absolute;
    inset: 0;
    opacity: 0;
    cursor: pointer;
}

.file-upload-content {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 1rem;
    border: 2px dashed #e2e8f0;
    border-radius: 8px;
    color: #64748b;
    transition: all 0.2s;
}

.file-upload:hover .file-upload-content {
    border-color: #7c3aed;
    color: #7c3aed;
    background: #faf5ff;
}

.image-preview {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-top: 1rem;
    padding: 0.75rem;
    background: #f8fafc;
    border-radius: 8px;
}

.preview-image {
    height: 80px;
    width: auto;
    max-width: 160px;
    object-fit: cover;
    border-radius: 6px;
}

.btn-crop {
    padding: 0.375rem 0.75rem;
    font-size: 0.8rem;
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    color: #475569;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-crop:hover {
    background: #f1f5f9;
}

/* Toggle Switch */
.toggle-switch {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    cursor: pointer;
}

.toggle-switch input {
    display: none;
}

.toggle-slider {
    width: 44px;
    height: 24px;
    background: #e2e8f0;
    border-radius: 12px;
    position: relative;
    transition: all 0.2s;
}

.toggle-slider::before {
    content: '';
    position: absolute;
    width: 18px;
    height: 18px;
    background: white;
    border-radius: 50%;
    top: 3px;
    left: 3px;
    transition: all 0.2s;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
}

.toggle-switch input:checked + .toggle-slider {
    background: #7c3aed;
}

.toggle-switch input:checked + .toggle-slider::before {
    transform: translateX(20px);
}

.toggle-label {
    font-size: 0.9rem;
    color: #475569;
}

/* Success Alert */
.success-alert {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem;
    background: #ecfdf5;
    border: 1px solid #a7f3d0;
    border-radius: 8px;
    color: #059669;
    font-size: 0.9rem;
    margin-bottom: 1.5rem;
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
    background: #7c3aed;
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-submit:hover:not(:disabled) {
    background: #6d28d9;
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

<!-- Estilos globales para el modal teleportado -->
<style>
.cropper-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.6);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 99999;
    padding: 1rem;
}

.cropper-modal-container {
    background: white;
    border-radius: 16px;
    padding: 1.5rem;
    width: min(640px, 95vw);
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
}

.cropper-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.25rem;
}

.cropper-modal-header h4 {
    font-size: 1.1rem;
    font-weight: 600;
    color: #1e293b;
    margin: 0;
}

.cropper-modal-close {
    width: 32px;
    height: 32px;
    background: #f1f5f9;
    border: none;
    border-radius: 8px;
    color: #64748b;
    cursor: pointer;
    transition: all 0.2s;
}

.cropper-modal-close:hover {
    background: #e2e8f0;
    color: #475569;
}

.cropper-modal-ratios {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.cropper-modal-ratio-btn {
    padding: 0.375rem 0.875rem;
    font-size: 0.85rem;
    font-weight: 500;
    background: #f1f5f9;
    color: #475569;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s;
}

.cropper-modal-ratio-btn:hover {
    background: #e2e8f0;
}

.cropper-modal-ratio-btn.active {
    background: #7c3aed;
    color: white;
    border-color: #7c3aed;
}

.cropper-modal-canvas {
    height: 350px;
    background: #1a1a1a;
    border-radius: 8px;
    overflow: hidden;
}

.cropper-modal-canvas .cropper {
    height: 100%;
}

.cropper-modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
    margin-top: 1.25rem;
}

.cropper-modal-actions .btn-cancel {
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

.cropper-modal-actions .btn-cancel:hover {
    background: #e2e8f0;
    color: #334155;
}

.cropper-modal-actions .btn-submit {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.625rem 1.25rem;
    font-size: 0.9rem;
    font-weight: 500;
    background: #7c3aed;
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s;
}

.cropper-modal-actions .btn-submit:hover {
    background: #6d28d9;
}
</style>
