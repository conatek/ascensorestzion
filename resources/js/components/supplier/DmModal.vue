<template>
    <Teleport to="body">
        <div class="dm-overlay sp-overlay" @mousedown.self="$emit('close')">
            <div class="sp-box" :class="{ 'sp-box--wide': wide }" role="dialog" aria-modal="true">
                <div class="sp-header">
                    <h5>{{ title }}</h5>
                    <button type="button" class="sp-close" title="Cerrar" @click="$emit('close')">&times;</button>
                </div>
                <form class="sp-form" @submit.prevent="$emit('submit')">
                    <div class="sp-body">
                        <slot />
                        <div v-if="error" class="sp-error"><i class="fa fa-exclamation-circle"></i> {{ error }}</div>
                    </div>
                    <div class="sp-footer">
                        <button type="button" class="sp-btn sp-btn-cancel" @click="$emit('close')">Cancelar</button>
                        <button type="submit" class="sp-btn sp-btn-save" :disabled="saving">
                            <i class="fa" :class="saving ? 'fa-spinner fa-spin' : 'fa-check'"></i>
                            {{ saving ? 'Guardando...' : submitLabel }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </Teleport>
</template>

<script>
// Cascarón de los modales del módulo de proveedores. Va teleportado a <body>,
// así que sus estilos (y los de los campos que se le pasan por slot) NO llevan
// scope: usan el prefijo sp- para no chocar con Bootstrap ni con otras vistas.
export default {
    name: 'DmModal',

    props: {
        title: { type: String, required: true },
        submitLabel: { type: String, default: 'Guardar' },
        saving: { type: Boolean, default: false },
        error: { type: String, default: null },
        wide: { type: Boolean, default: false },
    },

    emits: ['close', 'submit'],

    mounted() {
        this.onKey = (e) => { if (e.key === 'Escape') this.$emit('close'); };
        document.addEventListener('keydown', this.onKey);
        document.body.style.overflow = 'hidden';
    },

    beforeUnmount() {
        document.removeEventListener('keydown', this.onKey);
        document.body.style.overflow = '';
    },
};
</script>

<style>
.sp-overlay {
    position: fixed;
    inset: 0;
    z-index: 10050;
    display: flex;
    align-items: flex-start;
    justify-content: center;
    padding: 4vh 1rem 1rem;
    background: rgba(15, 23, 42, 0.55);
    backdrop-filter: blur(3px);
    overflow-y: auto;
}

.sp-box {
    width: 100%;
    max-width: 560px;
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
}

.sp-box--wide { max-width: 760px; }

.sp-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.1rem 1.25rem;
    border-bottom: 1px solid #e2e8f0;
}

.sp-header h5 {
    margin: 0;
    font-size: 1.05rem;
    font-weight: 700;
    color: #1e293b;
}

.sp-close {
    width: 32px;
    height: 32px;
    min-height: 0;
    border: none;
    border-radius: 8px;
    background: #f1f5f9;
    color: #64748b;
    font-size: 1.3rem;
    line-height: 1;
    cursor: pointer;
}

.sp-close:hover { background: #e2e8f0; color: #1e293b; }

.sp-body { padding: 1.25rem; }

.sp-footer {
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
    padding: 1rem 1.25rem;
    border-top: 1px solid #e2e8f0;
    background: #f8fafc;
    border-radius: 0 0 16px 16px;
}

.sp-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.6rem 1.2rem;
    font-size: 0.9rem;
    font-weight: 600;
    border-radius: 8px;
    border: 1px solid transparent;
    cursor: pointer;
    transition: all 0.2s;
}

.sp-btn-cancel { background: #f1f5f9; color: #475569; border-color: #e2e8f0; }
.sp-btn-cancel:hover { background: #e2e8f0; }
.sp-btn-save { background: #279208; color: #fff; }
.sp-btn-save:hover:not(:disabled) { background: #1f7506; }
.sp-btn-save:disabled { opacity: 0.7; cursor: not-allowed; }

.sp-error {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    margin-top: 1rem;
    padding: 0.8rem 1rem;
    background: #fef2f2;
    border: 1px solid #fecaca;
    border-radius: 8px;
    color: #dc2626;
    font-size: 0.88rem;
}

/* Campos dentro del modal */
.sp-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.sp-row--3 { grid-template-columns: 1fr 1fr 1fr; }

.sp-group { margin-bottom: 1rem; min-width: 0; }

.sp-label {
    display: block;
    margin-bottom: 0.4rem;
    font-size: 0.85rem;
    font-weight: 500;
    color: #374151;
}

.sp-label .sp-req { color: #ba2831; }

.sp-input {
    width: 100%;
    padding: 0.575rem 0.8rem;
    font-size: 0.93rem;
    font-family: inherit;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    background: #fff;
    color: #1e293b;
    transition: all 0.2s;
}

.sp-input:focus {
    outline: none;
    border-color: #279208;
    box-shadow: 0 0 0 3px rgba(39, 146, 8, 0.1);
}

/* La lista desplegable la pinta el navegador: con la fuente web heredada Chrome
   calcula mal su alto y la corta. Los select usan la fuente del sistema, como en
   el resto del panel. */
select.sp-input { font-family: revert; cursor: pointer; }

.sp-input.sp-has-error { border-color: #ba2831; }
.sp-input::placeholder { color: #94a3b8; }
textarea.sp-input { resize: vertical; min-height: 70px; }

.sp-field-error {
    display: block;
    margin-top: 0.3rem;
    font-size: 0.78rem;
    color: #ba2831;
}

.sp-hint {
    margin: 0.3rem 0 0;
    font-size: 0.76rem;
    color: #64748b;
    line-height: 1.4;
}

.sp-check {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.88rem;
    color: #475569;
    cursor: pointer;
}

.sp-check input { width: 16px; height: 16px; min-height: 0; accent-color: #279208; }

.sp-section-title {
    margin: 0.25rem 0 0.9rem;
    padding-bottom: 0.4rem;
    border-bottom: 1px solid #f1f5f9;
    font-size: 0.78rem;
    font-weight: 700;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    color: #64748b;
}

@media (max-width: 576px) {
    .sp-row, .sp-row--3 { grid-template-columns: 1fr; gap: 0; }
    .sp-overlay { padding-top: 1rem; }
}
</style>
