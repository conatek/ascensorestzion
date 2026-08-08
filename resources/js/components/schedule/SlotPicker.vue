<template>
    <div class="slot-picker">
        <p v-if="loading" class="slot-loading">
            <i class="fa fa-spinner fa-spin"></i> Buscando espacios libres…
        </p>

        <template v-else-if="hasAnySlot">
            <!-- Tira de días. En móvil son treinta, así que scrollea de lado. -->
            <div class="slot-days">
                <button
                    v-for="day in days"
                    :key="day.date"
                    type="button"
                    class="slot-day"
                    :class="{
                        'is-active': day.date === activeDate,
                        'is-disabled': !day.slot_count,
                    }"
                    :disabled="!day.slot_count"
                    @click="activeDate = day.date"
                >
                    <span class="slot-day__weekday">{{ weekday(day.date) }}</span>
                    <span class="slot-day__number">{{ dayNumber(day.date) }}</span>
                    <span class="slot-day__month">{{ month(day.date) }}</span>
                    <span class="slot-day__count">{{ day.slot_count || '—' }}</span>
                </button>
            </div>

            <!-- Horarios del día elegido -->
            <div v-if="activeSlots.length" class="slot-chips">
                <button
                    v-for="slot in activeSlots"
                    :key="slot.value"
                    type="button"
                    class="slot-chip"
                    :class="{ 'is-active': slot.value === modelValue }"
                    @click="$emit('update:modelValue', slot.value)"
                >
                    <strong>{{ slot.label }}</strong>
                    <span class="slot-chip__end">a {{ slot.end_label }}</span>
                </button>
            </div>

            <p v-else class="slot-empty">{{ emptyText }}</p>
        </template>

        <p v-else class="slot-empty slot-empty--all">
            No encontramos espacios libres en el próximo mes.
            Escríbenos y coordinamos una fecha contigo.
        </p>
    </div>
</template>

<script>
import dayjs from '@/utils/dayjs.js';

/**
 * Chips de horarios libres. Componente tonto: todas las horas vienen ya
 * formateadas del backend y aqui no se calcula ninguna — asi el navegador de un
 * cliente en otra zona horaria no puede desplazarlas. dayjs solo formatea la
 * fecha del dia, que es una cadena YYYY-MM-DD y se parsea como medianoche local.
 *
 * Estilos propios con prefijo slot-: los de la vista que lo aloja son scoped y no
 * le llegan.
 */
export default {
    name: 'SlotPicker',

    props: {
        days: { type: Array, default: () => [] },
        slots: { type: Object, default: () => ({}) },
        modelValue: { type: String, default: '' },
        loading: { type: Boolean, default: false },
    },

    emits: ['update:modelValue'],

    data() {
        return { activeDate: '' };
    },

    computed: {
        hasAnySlot() {
            return this.days.some((day) => day.slot_count > 0);
        },

        activeSlots() {
            return this.slots[this.activeDate] || [];
        },

        activeDay() {
            return this.days.find((day) => day.date === this.activeDate);
        },

        emptyText() {
            // Un festivo se explica por su nombre; el resto, con un texto fijo.
            if (this.activeDay?.reason === 'excepcion') {
                return this.activeDay.exception_note
                    ? `Ese día no se trabaja (${this.activeDay.exception_note}).`
                    : 'Ese día no se trabaja.';
            }

            const reasons = {
                no_laborable: 'Tu técnico no trabaja ese día.',
                agenda_llena: 'Tu técnico ya tiene la agenda llena ese día.',
                antelacion: 'Ese día está dentro del plazo mínimo de aviso.',
                no_cabe: 'La visita no cabe en la jornada de ese día.',
            };

            return reasons[this.activeDay?.reason] || 'No hay espacios libres ese día.';
        },
    },

    watch: {
        // Al cargar (o recargar tras un choque) se abre el primer día con hueco:
        // dejar la tira sin selección obliga a un clic que no aporta nada.
        days: {
            immediate: true,
            handler() {
                if (this.activeDate && this.slots[this.activeDate]?.length) return;
                this.activeDate = this.days.find((day) => day.slot_count > 0)?.date || '';
            },
        },
    },

    methods: {
        weekday(date) {
            return dayjs(date).format('ddd').toUpperCase();
        },
        dayNumber(date) {
            return dayjs(date).format('D');
        },
        month(date) {
            return dayjs(date).format('MMM').toUpperCase();
        },
    },
};
</script>

<style scoped>
.slot-loading {
    color: #64748b;
    font-size: 0.88rem;
    margin: 0;
    padding: 1.5rem 0;
    text-align: center;
}

.slot-days {
    display: flex;
    gap: 0.5rem;
    overflow-x: auto;
    padding-bottom: 0.5rem;
    scroll-snap-type: x proximity;
}

.slot-day {
    flex: 0 0 auto;
    scroll-snap-align: start;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.1rem;
    min-width: 62px;
    padding: 0.5rem 0.4rem;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    background: #fff;
    cursor: pointer;
}

.slot-day__weekday,
.slot-day__month {
    font-size: 0.62rem;
    font-weight: 700;
    letter-spacing: 0.04em;
    color: #94a3b8;
}

.slot-day__number {
    font-size: 1.15rem;
    font-weight: 700;
    color: #1e293b;
    line-height: 1.1;
}

.slot-day__count {
    margin-top: 0.15rem;
    font-size: 0.62rem;
    font-weight: 700;
    color: #30ab0a;
}

.slot-day.is-active {
    border-color: #30ab0a;
    background: #eefbe9;
}

.slot-day.is-disabled {
    opacity: 0.45;
    cursor: not-allowed;
}

.slot-day.is-disabled .slot-day__count {
    color: #94a3b8;
}

.slot-chips {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-top: 0.9rem;
}

.slot-chip {
    display: inline-flex;
    align-items: baseline;
    gap: 0.35rem;
    padding: 0.5rem 0.85rem;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    background: #fff;
    font-size: 0.9rem;
    color: #1e293b;
    cursor: pointer;
}

.slot-chip__end {
    font-size: 0.72rem;
    color: #94a3b8;
}

.slot-chip.is-active {
    border-color: #30ab0a;
    background: #30ab0a;
    color: #fff;
}

.slot-chip.is-active .slot-chip__end {
    color: rgba(255, 255, 255, 0.85);
}

.slot-empty {
    margin: 1rem 0 0;
    padding: 0.75rem 0.9rem;
    background: #fffbeb;
    border: 1px solid #fde68a;
    border-radius: 10px;
    font-size: 0.85rem;
    color: #b45309;
}

.slot-empty--all {
    margin-top: 0;
}
</style>
