<template>
    <section class="jor-card">
        <h2 class="jor-card__title">
            <i class="fa fa-business-time"></i> Jornada y excepciones
        </h2>

        <p v-if="loading" class="jor-loading">
            <i class="fa fa-spinner fa-spin"></i> Cargando…
        </p>

        <template v-else>
            <p v-if="error" class="jor-error">{{ error }}</p>

            <!-- ── Jornada ── -->
            <div class="jor-block">
                <div class="jor-block__head">
                    <h3 class="jor-block__title">Jornada habitual</h3>
                    <span v-if="!isCustom" class="jor-chip">Hereda la general</span>
                    <span v-else class="jor-chip is-custom">Personalizada</span>
                </div>

                <p class="jor-resolved">
                    {{ resolvedDays }} · {{ resolved.start }}–{{ resolved.end }}
                    <template v-if="resolved.break_start">
                        · descanso {{ resolved.break_start }}–{{ resolved.break_end }}
                    </template>
                    <template v-else> · sin descanso</template>
                </p>

                <template v-if="editing">
                    <div class="jor-days">
                        <label v-for="day in dayOptions" :key="day.value" class="jor-day">
                            <input type="checkbox" :value="day.value" v-model="form.working_days">
                            <span>{{ day.label }}</span>
                        </label>
                    </div>

                    <div class="jor-times">
                        <label class="jor-field">
                            <span>Entrada</span>
                            <input type="time" v-model="form.start" class="jor-time">
                        </label>
                        <label class="jor-field">
                            <span>Salida</span>
                            <input type="time" v-model="form.end" class="jor-time">
                        </label>
                        <label class="jor-field">
                            <span>Descanso desde</span>
                            <input type="time" v-model="form.break_start" class="jor-time">
                        </label>
                        <label class="jor-field">
                            <span>hasta</span>
                            <input type="time" v-model="form.break_end" class="jor-time">
                        </label>
                    </div>
                    <p class="jor-hint">Deja el descanso vacío si este técnico no lo tiene.</p>

                    <div class="jor-actions">
                        <button class="jor-btn jor-btn--ghost" :disabled="saving" @click="editing = false">
                            Cancelar
                        </button>
                        <button class="jor-btn jor-btn--primary" :disabled="saving" @click="saveSchedule">
                            <i :class="saving ? 'fa fa-spinner fa-spin' : 'fa fa-check'" class="me-1"></i>
                            Guardar jornada
                        </button>
                    </div>
                </template>

                <div v-else class="jor-actions">
                    <button v-if="isCustom" class="jor-link" :disabled="saving" @click="resetSchedule">
                        Volver a la general
                    </button>
                    <button class="jor-btn jor-btn--ghost" @click="startEditing">
                        <i class="fa fa-pen me-1"></i> Personalizar
                    </button>
                </div>
            </div>

            <!-- ── Excepciones ── -->
            <div class="jor-block">
                <div class="jor-block__head">
                    <h3 class="jor-block__title">Excepciones por fecha</h3>
                    <button class="jor-btn jor-btn--ghost jor-btn--sm" @click="showForm = !showForm">
                        <i class="fa fa-plus me-1"></i> Añadir
                    </button>
                </div>

                <form v-if="showForm" class="jor-exc-form" @submit.prevent="saveException">
                    <div class="jor-times">
                        <label class="jor-field">
                            <span>Desde</span>
                            <input type="date" v-model="exc.date" class="jor-time" required>
                        </label>
                        <label class="jor-field">
                            <span>Hasta (opcional)</span>
                            <input type="date" v-model="exc.date_end" class="jor-time">
                        </label>
                    </div>

                    <label class="jor-check">
                        <input type="checkbox" v-model="exc.closed">
                        <span>No trabaja esos días</span>
                    </label>

                    <div v-if="!exc.closed" class="jor-times">
                        <label class="jor-field">
                            <span>Entrada</span>
                            <input type="time" v-model="exc.start" class="jor-time">
                        </label>
                        <label class="jor-field">
                            <span>Salida</span>
                            <input type="time" v-model="exc.end" class="jor-time">
                        </label>
                    </div>

                    <label class="jor-field jor-field--wide">
                        <span>Motivo</span>
                        <input type="text" v-model="exc.note" class="jor-time" maxlength="255"
                               placeholder="Vacaciones, incapacidad, turno especial…">
                    </label>

                    <div class="jor-actions">
                        <button type="button" class="jor-btn jor-btn--ghost" @click="showForm = false">Cancelar</button>
                        <button type="submit" class="jor-btn jor-btn--primary" :disabled="saving">
                            <i :class="saving ? 'fa fa-spinner fa-spin' : 'fa fa-check'" class="me-1"></i>
                            Guardar
                        </button>
                    </div>
                </form>

                <p v-if="!exceptions.length" class="jor-empty">
                    Sin excepciones próximas. La jornada de arriba aplica todos los días.
                </p>

                <ul v-else class="jor-exc-list">
                    <li v-for="item in exceptions" :key="item.id" :class="{ 'is-global': !item.user_id }">
                        <span class="jor-exc__date">{{ formatDate(item.date) }}</span>
                        <span class="jor-exc__what">
                            {{ item.working_hours
                                ? `${item.working_hours.start}–${item.working_hours.end}`
                                : 'No trabaja' }}
                        </span>
                        <span class="jor-exc__note">{{ item.note || '—' }}</span>
                        <span v-if="!item.user_id" class="jor-chip jor-chip--global">General</span>
                        <button v-else class="jor-exc__del" title="Quitar" @click="removeException(item)">
                            <i class="fa fa-times"></i>
                        </button>
                    </li>
                </ul>
                <p class="jor-hint">
                    Las marcadas como <strong>General</strong> son festivos de la empresa y se
                    gestionan desde el cronograma.
                </p>
            </div>
        </template>
    </section>
</template>

<script>
import dayjs from '@/utils/dayjs.js';
import scheduleService from '@/services/scheduleService.js';

const DAY_LABELS = { 1: 'Lun', 2: 'Mar', 3: 'Mié', 4: 'Jue', 5: 'Vie', 6: 'Sáb', 7: 'Dom' };

/**
 * Jornada propia de un tecnico y sus excepciones por fecha, en la ficha del
 * usuario. Autonomo: carga y guarda lo suyo.
 *
 * Estilos con prefijo jor- por lo de siempre: los de la vista anfitriona son
 * scoped y no llegan hasta aqui.
 */
export default {
    name: 'TechnicianScheduleCard',

    props: {
        userId: { type: [Number, String], required: true },
    },

    data() {
        return {
            loading: true,
            saving: false,
            editing: false,
            showForm: false,
            error: null,
            override: null,
            resolved: { days: [], start: '08:00', end: '18:00', break_start: null, break_end: null },
            exceptions: [],
            form: { working_days: [], start: '08:00', end: '18:00', break_start: '', break_end: '' },
            exc: { date: '', date_end: '', closed: true, start: '08:00', end: '12:00', note: '' },
            dayOptions: Object.entries(DAY_LABELS).map(([value, label]) => ({ value: Number(value), label })),
        };
    },

    computed: {
        isCustom() {
            return Boolean(this.override);
        },
        resolvedDays() {
            return (this.resolved.days || []).map(d => DAY_LABELS[d]).join(' ') || 'Ningún día';
        },
    },

    created() {
        this.load();
    },

    methods: {
        formatDate(value) {
            return dayjs(value).format('ddd D MMM YYYY');
        },

        async load() {
            this.loading = true;
            try {
                const [scheduleRes, excRes] = await Promise.all([
                    scheduleService.technicianSchedule(this.userId),
                    scheduleService.scheduleExceptions({ user_id: this.userId }),
                ]);
                this.override = scheduleRes.data.override;
                this.resolved = scheduleRes.data.resolved;
                this.exceptions = excRes.data.exceptions || [];
            } catch (e) {
                this.error = 'No se pudo cargar la jornada.';
            } finally {
                this.loading = false;
            }
        },

        startEditing() {
            this.form = {
                working_days: [...(this.resolved.days || [])],
                start: this.resolved.start,
                end: this.resolved.end,
                break_start: this.resolved.break_start || '',
                break_end: this.resolved.break_end || '',
            };
            this.editing = true;
        },

        async saveSchedule() {
            this.saving = true;
            this.error = null;
            try {
                const { data } = await scheduleService.saveTechnicianSchedule(this.userId, {
                    enabled: true,
                    working_days: this.form.working_days,
                    working_hours: { start: this.form.start, end: this.form.end },
                    break_start: this.form.break_start || null,
                    break_end: this.form.break_end || null,
                });
                this.override = data.override;
                this.resolved = data.resolved;
                this.editing = false;
                this.toast('Jornada actualizada');
            } catch (e) {
                this.error = this.flatten(e);
            } finally {
                this.saving = false;
            }
        },

        async resetSchedule() {
            this.saving = true;
            try {
                const { data } = await scheduleService.resetTechnicianSchedule(this.userId);
                this.override = data.override;
                this.resolved = data.resolved;
                this.toast('Vuelve a la jornada general');
            } finally {
                this.saving = false;
            }
        },

        async saveException() {
            this.saving = true;
            this.error = null;
            try {
                const { data } = await scheduleService.saveScheduleException({
                    user_id: this.userId,
                    date: this.exc.date,
                    date_end: this.exc.date_end || null,
                    working_hours: this.exc.closed ? null : { start: this.exc.start, end: this.exc.end },
                    note: this.exc.note || null,
                });

                this.showForm = false;
                this.exc = { date: '', date_end: '', closed: true, start: '08:00', end: '12:00', note: '' };
                await this.load();
                this.warnAffected(data.affected_visits);
            } catch (e) {
                this.error = this.flatten(e);
            } finally {
                this.saving = false;
            }
        },

        async removeException(item) {
            const { isConfirmed } = await this.$swal.fire({
                icon: 'question',
                title: '¿Quitar esta excepción?',
                text: `${this.formatDate(item.date)} · ${item.note || 'sin motivo'}`,
                showCancelButton: true,
                confirmButtonText: 'Quitar',
                cancelButtonText: 'Volver',
                confirmButtonColor: '#ba2831',
            });

            if (!isConfirmed) return;

            await scheduleService.deleteScheduleException(item.id);
            await this.load();
        },

        /**
         * Cerrar un día no cancela lo que ya había: el backend lo devuelve y aquí
         * se avisa, porque si no coordinación se entera el lunes por teléfono.
         */
        warnAffected(visits) {
            if (!visits?.length) {
                this.toast('Excepción guardada');
                return;
            }

            const lista = visits
                .map(v => `${dayjs(v.scheduled_start).format('D MMM HH:mm')} · ${v.equipment_code}`)
                .join('<br>');

            this.$swal.fire({
                icon: 'warning',
                title: `Hay ${visits.length} visita(s) en esos días`,
                html: `${lista}<br><br>No se cancelaron. Muévelas o cancélalas desde el cronograma.`,
                confirmButtonText: 'Entendido',
                confirmButtonColor: '#f59e0b',
            });
        },

        toast(title) {
            this.$swal?.fire({
                toast: true, position: 'top-end', icon: 'success',
                title, showConfirmButton: false, timer: 2200,
            });
        },

        flatten(e) {
            const bag = e.response?.data?.errors;
            return bag
                ? Object.values(bag).flat().join(' ')
                : (e.response?.data?.message || 'No se pudo guardar.');
        },
    },
};
</script>

<style scoped>
.jor-card {
    background: #fff;
    border-radius: 14px;
    padding: 1.25rem;
    margin-bottom: 1.25rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
}

.jor-card__title {
    font-size: 1rem;
    font-weight: 700;
    color: #1e293b;
    margin: 0 0 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.jor-card__title i { color: #30ab0a; }

.jor-loading { color: #64748b; font-size: 0.88rem; margin: 0; }

.jor-error {
    background: #fef2f2;
    border: 1px solid #fecaca;
    border-radius: 9px;
    padding: 0.6rem 0.8rem;
    color: #ba2831;
    font-size: 0.84rem;
    margin: 0 0 0.9rem;
}

.jor-block + .jor-block {
    margin-top: 1.4rem;
    padding-top: 1.2rem;
    border-top: 1px solid #f1f5f9;
}

.jor-block__head {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    margin-bottom: 0.6rem;
}

.jor-block__title {
    font-size: 0.8rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #94a3b8;
    margin: 0;
    flex: 1;
}

.jor-chip {
    font-size: 0.68rem;
    font-weight: 700;
    padding: 0.18rem 0.55rem;
    border-radius: 999px;
    background: #f1f5f9;
    color: #64748b;
}

.jor-chip.is-custom { background: #eefbe9; color: #227a0c; }
.jor-chip--global { background: #e0f2fe; color: #0369a1; }

.jor-resolved {
    margin: 0 0 0.8rem;
    font-size: 0.92rem;
    color: #1e293b;
    font-weight: 600;
}

.jor-days {
    display: flex;
    flex-wrap: wrap;
    gap: 0.4rem;
    margin-bottom: 0.8rem;
}

.jor-day {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    font-size: 0.82rem;
    color: #475569;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 0.3rem 0.6rem;
    cursor: pointer;
}

.jor-times {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    margin-bottom: 0.6rem;
}

.jor-field {
    display: flex;
    flex-direction: column;
    gap: 0.2rem;
    font-size: 0.75rem;
    color: #64748b;
}

.jor-field--wide { width: 100%; }

.jor-time {
    padding: 0.45rem 0.65rem;
    border: 1px solid #e2e8f0;
    border-radius: 9px;
    font-size: 0.88rem;
    color: #1e293b;
    background: #fff;
}

.jor-field--wide .jor-time { width: 100%; }

.jor-check {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.85rem;
    color: #475569;
    margin-bottom: 0.6rem;
    cursor: pointer;
}

.jor-hint {
    font-size: 0.78rem;
    color: #94a3b8;
    margin: 0.5rem 0 0;
}

.jor-actions {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 0.5rem;
    margin-top: 0.6rem;
}

.jor-btn {
    border: 0;
    border-radius: 9px;
    padding: 0.45rem 0.95rem;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
}

.jor-btn--sm { padding: 0.3rem 0.7rem; font-size: 0.78rem; }
.jor-btn--ghost { background: #f1f5f9; color: #64748b; }
.jor-btn--primary { background: linear-gradient(135deg, #30ab0a, #279208); color: #fff; }
.jor-btn:disabled { opacity: 0.6; cursor: not-allowed; }

.jor-link {
    border: 0;
    background: none;
    color: #64748b;
    font-size: 0.82rem;
    text-decoration: underline;
    cursor: pointer;
    margin-right: auto;
}

.jor-exc-form {
    background: #f8fafc;
    border-radius: 10px;
    padding: 0.9rem;
    margin-bottom: 0.9rem;
}

.jor-empty {
    font-size: 0.85rem;
    color: #94a3b8;
    margin: 0;
}

.jor-exc-list {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
}

.jor-exc-list li {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    font-size: 0.84rem;
    color: #475569;
    padding: 0.45rem 0.6rem;
    border: 1px solid #e2e8f0;
    border-radius: 9px;
}

.jor-exc-list li.is-global { background: #f8fafc; }

.jor-exc__date { font-weight: 700; color: #1e293b; min-width: 120px; }
.jor-exc__what { min-width: 90px; }
.jor-exc__note { flex: 1; color: #94a3b8; }

.jor-exc__del {
    border: 0;
    background: none;
    color: #cbd5e1;
    cursor: pointer;
}

.jor-exc__del:hover { color: #ba2831; }

@media (max-width: 640px) {
    .jor-exc-list li { flex-wrap: wrap; }
    .jor-exc__date { min-width: 0; }
}
</style>
