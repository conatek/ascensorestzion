<template>
    <!-- Teleport a body como el resto de overlays del proyecto; prefijo hol- para
         no rozar con las clases .modal-* de Bootstrap. -->
    <Teleport to="body">
    <div class="hol-overlay" @click.self="$emit('close')">
        <div class="hol-modal">
            <div class="hol-head">
                <h4 class="hol-title"><i class="fa fa-calendar-xmark me-2"></i> Días no laborables</h4>
                <button class="hol-close" :disabled="saving" @click="$emit('close')">
                    <i class="fa fa-times"></i>
                </button>
            </div>

            <div class="hol-body">
                <p class="hol-lead">
                    Festivos y cierres de la empresa. Aplican a <strong>todos los técnicos</strong>:
                    esos días no se puede programar y el portal no ofrece horarios.
                    Las vacaciones de una persona se ponen en su ficha.
                </p>

                <p v-if="error" class="hol-error">{{ error }}</p>

                <form class="hol-form" @submit.prevent="save">
                    <label class="hol-field">
                        <span>Desde</span>
                        <input type="date" v-model="form.date" class="hol-input" required>
                    </label>
                    <label class="hol-field">
                        <span>Hasta (opcional)</span>
                        <input type="date" v-model="form.date_end" class="hol-input">
                    </label>
                    <label class="hol-field hol-field--grow">
                        <span>Motivo</span>
                        <input type="text" v-model="form.note" class="hol-input" maxlength="255"
                               placeholder="Festivo, cierre por inventario…">
                    </label>
                    <button type="submit" class="hol-btn hol-btn--primary" :disabled="saving">
                        <i :class="saving ? 'fa fa-spinner fa-spin' : 'fa fa-plus'" class="me-1"></i>
                        Añadir
                    </button>
                </form>

                <p v-if="loading" class="hol-empty">
                    <i class="fa fa-spinner fa-spin me-1"></i> Cargando…
                </p>

                <p v-else-if="!holidays.length" class="hol-empty">
                    No hay días no laborables cargados de hoy en adelante.
                </p>

                <ul v-else class="hol-list">
                    <li v-for="item in holidays" :key="item.id">
                        <span class="hol-list__date">{{ formatDate(item.date) }}</span>
                        <span class="hol-list__note">{{ item.note || 'Sin motivo' }}</span>
                        <button class="hol-list__del" title="Quitar" @click="remove(item)">
                            <i class="fa fa-times"></i>
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    </Teleport>
</template>

<script>
import dayjs from '@/utils/dayjs.js';
import scheduleService from '@/services/scheduleService.js';

/**
 * Festivos y cierres de la empresa: las excepciones sin tecnico, que aplican a
 * todos. Vive en el tablero porque es cosa de coordinacion, no de una persona.
 */
export default {
    name: 'HolidaysModal',

    emits: ['close', 'changed'],

    data() {
        return {
            loading: true,
            saving: false,
            error: null,
            holidays: [],
            form: { date: '', date_end: '', note: '' },
        };
    },

    created() {
        this.load();
    },

    methods: {
        formatDate(value) {
            return dayjs(value).format('ddd D [de] MMMM YYYY');
        },

        async load() {
            this.loading = true;
            try {
                const { data } = await scheduleService.scheduleExceptions({ scope: 'generales' });
                this.holidays = data.exceptions || [];
            } catch (e) {
                this.error = 'No se pudieron cargar los días no laborables.';
            } finally {
                this.loading = false;
            }
        },

        async save() {
            this.saving = true;
            this.error = null;
            try {
                const { data } = await scheduleService.saveScheduleException({
                    user_id: null,
                    date: this.form.date,
                    date_end: this.form.date_end || null,
                    working_hours: null,
                    note: this.form.note || null,
                });

                this.form = { date: '', date_end: '', note: '' };
                await this.load();
                this.$emit('changed');
                this.warnAffected(data.affected_visits);
            } catch (e) {
                const bag = e.response?.data?.errors;
                this.error = bag
                    ? Object.values(bag).flat().join(' ')
                    : (e.response?.data?.message || 'No se pudo guardar.');
            } finally {
                this.saving = false;
            }
        },

        async remove(item) {
            const { isConfirmed } = await this.$swal.fire({
                icon: 'question',
                title: '¿Quitar este día?',
                text: `${this.formatDate(item.date)} · ${item.note || 'sin motivo'}`,
                showCancelButton: true,
                confirmButtonText: 'Quitar',
                cancelButtonText: 'Volver',
                confirmButtonColor: '#ba2831',
            });

            if (!isConfirmed) return;

            await scheduleService.deleteScheduleException(item.id);
            await this.load();
            this.$emit('changed');
        },

        /** Cerrar un día no cancela lo que ya estaba programado; hay que decirlo. */
        warnAffected(visits) {
            if (!visits?.length) return;

            const lista = visits
                .map(v => `${dayjs(v.scheduled_start).format('D MMM HH:mm')} · ${v.equipment_code} · ${v.technician_name}`)
                .join('<br>');

            this.$swal.fire({
                icon: 'warning',
                title: `Hay ${visits.length} visita(s) programadas esos días`,
                html: `${lista}<br><br>No se cancelaron. Muévelas o cancélalas desde el calendario.`,
                confirmButtonText: 'Entendido',
                confirmButtonColor: '#f59e0b',
            });
        },
    },
};
</script>

<style scoped>
.hol-overlay {
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

.hol-modal {
    width: 100%;
    max-width: 640px;
    max-height: 90vh;
    background: #fff;
    border-radius: 18px;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.hol-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem 1.25rem;
    border-bottom: 1px solid #f1f5f9;
}

.hol-title {
    margin: 0;
    font-size: 1rem;
    font-weight: 700;
    color: #1e293b;
}

.hol-title i { color: #f59e0b; }

.hol-close {
    border: 0;
    background: none;
    color: #94a3b8;
    cursor: pointer;
}

.hol-body {
    padding: 1.1rem 1.25rem;
    overflow-y: auto;
}

.hol-lead {
    margin: 0 0 0.9rem;
    font-size: 0.85rem;
    color: #64748b;
}

.hol-error {
    background: #fef2f2;
    border: 1px solid #fecaca;
    border-radius: 9px;
    padding: 0.6rem 0.8rem;
    color: #ba2831;
    font-size: 0.84rem;
    margin: 0 0 0.8rem;
}

.hol-form {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-end;
    gap: 0.7rem;
    background: #f8fafc;
    border-radius: 10px;
    padding: 0.85rem;
    margin-bottom: 1rem;
}

.hol-field {
    display: flex;
    flex-direction: column;
    gap: 0.2rem;
    font-size: 0.75rem;
    color: #64748b;
}

.hol-field--grow { flex: 1; min-width: 160px; }

.hol-input {
    padding: 0.45rem 0.65rem;
    border: 1px solid #e2e8f0;
    border-radius: 9px;
    font-size: 0.88rem;
    color: #1e293b;
    background: #fff;
    width: 100%;
}

.hol-btn {
    border: 0;
    border-radius: 9px;
    padding: 0.5rem 1rem;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
}

.hol-btn--primary { background: linear-gradient(135deg, #30ab0a, #279208); color: #fff; }
.hol-btn:disabled { opacity: 0.6; cursor: not-allowed; }

.hol-empty {
    font-size: 0.85rem;
    color: #94a3b8;
    margin: 0;
}

.hol-list {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
}

.hol-list li {
    display: flex;
    align-items: center;
    gap: 0.7rem;
    font-size: 0.86rem;
    color: #475569;
    padding: 0.5rem 0.7rem;
    border: 1px solid #fde68a;
    background: #fffbeb;
    border-radius: 9px;
}

.hol-list__date { font-weight: 700; color: #1e293b; }
.hol-list__note { flex: 1; color: #b45309; }

.hol-list__del {
    border: 0;
    background: none;
    color: #cbd5e1;
    cursor: pointer;
}

.hol-list__del:hover { color: #ba2831; }

@media (max-width: 640px) {
    .hol-list li { flex-wrap: wrap; }
}
</style>
