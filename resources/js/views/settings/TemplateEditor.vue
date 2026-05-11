<template>
    <div class="template-editor">
        <!-- Header -->
        <div class="app-page-title">
            <div class="page-title-wrapper">
                <div class="page-title-heading">
                    <div class="page-title-icon">
                        <i class="fa fa-palette icon-gradient bg-mean-fruit"></i>
                    </div>
                    <div>
                        Editor de Plantilla
                        <div class="page-title-subheading text-muted">
                            {{ company?.name }}
                        </div>
                    </div>
                </div>
                <div class="page-title-actions d-flex gap-2">
                    <router-link
                        :to="{ name: 'companies.show', params: { id: $route.params.companyId }, query: $route.query.from === 'admin' ? { from: 'admin' } : {} }"
                        class="btn-editor btn-editor-back"
                    >
                        <i class="fa fa-arrow-left me-1"></i> Volver
                    </router-link>
                    <button
                        class="btn-editor btn-editor-reset"
                        @click="resetToDefaults"
                        :disabled="saving"
                    >
                        <i class="fa fa-undo me-1"></i> Restablecer
                    </button>
                    <button
                        class="btn-editor btn-editor-save"
                        @click="saveSettings"
                        :disabled="saving || !hasChanges"
                    >
                        <span v-if="saving" class="spinner-border spinner-border-sm me-1"></span>
                        <i v-else class="fa fa-save me-1"></i>
                        {{ saving ? 'Guardando...' : 'Guardar' }}
                    </button>
                </div>
            </div>
        </div>

        <div class="editor-container" v-if="!loading">
            <!-- Panel de Controles (Izquierda) -->
            <div class="controls-panel">
                <!-- Selector de Plantilla -->
                <div class="template-selector mb-4">
                    <label class="form-label fw-bold">Plantilla Base</label>
                    <select
                        class="form-select"
                        v-model="selectedTemplate"
                        @change="onTemplateChange"
                    >
                        <option
                            v-for="(tmpl, key) in templates"
                            :key="key"
                            :value="key"
                        >
                            {{ tmpl.name }}
                        </option>
                    </select>
                    <small class="text-muted" v-if="templates[selectedTemplate]">
                        {{ templates[selectedTemplate].description }}
                    </small>
                </div>

                <!-- Acordeón de Secciones -->
                <div class="accordion" id="settingsAccordion">
                    <div
                        class="accordion-item"
                        v-for="(section, sectionKey) in schema"
                        :key="sectionKey"
                    >
                        <h2 class="accordion-header">
                            <button
                                class="accordion-button"
                                :class="{ collapsed: activeSection !== sectionKey }"
                                type="button"
                                @click="toggleSection(sectionKey)"
                            >
                                {{ section._label || sectionKey }}
                            </button>
                        </h2>
                        <div
                            class="accordion-collapse collapse"
                            :class="{ show: activeSection === sectionKey }"
                        >
                            <div class="accordion-body">
                                <div
                                    class="control-group mb-3"
                                    v-for="(field, fieldKey) in getFieldsForSection(section)"
                                    :key="fieldKey"
                                >
                                    <!-- Color Input -->
                                    <template v-if="field.type === 'color'">
                                        <label class="form-label">{{ field.label }}</label>
                                        <div class="input-group">
                                            <input
                                                type="color"
                                                class="form-control form-control-color"
                                                :value="getValue(sectionKey, fieldKey)"
                                                @input="setValue(sectionKey, fieldKey, $event.target.value)"
                                            >
                                            <input
                                                type="text"
                                                class="form-control"
                                                :value="getValue(sectionKey, fieldKey)"
                                                @input="setValue(sectionKey, fieldKey, $event.target.value)"
                                                placeholder="#000000"
                                            >
                                        </div>
                                    </template>

                                    <!-- Range/Slider Input -->
                                    <template v-else-if="field.type === 'range'">
                                        <label class="form-label d-flex justify-content-between">
                                            <span>{{ field.label }}</span>
                                            <span class="text-muted">
                                                {{ getValue(sectionKey, fieldKey) }}{{ field.unit || '' }}
                                            </span>
                                        </label>
                                        <input
                                            type="range"
                                            class="form-range"
                                            :min="field.min"
                                            :max="field.max"
                                            :step="field.step || 1"
                                            :value="getValue(sectionKey, fieldKey)"
                                            @input="setValue(sectionKey, fieldKey, parseFloat($event.target.value))"
                                        >
                                    </template>

                                    <!-- Select Input -->
                                    <template v-else-if="field.type === 'select'">
                                        <label class="form-label">{{ field.label }}</label>
                                        <select
                                            class="form-select"
                                            :value="getValue(sectionKey, fieldKey)"
                                            @change="setValue(sectionKey, fieldKey, $event.target.value)"
                                        >
                                            <option
                                                v-for="opt in field.options"
                                                :key="opt"
                                                :value="opt"
                                            >
                                                {{ opt }}
                                            </option>
                                        </select>
                                    </template>

                                    <!-- Toggle/Checkbox Input -->
                                    <template v-else-if="field.type === 'toggle'">
                                        <div class="form-check form-switch">
                                            <input
                                                type="checkbox"
                                                class="form-check-input"
                                                :id="`toggle-${sectionKey}-${fieldKey}`"
                                                :checked="getValue(sectionKey, fieldKey)"
                                                @change="setValue(sectionKey, fieldKey, $event.target.checked)"
                                            >
                                            <label
                                                class="form-check-label"
                                                :for="`toggle-${sectionKey}-${fieldKey}`"
                                            >
                                                {{ field.label }}
                                            </label>
                                        </div>
                                    </template>

                                    <!-- Number Input -->
                                    <template v-else-if="field.type === 'number'">
                                        <label class="form-label">{{ field.label }}</label>
                                        <input
                                            type="number"
                                            class="form-control"
                                            :value="getValue(sectionKey, fieldKey)"
                                            @input="setValue(sectionKey, fieldKey, parseFloat($event.target.value))"
                                        >
                                    </template>

                                    <!-- Text Input -->
                                    <template v-else-if="field.type === 'text'">
                                        <label class="form-label">{{ field.label }}</label>
                                        <input
                                            type="text"
                                            class="form-control"
                                            :value="getValue(sectionKey, fieldKey)"
                                            @input="setValue(sectionKey, fieldKey, $event.target.value)"
                                        >
                                    </template>

                                    <!-- Image Input -->
                                    <template v-else-if="field.type === 'image'">
                                        <label class="form-label">{{ field.label }}</label>
                                        <div class="image-upload-control">
                                            <div v-if="getValue(sectionKey, fieldKey)" class="image-preview-thumb">
                                                <img :src="getValue(sectionKey, fieldKey)" />
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-outline-danger mt-1"
                                                    @click="setValue(sectionKey, fieldKey, '')"
                                                >
                                                    <i class="fa fa-trash me-1"></i> Quitar
                                                </button>
                                            </div>
                                            <div class="d-flex gap-2">
                                                <input
                                                    type="file"
                                                    class="form-control form-control-sm"
                                                    accept="image/*"
                                                    @change="onImageSelected($event, sectionKey, fieldKey, field.aspectRatio)"
                                                >
                                            </div>
                                            <small v-if="imageUploading" class="text-muted">
                                                <span class="spinner-border spinner-border-sm me-1"></span> Subiendo...
                                            </small>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Panel de Preview (Derecha) -->
            <div class="preview-panel">
                <div class="preview-header">
                    <span class="preview-title"><i class="fa fa-eye me-1"></i> Vista Previa</span>
                    <div class="viewport-toggle">
                        <button
                            class="viewport-btn"
                            :class="{ active: previewMode === 'mobile' }"
                            @click="previewMode = 'mobile'"
                        >
                            <i class="fa fa-mobile-alt me-1"></i> Móvil
                        </button>
                        <button
                            class="viewport-btn"
                            :class="{ active: previewMode === 'tablet' }"
                            @click="previewMode = 'tablet'"
                        >
                            <i class="fa fa-tablet-alt me-1"></i> Tablet
                        </button>
                    </div>
                </div>

                <div class="preview-with-selector">
                    <div
                        class="preview-frame"
                        :class="previewMode"
                        :style="cssVariables"
                    >
                        <LivePreview
                            :customization="customization"
                            :template-name="selectedTemplate"
                            :company="company"
                            :sample-card="sampleCard"
                        />
                    </div>

                    <!-- Selector de tarjetas -->
                    <div v-if="cards.length > 0" class="card-selector">
                        <div class="card-selector-title">
                            <i class="fa fa-id-card me-1"></i> Tarjetas
                        </div>
                        <div
                            v-for="(card, index) in cards"
                            :key="card.id"
                            class="card-selector-item"
                            :class="{ active: selectedCardIndex === index }"
                            @click="selectedCardIndex = index"
                        >
                            <img
                                v-if="card.photo_path"
                                :src="card.photo_path"
                                :alt="card.first_name"
                                class="card-selector-photo"
                            />
                            <div v-else class="card-selector-photo card-selector-placeholder">
                                <i class="fa fa-user"></i>
                            </div>
                            <div class="card-selector-info">
                                <span class="card-selector-name">{{ card.first_name }} {{ card.last_name }}</span>
                                <span class="card-selector-job">{{ card.job_title }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Loading State -->
        <div v-else class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <p class="mt-3">Cargando configuración...</p>
        </div>

        <!-- Modal de recorte para imágenes de plantilla -->
        <Teleport to="body">
            <div v-if="cropperOpen" class="cropper-modal-overlay" @click.self="cancelCrop">
                <div class="cropper-modal-container">
                    <div class="cropper-modal-header">
                        <h4>Recortar imagen</h4>
                        <button type="button" class="cropper-modal-close" @click="cancelCrop">
                            <i class="fa fa-times"></i>
                        </button>
                    </div>

                    <div class="cropper-modal-canvas">
                        <Cropper
                            ref="cropper"
                            :src="cropperSrc"
                            :stencil-props="{ aspectRatio: cropperAspectRatio }"
                            class="cropper"
                        />
                    </div>

                    <div class="cropper-modal-actions">
                        <button type="button" class="btn-crop-cancel" @click="cancelCrop">Cancelar</button>
                        <button type="button" class="btn-crop-submit" @click="confirmCrop">
                            <i class="fa fa-check"></i> Aplicar recorte
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </div>
</template>

<script>
import Swal from 'sweetalert2'
import { Cropper } from 'vue-advanced-cropper'
import 'vue-advanced-cropper/dist/style.css'
import settingService from '../../services/settingService'
import companyService from '../../services/companyService'
import cardService from '../../services/cardService'
import LivePreview from '../../components/templates/LivePreview.vue'

export default {
    name: 'TemplateEditor',

    components: {
        LivePreview,
        Cropper,
    },

    data() {
        return {
            loading: true,
            saving: false,
            company: null,
            templates: {},
            selectedTemplate: 'modern',
            schema: {},
            customization: {},
            originalCustomization: {},
            activeSection: 'general',
            previewMode: 'mobile',
            cards: [],
            selectedCardIndex: null,
            defaultCard: {
                first_name: 'Juan',
                last_name: 'Pérez',
                job_title: 'Director Comercial',
                email: 'juan@empresa.com',
                mobile_phone: '+52 55 1234 5678',
                whatsapp: '5551234567',
                description: 'Profesional con más de 10 años de experiencia en ventas y desarrollo de negocios.',
                photo_path: null,
            },
            // Cropper
            cropperOpen: false,
            cropperSrc: null,
            cropperAspectRatio: 16 / 9,
            cropperTarget: null, // { sectionKey, fieldKey }
            imageUploading: false,
        }
    },

    computed: {
        companyId() {
            return this.$route.params.companyId
        },

        sampleCard() {
            if (this.selectedCardIndex !== null && this.cards[this.selectedCardIndex]) {
                return this.cards[this.selectedCardIndex]
            }
            return this.defaultCard
        },

        hasChanges() {
            return JSON.stringify(this.customization) !== JSON.stringify(this.originalCustomization)
        },

        /**
         * Genera CSS Variables a partir de la customización actual
         * Este es el corazón del Live Preview - cada cambio actualiza las variables
         */
        cssVariables() {
            const vars = {}

            for (const [sectionKey, section] of Object.entries(this.customization)) {
                for (const [fieldKey, value] of Object.entries(section)) {
                    const varName = `--${sectionKey}-${this.camelToKebab(fieldKey)}`

                    // Agregar unidad si está definida en el schema
                    const fieldSchema = this.schema[sectionKey]?.[fieldKey]
                    const numValue = parseFloat(value)

                    if (fieldSchema?.unit && !isNaN(numValue)) {
                        vars[varName] = `${numValue}${fieldSchema.unit}`
                    } else {
                        vars[varName] = value
                    }
                }
            }

            return vars
        },
    },

    async created() {
        await this.loadData()
    },

    methods: {
        async loadData() {
            this.loading = true
            try {
                // Cargar plantillas disponibles, settings, datos de empresa y tarjetas en paralelo
                const [templatesRes, settingsRes, companyRes, cardsRes] = await Promise.all([
                    settingService.getTemplates(),
                    settingService.getSettings(this.companyId),
                    companyService.get(this.companyId),
                    cardService.all(this.companyId),
                ])

                this.templates = templatesRes.data.templates
                this.company = companyRes.data

                // Últimas 3 tarjetas
                const allCards = cardsRes.data.data || cardsRes.data || []
                this.cards = allCards.slice(-3)
                if (this.cards.length > 0) {
                    this.selectedCardIndex = 0
                }

                // Configurar settings actuales
                const settings = settingsRes.data.settings
                this.selectedTemplate = settings.template_name
                this.schema = settingsRes.data.schema
                this.customization = this.extractValues(settingsRes.data.full_customization)
                this.originalCustomization = JSON.parse(JSON.stringify(this.customization))

            } catch (error) {
                console.error('Error cargando datos:', error)
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al cargar la configuración',
                })
            } finally {
                this.loading = false
            }
        },

        /**
         * Extrae solo los valores del schema (sin metadatos de tipo/label)
         */
        extractValues(fullCustomization) {
            const values = {}

            for (const [sectionKey, section] of Object.entries(fullCustomization)) {
                values[sectionKey] = {}

                for (const [fieldKey, field] of Object.entries(section)) {
                    if (fieldKey.startsWith('_')) continue

                    // Si es un objeto con 'value', extraer solo el valor
                    if (typeof field === 'object' && field !== null && 'value' in field) {
                        values[sectionKey][fieldKey] = field.value
                    } else {
                        values[sectionKey][fieldKey] = field
                    }
                }
            }

            return values
        },

        /**
         * Obtener campos de una sección (excluyendo metadatos)
         */
        getFieldsForSection(section) {
            const fields = {}
            for (const [key, value] of Object.entries(section)) {
                if (!key.startsWith('_') && typeof value === 'object') {
                    fields[key] = value
                }
            }
            return fields
        },

        getValue(sectionKey, fieldKey) {
            return this.customization[sectionKey]?.[fieldKey]
        },

        setValue(sectionKey, fieldKey, value) {
            if (!this.customization[sectionKey]) {
                this.customization[sectionKey] = {}
            }
            this.customization[sectionKey][fieldKey] = value
        },

        toggleSection(sectionKey) {
            this.activeSection = this.activeSection === sectionKey ? null : sectionKey
        },

        async onTemplateChange() {
            const result = await Swal.fire({
                title: 'Cambiar plantilla',
                text: 'Cambiar de plantilla restablecerá toda la personalización. ¿Continuar?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, cambiar',
                cancelButtonText: 'Cancelar',
            })

            if (!result.isConfirmed) {
                this.selectedTemplate = this.originalCustomization.template || 'modern'
                return
            }

            this.loading = true
            try {
                // Obtener schema de la nueva plantilla
                const schemaRes = await settingService.getSchema(this.selectedTemplate)
                this.schema = schemaRes.data.schema

                // Extraer valores por defecto
                this.customization = this.extractValues(schemaRes.data.schema)

            } catch (error) {
                console.error('Error cambiando plantilla:', error)
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al cambiar de plantilla',
                })
            } finally {
                this.loading = false
            }
        },

        async saveSettings() {
            this.saving = true
            try {
                await settingService.updateSettings(this.companyId, {
                    template_name: this.selectedTemplate,
                    customization: this.customization,
                })

                this.originalCustomization = JSON.parse(JSON.stringify(this.customization))
                Swal.fire({
                    icon: 'success',
                    title: 'Guardado',
                    text: 'Configuración guardada correctamente',
                    timer: 2000,
                    showConfirmButton: false,
                })

            } catch (error) {
                console.error('Error guardando:', error)
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al guardar la configuración',
                })
            } finally {
                this.saving = false
            }
        },

        async resetToDefaults() {
            const result = await Swal.fire({
                title: 'Restablecer configuración',
                text: '¿Restablecer toda la configuración a los valores por defecto?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, restablecer',
                cancelButtonText: 'Cancelar',
            })

            if (!result.isConfirmed) {
                return
            }

            this.saving = true
            try {
                const response = await settingService.resetSettings(this.companyId)
                this.customization = this.extractValues(response.data.full_customization)
                this.originalCustomization = JSON.parse(JSON.stringify(this.customization))
                Swal.fire({
                    icon: 'success',
                    title: 'Restablecido',
                    text: 'Configuración restablecida correctamente',
                    timer: 2000,
                    showConfirmButton: false,
                })

            } catch (error) {
                console.error('Error restableciendo:', error)
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al restablecer la configuración',
                })
            } finally {
                this.saving = false
            }
        },

        /**
         * Convierte camelCase a kebab-case para nombres de CSS variables
         */
        camelToKebab(str) {
            return str.replace(/([a-z])([A-Z])/g, '$1-$2').toLowerCase()
        },

        // --- Métodos de imagen con cropper ---

        onImageSelected(e, sectionKey, fieldKey, aspectRatio) {
            const file = e.target.files[0]
            if (!file) return
            this.cropperSrc = URL.createObjectURL(file)
            this.cropperAspectRatio = aspectRatio || 16 / 9
            this.cropperTarget = { sectionKey, fieldKey }
            this.cropperOpen = true
            e.target.value = ''
        },

        confirmCrop() {
            const { canvas } = this.$refs.cropper.getResult()
            canvas.toBlob(async (blob) => {
                this.cropperOpen = false
                this.imageUploading = true

                try {
                    const file = new File([blob], 'hero-bg.png', { type: 'image/png' })
                    const { data } = await settingService.uploadImage(this.companyId, file)
                    this.setValue(this.cropperTarget.sectionKey, this.cropperTarget.fieldKey, data.url)
                } catch (error) {
                    console.error('Error subiendo imagen:', error)
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error al subir la imagen',
                    })
                } finally {
                    this.imageUploading = false
                    this.cropperTarget = null
                    if (this.cropperSrc) {
                        URL.revokeObjectURL(this.cropperSrc)
                        this.cropperSrc = null
                    }
                }
            }, 'image/png')
        },

        cancelCrop() {
            this.cropperOpen = false
            if (this.cropperSrc) {
                URL.revokeObjectURL(this.cropperSrc)
                this.cropperSrc = null
            }
            this.cropperTarget = null
        },
    },
}
</script>

<style scoped>
.template-editor {
    max-width: 100%;
    overflow: hidden;
}

/* Preview + Selector layout */
.preview-with-selector {
    display: flex;
    gap: 1rem;
    flex: 1;
    min-height: 0;
    overflow: hidden;
}

/* Selector de tarjetas */
.card-selector {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    width: 170px;
    flex-shrink: 0;
}

.card-selector-title {
    font-size: 0.75rem;
    font-weight: 600;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    padding-bottom: 0.25rem;
    border-bottom: 1px solid #e2e8f0;
}

.card-selector-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.4rem 0.7rem;
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.2s ease;
    min-width: 0;
}

.card-selector-item:hover {
    border-color: #cbd5e1;
    background: #f8fafc;
}

.card-selector-item.active {
    border-color: #7c3aed;
    background: #f5f3ff;
    box-shadow: 0 2px 8px rgba(124, 58, 237, 0.15);
}

.card-selector-photo {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    object-fit: cover;
    flex-shrink: 0;
}

.card-selector-placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f1f5f9;
    color: #94a3b8;
    font-size: 0.9rem;
}

.card-selector-info {
    display: flex;
    flex-direction: column;
    min-width: 0;
}

.card-selector-name {
    font-size: 0.78rem;
    font-weight: 600;
    color: #1e293b;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.card-selector-job {
    font-size: 0.68rem;
    color: #64748b;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Header buttons */
.btn-editor {
    display: inline-flex;
    align-items: center;
    padding: 0.5rem 1rem;
    font-size: 0.85rem;
    font-weight: 600;
    border-radius: 10px;
    border: none;
    cursor: pointer;
    transition: all 0.2s ease;
    text-decoration: none;
}

.btn-editor-back {
    background: #f1f5f9;
    color: #475569;
    border: 1px solid #e2e8f0;
}

.btn-editor-back:hover {
    background: #e2e8f0;
    color: #1e293b;
}

.btn-editor-reset {
    background: #fef2f2;
    color: #dc2626;
    border: 1px solid #fecaca;
}

.btn-editor-reset:hover {
    background: #fee2e2;
    color: #b91c1c;
}

.btn-editor-reset:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.btn-editor-save {
    background: #7c3aed;
    color: white;
    box-shadow: 0 4px 12px rgba(124, 58, 237, 0.3);
}

.btn-editor-save:hover {
    background: #6d28d9;
    box-shadow: 0 6px 16px rgba(124, 58, 237, 0.4);
}

.btn-editor-save:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    box-shadow: none;
}

/* Preview header */
.preview-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.75rem;
}

.preview-title {
    font-weight: 600;
    font-size: 0.95rem;
    color: #1e293b;
}

/* Viewport toggle */
.viewport-toggle {
    display: flex;
    background: #f1f5f9;
    border-radius: 10px;
    padding: 3px;
    gap: 2px;
}

.viewport-btn {
    padding: 0.35rem 0.85rem;
    font-size: 0.8rem;
    font-weight: 500;
    border: none;
    border-radius: 8px;
    background: transparent;
    color: #64748b;
    cursor: pointer;
    transition: all 0.2s ease;
}

.viewport-btn:hover {
    color: #475569;
}

.viewport-btn.active {
    background: white;
    color: #7c3aed;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    font-weight: 600;
}

.editor-container {
    display: grid;
    grid-template-columns: 380px 1fr;
    gap: 2rem;
    height: calc(100vh - 180px);
}

.controls-panel {
    overflow-y: auto;
    padding-right: 1rem;
}

.preview-panel {
    display: flex;
    flex-direction: column;
    min-height: 0;
    overflow: hidden;
}

.preview-frame {
    flex: 1;
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    overflow-y: auto;
    display: flex;
    justify-content: center;
    align-items: flex-start;
    padding: 1rem;
    min-height: 0;
}

.preview-frame.mobile {
    max-width: 100%;
}

.preview-frame.mobile > :deep(*) {
    width: 375px;
    max-width: 100%;
    height: fit-content;
}

.preview-frame.tablet > :deep(*) {
    width: 768px;
    max-width: 100%;
    height: fit-content;
}

.accordion-button:not(.collapsed) {
    background-color: #f5f3ff;
    color: #1e293b;
    box-shadow: none;
    border-left: 3px solid #7c3aed;
}

.form-control-color {
    width: 50px;
    padding: 0.25rem;
}

.control-group {
    padding-bottom: 0.75rem;
    border-bottom: 1px solid #e2e8f0;
}

.control-group:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.template-selector select {
    font-weight: 500;
    border-color: #e2e8f0;
    border-radius: 10px;
}

.template-selector select:focus {
    border-color: #7c3aed;
    box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.15);
}

/* Image upload control */
.image-upload-control {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.image-preview-thumb {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
}

.image-preview-thumb img {
    width: 100%;
    max-height: 120px;
    object-fit: cover;
    border-radius: 6px;
    border: 1px solid #e2e8f0;
}

/* Cropper Modal */
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

.btn-crop-cancel {
    padding: 0.625rem 1.25rem;
    font-size: 0.9rem;
    font-weight: 500;
    background: #f1f5f9;
    color: #475569;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-crop-cancel:hover {
    background: #e2e8f0;
    color: #334155;
}

.btn-crop-submit {
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

.btn-crop-submit:hover {
    background: #6d28d9;
}

@media (max-width: 1200px) {
    .editor-container {
        grid-template-columns: 1fr;
        height: auto;
    }

    .controls-panel {
        max-height: 400px;
    }

    .preview-panel {
        order: -1;
    }
}
</style>
