<template>
    <div class="card-template creative" :style="containerStyle">
        <div class="ambient-light light-1" :style="light1Style" v-if="customization?.hero?.mostrarFondoEfecto !== false"></div>
        <div class="ambient-light light-2" :style="light2Style" v-if="customization?.hero?.mostrarFondoEfecto !== false"></div>

        <main class="glass-container" :style="glassContainerStyle">
            <header class="creative-header">
                <div class="company-badge" v-if="company?.logo_path || company?.name">
                    <img
                        v-if="company?.logo_path"
                        :src="company.logo_path"
                        :alt="company.name"
                        class="mini-logo"
                        :style="logoStyle"
                    >
                    <span v-else>{{ company?.name }}</span>
                </div>

                <div class="avatar-wrapper" :style="avatarWrapperStyle" v-if="showAvatar">
                    <img
                        v-if="card?.photo_path"
                        :src="card.photo_path"
                        :alt="`${card.first_name} ${card.last_name}`"
                        class="avatar-image"
                        :style="avatarStyle"
                    >
                    <div v-else class="avatar-placeholder" :style="avatarStyle">
                        <i class="bi bi-person"></i>
                    </div>
                </div>
            </header>

            <section class="personal-info">
                <h1 class="name" :style="nameStyle">
                    {{ card?.first_name || 'Nombre' }}
                    <span class="fw-light">{{ card?.last_name || 'Apellido' }}</span>
                </h1>
                <p class="role" :style="roleStyle">{{ card?.job_title || 'Cargo profesional' }}</p>
                <p class="bio" v-if="card?.description">{{ card.description }}</p>
            </section>

            <section class="quick-actions">
                <a v-if="card?.mobile_phone" :href="`tel:${card.mobile_phone}`" class="action-btn" :style="actionBtnStyle">
                    <i class="bi bi-telephone-fill"></i>
                </a>
                <a v-if="card?.whatsapp" :href="whatsappLink" class="action-btn whatsapp" :style="actionBtnStyle">
                    <i class="bi bi-whatsapp"></i>
                </a>
                <a v-if="card?.email" :href="`mailto:${card.email}`" class="action-btn" :style="actionBtnStyle">
                    <i class="bi bi-envelope-fill"></i>
                </a>
                <a v-if="company?.web" :href="company.web" target="_blank" class="action-btn" :style="actionBtnStyle">
                    <i class="bi bi-globe"></i>
                </a>
            </section>

            <section class="content-cards" v-if="services?.length || products?.length">
                <div class="glass-card" :style="glassCardStyle" v-if="services?.length">
                    <h3 class="card-title">Servicios</h3>
                    <div class="pill-list">
                        <span class="glass-pill" v-for="service in services" :key="'s-'+service.id">
                            {{ service.name }}
                        </span>
                    </div>
                </div>

                <div class="glass-card" :style="glassCardStyle" v-if="products?.length">
                    <h3 class="card-title">Productos Destacados</h3>
                    <div class="pill-list">
                        <span class="glass-pill" v-for="product in products" :key="'p-'+product.id">
                            {{ product.name }}
                        </span>
                    </div>
                </div>
            </section>

            <footer class="social-footer">
                <a v-if="card?.linkedin" :href="card.linkedin" class="social-link" :style="socialLinkStyle"><i class="bi bi-linkedin"></i></a>
                <a v-if="company?.instagram" :href="company.instagram" class="social-link" :style="socialLinkStyle"><i class="bi bi-instagram"></i></a>
                <a v-if="company?.facebook" :href="company.facebook" class="social-link" :style="socialLinkStyle"><i class="bi bi-facebook"></i></a>
            </footer>
        </main>
    </div>
</template>

<script>
export default {
    name: 'TemplateCreative',
    props: {
        customization: { type: Object, default: () => ({}) },
        company: { type: Object, default: () => ({}) },
        card: { type: Object, default: () => ({}) },
        services: { type: Array, default: () => [] },
        products: { type: Array, default: () => [] }
    },
    computed: {
        // Colores base
        bgColor() {
            return this.customization?.general?.colorFondo || '#0f172a'
        },
        accentColor() {
            return this.customization?.general?.colorAcento || '#38bdf8'
        },
        fontFamily() {
            return this.customization?.general?.fuentePrincipal || 'Poppins'
        },
        secondaryLightColor() {
            return this.customization?.hero?.colorLuzSecundaria || '#c084fc'
        },
        borderRadius() {
            return this.customization?.hero?.radioBorde ?? 24
        },
        glassOpacity() {
            return this.customization?.glass?.opacidad ?? 0.1
        },
        glassBlur() {
            return this.customization?.glass?.desenfoque ?? 12
        },

        // Avatar
        showAvatar() {
            return this.customization?.avatar?.mostrar !== false
        },
        avatarSize() {
            return this.customization?.avatar?.tamano ?? 120
        },
        avatarBorderWidth() {
            return this.customization?.avatar?.grosorBorde ?? 5
        },

        // Profile
        nameSize() {
            return this.customization?.profile?.nombreTamano ?? 1.5
        },
        nameColor() {
            return this.customization?.profile?.nombreColor || '#ffffff'
        },
        roleSize() {
            return this.customization?.profile?.cargoTamano ?? 0.95
        },

        // Acciones
        actionBtnSize() {
            return this.customization?.acciones?.tamanoBoton ?? 50
        },
        actionBtnRadius() {
            return this.customization?.acciones?.radioBoton ?? 50
        },

        // Logo
        logoHeight() {
            return this.customization?.header?.alturaLogo ?? 24
        },

        // WhatsApp link
        whatsappLink() {
            if (!this.card?.whatsapp) return '#'
            const phone = this.card.whatsapp.replace(/\D/g, '')
            return `https://api.whatsapp.com/send?phone=${phone}`
        },

        // Estilos computados
        containerStyle() {
            return {
                backgroundColor: this.bgColor,
                fontFamily: `'${this.fontFamily}', sans-serif`,
            }
        },
        light1Style() {
            return {
                background: this.accentColor,
            }
        },
        light2Style() {
            return {
                background: this.secondaryLightColor,
            }
        },
        glassContainerStyle() {
            return {
                background: `rgba(255, 255, 255, ${this.glassOpacity})`,
                backdropFilter: `blur(${this.glassBlur}px)`,
                WebkitBackdropFilter: `blur(${this.glassBlur}px)`,
                borderRadius: `${this.borderRadius}px`,
            }
        },
        glassCardStyle() {
            return {
                borderRadius: `${Math.round(this.borderRadius / 1.5)}px`,
            }
        },
        logoStyle() {
            return {
                height: `${this.logoHeight}px`,
                width: 'auto',
            }
        },
        avatarWrapperStyle() {
            return {
                padding: `${this.avatarBorderWidth}px`,
                background: `linear-gradient(135deg, ${this.accentColor}, transparent)`,
            }
        },
        avatarStyle() {
            return {
                width: `${this.avatarSize}px`,
                height: `${this.avatarSize}px`,
                border: `3px solid ${this.bgColor}`,
            }
        },
        nameStyle() {
            return {
                fontSize: `${this.nameSize}rem`,
                color: this.nameColor,
            }
        },
        roleStyle() {
            return {
                fontSize: `${this.roleSize}rem`,
                color: this.accentColor,
            }
        },
        actionBtnStyle() {
            return {
                width: `${this.actionBtnSize}px`,
                height: `${this.actionBtnSize}px`,
                borderRadius: `${this.actionBtnRadius}%`,
            }
        },
        socialLinkStyle() {
            return {
                '--accent-color': this.accentColor,
            }
        },
    }
}
</script>

<style scoped>
.card-template.creative {
    color: #ffffff;
    min-height: 100vh;
    padding: 2rem 1rem;
    position: relative;
    overflow: hidden;
    display: flex;
    justify-content: center;
    align-items: flex-start;
}

/* Luces de ambiente en el fondo */
.ambient-light {
    position: absolute;
    border-radius: 50%;
    filter: blur(60px);
    z-index: 0;
    opacity: 0.5;
}
.light-1 {
    top: -10%;
    left: -10%;
    width: 300px;
    height: 300px;
}
.light-2 {
    bottom: -10%;
    right: -10%;
    width: 250px;
    height: 250px;
}

/* Contenedor Glassmorphism */
.glass-container {
    position: relative;
    z-index: 1;
    width: 100%;
    max-width: 400px;
    border: 1px solid rgba(255, 255, 255, 0.1);
    padding: 2rem;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
}

.creative-header {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-bottom: 1.5rem;
}

.company-badge {
    background: rgba(0, 0, 0, 0.2);
    padding: 0.5rem 1rem;
    border-radius: 50px;
    font-size: 0.8rem;
    letter-spacing: 1px;
    text-transform: uppercase;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
}

.mini-logo {
    max-width: 150px;
}

.avatar-wrapper {
    position: relative;
    border-radius: 50%;
}

.avatar-image, .avatar-placeholder {
    border-radius: 50%;
    object-fit: cover;
}

.avatar-placeholder {
    display: flex;
    justify-content: center;
    align-items: center;
    background: #334155;
    font-size: 3rem;
    color: rgba(255,255,255,0.5);
}

.personal-info {
    text-align: center;
    margin-bottom: 2rem;
}

.name {
    font-weight: 600;
    margin: 0 0 0.25rem 0;
}

.role {
    font-weight: 500;
    margin: 0 0 1rem 0;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.bio {
    font-size: 0.9rem;
    line-height: 1.5;
    color: rgba(255, 255, 255, 0.8);
    margin: 0;
}

.quick-actions {
    display: flex;
    justify-content: center;
    gap: 1rem;
    margin-bottom: 2rem;
}

.action-btn {
    background: rgba(255, 255, 255, 0.1);
    color: #fff;
    display: flex;
    justify-content: center;
    align-items: center;
    font-size: 1.2rem;
    text-decoration: none;
    transition: all 0.3s ease;
    border: 1px solid rgba(255, 255, 255, 0.05);
}

.action-btn:hover {
    background: rgba(255, 255, 255, 0.2);
    transform: translateY(-3px);
}

.action-btn.whatsapp:hover {
    background: #25d366;
    box-shadow: 0 10px 20px -10px #25d366;
}

.glass-card {
    background: rgba(0, 0, 0, 0.2);
    padding: 1.25rem;
    margin-bottom: 1rem;
    border: 1px solid rgba(255, 255, 255, 0.05);
}

.card-title {
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: rgba(255, 255, 255, 0.6);
    margin: 0 0 1rem 0;
}

.pill-list {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.glass-pill {
    background: rgba(255, 255, 255, 0.05);
    padding: 0.4rem 0.8rem;
    border-radius: 20px;
    font-size: 0.85rem;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.social-footer {
    display: flex;
    justify-content: center;
    gap: 1.5rem;
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.social-link {
    color: rgba(255, 255, 255, 0.5);
    font-size: 1.2rem;
    transition: color 0.2s;
}

.social-link:hover {
    color: var(--accent-color, #38bdf8);
}
</style>
