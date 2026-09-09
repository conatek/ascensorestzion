<template>
    <!-- Pantalla de carga -->
    <div v-if="loading" class="card-loading">
        <div class="spinner"></div>
    </div>

    <!-- Error 404 -->
    <div v-else-if="notFound" class="card-not-found">
        <div class="nf-icon">🪪</div>
        <h2>Tarjeta no encontrada</h2>
        <p>Esta tarjeta no existe o no está disponible.</p>
    </div>

    <!-- Tarjeta con plantilla dinámica -->
    <div v-else class="public-card-wrapper" :style="cssVariablesStyle">
        <component
            :is="currentTemplateComponent"
            :customization="customization"
            :company="company"
            :card="card"
            :services="company.services || []"
            :products="company.products || []"
        />

        <!-- Flecha "hay más contenido abajo": por ahora solo en la plantilla Cristal -->
        <ScrollHint v-if="templateName === 'cristal'" />
    </div>
</template>

<script>
import publicCardService from '@/services/publicCardService.js';
import { defineAsyncComponent } from 'vue';
import ScrollHint from '@/components/shared/ScrollHint.vue';

// Carga perezosa: cada plantilla va a su propio chunk y no engorda el bundle
// principal (roza el limite de precache de la PWA, 2 MiB).
export default {
    name: 'CardPublic',

    components: {
        ScrollHint,
        TemplateModern: defineAsyncComponent(() => import('@/components/templates/TemplateModern.vue')),
        TemplateCristal: defineAsyncComponent(() => import('@/components/templates/TemplateCristal.vue')),
        TemplateCreative: defineAsyncComponent(() => import('@/components/templates/TemplateCreative.vue')),
        TemplateCyber: defineAsyncComponent(() => import('@/components/templates/TemplateCyber.vue')),
        TemplateVibrant: defineAsyncComponent(() => import('@/components/templates/TemplateVibrant.vue')),
    },

    data() {
        return {
            loading: true,
            notFound: false,
            card: {},
            company: {},
            templateName: 'modern',
            customization: {},
        };
    },

    computed: {
        currentTemplateComponent() {
            const templates = {
                modern: 'TemplateModern',
                cristal: 'TemplateCristal',
                creative: 'TemplateCreative',
                cyber: 'TemplateCyber',
                vibrant: 'TemplateVibrant',
            };
            return templates[this.templateName] || 'TemplateModern';
        },

        cssVariablesStyle() {
            const vars = {};
            for (const [sectionKey, section] of Object.entries(this.customization || {})) {
                if (typeof section !== 'object' || section === null) continue;
                for (const [fieldKey, value] of Object.entries(section)) {
                    const varName = `--${sectionKey}-${this.camelToKebab(fieldKey)}`;
                    vars[varName] = value;
                }
            }
            return vars;
        },
    },

    methods: {
        camelToKebab(str) {
            return str.replace(/([a-z])([A-Z])/g, '$1-$2').toLowerCase();
        },
    },

    async created() {
        const { cardSlug } = this.$route.params;
        try {
            const { data } = await publicCardService.cardBySlug(cardSlug);
            this.card = data.card;
            this.company = data.company;
            this.templateName = data.template?.name || 'modern';
            this.customization = data.template?.customization || {};
        } catch (err) {
            this.notFound = true;
        } finally {
            this.loading = false;
        }
    },
};
</script>

<style scoped>
.public-card-wrapper {
    min-height: 100vh;
    max-width: 480px;
    margin: 0 auto;
}

/* ---- Loading / Not found ---- */
.card-loading,
.card-not-found {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 100vh;
    gap: 12px;
    text-align: center;
    font-family: system-ui, sans-serif;
    color: #555;
    padding: 24px;
}

.spinner {
    width: 40px;
    height: 40px;
    border: 3px solid #e5e7eb;
    border-top-color: #3b82f6;
    border-radius: 50%;
    animation: spin 0.7s linear infinite;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

.nf-icon {
    font-size: 3rem;
}

.card-not-found h2 {
    margin: 0;
    font-size: 1.3rem;
}

.card-not-found p {
    margin: 0;
    font-size: 0.9rem;
}
</style>
