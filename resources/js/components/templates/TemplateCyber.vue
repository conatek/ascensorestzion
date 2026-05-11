<template>
    <div class="card-template cyber" :style="containerStyle">

        <main class="cyber-box" :style="neonBoxStyle">

            <header class="terminal-header" :style="terminalHeaderStyle">
                <span class="dot close"></span>
                <span class="dot min"></span>
                <span class="dot max"></span>
                <span class="term-title">usr@{{ company?.slug || 'empresa' }}:~</span>
            </header>

            <div class="cyber-content">
                <div class="avatar-container" v-if="showAvatar">
                    <img
                        v-if="card?.photo_path"
                        :src="card.photo_path"
                        :style="avatarStyle"
                        :alt="card?.first_name"
                    >
                    <div v-else class="avatar-placeholder" :style="avatarStyle">
                        <i class="bi bi-person-bounding-box"></i>
                    </div>
                </div>

                <h1 :style="nameStyle">> {{ card?.first_name }} <span class="last-name">{{ card?.last_name }}</span><span class="cursor" :style="cursorStyle">_</span></h1>
                <h2 :style="jobTitleStyle">{{ card?.job_title }}</h2>
                <p v-if="card?.description" :style="bioStyle">{{ card.description }}</p>

                <div class="social-grid">
                    <a v-if="card?.mobile_phone" :href="`tel:${card.mobile_phone}`" class="social-btn" :style="getSocialBtnStyle('phone')">
                        <i class="bi bi-telephone"></i>
                    </a>
                    <a v-if="card?.whatsapp" :href="whatsappLink" class="social-btn" :style="getSocialBtnStyle('whatsapp')">
                        <i class="bi bi-whatsapp"></i>
                    </a>
                    <a v-if="card?.email" :href="`mailto:${card.email}`" class="social-btn" :style="getSocialBtnStyle('email')">
                        <i class="bi bi-envelope"></i>
                    </a>
                    <a v-if="company?.web" :href="company.web" target="_blank" class="social-btn" :style="getSocialBtnStyle('web')">
                        <i class="bi bi-globe"></i>
                    </a>
                    <a v-if="card?.linkedin" :href="card.linkedin" target="_blank" class="social-btn" :style="getSocialBtnStyle('linkedin')">
                        <i class="bi bi-linkedin"></i>
                    </a>
                </div>

                <div class="data-grid" v-if="services?.length">
                    <h3 :style="sectionTitleStyle">/// SERVICIOS</h3>
                    <div v-for="service in services" :key="service.id" class="data-row" :style="dataRowStyle">
                        <span class="data-key">[{{ service.id }}]</span>
                        <span class="data-val">{{ service.name }}</span>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>

<script>
export default {
    name: 'TemplateCyber',

    props: {
        customization: { type: Object, default: () => ({}) },
        company: { type: Object, default: () => ({}) },
        card: { type: Object, default: () => ({}) },
        services: { type: Array, default: () => [] },
        products: { type: Array, default: () => [] }
    },

    computed: {
        // Enlace de WhatsApp
        whatsappLink() {
            if (!this.card?.whatsapp) return '#'
            const phone = this.card.whatsapp.replace(/\D/g, '')
            return `https://api.whatsapp.com/send?phone=${phone}`
        },

        // --- ESTILOS COMPUTADOS ---

        containerStyle() {
            const gen = this.customization?.general || {}
            return {
                backgroundColor: gen.colorFondo || '#0a0a1a',
                fontFamily: `'${gen.fuentePrincipal || 'Space Mono'}', monospace`,
                color: gen.colorTexto || '#e2e8f0',
            }
        },

        neonBoxStyle() {
            const neon = this.customization?.neon || {}
            const colorAcento = neon.colorAcento || '#00ffcc'
            const brillo = neon.brillo ?? 10

            return {
                border: `1px solid ${colorAcento}`,
                boxShadow: `0 0 ${brillo}px ${colorAcento}40, inset 0 0 ${brillo/2}px ${colorAcento}20`,
                backgroundColor: 'rgba(0, 0, 0, 0.4)',
            }
        },

        terminalHeaderStyle() {
            const neon = this.customization?.neon || {}
            return {
                backgroundColor: 'rgba(255, 255, 255, 0.05)',
                borderBottom: `1px solid ${neon.colorAcento || '#00ffcc'}50`,
            }
        },

        showAvatar() {
            return this.customization?.avatar?.mostrar !== false
        },

        avatarStyle() {
            const avatar = this.customization?.avatar || {}
            const neon = this.customization?.neon || {}
            const tamano = avatar.tamano ?? 130
            const acento = neon.colorAcento || '#00ffcc'
            const brillo = neon.brillo ?? 10

            return {
                width: `${tamano}px`,
                height: `${tamano}px`,
                borderRadius: `${avatar.radio ?? 15}%`,
                border: `2px solid ${acento}`,
                boxShadow: `0 0 ${brillo}px ${acento}60`,
            }
        },

        nameStyle() {
            const neon = this.customization?.neon || {}
            return {
                color: neon.colorAcento || '#00ffcc',
                fontSize: '1.4rem',
                fontWeight: '700',
                marginBottom: '0.2rem',
                textShadow: `0 0 8px ${neon.colorAcento || '#00ffcc'}80`,
            }
        },

        cursorStyle() {
            const gen = this.customization?.general || {}
            return { color: gen.colorTexto || '#e2e8f0' }
        },

        jobTitleStyle() {
            const gen = this.customization?.general || {}
            return {
                color: gen.colorTexto || '#e2e8f0',
                fontSize: '0.9rem',
                opacity: '0.8',
                marginBottom: '1rem',
                textTransform: 'uppercase',
                letterSpacing: '1px',
            }
        },

        bioStyle() {
            const gen = this.customization?.general || {}
            return {
                color: gen.colorTexto || '#e2e8f0',
                fontSize: '0.85rem',
                lineHeight: '1.6',
                borderLeft: `2px dashed ${this.customization?.neon?.colorAcento || '#00ffcc'}50`,
                paddingLeft: '10px',
                textAlign: 'left',
                opacity: '0.9',
            }
        },

        sectionTitleStyle() {
            const neon = this.customization?.neon || {}
            return {
                color: neon.colorAcento || '#00ffcc',
                fontSize: '1rem',
                borderBottom: `1px solid ${neon.colorAcento || '#00ffcc'}`,
                paddingBottom: '5px',
                marginBottom: '10px',
                textAlign: 'left',
            }
        },

        dataRowStyle() {
            return {
                borderBottom: '1px solid rgba(255,255,255,0.1)',
                padding: '8px 0',
            }
        }
    },

    methods: {
        // Método para botones sociales según requerimiento de la guía
        getSocialBtnStyle(type) {
            const config = this.customization?.botones || {}
            const neon = this.customization?.neon || {}
            const acento = neon.colorAcento || '#00ffcc'
            const estilo = config.estiloBoton || 'solido'

            // Colores por defecto de las marcas
            const mapping = {
                whatsapp: '#25d366',
                phone: '#34b7f1',
                email: '#ea4335',
                web: '#4285f4',
                linkedin: '#0077b5',
            }
            const colorMarca = mapping[type] || acento

            const baseStyle = {
                borderRadius: `${config.radio ?? 8}%`,
                transition: 'all 0.3s ease',
            }

            if (estilo === 'solido') {
                return {
                    ...baseStyle,
                    backgroundColor: colorMarca,
                    color: '#ffffff',
                    border: `1px solid ${colorMarca}`,
                    boxShadow: `0 0 10px ${colorMarca}50`,
                }
            } else {
                return {
                    ...baseStyle,
                    backgroundColor: 'transparent',
                    color: colorMarca,
                    border: `1px solid ${colorMarca}`,
                    boxShadow: `inset 0 0 5px ${colorMarca}30`,
                }
            }
        }
    }
}
</script>

<style scoped>
/*
 * ESTILOS ESTÁTICOS
 * Layout estructural, flexbox, grid, keyframes y pseudo-clases.
 */
.card-template.cyber {
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: flex-start;
    padding: 2rem 1rem;
    box-sizing: border-box;
}

.cyber-box {
    width: 100%;
    max-width: 400px;
    border-radius: 6px;
    overflow: hidden;
    position: relative;
}

.terminal-header {
    display: flex;
    align-items: center;
    padding: 8px 12px;
    gap: 6px;
}

.dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
}
.dot.close { background-color: #ff5f56; }
.dot.min { background-color: #ffbd2e; }
.dot.max { background-color: #27c93f; }

.term-title {
    margin-left: 10px;
    font-size: 0.75rem;
    opacity: 0.7;
}

.cyber-content {
    padding: 1.5rem;
    text-align: center;
}

.avatar-container {
    margin-bottom: 1.5rem;
    display: flex;
    justify-content: center;
}

.avatar-container img {
    object-fit: cover;
    display: block;
}

.avatar-placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3rem;
    background-color: rgba(255, 255, 255, 0.05);
}

.last-name {
    font-weight: 400;
}

/* Animación del cursor de la terminal */
.cursor {
    animation: blink 1s step-end infinite;
}
@keyframes blink {
    0%, 100% { opacity: 1; }
    50% { opacity: 0; }
}

.social-grid {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 12px;
    margin: 1.5rem 0;
}

.social-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 45px;
    height: 45px;
    font-size: 1.2rem;
    text-decoration: none;
    cursor: pointer;
}

.social-btn:hover {
    transform: translateY(-3px);
    filter: brightness(1.2);
}

.data-grid {
    margin-top: 2rem;
}

.data-row {
    display: flex;
    justify-content: flex-start;
    align-items: center;
    gap: 10px;
    font-size: 0.85rem;
    text-align: left;
}

.data-key {
    opacity: 0.6;
}

.data-val {
    flex-grow: 1;
}
</style>
