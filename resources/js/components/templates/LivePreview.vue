<template>
    <div class="live-preview" :style="cssVariablesStyle">
        <component
            :is="currentTemplateComponent"
            :customization="customization"
            :company="company"
            :card="sampleCard"
            :services="realServices"
            :products="realProducts"
        />
    </div>
</template>

<script>
import { defineAsyncComponent } from 'vue'

// Carga perezosa: cada plantilla en su propio chunk, para no engordar el bundle
// principal (roza el limite de precache de la PWA, 2 MiB).
export default {
    name: 'LivePreview',

    components: {
        TemplateModern: defineAsyncComponent(() => import('./TemplateModern.vue')),
        TemplateCristal: defineAsyncComponent(() => import('./TemplateCristal.vue')),
        TemplateCreative: defineAsyncComponent(() => import('./TemplateCreative.vue')),
        TemplateCyber: defineAsyncComponent(() => import('./TemplateCyber.vue')),
        TemplateVibrant: defineAsyncComponent(() => import('./TemplateVibrant.vue')),
    },

    props: {
        customization: {
            type: Object,
            required: true,
        },
        templateName: {
            type: String,
            default: 'modern',
        },
        company: {
            type: Object,
            default: () => ({
                name: 'Mi Empresa',
                logo_path: null,
                slug: 'mi-empresa',
            }),
        },
        sampleCard: {
            type: Object,
            default: () => ({
                first_name: 'Juan',
                last_name: 'Pérez',
                job_title: 'Director Comercial',
                email: 'juan@empresa.com',
                mobile_phone: '+52 55 1234 5678',
                whatsapp: '5551234567',
                description: 'Profesional con más de 10 años de experiencia.',
                photo_path: null,
            }),
        },
        services: {
            type: Array,
            default: () => [],
        },
        products: {
            type: Array,
            default: () => [],
        },
    },

    data() {
        return {
            sampleServices: [
                { id: 1, name: 'Consultoría', description: 'Asesoría profesional para tu negocio.', image_path: null },
                { id: 2, name: 'Desarrollo', description: 'Soluciones tecnológicas a medida.', image_path: null },
            ],
            sampleProducts: [
                { id: 1, name: 'Producto Premium', description: 'Nuestro producto estrella.', price: 199.99, discount: 149.99, comment: 'Oferta especial', image_path: null },
                { id: 2, name: 'Producto Básico', description: 'Ideal para comenzar.', price: 49.99, discount: null, comment: null, image_path: null },
            ],
        }
    },

    computed: {
        realServices() {
            return this.services.filter(s => s.is_active !== false)
        },
        realProducts() {
            return this.products.filter(p => p.is_active !== false)
        },
        currentTemplateComponent() {
            const templates = {
                modern: 'TemplateModern',
                cristal: 'TemplateCristal',
                creative: 'TemplateCreative',
                cyber: 'TemplateCyber',
                vibrant: 'TemplateVibrant',
            }
            return templates[this.templateName] || 'TemplateModern'
        },

        /**
         * Convierte el objeto customization a CSS Variables
         * Esta es la magia del Live Preview reactivo
         */
        cssVariablesStyle() {
            const vars = {}

            for (const [sectionKey, section] of Object.entries(this.customization || {})) {
                if (typeof section !== 'object' || section === null) continue

                for (const [fieldKey, value] of Object.entries(section)) {
                    // Crear nombre de variable CSS: --section-field
                    const varName = `--${sectionKey}-${this.camelToKebab(fieldKey)}`
                    vars[varName] = value
                }
            }

            return vars
        },
    },

    methods: {
        camelToKebab(str) {
            return str.replace(/([a-z])([A-Z])/g, '$1-$2').toLowerCase()
        },
    },
}
</script>

<style scoped>
.live-preview {
    width: 100%;
    height: fit-content;
    background: var(--general-color-fondo, #ffffff);
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}
</style>
