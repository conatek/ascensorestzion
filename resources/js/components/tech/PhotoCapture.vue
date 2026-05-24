<template>
    <div class="photo-capture">
        <!-- Boton de camara / preview -->
        <button v-if="!preview" type="button" class="capture-btn" @click="openFileInput">
            <i class="fa fa-camera"></i>
            <span v-if="label">{{ label }}</span>
        </button>

        <!-- Preview de foto tomada -->
        <div v-else class="capture-preview">
            <img :src="preview" alt="Foto" class="preview-img" />
            <button type="button" class="preview-remove" @click="removePhoto">
                <i class="fa fa-times"></i>
            </button>
        </div>

        <!-- Input file oculto -->
        <input
            ref="fileInput"
            type="file"
            accept="image/*"
            capture="environment"
            class="capture-input-hidden"
            @change="onFileSelected"
        />

        <!-- Modal cropper -->
        <Teleport to="body">
            <div v-if="showCropper" class="cropper-modal">
                <div class="cropper-modal__header">
                    <span>Recortar foto</span>
                    <button type="button" @click="cancelCrop"><i class="fa fa-times"></i></button>
                </div>
                <div class="cropper-modal__body">
                    <Cropper
                        ref="cropper"
                        :src="cropperSrc"
                        :stencil-props="{ aspectRatio: aspectRatio }"
                        class="cropper-component"
                    />
                </div>
                <div class="cropper-modal__footer">
                    <button type="button" class="crop-btn crop-btn--secondary" @click="cancelCrop">
                        Cancelar
                    </button>
                    <button type="button" class="crop-btn crop-btn--primary" @click="confirmCrop">
                        <i class="fa fa-check"></i> Aceptar
                    </button>
                </div>
            </div>
        </Teleport>
    </div>
</template>

<script>
import { Cropper } from 'vue-advanced-cropper';
import 'vue-advanced-cropper/dist/style.css';

export default {
    name: 'PhotoCapture',
    components: { Cropper },

    props: {
        modelValue: { type: [Blob, null], default: null },
        aspectRatio: { type: Number, default: 1 },
        maxSizeKb: { type: Number, default: 500 },
        label: { type: String, default: '' },
    },

    emits: ['update:modelValue'],

    data() {
        return {
            preview: null,
            showCropper: false,
            cropperSrc: null,
        };
    },

    watch: {
        modelValue(val) {
            if (!val && this.preview) {
                this.preview = null;
            }
        },
    },

    methods: {
        openFileInput() {
            this.$refs.fileInput.value = '';
            this.$refs.fileInput.click();
        },

        onFileSelected(event) {
            const file = event.target.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = (e) => {
                this.cropperSrc = e.target.result;
                this.showCropper = true;
            };
            reader.readAsDataURL(file);
        },

        confirmCrop() {
            const { canvas } = this.$refs.cropper.getResult();
            if (!canvas) return;

            canvas.toBlob(
                (blob) => {
                    this.preview = URL.createObjectURL(blob);
                    this.showCropper = false;
                    this.cropperSrc = null;
                    this.$emit('update:modelValue', blob);
                },
                'image/jpeg',
                0.8,
            );
        },

        cancelCrop() {
            this.showCropper = false;
            this.cropperSrc = null;
        },

        removePhoto() {
            if (this.preview) {
                URL.revokeObjectURL(this.preview);
            }
            this.preview = null;
            this.$emit('update:modelValue', null);
        },
    },

    beforeUnmount() {
        if (this.preview) {
            URL.revokeObjectURL(this.preview);
        }
    },
};
</script>

<style scoped>
.photo-capture {
    display: inline-block;
}

.capture-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.35rem;
    width: 44px;
    height: 44px;
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    color: #64748b;
    font-size: 1rem;
    cursor: pointer;
    transition: all 0.2s;
    flex-shrink: 0;
}

.capture-btn span {
    font-size: 0.75rem;
    display: none;
}

.capture-btn:active {
    background: #e2e8f0;
}

.capture-input-hidden {
    position: absolute;
    width: 0;
    height: 0;
    opacity: 0;
    pointer-events: none;
}

/* Preview */
.capture-preview {
    position: relative;
    width: 56px;
    height: 56px;
    border-radius: 10px;
    overflow: hidden;
    flex-shrink: 0;
}

.preview-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.preview-remove {
    position: absolute;
    top: 2px;
    right: 2px;
    width: 20px;
    height: 20px;
    background: rgba(186, 40, 49, 0.9);
    border: none;
    border-radius: 50%;
    color: white;
    font-size: 0.6rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Cropper modal */
.cropper-modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: white;
    z-index: 9999;
    display: flex;
    flex-direction: column;
}

.cropper-modal__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.75rem 1rem;
    border-bottom: 1px solid #e2e8f0;
    font-weight: 600;
    color: #1e293b;
    flex-shrink: 0;
}

.cropper-modal__header button {
    background: none;
    border: none;
    font-size: 1.1rem;
    color: #64748b;
    cursor: pointer;
    padding: 0.25rem;
}

.cropper-modal__body {
    flex: 1;
    overflow: hidden;
    background: #000;
}

.cropper-component {
    height: 100%;
    width: 100%;
}

.cropper-modal__footer {
    display: flex;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    border-top: 1px solid #e2e8f0;
    flex-shrink: 0;
}

.crop-btn {
    flex: 1;
    padding: 0.75rem;
    border: none;
    border-radius: 10px;
    font-size: 0.95rem;
    font-weight: 600;
    cursor: pointer;
    min-height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
}

.crop-btn--secondary {
    background: #f1f5f9;
    color: #475569;
}

.crop-btn--primary {
    background: linear-gradient(135deg, #30ab0a, #279208);
    color: white;
}
</style>
