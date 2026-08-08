<template>
    <!-- Teleport a body por lo mismo que los demas paneles del proyecto: dentro del
         contenedor del layout el position:fixed se resuelve contra ese contenedor.
         El prefijo es resch- para no rozar con las clases .modal-* de Bootstrap ni
         con el visit- del modal de coordinación. -->
    <Teleport to="body">
    <div class="resch-overlay" @click.self="close">
        <div class="resch-modal">
            <div class="resch-head">
                <h4 class="resch-title">
                    <i class="fa fa-calendar-day me-2"></i>
                    {{ step === 1 ? '¿Cuándo te viene mejor?' : 'Confirma la solicitud' }}
                </h4>
                <button class="resch-close" :disabled="saving" @click="close">
                    <i class="fa fa-times"></i>
                </button>
            </div>

            <div class="resch-body">
                <div v-if="errors.length" class="resch-alert">
                    <p v-for="(error, i) in errors" :key="i" class="resch-alert__line">{{ error }}</p>
                </div>

                <!-- Paso 1: elegir horario -->
                <template v-if="step === 1">
                    <p class="resch-lead">
                        Estos son los espacios libres de
                        <strong>{{ technicianName }}</strong> para mover la visita de
                        <strong>{{ equipmentCode }}</strong>.
                    </p>

                    <SlotPicker
                        v-model="selected"
                        :days="days"
                        :slots="slots"
                        :loading="loading"
                    />

                    <p v-if="!loading" class="resch-note">
                        <i class="fa fa-info-circle me-1"></i>
                        Necesitamos al menos {{ noticeHours }} horas de antelación.
                    </p>
                </template>

                <!-- Paso 2: resumen y motivo -->
                <template v-else>
                    <div class="resch-summary">
                        <div class="resch-summary__row">
                            <span class="resch-summary__label">Ahora</span>
                            <span class="resch-summary__value is-old">{{ currentLabel }}</span>
                        </div>
                        <div class="resch-summary__row">
                            <span class="resch-summary__label">Propones</span>
                            <span class="resch-summary__value is-new">{{ proposedLabel }}</span>
                        </div>
                    </div>

                    <label class="resch-label" for="resch-reason">Motivo (opcional)</label>
                    <textarea
                        id="resch-reason"
                        v-model="reason"
                        class="resch-textarea"
                        rows="3"
                        maxlength="1000"
                        placeholder="Por ejemplo: ese día el edificio tiene fumigación"
                    ></textarea>

                    <p class="resch-note">
                        <i class="fa fa-info-circle me-1"></i>
                        Coordinación revisará tu solicitud y te avisaremos por correo.
                        Mientras tanto, la visita mantiene su fecha actual.
                    </p>
                </template>
            </div>

            <div class="resch-foot">
                <button v-if="step === 2" class="resch-btn resch-btn--ghost" :disabled="saving" @click="step = 1">
                    <i class="fa fa-arrow-left me-1"></i> Atrás
                </button>
                <button v-else class="resch-btn resch-btn--ghost" :disabled="saving" @click="close">
                    Cancelar
                </button>

                <button
                    v-if="step === 1"
                    class="resch-btn resch-btn--primary"
                    :disabled="!selected"
                    @click="step = 2"
                >
                    Continuar <i class="fa fa-arrow-right ms-1"></i>
                </button>
                <button
                    v-else
                    class="resch-btn resch-btn--primary"
                    :disabled="saving"
                    @click="submit"
                >
                    <i :class="saving ? 'fa fa-spinner fa-spin' : 'fa fa-paper-plane'" class="me-2"></i>
                    {{ saving ? 'Enviando…' : 'Enviar solicitud' }}
                </button>
            </div>
        </div>
    </div>
    </Teleport>
</template>

<script>
import dayjs from '@/utils/dayjs.js';
import portalService from '@/services/portalService.js';
import SlotPicker from './SlotPicker.vue';

export default {
    name: 'RescheduleRequestModal',

    components: { SlotPicker },

    props: {
        visit: { type: Object, required: true },
    },

    emits: ['close', 'submitted'],

    data() {
        return {
            step: 1,
            loading: true,
            saving: false,
            days: [],
            slots: {},
            selected: '',
            reason: '',
            noticeHours: 24,
            technicianName: '',
            errors: [],
        };
    },

    computed: {
        equipmentCode() {
            return this.visit.equipment?.internal_code || 'tu equipo';
        },

        currentLabel() {
            const start = dayjs(this.visit.scheduled_start);
            return `${this.capitalize(start.format('dddd D [de] MMMM'))}, ${start.format('HH:mm')}`;
        },

        /**
         * El texto sale del value del chip elegido ("YYYY-MM-DD HH:mm"), que es hora
         * local ya resuelta por el backend: dayjs lo parsea sin desplazarlo.
         */
        proposedLabel() {
            if (!this.selected) return '';
            const slot = (this.slots[this.selected.slice(0, 10)] || [])
                .find((s) => s.value === this.selected);
            const start = dayjs(this.selected);

            return `${this.capitalize(start.format('dddd D [de] MMMM'))}, ${start.format('HH:mm')}`
                + (slot ? `–${slot.end_label}` : '');
        },
    },

    mounted() {
        this.load();
    },

    methods: {
        capitalize(text) {
            return text.charAt(0).toUpperCase() + text.slice(1);
        },

        async load() {
            this.loading = true;
            try {
                const { data } = await portalService.scheduleAvailability(this.visit.id);
                this.days = data.days || [];
                this.slots = data.slots || {};
                this.noticeHours = data.notice_hours ?? 24;
                this.technicianName = data.visit?.technician_name || 'tu técnico';
            } catch (e) {
                this.errors = ['No pudimos cargar los horarios disponibles. Inténtalo de nuevo.'];
            } finally {
                this.loading = false;
            }
        },

        async submit() {
            this.saving = true;
            this.errors = [];

            try {
                await portalService.requestReschedule(this.visit.id, {
                    proposed_start: this.selected,
                    reason: this.reason || null,
                });

                this.$emit('submitted');
            } catch (e) {
                const errors = e.response?.data?.errors;
                this.errors = errors
                    ? Object.values(errors).flat()
                    : [e.response?.data?.message || 'No pudimos enviar la solicitud.'];

                // Si el problema es el horario, alguien ocupo el hueco mientras el
                // cliente rellenaba el motivo: volver atras con la lista ya al dia.
                if (errors?.proposed_start) {
                    this.selected = '';
                    this.step = 1;
                    await this.load();
                }
            } finally {
                this.saving = false;
            }
        },

        close() {
            if (this.saving) return;
            this.$emit('close');
        },
    },
};
</script>

<style scoped>
.resch-overlay {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.6);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    z-index: 9999;
}

.resch-modal {
    width: 100%;
    max-width: 620px;
    max-height: 92vh;
    background: #fff;
    border-radius: 18px;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.resch-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: 1rem 1.25rem;
    border-bottom: 1px solid #f1f5f9;
}

.resch-title {
    margin: 0;
    font-size: 1rem;
    font-weight: 700;
    color: #1e293b;
}

.resch-title i {
    color: #30ab0a;
}

.resch-close {
    border: 0;
    background: none;
    color: #94a3b8;
    font-size: 1rem;
    cursor: pointer;
}

.resch-body {
    padding: 1.1rem 1.25rem;
    overflow-y: auto;
}

.resch-lead {
    margin: 0 0 0.9rem;
    font-size: 0.88rem;
    color: #475569;
}

.resch-alert {
    background: #fef2f2;
    border: 1px solid #fecaca;
    border-radius: 10px;
    padding: 0.7rem 0.9rem;
    margin-bottom: 0.9rem;
}

.resch-alert__line {
    margin: 0;
    font-size: 0.84rem;
    color: #ba2831;
}

.resch-note {
    margin: 0.9rem 0 0;
    font-size: 0.8rem;
    color: #94a3b8;
}

.resch-summary {
    background: #f8fafc;
    border-radius: 12px;
    padding: 0.9rem 1rem;
    margin-bottom: 1.1rem;
}

.resch-summary__row {
    display: flex;
    align-items: baseline;
    gap: 0.75rem;
}

.resch-summary__row + .resch-summary__row {
    margin-top: 0.5rem;
}

.resch-summary__label {
    flex: 0 0 70px;
    font-size: 0.68rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #94a3b8;
}

.resch-summary__value {
    font-size: 0.92rem;
    font-weight: 600;
}

.resch-summary__value.is-old {
    color: #94a3b8;
    text-decoration: line-through;
}

.resch-summary__value.is-new {
    color: #227a0c;
}

.resch-label {
    display: block;
    font-size: 0.78rem;
    font-weight: 600;
    color: #475569;
    margin-bottom: 0.35rem;
}

.resch-textarea {
    width: 100%;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 0.6rem 0.75rem;
    font-size: 0.88rem;
    color: #1e293b;
    resize: vertical;
}

.resch-foot {
    display: flex;
    justify-content: flex-end;
    gap: 0.6rem;
    padding: 0.9rem 1.25rem;
    border-top: 1px solid #f1f5f9;
    background: #fcfdfe;
}

.resch-btn {
    border: 0;
    border-radius: 10px;
    padding: 0.6rem 1.2rem;
    font-size: 0.9rem;
    font-weight: 600;
    cursor: pointer;
}

.resch-btn:disabled {
    opacity: 0.55;
    cursor: not-allowed;
}

.resch-btn--ghost {
    background: #f1f5f9;
    color: #64748b;
}

.resch-btn--primary {
    background: linear-gradient(135deg, #30ab0a, #37c20c);
    color: #fff;
}

@media (max-width: 640px) {
    .resch-overlay {
        padding: 0;
        align-items: flex-end;
    }

    .resch-modal {
        max-width: none;
        max-height: 94vh;
        border-radius: 18px 18px 0 0;
    }

    /* Sobre la barra inferior del portal, que es fixed. */
    .resch-foot {
        padding-bottom: 1.4rem;
    }
}
</style>
