<template>
    <div class="condition-item" :class="{ 'is-answered': value !== null }">
        <!-- Header: label + camara -->
        <div class="condition-item__header">
            <span class="condition-item__label">{{ condition.label }}</span>
            <PhotoCapture :modelValue="photo" @update:modelValue="$emit('update:photo', $event)" />
        </div>

        <!-- Toggle 3 estados -->
        <div class="condition-toggle">
            <button
                type="button"
                class="toggle-btn toggle-btn--si"
                :class="{ 'is-selected': value === 'si' }"
                @click="selectValue('si')"
            >
                Sí
            </button>
            <button
                type="button"
                class="toggle-btn toggle-btn--no"
                :class="{ 'is-selected': value === 'no' }"
                @click="selectValue('no')"
            >
                No
            </button>
            <button
                type="button"
                class="toggle-btn toggle-btn--na"
                :class="{ 'is-selected': value === 'na' }"
                @click="selectValue('na')"
            >
                N/A
            </button>
        </div>

        <!-- Observacion (colapsable) -->
        <div class="condition-obs">
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
    name: 'ConditionItem',
    components: { PhotoCapture },

    props: {
        condition: { type: Object, required: true },
        value: { type: [String, null], default: null },
        observation: { type: String, default: '' },
        photo: { type: [Blob, null], default: null },
    },

    emits: ['update:value', 'update:observation', 'update:photo'],

    data() {
        return {
            obsExpanded: false,
        };
    },

    watch: {
        observation(val) {
            if (val && !this.obsExpanded) {
                this.obsExpanded = true;
            }
        },
    },

    methods: {
        selectValue(val) {
            this.$emit('update:value', val);
        },
    },
};
</script>

<style scoped>
.condition-item {
    background: white;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    padding: 0.85rem;
    transition: border-color 0.2s;
}

.condition-item.is-answered {
    border-color: #cbd5e1;
}

/* Header */
.condition-item__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.5rem;
    margin-bottom: 0.6rem;
}

.condition-item__label {
    font-size: 0.85rem;
    font-weight: 600;
    color: #1e293b;
    flex: 1;
    line-height: 1.3;
}

/* Toggle 3 estados */
.condition-toggle {
    display: flex;
    gap: 0;
    border-radius: 10px;
    overflow: hidden;
    border: 2px solid #e2e8f0;
}

.toggle-btn {
    flex: 1;
    padding: 0.6rem 0;
    background: #f8fafc;
    border: none;
    font-size: 0.85rem;
    font-weight: 600;
    color: #94a3b8;
    cursor: pointer;
    transition: all 0.15s;
    min-height: 44px;
    border-right: 1px solid #e2e8f0;
}

.toggle-btn:last-child {
    border-right: none;
}

.toggle-btn:active {
    transform: scale(0.97);
}

/* Seleccionado: Si = verde */
.toggle-btn--si.is-selected {
    background: #30ab0a;
    color: white;
}

/* Seleccionado: No = rojo */
.toggle-btn--no.is-selected {
    background: #ba2831;
    color: white;
}

/* Seleccionado: N/A = gris */
.toggle-btn--na.is-selected {
    background: #64748b;
    color: white;
}

/* Observacion */
.condition-obs {
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
