<template>
    <section class="rem-card">
        <h2 class="rem-card__title"><i class="fa fa-bell"></i> Recordatorios de visita</h2>

        <p v-if="loading" class="rem-loading"><i class="fa fa-spinner fa-spin"></i> Cargando…</p>

        <template v-else>
            <p class="rem-intro">
                Te avisamos por correo antes de cada visita programada.
                <span v-if="!isCustom">Ahora mismo se usan los avisos por defecto.</span>
            </p>

            <label class="rem-toggle">
                <input v-model="enabled" type="checkbox" />
                <span>Quiero recibir recordatorios</span>
            </label>

            <div v-if="enabled" class="rem-list">
                <div v-for="(offset, i) in offsets" :key="i" class="rem-row">
                    <select v-model.number="offset.days_before" class="rem-select">
                        <option v-for="d in dayOptions" :key="d" :value="d">{{ dayLabel(d) }}</option>
                    </select>
                    <span class="rem-at">a las</span>
                    <input v-model="offset.time" type="time" class="rem-time" />
                    <button type="button" class="rem-remove" title="Quitar" @click="remove(i)">
                        <i class="fa fa-times"></i>
                    </button>
                </div>

                <p v-if="!offsets.length" class="rem-empty">
                    Sin avisos configurados: no recibirás recordatorios.
                </p>

                <button
                    v-if="offsets.length < maxOffsets"
                    type="button"
                    class="rem-add"
                    @click="add"
                >
                    <i class="fa fa-plus"></i> Agregar recordatorio
                </button>
                <p v-else class="rem-hint">Puedes configurar hasta {{ maxOffsets }}.</p>
            </div>

            <p v-if="error" class="rem-err">{{ error }}</p>

            <div class="rem-actions">
                <button type="button" class="rem-save" :disabled="saving" @click="save">
                    <i v-if="saving" class="fa fa-spinner fa-spin"></i>
                    {{ saving ? 'Guardando…' : 'Guardar recordatorios' }}
                </button>
                <button v-if="isCustom" type="button" class="rem-reset" :disabled="saving" @click="reset">
                    Volver a los de por defecto
                </button>
            </div>
        </template>
    </section>
</template>

<script>
import reminderService from '@/services/reminderService.js';

export default {
    name: 'ReminderSettingsCard',

    data() {
        return {
            loading: true,
            saving: false,
            enabled: true,
            offsets: [],
            defaults: [],
            isCustom: false,
            maxOffsets: 3,
            error: '',
            // 0 es "el mismo día": es lo que hace útil el aviso de primera hora.
            dayOptions: [0, 1, 2, 3, 5, 7, 15, 30],
        };
    },

    created() {
        this.load();
    },

    methods: {
        dayLabel(d) {
            if (d === 0) return 'El mismo día';
            if (d === 1) return '1 día antes';
            return `${d} días antes`;
        },

        async load() {
            this.loading = true;
            try {
                const { data } = await reminderService.get();
                this.enabled = data.enabled;
                this.defaults = data.defaults || [];
                this.isCustom = data.is_custom;
                this.maxOffsets = data.max_offsets ?? 3;
                // Sin configuración propia se muestran los de fábrica, que son
                // los que de verdad se están aplicando.
                this.offsets = (data.is_custom ? data.offsets : this.defaults).map(o => ({ ...o }));
            } finally {
                this.loading = false;
            }
        },

        add() {
            if (this.offsets.length >= this.maxOffsets) return;
            this.offsets.push({ days_before: 1, time: '08:00' });
        },

        remove(i) {
            this.offsets.splice(i, 1);
        },

        async save() {
            this.saving = true;
            this.error = '';
            try {
                const { data } = await reminderService.update({
                    enabled: this.enabled,
                    offsets: this.enabled ? this.offsets : [],
                });
                this.offsets = data.offsets.map(o => ({ ...o }));
                this.isCustom = true;
                this.$swal?.fire({
                    toast: true, position: 'top-end', icon: 'success',
                    title: 'Recordatorios actualizados',
                    showConfirmButton: false, timer: 2500,
                });
            } catch (e) {
                const errors = e.response?.data?.errors;
                this.error = errors ? Object.values(errors).flat()[0] : 'No se pudieron guardar los recordatorios.';
            } finally {
                this.saving = false;
            }
        },

        async reset() {
            this.saving = true;
            try {
                await reminderService.reset();
                await this.load();
            } finally {
                this.saving = false;
            }
        },
    },
};
</script>

<style scoped>
/* Autocontenido a propósito: los estilos de la vista que lo aloja son scoped y
   no alcanzan a este componente. Sin esto, .card y .btn-primary caían en las
   clases de Bootstrap y el botón salía azul. */
.rem-card {
    background: white;
    border-radius: 14px;
    padding: 1.25rem;
    margin-bottom: 1.25rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
}

.rem-card__title {
    font-size: 1rem;
    font-weight: 700;
    color: #1e293b;
    margin: 0 0 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.rem-card__title i {
    color: #30ab0a;
}

.rem-actions {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-top: 1.1rem;
}

.rem-save {
    padding: 0.65rem 1.4rem;
    border: none;
    border-radius: 10px;
    background: linear-gradient(135deg, #30ab0a, #279208);
    color: white;
    font-size: 0.92rem;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.rem-save:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.rem-loading,
.rem-intro {
    color: #64748b;
    font-size: 0.88rem;
    margin: 0 0 0.9rem;
}

.rem-toggle {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    font-size: 0.9rem;
    color: #1e293b;
    cursor: pointer;
    margin-bottom: 0.9rem;
}

.rem-toggle input {
    width: 18px;
    height: 18px;
    accent-color: #30ab0a;
}

.rem-list {
    display: flex;
    flex-direction: column;
    gap: 0.6rem;
    margin-bottom: 0.5rem;
}

.rem-row {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.rem-select,
.rem-time {
    padding: 0.5rem 0.7rem;
    border: 1px solid #e2e8f0;
    border-radius: 9px;
    font-size: 0.88rem;
    color: #1e293b;
    background: #fff;
}

.rem-select {
    flex: 1;
    min-width: 0;
}

.rem-at {
    font-size: 0.85rem;
    color: #94a3b8;
}

.rem-remove {
    border: 0;
    background: #f8fafc;
    color: #94a3b8;
    width: 32px;
    height: 32px;
    border-radius: 9px;
    cursor: pointer;
    flex-shrink: 0;
}

.rem-remove:hover {
    background: #fef2f2;
    color: #ba2831;
}

.rem-add {
    align-self: flex-start;
    border: 1px dashed #cbd5e1;
    background: none;
    color: #30ab0a;
    font-size: 0.85rem;
    font-weight: 600;
    padding: 0.5rem 0.9rem;
    border-radius: 9px;
    cursor: pointer;
}

.rem-empty {
    font-size: 0.85rem;
    color: #b45309;
    background: #fffbeb;
    border: 1px solid #fde68a;
    border-radius: 9px;
    padding: 0.6rem 0.8rem;
    margin: 0;
}

.rem-reset {
    border: 0;
    background: none;
    color: #64748b;
    font-size: 0.85rem;
    text-decoration: underline;
    cursor: pointer;
}

.rem-hint {
    font-size: 0.8rem;
    color: #94a3b8;
    margin: 0.2rem 0 0;
}

.rem-err {
    display: block;
    color: #ba2831;
    font-size: 0.82rem;
    margin: 0.4rem 0 0;
}
</style>
