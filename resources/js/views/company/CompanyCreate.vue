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
                        Nueva Empresa
                        <div class="page-title-subheading text-muted">
                            Crea una nueva empresa para gestionar sus tarjetas
                        </div>
                    </div>
                </div>
                <div class="page-title-actions">
                    <router-link :to="{ name: 'companies.index' }" class="btn-action btn-back">
                        <i class="fa fa-arrow-left me-1"></i> Volver
                    </router-link>
                </div>
            </div>
        </div>

        <form @submit.prevent="submit">
            <div class="form-grid">
                <!-- Columna izquierda: Info general -->
                <div class="form-section">
                    <div class="section-card">
                        <div class="section-header">
                            <i class="fa fa-building section-icon"></i>
                            <span>Informacion general</span>
                        </div>
                        <div class="section-body">
                            <div class="form-group">
                                <label class="form-label">Nombre de la empresa <span class="required">*</span></label>
                                <input v-model="form.name" type="text" class="form-input" :class="{ 'has-error': errors.name }"
                                    @input="autoSlug" placeholder="Ej: Mi Empresa S.A." />
                                <span v-if="errors.name" class="error-text">{{ errors.name[0] }}</span>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Slug (URL publica) <span class="required">*</span></label>
                                <div class="input-with-prefix">
                                    <span class="input-prefix">dominio.com/</span>
                                    <input v-model="form.slug" type="text" class="form-input" :class="{ 'has-error': errors.slug }"
                                        @input="form.slug = slugify(form.slug)" />
                                </div>
                                <span v-if="errors.slug" class="error-text">{{ errors.slug[0] }}</span>
                                <span class="help-text">Solo letras minusculas, numeros y guiones.</span>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Logotipo</label>
                                <div class="file-upload">
                                    <input ref="fileInput" type="file" class="file-input" accept="image/*" @change="onFileSelected" />
                                    <div class="file-upload-content">
                                        <i class="fa fa-cloud-upload-alt"></i>
                                        <span>Seleccionar imagen</span>
                                    </div>
                                </div>
                                <div v-if="logoPreview" class="logo-preview">
                                    <img :src="logoPreview" />
                                    <button type="button" class="btn-crop" @click="openCropper">
                                        <i class="fa fa-crop"></i> Recortar
                                    </button>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Direccion</label>
                                <input v-model="form.address" type="text" class="form-input" :class="{ 'has-error': errors.address }"
                                    placeholder="Ej: Av. Principal #123, Ciudad" />
                                <span v-if="errors.address" class="error-text">{{ errors.address[0] }}</span>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Sitio web</label>
                                    <input v-model="form.web" type="url" class="form-input" :class="{ 'has-error': errors.web }"
                                        placeholder="https://ejemplo.com" />
                                    <span v-if="errors.web" class="error-text">{{ errors.web[0] }}</span>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Google My Business</label>
                                    <input v-model="form.my_business" type="url" class="form-input" :class="{ 'has-error': errors.my_business }"
                                        placeholder="https://g.page/..." />
                                    <span v-if="errors.my_business" class="error-text">{{ errors.my_business[0] }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Columna derecha: Redes sociales -->
                <div class="form-section">
                    <div class="section-card">
                        <div class="section-header">
                            <i class="fa fa-share-alt section-icon"></i>
                            <span>Redes Sociales</span>
                        </div>
                        <div class="section-body">
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fab fa-facebook social-icon" style="color: #1877f2"></i> Facebook
                                </label>
                                <input v-model="form.facebook" type="url" class="form-input" :class="{ 'has-error': errors.facebook }"
                                    placeholder="https://facebook.com/mi-pagina" />
                                <span v-if="errors.facebook" class="error-text">{{ errors.facebook[0] }}</span>
                            </div>

                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fab fa-instagram social-icon" style="color: #E4405F"></i> Instagram
                                </label>
                                <input v-model="form.instagram" type="url" class="form-input" :class="{ 'has-error': errors.instagram }"
                                    placeholder="https://instagram.com/mi-cuenta" />
                                <span v-if="errors.instagram" class="error-text">{{ errors.instagram[0] }}</span>
                            </div>

                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fab fa-twitter social-icon" style="color: #1da1f2"></i> Twitter / X
                                </label>
                                <input v-model="form.twitter" type="url" class="form-input" :class="{ 'has-error': errors.twitter }"
                                    placeholder="https://twitter.com/mi-cuenta" />
                                <span v-if="errors.twitter" class="error-text">{{ errors.twitter[0] }}</span>
                            </div>

                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fab fa-youtube social-icon" style="color: #ff0000"></i> YouTube
                                </label>
                                <input v-model="form.youtube" type="url" class="form-input" :class="{ 'has-error': errors.youtube }"
                                    placeholder="https://youtube.com/@mi-canal" />
                                <span v-if="errors.youtube" class="error-text">{{ errors.youtube[0] }}</span>
                            </div>

                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fab fa-tiktok social-icon"></i> TikTok
                                </label>
                                <input v-model="form.tiktok" type="url" class="form-input" :class="{ 'has-error': errors.tiktok }"
                                    placeholder="https://tiktok.com/@mi-cuenta" />
                                <span v-if="errors.tiktok" class="error-text">{{ errors.tiktok[0] }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Error general y botones -->
                    <div v-if="generalError" class="error-alert">
                        <i class="fa fa-exclamation-circle"></i>
                        {{ generalError }}
                    </div>

                    <div class="form-actions">
                        <router-link :to="{ name: 'companies.index' }" class="btn-cancel">
                            Cancelar
                        </router-link>
                        <button type="submit" class="btn-submit" :disabled="loading">
                            <span v-if="loading" class="spinner"></span>
                            <i v-else class="fa fa-check"></i>
                            {{ loading ? 'Creando...' : 'Crear empresa' }}
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
                        <h4>Recortar logotipo</h4>
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
    </div>
</template>

<script>
import { Cropper } from 'vue-advanced-cropper';
import 'vue-advanced-cropper/dist/style.css';
import companyService from '@/services/companyService.js';

export default {
    name: 'CompanyCreate',

    components: { Cropper },

    data() {
        return {
            loading: false,
            errors: {},
            generalError: null,
            logoPreview: null,
            logoFile: null,
            slugManuallyEdited: false,
            form: {
                name: '',
                slug: '',
                address: '',
                web: '',
                my_business: '',
                facebook: '',
                instagram: '',
                twitter: '',
                youtube: '',
                tiktok: '',
            },
            cropperOpen: false,
            cropperSrc: null,
            selectedRatio: 1,
            ratios: [
                { label: '1:1', value: 1 },
                { label: '2:1', value: 2 },
                { label: '3:1', value: 3 },
                { label: '4:1', value: 4 },
            ],
        };
    },

    methods: {
        slugify(v) {
            return v.toLowerCase().replace(/\s+/g, '-').replace(/[^a-z0-9\-_]/g, '');
        },

        autoSlug() {
            if (!this.slugManuallyEdited) {
                this.form.slug = this.slugify(this.form.name);
            }
        },

        onFileSelected(e) {
            const file = e.target.files[0];
            if (!file) return;
            this.cropperSrc = URL.createObjectURL(file);
            this.cropperOpen = true;
        },

        openCropper() {
            this.cropperOpen = true;
        },

        confirmCrop() {
            const { canvas } = this.$refs.cropper.getResult();
            canvas.toBlob((blob) => {
                const filename = 'logo.png';
                this.logoFile = new File([blob], filename, { type: 'image/png' });
                if (this.logoPreview) URL.revokeObjectURL(this.logoPreview);
                this.logoPreview = URL.createObjectURL(blob);
                this.cropperOpen = false;
            }, 'image/png');
        },

        cancelCrop() {
            this.cropperOpen = false;
            if (!this.logoFile) {
                this.cropperSrc = null;
                this.$refs.fileInput.value = '';
            }
        },

        errorClass(field) {
            return this.errors[field] ? 'is-invalid' : '';
        },

        async submit() {
            this.loading = true;
            this.errors = {};
            this.generalError = null;

            const payload = new FormData();
            payload.append('name', this.form.name);
            payload.append('slug', this.form.slug);
            if (this.logoFile) payload.append('logo', this.logoFile, 'logo.png');

            if (this.form.address) payload.append('address', this.form.address);
            if (this.form.web) payload.append('web', this.form.web);
            if (this.form.my_business) payload.append('my_business', this.form.my_business);
            if (this.form.facebook) payload.append('facebook', this.form.facebook);
            if (this.form.instagram) payload.append('instagram', this.form.instagram);
            if (this.form.twitter) payload.append('twitter', this.form.twitter);
            if (this.form.youtube) payload.append('youtube', this.form.youtube);
            if (this.form.tiktok) payload.append('tiktok', this.form.tiktok);

            try {
                const { data } = await companyService.store(payload);
                this.$router.push({ name: 'companies.show', params: { id: data.id } });
            } catch (err) {
                if (err.response?.status === 422) {
                    this.errors = err.response.data.errors;
                } else {
                    this.generalError = err.response?.data?.message || 'Error al crear la empresa.';
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
    background: #f3e8ff;
    color: #7c3aed;
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
    color: #ef4444;
}

.social-icon {
    margin-right: 0.5rem;
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

.input-with-prefix {
    display: flex;
    align-items: stretch;
}

.input-prefix {
    padding: 0.625rem 0.875rem;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-right: none;
    border-radius: 8px 0 0 8px;
    color: #64748b;
    font-size: 0.875rem;
    display: flex;
    align-items: center;
}

.input-with-prefix .form-input {
    border-radius: 0 8px 8px 0;
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

.logo-preview {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-top: 1rem;
    padding: 0.75rem;
    background: #f8fafc;
    border-radius: 8px;
}

.logo-preview img {
    height: 64px;
    width: auto;
    max-width: 180px;
    object-fit: contain;
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
