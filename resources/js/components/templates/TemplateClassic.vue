<template>
    <div class="card-template classic" :style="containerStyle">

        <div class="classic-border" :style="borderOuterStyle">

            <header class="classic-header" :style="alignmentStyle">
                <img
                    v-if="company?.logo_path"
                    :src="company.logo_path"
                    :alt="company.name"
                    class="company-logo"
                >
                <h4 v-else :style="companyNameStyle">{{ company?.name || 'Nombre de la Firma' }}</h4>

                <hr class="elegant-divider" :style="dividerStyle">
            </header>

            <main class="classic-main" :style="alignmentStyle">

                <div class="portrait-container" v-if="showAvatar">
                    <img
                        v-if="card?.photo_path"
                        :src="card.photo_path"
                        :style="avatarStyle"
                        :alt="`${card?.first_name} ${card?.last_name}`"
                    >
                    <div v-else class="portrait-placeholder" :style="avatarStyle">
                        <i class="bi bi-person"></i>
                    </div>
                </div>

                <h1 :style="nameStyle">
                    {{ card?.first_name }} {{ card?.last_name }}
                </h1>
                <p :style="jobTitleStyle">{{ card?.job_title }}</p>

                <p v-if="card?.description" :style="bioStyle">
                    "{{ card.description }}"
                </p>

                <div class="contact-block" :style="contactBlockStyle">
                    <div v-if="card?.mobile_phone" class="contact-line">
                        <i class="bi bi-telephone" :style="iconStyle"></i>
                        <a :href="`tel:${card.mobile_phone}`" :style="linkStyle">{{ card.mobile_phone }}</a>
                    </div>
                    <div v-if="card?.email" class="contact-line">
                        <i class="bi bi-envelope" :style="iconStyle"></i>
                        <a :href="`mailto:${card.email}`" :style="linkStyle">{{ card.email }}</a>
                    </div>
                    <div v-if="company?.web" class="contact-line">
                        <i class="bi bi-globe" :style="iconStyle"></i>
                        <a :href="company.web" target="_blank" :style="linkStyle">{{ company.web.replace(/^https?:\/\//, '') }}</a>
                    </div>
                    <div v-if="company?.address" class="contact-line">
                        <i class="bi bi-geo-alt" :style="iconStyle"></i>
                        <span :style="textStyle">{{ company.address }}</span>
                    </div>
                </div>

                <div class="social-actions" :style="alignmentCenterStyle">
                    <a v-if="card?.whatsapp" :href="whatsappLink" class="social-btn" :style="getSocialBtnStyle('whatsapp')">
                        <i class="bi bi-whatsapp"></i>
                    </a>
                    <a v-if="card?.linkedin" :href="card.linkedin" target="_blank" class="social-btn" :style="getSocialBtnStyle('linkedin')">
                        <i class="bi bi-linkedin"></i>
                    </a>
                    <a v-if="company?.facebook" :href="company.facebook" target="_blank" class="social-btn" :style="getSocialBtnStyle('facebook')">
                        <i class="bi bi-facebook"></i>
                    </a>
                    <a v-if="company?.instagram" :href="company.instagram" target="_blank" class="social-btn" :style="getSocialBtnStyle('instagram')">
                        <i class="bi bi-instagram"></i>
                    </a>
                </div>

                <hr class="elegant-divider" :style="dividerStyle">

                <section class="offerings" v-if="services?.length">
                    <h3 :style="sectionTitleStyle">Áreas de Práctica</h3>
                    <ul class="classic-list">
                        <li v-for="service in services" :key="`s-${service.id}`" :style="listItemStyle">
                            <i class="bi bi-diamond-fill" :style="bulletIconStyle"></i>
                            <div>
                                <strong :style="itemTitleStyle">{{ service.name }}</strong>
                                <p v-if="service.description" :style="itemDescStyle">{{ service.description }}</p>
                            </div>
                        </li>
                    </ul>
                </section>

                <section class="offerings" v-if="products?.length">
                    <h3 :style="sectionTitleStyle">Portafolio Destacado</h3>
                    <div class="products-grid">
                        <div v-for="product in products" :key="`p-${product.id}`" class="product-card" :style="productCardStyle">
                            <h4 :style="itemTitleStyle">{{ product.name }}</h4>
                            <p v-if="product.description" :style="itemDescStyle">{{ product.description }}</p>
                            <p v-if="product.price" :style="priceStyle">
                                <span v-if="product.discount" class="old-price" :style="oldPriceStyle">${{ product.price }}</span>
                                ${{ product.discount || product.price }}
                            </p>
                        </div>
                    </div>
                </section>

            </main>
        </div>
    </div>
</template>

<script>
export default {
    name: 'TemplateClassic',

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

        // Usamos || para strings/colores y ?? para numéricos según la guía [cite: 48, 49]

        containerStyle() {
            const gen = this.customization?.general || {}
            return {
                backgroundColor: gen.colorFondo || '#fdfbf7',
                fontFamily: "'Lato', 'Helvetica Neue', sans-serif", // Fuente secundaria limpia
                color: gen.colorTexto || '#2c3e50',
            }
        },

        borderOuterStyle() {
            const acento = this.customization?.general?.colorAcento || '#b89947'
            return {
                border: `1px solid ${acento}40`, // Acento con transparencia
                outline: `1px solid ${acento}`,
                outlineOffset: '-6px',
            }
        },

        alignmentStyle() {
            const align = this.customization?.perfil?.alineacion || 'center'
            return { textAlign: align }
        },

        alignmentCenterStyle() {
            const align = this.customization?.perfil?.alineacion || 'center'
            return {
                justifyContent: align === 'center' ? 'center' : (align === 'right' ? 'flex-end' : 'flex-start')
            }
        },

        companyNameStyle() {
            const gen = this.customization?.general || {}
            return {
                fontFamily: `'${gen.fuenteTitulos || 'Playfair Display'}', serif`,
                color: gen.colorAcento || '#b89947',
                letterSpacing: '2px',
                textTransform: 'uppercase',
                fontSize: '1.1rem',
                margin: '0',
            }
        },

        dividerStyle() {
            return {
                borderTop: `1px solid ${this.customization?.general?.colorAcento || '#b89947'}`,
                opacity: '0.5'
            }
        },

        showAvatar() {
            return this.customization?.avatar?.mostrar !== false
        },

        avatarStyle() {
            const avatar = this.customization?.avatar || {}
            const acento = this.customization?.general?.colorAcento || '#b89947'

            return {
                width: `${avatar.tamano ?? 140}px`,
                height: `${avatar.tamano ?? 140}px`,
                borderRadius: `${avatar.radio ?? 0}%`,
                border: `3px double ${acento}`,
                padding: '4px', // Crea un efecto de paspartú clásico
                backgroundColor: this.customization?.general?.colorFondo || '#fdfbf7',
            }
        },

        nameStyle() {
            const gen = this.customization?.general || {}
            return {
                fontFamily: `'${gen.fuenteTitulos || 'Playfair Display'}', serif`,
                fontSize: '1.8rem',
                color: gen.colorTexto || '#2c3e50',
                fontWeight: '700',
                margin: '1.5rem 0 0.25rem 0',
            }
        },

        jobTitleStyle() {
            return {
                color: this.customization?.general?.colorAcento || '#b89947',
                fontSize: '0.9rem',
                textTransform: 'uppercase',
                letterSpacing: '3px',
                marginBottom: '1.5rem',
            }
        },

        bioStyle() {
            return {
                fontStyle: 'italic',
                fontSize: '0.95rem',
                lineHeight: '1.6',
                opacity: '0.85',
                marginBottom: '2rem',
                color: this.customization?.general?.colorTexto || '#2c3e50',
            }
        },

        contactBlockStyle() {
            const acento = this.customization?.general?.colorAcento || '#b89947'
            return {
                borderTop: `1px dashed ${acento}60`,
                borderBottom: `1px dashed ${acento}60`,
                padding: '1.5rem 0',
                margin: '0 1rem 2rem 1rem',
            }
        },

        textStyle() {
            return { color: this.customization?.general?.colorTexto || '#2c3e50' }
        },

        linkStyle() {
            return {
                color: this.customization?.general?.colorTexto || '#2c3e50',
                textDecoration: 'none'
            }
        },

        iconStyle() {
            return {
                color: this.customization?.general?.colorAcento || '#b89947',
                marginRight: '12px',
                fontSize: '1.1rem'
            }
        },

        sectionTitleStyle() {
            const gen = this.customization?.general || {}
            return {
                fontFamily: `'${gen.fuenteTitulos || 'Playfair Display'}', serif`,
                color: gen.colorAcento || '#b89947',
                fontSize: '1.3rem',
                borderBottom: `1px solid ${gen.colorAcento || '#b89947'}40`,
                paddingBottom: '0.5rem',
                marginBottom: '1.5rem',
            }
        },

        listItemStyle() {
            return {
                marginBottom: '1rem',
                color: this.customization?.general?.colorTexto || '#2c3e50',
            }
        },

        bulletIconStyle() {
            return {
                color: this.customization?.general?.colorAcento || '#b89947',
                fontSize: '0.5rem',
                marginRight: '10px',
                marginTop: '6px'
            }
        },

        itemTitleStyle() {
            const gen = this.customization?.general || {}
            return {
                fontFamily: `'${gen.fuenteTitulos || 'Playfair Display'}', serif`,
                fontSize: '1.1rem',
                color: gen.colorTexto || '#2c3e50',
            }
        },

        itemDescStyle() {
            return {
                fontSize: '0.85rem',
                opacity: '0.8',
                margin: '0.25rem 0 0 0',
            }
        },

        productCardStyle() {
            const acento = this.customization?.general?.colorAcento || '#b89947'
            return {
                border: `1px solid ${acento}30`,
                padding: '1rem',
                backgroundColor: '#ffffff', // Fondo fijo para contraste sutil
                marginBottom: '1rem',
            }
        },

        priceStyle() {
            return {
                color: this.customization?.general?.colorAcento || '#b89947',
                fontWeight: 'bold',
                marginTop: '0.5rem',
                marginBottom: '0',
            }
        },

        oldPriceStyle() {
            return {
                color: '#999',
                textDecoration: 'line-through',
                fontSize: '0.8em',
                marginRight: '8px',
                fontWeight: 'normal',
            }
        }
    },

    methods: {
        getSocialBtnStyle(type) {
            const config = this.customization?.botones || {}
            const acento = this.customization?.general?.colorAcento || '#b89947'
            const texto = this.customization?.general?.colorTexto || '#2c3e50'
            const estilo = config.estilo || 'borde'

            const base = {
                borderRadius: '0%', // Cuadrado clásico por defecto
                transition: 'all 0.3s ease',
            }

            if (estilo === 'solido') {
                return {
                    ...base,
                    backgroundColor: acento,
                    color: '#ffffff',
                    border: `1px solid ${acento}`,
                }
            } else if (estilo === 'borde') {
                return {
                    ...base,
                    backgroundColor: 'transparent',
                    color: texto,
                    border: `1px solid ${acento}`,
                }
            } else {
                // Minimalista
                return {
                    ...base,
                    backgroundColor: 'transparent',
                    color: acento,
                    border: 'none',
                    fontSize: '1.4rem'
                }
            }
        }
    }
}
</script>

<style scoped>
/*
 * ESTILOS ESTÁTICOS
 * Contiene únicamente reglas de flexbox, anchos, márgenes estáticos y transiciones.
 */
.card-template.classic {
    min-height: 100vh;
    padding: 1rem;
    box-sizing: border-box;
}

.classic-border {
    padding: 2rem 1.5rem;
    height: calc(100% - 2rem);
    display: flex;
    flex-direction: column;
}

.classic-header {
    margin-bottom: 2rem;
}

.company-logo {
    max-width: 160px;
    height: auto;
    margin-bottom: 1rem;
}

.elegant-divider {
    margin: 1.5rem auto;
    width: 60px;
    border-top-width: 2px;
}

.portrait-container {
    display: flex;
    justify-content: center;
    margin-bottom: 1.5rem;
}

.portrait-container img {
    object-fit: cover;
    display: block;
}

.portrait-placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3.5rem;
    color: inherit;
    opacity: 0.2;
}

.contact-block {
    display: flex;
    flex-direction: column;
    gap: 0.8rem;
    text-align: left;
}

.contact-line {
    display: flex;
    align-items: center;
}

.social-actions {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
    margin-bottom: 1.5rem;
}

.social-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    font-size: 1.1rem;
    text-decoration: none;
    cursor: pointer;
}

.social-btn:hover {
    transform: translateY(-2px);
    opacity: 0.8;
}

.offerings {
    margin-top: 2rem;
    text-align: left;
}

.classic-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.classic-list li {
    display: flex;
    align-items: flex-start;
}

.products-grid {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.product-card {
    display: flex;
    flex-direction: column;
}
</style>
