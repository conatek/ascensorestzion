<template>
    <div class="modal-overlay" @click.self="close">
        <div class="visit-modal">
            <div class="visit-modal-head">
                <h4 class="visit-modal-title">
                    <i class="fa fa-calendar-plus me-2"></i>
                    {{ isEdit ? 'Editar visita' : 'Programar visita' }}
                </h4>
                <button class="visit-modal-close" @click="close" :disabled="saving">
                    <i class="fa fa-times"></i>
                </button>
            </div>

            <div class="visit-modal-body">
                <!-- Errores del backend: la validacion real de jornada y solapes vive
                     alli, asi que se muestran tal cual llegan. -->
                <div v-if="errors.length" class="visit-alert visit-alert-error">
                    <i class="fa fa-exclamation-triangle me-2"></i>
                    <ul>
                        <li v-for="(err, i) in errors" :key="i">{{ err }}</li>
                    </ul>
                </div>

                <div class="visit-grid">
                    <div class="visit-field">
                        <label class="visit-label">Cliente</label>
                        <select v-model="form.client_id" class="visit-input" :disabled="isEdit" @change="onClientChange">
                            <option value="">Selecciona…</option>
                            <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.business_name }}</option>
                        </select>
                    </div>

                    <div class="visit-field">
                        <label class="visit-label">Sede</label>
                        <select v-model="form.site_id" class="visit-input" :disabled="isEdit || !form.client_id" @change="onSiteChange">
                            <option value="">Selecciona…</option>
                            <option v-for="s in filteredSites" :key="s.id" :value="s.id">{{ s.name }}</option>
                        </select>
                    </div>

                    <div class="visit-field">
                        <label class="visit-label">Equipo</label>
                        <select v-model="form.equipment_id" class="visit-input" :disabled="isEdit || !form.site_id" @change="onEquipmentChange">
                            <option value="">Selecciona…</option>
                            <option v-for="e in filteredEquipment" :key="e.id" :value="e.id">
                                {{ e.internal_code }} — {{ typeLabel(e.equipment_type) }}
                            </option>
                        </select>
                    </div>

                    <div class="visit-field">
                        <label class="visit-label">Técnico</label>
                        <select v-model="form.technician_id" class="visit-input">
                            <option value="">Selecciona…</option>
                            <option v-for="t in technicians" :key="t.id" :value="t.id">{{ t.name }}</option>
                        </select>
                        <span v-if="technicianWindow" class="visit-hint">
                            Jornada: {{ technicianWindow.start }}–{{ technicianWindow.end }}
                            <template v-if="technicianWindow.break_start">
                                · descanso {{ technicianWindow.break_start }}–{{ technicianWindow.break_end }}
                            </template>
                            · {{ dayNames(technicianWindow.days) }}
                        </span>
                    </div>

                    <div class="visit-field">
                        <label class="visit-label">Fecha</label>
                        <input v-model="form.date" type="date" class="visit-input" @change="recomputeEnd" />
                    </div>

                    <div class="visit-field">
                        <label class="visit-label">Tipo</label>
                        <select v-model="form.visit_type" class="visit-input">
                            <option value="preventivo">Preventivo</option>
                            <option value="correctivo">Correctivo</option>
                            <option value="especial">Especial</option>
                        </select>
                    </div>

                    <div class="visit-field">
                        <label class="visit-label">Hora de inicio</label>
                        <input v-model="form.start_time" type="time" class="visit-input" step="300" @change="recomputeEnd" />
                    </div>

                    <div class="visit-field">
                        <label class="visit-label">
                            Hora de fin
                            <span v-if="durationSource" class="visit-badge">{{ durationSource }}</span>
                        </label>
                        <input v-model="form.end_time" type="time" class="visit-input" step="300" />
                        <span class="visit-hint">Duración: {{ durationLabel }}</span>
                    </div>

                    <div class="visit-field visit-field-wide">
                        <label class="visit-label">Notas</label>
                        <textarea v-model="form.notes" class="visit-input" rows="3"
                            placeholder="Instrucciones para el técnico, contacto en sitio, etc."></textarea>
                    </div>
                </div>
            </div>

            <div class="visit-modal-foot">
                <button class="visit-btn visit-btn-ghost" @click="close" :disabled="saving">Cancelar</button>
                <button class="visit-btn visit-btn-primary" @click="save" :disabled="saving || !isComplete">
                    <i :class="saving ? 'fa fa-spinner fa-spin' : 'fa fa-check'" class="me-2"></i>
                    {{ saving ? 'Guardando…' : (isEdit ? 'Guardar cambios' : 'Programar') }}
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import dayjs from '@/utils/dayjs.js';
import scheduleService from '@/services/scheduleService.js';

const TYPE_LABELS = {
    ascensor: 'Ascensor',
    escalera_electrica: 'Escalera eléctrica',
    montacargas: 'Montacargas',
    otro: 'Otro',
};

const DAY_ABBR = { 1: 'Lu', 2: 'Ma', 3: 'Mi', 4: 'Ju', 5: 'Vi', 6: 'Sá', 7: 'Do' };

export default {
    name: 'VisitFormModal',
    props: {
        visit: { type: Object, default: null },        // null = crear
        clients: { type: Array, default: () => [] },
        sites: { type: Array, default: () => [] },
        equipment: { type: Array, default: () => [] },
        technicians: { type: Array, default: () => [] },
        defaults: { type: Object, default: () => ({}) },
        // Hueco donde el usuario hizo clic en el calendario
        prefill: { type: Object, default: null },
    },
    emits: ['close', 'saved'],
    data() {
        return {
            saving: false,
            errors: [],
            // Duración resuelta por el backend para el equipo elegido (cascada
            // equipo → global). Se usa solo para precargar la hora de fin.
            equipmentDuration: null,
            equipmentDurationIsCustom: false,
            form: {
                client_id: '',
                site_id: '',
                equipment_id: '',
                technician_id: '',
                date: dayjs().format('YYYY-MM-DD'),
                start_time: '09:00',
                end_time: '10:30',
                visit_type: 'preventivo',
                notes: '',
            },
        };
    },
    computed: {
        isEdit() {
            return !!this.visit;
        },
        filteredSites() {
            if (!this.form.client_id) return [];
            return this.sites.filter(s => String(s.client_id) === String(this.form.client_id));
        },
        filteredEquipment() {
            if (!this.form.site_id) return [];
            return this.equipment.filter(e => String(e.site_id) === String(this.form.site_id));
        },
        technicianWindow() {
            const t = this.technicians.find(t => String(t.id) === String(this.form.technician_id));
            return t?.working_window || null;
        },
        durationMinutes() {
            const start = dayjs(`${this.form.date} ${this.form.start_time}`);
            const end = dayjs(`${this.form.date} ${this.form.end_time}`);
            const diff = end.diff(start, 'minute');
            return diff > 0 ? diff : 0;
        },
        durationLabel() {
            const m = this.durationMinutes;
            if (!m) return '—';
            const h = Math.floor(m / 60);
            const rest = m % 60;
            if (!h) return `${rest} min`;
            return rest ? `${h} h ${rest} min` : `${h} h`;
        },
        /** De dónde salió la duración precargada, para que se vea que es configurable. */
        durationSource() {
            if (this.equipmentDuration === null) return '';
            return this.equipmentDurationIsCustom ? 'según el equipo' : 'por defecto';
        },
        isComplete() {
            return this.form.equipment_id && this.form.technician_id
                && this.form.date && this.form.start_time && this.form.end_time;
        },
    },
    created() {
        if (this.visit) {
            this.hydrateFromVisit();
        } else if (this.prefill) {
            this.hydrateFromPrefill();
        }
    },
    methods: {
        typeLabel(type) {
            return TYPE_LABELS[type] || type;
        },
        dayNames(days) {
            if (!days?.length) return '';
            return days.map(d => DAY_ABBR[d] || d).join(' ');
        },

        hydrateFromVisit() {
            const v = this.visit;
            const start = dayjs(v.scheduled_start);
            const end = dayjs(v.scheduled_end);
            this.form = {
                client_id: v.client_id,
                site_id: v.site_id,
                equipment_id: v.equipment_id,
                technician_id: v.technician_id,
                date: start.format('YYYY-MM-DD'),
                start_time: start.format('HH:mm'),
                end_time: end.format('HH:mm'),
                visit_type: v.visit_type,
                notes: v.notes || '',
            };
        },

        hydrateFromPrefill() {
            const start = dayjs(this.prefill.start);
            this.form.date = start.format('YYYY-MM-DD');
            this.form.start_time = start.format('HH:mm');
            if (this.prefill.technician_id) {
                this.form.technician_id = this.prefill.technician_id;
            }
            this.applyDuration(this.defaults.default_duration_minutes || 90);
        },

        onClientChange() {
            this.form.site_id = '';
            this.form.equipment_id = '';
        },
        onSiteChange() {
            this.form.equipment_id = '';
        },

        /**
         * Al elegir equipo se pide su duración al backend, que es quien conoce la
         * cascada. Precarga la hora de fin, que el usuario puede seguir cambiando.
         */
        async onEquipmentChange() {
            if (!this.form.equipment_id) return;
            try {
                const { data } = await scheduleService.equipmentDuration(this.form.equipment_id);
                this.equipmentDuration = data.duration_minutes;
                this.equipmentDurationIsCustom = data.is_custom;
                this.applyDuration(data.duration_minutes);
            } catch {
                // Sin respuesta se deja lo que haya: no vale la pena molestar al
                // usuario, la hora de fin es editable.
            }
        },

        recomputeEnd() {
            if (this.equipmentDuration) this.applyDuration(this.equipmentDuration);
        },

        applyDuration(minutes) {
            if (!this.form.start_time || !minutes) return;
            this.form.end_time = dayjs(`${this.form.date} ${this.form.start_time}`)
                .add(minutes, 'minute')
                .format('HH:mm');
        },

        async save() {
            this.saving = true;
            this.errors = [];

            const payload = {
                equipment_id: this.form.equipment_id,
                technician_id: this.form.technician_id,
                scheduled_start: `${this.form.date} ${this.form.start_time}`,
                scheduled_end: `${this.form.date} ${this.form.end_time}`,
                visit_type: this.form.visit_type,
                notes: this.form.notes || null,
            };

            try {
                const { data } = this.isEdit
                    ? await scheduleService.update(this.visit.id, payload)
                    : await scheduleService.store(payload);
                this.$emit('saved', data);
            } catch (err) {
                this.errors = this.collectErrors(err);
            } finally {
                this.saving = false;
            }
        },

        /** Aplana errors de 422 (jornada, descanso, solapes) en una lista plana. */
        collectErrors(err) {
            const bag = err?.response?.data?.errors;
            if (bag) return Object.values(bag).flat();
            return [err?.response?.data?.message || err.message || 'No se pudo guardar la visita.'];
        },

        close() {
            this.$emit('close');
        },
    },
};
</script>

<style scoped>
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.6);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    padding: 1rem;
}

.visit-modal {
    background: #fff;
    border-radius: 18px;
    width: 100%;
    max-width: 760px;
    max-height: 92vh;
    display: flex;
    flex-direction: column;
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
    overflow: hidden;
}

.visit-modal-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.15rem 1.5rem;
    border-bottom: 1px solid #e9edf2;
}

.visit-modal-title {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 700;
    color: #1e293b;
}

.visit-modal-close {
    border: 0;
    background: #f1f5f9;
    color: #64748b;
    width: 34px;
    height: 34px;
    border-radius: 10px;
    cursor: pointer;
}

.visit-modal-close:hover {
    background: #e2e8f0;
}

.visit-modal-body {
    padding: 1.35rem 1.5rem;
    overflow-y: auto;
}

.visit-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem 1.25rem;
}

.visit-field {
    display: flex;
    flex-direction: column;
}

.visit-field-wide {
    grid-column: 1 / -1;
}

.visit-label {
    font-size: 0.78rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    color: #64748b;
    margin-bottom: 0.35rem;
}

.visit-badge {
    text-transform: none;
    letter-spacing: 0;
    font-weight: 500;
    font-size: 0.72rem;
    color: #30ab0a;
    background: #eefbe9;
    border-radius: 6px;
    padding: 0.1rem 0.4rem;
    margin-left: 0.4rem;
}

.visit-input {
    border: 1px solid #d8e0e8;
    border-radius: 10px;
    padding: 0.6rem 0.75rem;
    font-size: 0.9rem;
    color: #1e293b;
    background: #fff;
    outline: none;
    transition: border-color 0.15s, box-shadow 0.15s;
}

.visit-input:focus {
    border-color: #30ab0a;
    box-shadow: 0 0 0 3px rgba(48, 171, 10, 0.12);
}

.visit-input:disabled {
    background: #f8fafc;
    color: #94a3b8;
}

.visit-hint {
    font-size: 0.75rem;
    color: #94a3b8;
    margin-top: 0.3rem;
}

.visit-alert {
    border-radius: 12px;
    padding: 0.85rem 1rem;
    font-size: 0.85rem;
    margin-bottom: 1.1rem;
    display: flex;
    align-items: flex-start;
}

.visit-alert ul {
    margin: 0;
    padding-left: 1.1rem;
}

.visit-alert-error {
    background: #fef2f2;
    border: 1px solid #fecaca;
    color: #ba2831;
}

.visit-modal-foot {
    display: flex;
    justify-content: flex-end;
    gap: 0.6rem;
    padding: 1.05rem 1.5rem;
    border-top: 1px solid #e9edf2;
    background: #fafcfe;
}

.visit-btn {
    border: 0;
    border-radius: 10px;
    padding: 0.6rem 1.15rem;
    font-size: 0.88rem;
    font-weight: 600;
    cursor: pointer;
}

.visit-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.visit-btn-ghost {
    background: #eef2f6;
    color: #475569;
}

.visit-btn-primary {
    background: linear-gradient(135deg, #30ab0a, #37c20c);
    color: #fff;
}

@media (max-width: 640px) {
    .visit-grid {
        grid-template-columns: 1fr;
    }
}
</style>
