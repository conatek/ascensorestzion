<template>
    <div class="card-template minimal" :style="containerStyle">

        <main class="minimal-wrapper" :style="wrapperStyle">

            <header class="minimal-header" v-if="company?.logo_path || company?.name">
                <img
                    v-if="company?.logo_path"
                    :src="company.logo_path"
                    :alt="company.name"
                    class="minimal-logo"
                >
                <span v-else :style="companyTextStyle">{{ company.name }}</span>
            </header>

            <section class="profile-section">
                <div class="avatar-box" v-if="showAvatar">
                    <img
                        v-if="card?.photo_path"
                        :src="card.photo_path"
                        :style="avatarStyle"
                        :alt="card?.first_name"
                    >
                    <div v-else class="avatar-fallback" :style="avatarStyle">
                        <i class="bi bi-person"></i>
                    </div>
                </div>

                <div class="title-group">
                    <h1 :style="nameStyle">{{ card?.first_name }} {{ card?.last_name }}</h1>
                    <h2 :style="jobTitleStyle">{{ card?.job_title }}</h2>
                </div>

                <p v-if="card?.description" :style="bioStyle">
                    {{ card.description }}
                </p>
            </section>

            <section class="quick-contact" :style="flexAlignStyle">
                <a v-if="card?.mobile_phone" :href="`tel:${card.mobile_phone}`" class="action-item" :style="actionItemStyle">
                    <span :style="iconWrapperStyle"><i class="bi bi-telephone"></i></span>
                    <span :style="actionTextStyle">Llamar</span>
                </a>
                <a v-if="card?.email" :href="`mailto:${card.email}`" class="action-item" :style="actionItemStyle">
                    <span :style="iconWrapperStyle"><i class="bi bi-envelope"></i></span>
                    <span :style="actionTextStyle">Email</span>
                </a>
                <a v-if="card?.whatsapp" :href="whatsappLink" class="action-item" :style="actionItemStyle">
                    <span :style="iconWrapperStyle"><i class="bi bi-whatsapp"></i></span>
                    <span :style="actionTextStyle">WhatsApp</span>
                </a>
            </section>

            <div :style="separatorStyle"></div>

            <section class="minimal-list-section" v-if="services?.length">
                <h3 :style="sectionTitleStyle">Servicios</h3>
                <div class="list-container">
                    <div v-for="service in services" :key="`srv-${service.id}`" :style="listItemStyle">
                        <h4 :style="itemTitleStyle">{{ service.name }}</h4>
                        <p v-if="service.description" :style="itemDescStyle">{{ service.description }}</p>
                    </div>
                </div>
            </section>

            <section class="minimal-list-section" v-if="products?.length">
                <h3 :style="sectionTitleStyle">Portafolio</h3>
                <div class="list-container">
                    <div v-for="product in products" :key="`prd-${product.id}`" :style="listItemStyle">
                        <div class="item-header">
                            <h4 :style="itemTitleStyle">{{ product.name }}</h4>
                            <span v-if="product.price" :style="priceStyle">
                                ${{ product.discount || product.price }}
                            </span>
                        </div>
                        <p v-if="product.description" :style="itemDescStyle">{{ product.description }}</p>
                    </div>
                </div>
            </section>

            <footer class="social-footer" :style="flexAlignStyle">
                <a v-if="company?.web" :href="company.web" target="_blank" class="social-icon" :style="getSocialBtnStyle('web')">
                    <i class="bi bi-globe"></i>
                </a>
                <a v-if="card?.linkedin" :href="card.linkedin" target="_blank" class="social-icon" :style="getSocialBtnStyle('linkedin')">
                    <i class="bi bi-linkedin"></i>
                </a>
                <a v-if="company?.instagram" :href="company.instagram" target="_blank" class="social-icon" :style="getSocialBtnStyle('instagram')">
                    <i class="bi bi-instagram"></i>
                </a>
                <a v-if="company?.facebook" :href="company.facebook" target="_blank" class="social-icon" :style="getSocialBtnStyle('facebook')">
                    <i class="bi bi-facebook"></i>
                </a>
            </footer>

        </main>
    </div>
</template>

<script>
export default {
    name: 'TemplateMinimal',

    props: {
        customization: { type: Object, default: () => ({}) },
        company: { type: Object, default: () => ({}) },
        card: { type: Object, default: () => ({}) },
        services: { type: Array, default: () => [] },
        products: { type: Array, default: () => [] }
    },

    computed: {
        whatsappLink() {
            if (!this.card?.whatsapp) return '#'
            const phone = this.card.whatsapp.replace(/\D/g, '')
            return `https://api.whatsapp.com/send?phone=${phone}`
        },

        // --- ESTILOS COMPUTADOS ---

        containerStyle() {
            const gen = this.customization?.general || {}
            return {
                backgroundColor: gen.colorFondo || '#ffffff',
                fontFamily: `'${gen.tipografia || 'Inter'}', sans-serif`,
                color: gen.colorTexto || '#1a1a1a',
            }
        },

        wrapperStyle() {
            const align = this.customization?.general?.alineacion || 'left'
            return {
                textAlign: align,
                padding: '2.5rem 2rem',
                maxWidth: '500px',
                margin: '0 auto',
            }
        },

        flexAlignStyle() {
            const align = this.customization?.general?.alineacion || 'left'
            return {
                justifyContent: align === 'center' ? 'center' : 'flex-start',
            }
        },

        companyTextStyle() {
            return {
                fontSize: '0.8rem',
                letterSpacing: '1px',
                textTransform: 'uppercase',
                color: this.customization?.general?.colorAcento || '#888888',
                fontWeight: '600',
            }
        },

        showAvatar() {
            return this.customization?.avatar?.mostrar !== false
        },

        avatarStyle() {
            const avatar = this.customization?.avatar || {}
            const tamano = avatar.tamano ?? 100
            const enGrises = avatar.escalaGrises !== false

            return {
                width: `${tamano}px`,
                height: `${tamano}px`,
                borderRadius: `${avatar.radio ?? 50}%`,
                filter: enGrises ? 'grayscale(100%)' : 'none',
                objectFit: 'cover',
                backgroundColor: '#f5f5f5', // Fondo suave para el placeholder
                border: 'none',
            }
        },

        nameStyle() {
            const gen = this.customization?.general || {}
            return {
                fontSize: '1.7rem',
                fontWeight: '400', // Minimalista suele usar pesos ligeros o regulares
                letterSpacing: '-0.5px',
                color: gen.colorTexto || '#1a1a1a',
                margin: '1.5rem 0 0.2rem 0',
            }
        },

        jobTitleStyle() {
            return {
                fontSize: '0.9rem',
                color: this.customization?.general?.colorAcento || '#888888',
                fontWeight: '400',
                margin: '0 0 1.5rem 0',
            }
        },

        bioStyle() {
            return {
                fontSize: '0.95rem',
                lineHeight: '1.7',
                color: this.customization?.general?.colorTexto || '#1a1a1a',
                opacity: '0.8',
                marginBottom: '2rem',
            }
        },

        actionItemStyle() {
            return {
                textDecoration: 'none',
                display: 'flex',
                alignItems: 'center',
                gap: '8px',
            }
        },

        iconWrapperStyle() {
            const color = this.customization?.general?.colorTexto || '#1a1a1a'
            return {
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                width: '36px',
                height: '36px',
                borderRadius: '50%',
                border: `1px solid ${color}20`, // Borde muy sutil
                color: color,
                fontSize: '1.1rem',
            }
        },

        actionTextStyle() {
            return {
                fontSize: '0.85rem',
                fontWeight: '500',
                color: this.customization?.general?.colorTexto || '#1a1a1a',
            }
        },

        separatorStyle() {
            const acento = this.customization?.general?.colorAcento || '#888888'
            return {
                height: '1px',
                backgroundColor: `${acento}30`, // Transparencia del 30%
                margin: '2.5rem 0',
                width: '100%',
            }
        },

        sectionTitleStyle() {
            return {
                fontSize: '0.75rem',
                textTransform: 'uppercase',
                letterSpacing: '2px',
                color: this.customization?.general?.colorAcento || '#888888',
                fontWeight: '600',
                marginBottom: '1.5rem',
            }
        },

        listItemStyle() {
            return {
                marginBottom: '1.5rem',
            }
        },

        itemTitleStyle() {
            return {
                fontSize: '1rem',
                fontWeight: '500',
                color: this.customization?.general?.colorTexto || '#1a1a1a',
                margin: '0 0 0.3rem 0',
            }
        },

        itemDescStyle() {
            return {
                fontSize: '0.85rem',
                lineHeight: '1.5',
                color: this.customization?.general?.colorAcento || '#888888',
                margin: '0',
            }
        },

        priceStyle() {
            return {
                fontSize: '0.9rem',
                fontWeight: '500',
                color: this.customization?.general?.colorTexto || '#1a1a1a',
            }
        }
    },

    methods: {
        getSocialBtnStyle(type) {
            const config = this.customization?.botones || {}
            const texto = this.customization?.general?.colorTexto || '#1a1a1a'
            const estilo = config.estilo || 'fantasma'

            const base = {
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                width: '40px',
                height: '40px',
                borderRadius: '50%',
                transition: 'opacity 0.2s ease',
                textDecoration: 'none',
                fontSize: '1.1rem',
            }

            if (estilo === 'solido') {
                return {
                    ...base,
                    backgroundColor: texto,
                    color: this.customization?.general?.colorFondo || '#ffffff',
                }
            } else if (estilo === 'contorno') {
                return {
                    ...base,
                    backgroundColor: 'transparent',
                    color: texto,
                    border: `1px solid ${texto}40`,
                }
            } else {
                // fantasma (ghost)
                return {
                    ...base,
                    backgroundColor: 'transparent',
                    color: texto,
                    border: 'none',
                }
            }
        }
    }
}
</script>

<style scoped>
/*
 * ESTILOS ESTÁTICOS
 * Únicamente maquetación estructural y comportamientos no ligados a colores/fuentes.
 */
.card-template.minimal {
    min-height: 100vh;
    box-sizing: border-box;
}

.minimal-wrapper {
    box-sizing: border-box;
}

.minimal-header {
    margin-bottom: 3rem;
    text-align: center;
}

.minimal-logo {
    height: 30px;
    width: auto;
    display: inline-block;
}

.profile-section {
    margin-bottom: 2rem;
}

.avatar-box {
    display: flex;
    justify-content: center;
}

.avatar-fallback {
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
    color: #999;
}

.quick-contact {
    display: flex;
    gap: 1.5rem;
    flex-wrap: wrap;
}

.action-item:hover {
    opacity: 0.7;
}

.minimal-list-section {
    margin-bottom: 3rem;
}

.list-container {
    display: flex;
    flex-direction: column;
}

.item-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    margin-bottom: 0.3rem;
}

.social-footer {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
    margin-top: 4rem;
}

.social-icon:hover {
    opacity: 0.6;
}
</style>
