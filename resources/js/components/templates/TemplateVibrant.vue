<template>
    <div class="card-template vibrant animated-bg" :style="containerStyle">

        <main class="vibrant-wrapper">

            <header class="vibrant-header" v-if="company?.logo_path || company?.name">
                <div class="pill-badge" :style="badgeStyle">
                    <img
                        v-if="company?.logo_path"
                        :src="company.logo_path"
                        :alt="company.name"
                        class="badge-logo"
                    >
                    <span v-else>{{ company.name }}</span>
                </div>
            </header>

            <div class="bento-grid">

                <section class="bento-card profile-card" :style="cardStyle">
                    <div class="avatar-wrapper" :class="avatarAnimationClass" v-if="showAvatar">
                        <img
                            v-if="card?.photo_path"
                            :src="card.photo_path"
                            :style="avatarStyle"
                            :alt="card?.first_name"
                        >
                        <div v-else class="avatar-placeholder" :style="avatarStyle">
                            <i class="bi bi-emoji-smile"></i>
                        </div>
                    </div>

                    <h1 :style="nameStyle">{{ card?.first_name }} {{ card?.last_name }}</h1>
                    <div class="role-pill" :style="rolePillStyle">{{ card?.job_title }}</div>

                    <p v-if="card?.description" :style="bioStyle">
                        {{ card.description }}
                    </p>
                </section>

                <section class="bento-card flex-row interactive" :style="cardStyle" v-if="card?.whatsapp">
                    <a :href="whatsappLink" class="full-link" :style="textNormalStyle">
                        <div class="icon-circle whatsapp-color">
                            <i class="bi bi-whatsapp"></i>
                        </div>
                        <div class="contact-info">
                            <strong>WhatsApp</strong>
                            <span>¡Escríbeme!</span>
                        </div>
                        <i class="bi bi-arrow-right-short arrow-icon"></i>
                    </a>
                </section>

                <section class="social-bento">
                    <a v-if="card?.mobile_phone" :href="`tel:${card.mobile_phone}`" class="bento-card social-box interactive" :style="cardStyle">
                        <i class="bi bi-telephone-fill" :style="iconStyle"></i>
                    </a>
                    <a v-if="card?.email" :href="`mailto:${card.email}`" class="bento-card social-box interactive" :style="cardStyle">
                        <i class="bi bi-envelope-fill" :style="iconStyle"></i>
                    </a>
                    <a v-if="company?.instagram" :href="company.instagram" target="_blank" class="bento-card social-box interactive" :style="cardStyle">
                        <i class="bi bi-instagram" :style="iconStyle"></i>
                    </a>
                    <a v-if="card?.linkedin" :href="card.linkedin" target="_blank" class="bento-card social-box interactive" :style="cardStyle">
                        <i class="bi bi-linkedin" :style="iconStyle"></i>
                    </a>
                    <a v-if="company?.web" :href="company.web" target="_blank" class="bento-card social-box interactive web-box" :style="cardStyle">
                        <i class="bi bi-globe" :style="iconStyle"></i>
                        <span :style="textSmallStyle">Website</span>
                    </a>
                </section>

                <section class="bento-card" :style="cardStyle" v-if="services?.length">
                    <h3 :style="titleSmallStyle">Lo que hacemos 🚀</h3>
                    <div class="service-stack">
                        <div v-for="service in services" :key="`s-${service.id}`" class="service-item interactive-light" :style="serviceItemStyle">
                            <strong :style="textNormalStyle">{{ service.name }}</strong>
                            <p v-if="service.description" :style="textSmallStyle">{{ service.description }}</p>
                        </div>
                    </div>
                </section>

                <section class="bento-card" :style="cardStyle" v-if="products?.length">
                    <h3 :style="titleSmallStyle">Destacados 🔥</h3>
                    <div class="scroll-horizontal">
                        <div v-for="product in products" :key="`p-${product.id}`" class="product-ticket interactive-light" :style="productItemStyle">
                            <h4 :style="textNormalStyle">{{ product.name }}</h4>
                            <div class="price-tag" :style="priceTagStyle">
                                ${{ product.discount || product.price }}
                            </div>
                        </div>
                    </div>
                </section>

            </div>
        </main>
    </div>
</template>

<script>
export default {
    name: 'TemplateVibrant',

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

        // --- CLASES DINÁMICAS ---
        avatarAnimationClass() {
            const anim = this.customization?.avatar?.animacion || 'flotar'
            if (anim === 'flotar') return 'anim-float'
            if (anim === 'latir') return 'anim-pulse'
            return ''
        },

        // --- ESTILOS COMPUTADOS ---
        containerStyle() {
            const gen = this.customization?.general || {}
            const color1 = gen.color1 || '#ff00cc'
            const color2 = gen.color2 || '#333399'
            return {
                backgroundImage: `linear-gradient(-45deg, ${color1}, ${color2}, ${color1})`,
                fontFamily: `'${gen.tipografia || 'Outfit'}', sans-serif`,
            }
        },

        badgeStyle() {
            return {
                backgroundColor: this.customization?.tarjetas?.colorFondo || '#ffffff',
                color: this.customization?.tarjetas?.colorTexto || '#1e1e2f',
                borderRadius: '50px',
            }
        },

        cardStyle() {
            const tarjetas = this.customization?.tarjetas || {}
            return {
                backgroundColor: tarjetas.colorFondo || '#ffffff',
                borderRadius: `${tarjetas.radio ?? 24}px`,
                boxShadow: '0 10px 30px rgba(0,0,0,0.1)',
            }
        },

        showAvatar() {
            return this.customization?.avatar?.mostrar !== false
        },

        avatarStyle() {
            const avatar = this.customization?.avatar || {}
            return {
                width: `${avatar.tamano ?? 120}px`,
                height: `${avatar.tamano ?? 120}px`,
                borderRadius: '50%', // Siempre círculo para este estilo juvenil
                border: `4px solid ${this.customization?.tarjetas?.colorFondo || '#ffffff'}`,
                objectFit: 'cover',
                boxShadow: '0 8px 20px rgba(0,0,0,0.15)',
            }
        },

        nameStyle() {
            return {
                fontSize: '1.8rem',
                fontWeight: '800',
                letterSpacing: '-0.5px',
                color: this.customization?.tarjetas?.colorTexto || '#1e1e2f',
                margin: '1rem 0 0.5rem 0',
            }
        },

        rolePillStyle() {
            const btn = this.customization?.botones || {}
            return {
                backgroundColor: btn.colorBoton || '#1e1e2f',
                color: btn.colorTextoBoton || '#ffffff',
                borderRadius: '50px',
            }
        },

        bioStyle() {
            return {
                fontSize: '0.95rem',
                lineHeight: '1.5',
                color: this.customization?.tarjetas?.colorTexto || '#1e1e2f',
                opacity: '0.8',
                marginTop: '1.2rem',
                marginBottom: '0',
            }
        },

        textNormalStyle() {
            return {
                color: this.customization?.tarjetas?.colorTexto || '#1e1e2f',
                margin: 0,
            }
        },

        textSmallStyle() {
            return {
                color: this.customization?.tarjetas?.colorTexto || '#1e1e2f',
                opacity: '0.7',
                fontSize: '0.85rem',
                margin: 0,
            }
        },

        titleSmallStyle() {
            return {
                color: this.customization?.tarjetas?.colorTexto || '#1e1e2f',
                fontSize: '1.1rem',
                fontWeight: '700',
                marginBottom: '1rem',
            }
        },

        iconStyle() {
            return {
                color: this.customization?.botones?.colorBoton || '#1e1e2f',
                fontSize: '1.8rem',
            }
        },

        serviceItemStyle() {
            const colorFondo = this.customization?.tarjetas?.colorFondo || '#ffffff'
            return {
                backgroundColor: colorFondo,
                border: `1px solid rgba(0,0,0,0.05)`,
                borderRadius: `${(this.customization?.tarjetas?.radio ?? 24) / 2}px`,
            }
        },

        productItemStyle() {
            const btn = this.customization?.botones || {}
            return {
                border: `2px solid ${btn.colorBoton || '#1e1e2f'}`,
                borderRadius: `${(this.customization?.tarjetas?.radio ?? 24) / 2}px`,
            }
        },

        priceTagStyle() {
            const btn = this.customization?.botones || {}
            return {
                backgroundColor: btn.colorBoton || '#1e1e2f',
                color: btn.colorTextoBoton || '#ffffff',
            }
        }
    }
}
</script>

<style scoped>
/*
 * ESTILOS ESTÁTICOS Y ANIMACIONES
 * Magia visual: Keyframes, flexbox, grid, hovers.
 */
.card-template.vibrant {
    min-height: 100vh;
    padding: 2rem 1rem;
    box-sizing: border-box;
}

/* Animación del fondo dinámico */
.animated-bg {
    background-size: 200% 200%;
    animation: gradientShift 8s ease infinite;
}
@keyframes gradientShift {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

.vibrant-wrapper {
    max-width: 450px;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.vibrant-header {
    display: flex;
    justify-content: center;
}

.pill-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.5rem 1.5rem;
    font-weight: 700;
    font-size: 0.9rem;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.badge-logo {
    height: 24px;
    width: auto;
}

.bento-grid {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.bento-card {
    padding: 1.5rem;
    position: relative;
    overflow: hidden;
}

/* Interacciones Hover para todas las tarjetas interactivas */
.interactive {
    transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275), box-shadow 0.3s ease;
    cursor: pointer;
}
.interactive:hover {
    transform: translateY(-5px) scale(1.02);
    box-shadow: 0 15px 35px rgba(0,0,0,0.15) !important;
}

.interactive-light {
    transition: transform 0.2s ease, background-color 0.2s ease;
    cursor: pointer;
}
.interactive-light:hover {
    transform: scale(1.02);
    filter: brightness(0.95);
}

.profile-card {
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.avatar-wrapper {
    position: relative;
    display: inline-block;
}

.avatar-placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3rem;
    background-color: #f0f0f0;
    color: #ccc;
}

/* Animaciones del Avatar */
.anim-float {
    animation: float 4s ease-in-out infinite;
}
@keyframes float {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}

.anim-pulse {
    animation: pulse 2s infinite;
}
@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

.role-pill {
    display: inline-block;
    padding: 0.4rem 1.2rem;
    font-size: 0.85rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
}

/* Estilos de Botones / Enlaces internos */
.flex-row {
    padding: 1rem 1.5rem;
}
.full-link {
    display: flex;
    align-items: center;
    text-decoration: none;
    width: 100%;
}
.icon-circle {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
    margin-right: 1rem;
}
.whatsapp-color { background-color: #25d366; }

.contact-info {
    display: flex;
    flex-direction: column;
    flex-grow: 1;
}
.contact-info span {
    font-size: 0.8rem;
    opacity: 0.7;
}

.arrow-icon {
    font-size: 1.5rem;
    opacity: 0.5;
    transition: transform 0.2s;
}
.interactive:hover .arrow-icon {
    transform: translateX(5px);
}

.social-bento {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
}

.social-box {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    text-decoration: none;
}

.web-box {
    grid-column: span 4;
    flex-direction: row;
    gap: 10px;
}

.service-stack {
    display: flex;
    flex-direction: column;
    gap: 0.8rem;
}
.service-item {
    padding: 1rem;
    box-shadow: 0 4px 10px rgba(0,0,0,0.03);
}

/* Scroll horizontal suave para productos */
.scroll-horizontal {
    display: flex;
    gap: 1rem;
    overflow-x: auto;
    padding-bottom: 1rem;
    /* Ocultar scrollbar */
    scrollbar-width: none;
    -ms-overflow-style: none;
}
.scroll-horizontal::-webkit-scrollbar {
    display: none;
}

.product-ticket {
    min-width: 140px;
    padding: 1.2rem 1rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: 0.8rem;
}

.price-tag {
    padding: 0.3rem 0.8rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 700;
}
</style>
