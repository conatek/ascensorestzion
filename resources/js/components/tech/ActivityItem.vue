<template>
    <div class="activity-item" :class="{ 'is-answered': value !== null }">
        <!-- Header: toggle + label + camara -->
        <div class="activity-item__header">
            <div class="activity-toggle-wrap">
                <div class="activity-toggle" :class="toggleClass" @click="toggleValue">
                    <div class="activity-toggle__track">
                        <div class="activity-toggle__thumb"></div>
                    </div>
                </div>
            </div>
            <span class="activity-item__label" @click="toggleValue">{{ activity.label }}</span>
            <PhotoCapture :modelValue="photo" @update:modelValue="$emit('update:photo', $event)" />
        </div>

        <!-- Observacion (colapsable) -->
        <div class="activity-obs">
            <button type="button" class="obs-toggle" @click="obsExpanded = !obsExpanded">
                <i class="fa" :class="obsExpanded ? 'fa-chevron-up' : 'fa-plus'"></i>
                <span>{{ obsExpanded ? 'Observación' : 'Agregar observación' }}</span>
            </button>
            <div v-show="obsExpanded" class="obs-field">
                <textarea
                    :value="observation"
                    @input="$emit('update:observation', $event.target.value)"
                    placeholder="Escriba una observación..."
                    rows="2"
                    spellcheck="true"
                    lang="es"
                    class="obs-textarea"
                ></textarea>
            </div>
        </div>
    </div>
</template>

<script>
import PhotoCapture from './PhotoCapture.vue';

export default {
    name: 'ActivityItem',
    components: { PhotoCapture },

    props: {
        activity: { type: Object, required: true },
        value: { type: [Boolean, null], default: null },
        observation: { type: String, default: '' },
        photo: { type: [Blob, null], default: null },
    },

    emits: ['update:value', 'update:observation', 'update:photo'],

    data() {
        return {
            obsExpanded: false,
        };
    },

    computed: {
        toggleClass() {
            if (this.value === null) return 'is-unset';
            return this.value ? 'is-on' : 'is-off';
        },
    },

    watch: {
        observation(val) {
            if (val && !this.obsExpanded) {
                this.obsExpanded = true;
            }
        },
    },

    methods: {
        toggleValue() {
            if (this.value === null) {
                // Primer toque: activar (realizada)
                this.$emit('update:value', true);
            } else {
                // Toggle entre on/off
                this.$emit('update:value', !this.value);
            }
        },
    },
};
</script>

<style scoped>
.activity-item {
    background: white;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    padding: 0.85rem;
    transition: border-color 0.2s;
}

.activity-item.is-answered {
    border-color: #cbd5e1;
}

/* Header */
.activity-item__header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.activity-toggle-wrap {
    flex-shrink: 0;
}

/* Toggle switch custom */
.activity-toggle {
    width: 52px;
    height: 30px;
    cursor: pointer;
    position: relative;
}

.activity-toggle__track {
    width: 100%;
    height: 100%;
    border-radius: 15px;
    transition: background 0.2s;
    position: relative;
}

.activity-toggle__thumb {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: white;
    position: absolute;
    top: 3px;
    transition: left 0.2s;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
}

/* No tocado aun */
.activity-toggle.is-unset .activity-toggle__track {
    background: #e2e8f0;
    border: 2px dashed #cbd5e1;
}
.activity-toggle.is-unset .activity-toggle__thumb {
    left: 14px; /* centro */
    background: #cbd5e1;
}

/* Apagado (No realizada) */
.activity-toggle.is-off .activity-toggle__track {
    background: #fecaca;
}
.activity-toggle.is-off .activity-toggle__thumb {
    left: 3px;
    background: #ba2831;
}

/* Encendido (Realizada) */
.activity-toggle.is-on .activity-toggle__track {
    background: #bbf7d0;
}
.activity-toggle.is-on .activity-toggle__thumb {
    left: 25px;
    background: #30ab0a;
}

.activity-item__label {
    font-size: 0.85rem;
    font-weight: 500;
    color: #1e293b;
    flex: 1;
    line-height: 1.3;
    cursor: pointer;
}

/* Observacion */
.activity-obs {
    margin-top: 0.5rem;
}

.obs-toggle {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    background: none;
    border: none;
    color: #94a3b8;
    font-size: 0.75rem;
    font-weight: 500;
    cursor: pointer;
    padding: 0.25rem 0;
}

.obs-toggle i {
    font-size: 0.65rem;
}

.obs-field {
    margin-top: 0.35rem;
}

.obs-textarea {
    width: 100%;
    padding: 0.5rem 0.75rem;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-size: 0.85rem;
    color: #1e293b;
    resize: vertical;
    min-height: 60px;
    font-family: inherit;
    transition: border-color 0.2s;
}

.obs-textarea:focus {
    outline: none;
    border-color: #30ab0a;
}

.obs-textarea::placeholder {
    color: #cbd5e1;
}
</style>
