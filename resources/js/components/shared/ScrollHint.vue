<template>
    <Transition name="sh-fade">
        <button
            v-if="visible"
            type="button"
            class="sh-hint"
            aria-label="Ver más contenido"
            @click="scrollDown"
        >
            <svg class="sh-arrow" viewBox="0 0 56 40" aria-hidden="true">
                <defs>
                    <linearGradient id="sh-arrow-grad" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stop-color="#ffffff" stop-opacity="0.95" />
                        <stop offset="100%" stop-color="#ffffff" stop-opacity="0.4" />
                    </linearGradient>
                </defs>
                <!-- Trazo inferior difuminado: hace de contorno y sombra a la vez,
                     para que la flecha se lea también sobre plantillas claras. -->
                <path class="sh-arrow-halo" d="M8 11 28 30 48 11" />
                <path
                    class="sh-arrow-main"
                    d="M8 11 28 30 48 11"
                    stroke="url(#sh-arrow-grad)"
                />
            </svg>
        </button>
    </Transition>
</template>

<script>
/**
 * Indicador flotante de "hay más contenido abajo".
 *
 * Es agnóstico a la plantilla: se monta una sola vez sobre la tarjeta pública y
 * observa el scroll de la ventana. Se oculta solo cuando la página no tiene
 * scroll o cuando el visitante ya llegó al final.
 */
export default {
    name: 'ScrollHint',

    props: {
        // Margen en px para considerar que ya se llegó al final. Evita que el
        // indicador parpadee por diferencias de redondeo o por el rebote táctil.
        threshold: {
            type: Number,
            default: 48,
        },
    },

    data() {
        return {
            visible: false,
            observer: null,
            timers: [],
        };
    },

    mounted() {
        this.update();

        window.addEventListener('scroll', this.update, { passive: true });
        window.addEventListener('resize', this.update);

        // Las plantillas cargan imágenes remotas (logos, portadas, productos),
        // así que la altura del documento cambia después del montaje.
        if (window.ResizeObserver) {
            this.observer = new ResizeObserver(this.update);
            this.observer.observe(document.body);
        } else {
            [300, 1000, 2500].forEach((ms) => {
                this.timers.push(window.setTimeout(this.update, ms));
            });
        }
    },

    beforeUnmount() {
        window.removeEventListener('scroll', this.update);
        window.removeEventListener('resize', this.update);
        if (this.observer) this.observer.disconnect();
        this.timers.forEach((t) => window.clearTimeout(t));
    },

    methods: {
        update() {
            const el = document.scrollingElement || document.documentElement;
            const remaining = el.scrollHeight - el.clientHeight - el.scrollTop;
            const scrollable = el.scrollHeight - el.clientHeight > this.threshold;

            this.visible = scrollable && remaining > this.threshold;
        },

        scrollDown() {
            const el = document.scrollingElement || document.documentElement;
            window.scrollBy({ top: el.clientHeight * 0.8, behavior: 'smooth' });
        },
    },
};
</script>

<style scoped>
.sh-hint {
    position: fixed;
    left: 50%;
    bottom: calc(18px + env(safe-area-inset-bottom, 0px));
    transform: translateX(-50%);
    z-index: 900;

    display: flex;
    align-items: center;
    justify-content: center;
    width: 60px;
    height: 46px;
    padding: 0;
    border: none;
    background: none;
    cursor: pointer;

    animation: sh-float 2.2s ease-in-out infinite;
}

.sh-arrow {
    width: 56px;
    height: 40px;
    fill: none;
    stroke-linecap: round;
    stroke-linejoin: round;
    overflow: visible;
}

.sh-arrow-main {
    stroke-width: 7;
}

/* Copia difuminada del mismo trazo: da volumen y garantiza contraste sobre
   fondos claros, donde una flecha blanca sola desaparecería. */
.sh-arrow-halo {
    stroke: rgba(15, 23, 42, 0.5);
    stroke-width: 12;
    filter: blur(2.5px);
}

.sh-hint:active {
    animation: none;
    transform: translateX(-50%) scale(0.94);
}

@keyframes sh-float {
    0%,
    100% {
        transform: translateX(-50%) translateY(0);
    }
    50% {
        transform: translateX(-50%) translateY(5px);
    }
}

/* ---- Aparición / desaparición ---- */
.sh-fade-enter-active,
.sh-fade-leave-active {
    transition: opacity 0.28s ease;
}

.sh-fade-enter-from,
.sh-fade-leave-to {
    opacity: 0;
}

@media (prefers-reduced-motion: reduce) {
    .sh-hint {
        animation: none;
    }
}
</style>
