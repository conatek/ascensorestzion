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
    <div v-else class="public-card-wrapper">
        <component
            :is="currentTemplateComponent"
            :customization="customization"
            :company="company"
            :card="card"
            :services="company.services || []"
            :products="company.products || []"
        />
    </div>
</template>

<script>
import publicCardService from '@/services/publicCardService.js';
import TemplateModern from '@/components/templates/TemplateModern.vue';
import TemplateClassic from '@/components/templates/TemplateClassic.vue';
import TemplateMinimal from '@/components/templates/TemplateMinimal.vue';
import TemplateCreative from '@/components/templates/TemplateCreative.vue';
import TemplateCyber from '@/components/templates/TemplateCyber.vue';
import TemplateVibrant from '@/components/templates/TemplateVibrant.vue';
import TemplateAction from '@/components/templates/TemplateAction.vue';

export default {
    name: 'CardPublic',

    components: {
        TemplateModern,
        TemplateClassic,
        TemplateMinimal,
        TemplateCreative,
        TemplateCyber,
        TemplateVibrant,
        TemplateAction,
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
                classic: 'TemplateClassic',
                minimal: 'TemplateMinimal',
                creative: 'TemplateCreative',
                cyber: 'TemplateCyber',
                vibrant: 'TemplateVibrant',
                action: 'TemplateAction',
            };
            return templates[this.templateName] || 'TemplateModern';
        },
    },

    async created() {
        const { companySlug, cardSlug } = this.$route.params;
        try {
            const { data } = await publicCardService.card(companySlug, cardSlug);
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
