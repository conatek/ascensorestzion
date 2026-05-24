<template>
    <div class="video-capture">
        <!-- Boton agregar video -->
        <button v-if="!videoUrl" type="button" class="video-btn" @click="openFileInput">
            <i class="fa fa-video"></i>
            <span>Agregar video</span>
        </button>

        <!-- Preview -->
        <div v-else class="video-preview">
            <video :src="videoUrl" controls class="video-player"></video>
            <div class="video-meta">
                <span class="video-size">{{ fileSizeLabel }}</span>
                <button type="button" class="video-remove" @click="removeVideo">
                    <i class="fa fa-trash-alt"></i> Eliminar
                </button>
            </div>
        </div>

        <!-- Error -->
        <div v-if="error" class="video-error">
            <i class="fa fa-exclamation-circle"></i> {{ error }}
        </div>

        <!-- Input oculto -->
        <input
            ref="fileInput"
            type="file"
            accept="video/*"
            capture="environment"
            class="video-input-hidden"
            @change="onFileSelected"
        />
    </div>
</template>

<script>
export default {
    name: 'VideoCapture',

    props: {
        modelValue: { type: [File, Blob, null], default: null },
        maxSizeMb: { type: Number, default: 50 },
        maxDurationSec: { type: Number, default: 60 },
    },

    emits: ['update:modelValue'],

    data() {
        return {
            videoUrl: null,
            fileSize: 0,
            error: null,
        };
    },

    computed: {
        fileSizeLabel() {
            if (this.fileSize < 1024 * 1024) {
                return (this.fileSize / 1024).toFixed(0) + ' KB';
            }
            return (this.fileSize / (1024 * 1024)).toFixed(1) + ' MB';
        },
    },

    methods: {
        openFileInput() {
            this.error = null;
            this.$refs.fileInput.value = '';
            this.$refs.fileInput.click();
        },

        onFileSelected(event) {
            const file = event.target.files[0];
            if (!file) return;

            // Validar tamano
            const maxBytes = this.maxSizeMb * 1024 * 1024;
            if (file.size > maxBytes) {
                this.error = `El video excede el límite de ${this.maxSizeMb} MB (${(file.size / (1024 * 1024)).toFixed(1)} MB).`;
                return;
            }

            // Validar duracion via video element
            const url = URL.createObjectURL(file);
            const video = document.createElement('video');
            video.preload = 'metadata';

            video.onloadedmetadata = () => {
                URL.revokeObjectURL(video.src);
                if (video.duration > this.maxDurationSec) {
                    this.error = `El video excede el límite de ${this.maxDurationSec} segundos (${Math.round(video.duration)}s).`;
                    return;
                }

                this.error = null;
                this.videoUrl = URL.createObjectURL(file);
                this.fileSize = file.size;
                this.$emit('update:modelValue', file);
            };

            video.onerror = () => {
                URL.revokeObjectURL(url);
                this.error = 'No se pudo leer el video. Intente con otro archivo.';
            };

            video.src = url;
        },

        removeVideo() {
            if (this.videoUrl) {
                URL.revokeObjectURL(this.videoUrl);
            }
            this.videoUrl = null;
            this.fileSize = 0;
            this.error = null;
            this.$emit('update:modelValue', null);
        },
    },

    beforeUnmount() {
        if (this.videoUrl) {
            URL.revokeObjectURL(this.videoUrl);
        }
    },
};
</script>

<style scoped>
.video-capture {
    width: 100%;
}

.video-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    width: 100%;
    padding: 0.75rem;
    background: #f8fafc;
    border: 2px dashed #cbd5e1;
    border-radius: 12px;
    color: #64748b;
    font-size: 0.9rem;
    font-weight: 500;
    cursor: pointer;
    min-height: 48px;
    transition: all 0.2s;
}

.video-btn:active {
    background: #f1f5f9;
    border-color: #94a3b8;
}

.video-btn i {
    font-size: 1.1rem;
}

.video-input-hidden {
    position: absolute;
    width: 0;
    height: 0;
    opacity: 0;
    pointer-events: none;
}

/* Preview */
.video-preview {
    border-radius: 12px;
    overflow: hidden;
    border: 1px solid #e2e8f0;
}

.video-player {
    width: 100%;
    max-height: 200px;
    display: block;
    background: #000;
}

.video-meta {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.5rem 0.75rem;
    background: #f8fafc;
}

.video-size {
    font-size: 0.8rem;
    color: #64748b;
    font-weight: 500;
}

.video-remove {
    background: none;
    border: none;
    color: #ba2831;
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
    padding: 0.25rem 0.5rem;
    border-radius: 6px;
    display: flex;
    align-items: center;
    gap: 0.3rem;
}

.video-remove:active {
    background: #fef2f2;
}

/* Error */
.video-error {
    margin-top: 0.5rem;
    padding: 0.5rem 0.75rem;
    background: #fef2f2;
    border-radius: 8px;
    color: #ba2831;
    font-size: 0.8rem;
    font-weight: 500;
}
</style>
