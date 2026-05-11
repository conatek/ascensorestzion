<template>
    <div class="card-template modern">
        <!-- Sección Hero: Fondo + Cabecera + Foto -->
        <section class="hero-section" :style="heroSectionStyle" v-if="showHeroSection">
            <!-- Fondo parcial -->
            <div
                class="photo-background"
                :style="photoBackgroundStyle"
                v-if="showPhotoBackground"
            ></div>

            <!-- Espaciador cuando cabecera está oculta -->
            <div class="header-spacer" v-if="headerContent === 'oculto' && showPhoto"></div>

            <!-- Cabecera -->
            <header
                class="template-header"
                v-if="headerContent !== 'oculto'"
            >
                <img
                    v-if="headerContent === 'logo' && company?.logo_path"
                    :src="company.logo_path"
                    :alt="company.name"
                    class="company-logo"
                    :style="logoStyle"
                >
                <h4
                    v-else-if="headerContent === 'nombre' || (headerContent === 'logo' && !company?.logo_path)"
                    class="company-name"
                    :style="companyNameStyle"
                >
                    {{ company?.name || 'Empresa' }}
                </h4>
            </header>

            <!-- Contenedor de la foto -->
            <div class="photo-container" v-if="showPhoto">
                <img
                    v-if="card?.photo_path"
                    :src="card.photo_path"
                    :alt="`${card.first_name} ${card.last_name}`"
                    class="profile-photo"
                    :class="{ 'with-shadow': customization?.photo?.sombra }"
                    :style="photoStyle"
                >
                <div v-else class="photo-placeholder" :class="{ 'with-shadow': customization?.photo?.sombra }" :style="photoStyle">
                    <i class="bi bi-person-fill"></i>
                </div>
            </div>
        </section>

        <!-- Datos Personales -->
        <section class="profile-section" :style="profileSectionStyle">
            <div class="profile-data" :class="{ 'with-text-shadow': customization?.profile?.sombra }">
                <p class="full-name">
                    <span class="first-name" :style="firstNameStyle">{{ card?.first_name || 'Nombre' }}</span>
                    <span class="last-name" :style="lastNameStyle">{{ card?.last_name || 'Apellido' }}</span>
                </p>
                <p class="job-title" :style="jobTitleStyle">{{ card?.job_title || 'Cargo' }}</p>
            </div>
        </section>

        <!-- Redes Sociales -->
        <section class="social-section" :class="{ 'social-with-shadow': customization?.social?.sombra }">
            <ul class="social-icons company-social">
                <li v-if="company?.facebook">
                    <a :href="company.facebook" class="social-btn facebook" :style="getSocialBtnStyle('facebook')">
                        <i class="bi bi-facebook"></i>
                    </a>
                </li>
                <li v-if="company?.instagram">
                    <a :href="company.instagram" class="social-btn instagram" :style="getSocialBtnStyle('instagram')">
                        <i class="bi bi-instagram"></i>
                    </a>
                </li>
                <li v-if="company?.twitter">
                    <a :href="company.twitter" class="social-btn twitter" :style="getSocialBtnStyle('twitter')">
                        <i class="bi bi-twitter-x"></i>
                    </a>
                </li>
                <li v-if="card?.linkedin">
                    <a :href="card.linkedin" class="social-btn linkedin" :style="getSocialBtnStyle('linkedin')">
                        <i class="bi bi-linkedin"></i>
                    </a>
                </li>
                <li v-if="company?.youtube">
                    <a :href="company.youtube" class="social-btn youtube" :style="getSocialBtnStyle('youtube')">
                        <i class="bi bi-youtube"></i>
                    </a>
                </li>
            </ul>

            <ul class="social-icons contact-social">
                <li v-if="card?.mobile_phone">
                    <a :href="`tel:${card.mobile_phone}`" class="social-btn phone" :style="getSocialBtnStyle('phone')">
                        <i class="bi bi-telephone-fill"></i>
                    </a>
                </li>
                <li v-if="card?.whatsapp">
                    <a :href="whatsappLink" class="social-btn whatsapp" :style="getSocialBtnStyle('whatsapp')">
                        <i class="bi bi-whatsapp"></i>
                    </a>
                </li>
                <li v-if="company?.web">
                    <a :href="company.web" class="social-btn web" :style="getSocialBtnStyle('web')">
                        <i class="bi bi-globe"></i>
                    </a>
                </li>
                <li v-if="card?.email">
                    <a :href="`mailto:${card.email}`" class="social-btn email" :style="getSocialBtnStyle('email')">
                        <i class="bi bi-envelope-fill"></i>
                    </a>
                </li>
                <li v-if="company?.my_business">
                    <a :href="company.my_business" class="social-btn location" :style="getSocialBtnStyle('location')">
                        <i class="bi bi-geo-alt-fill"></i>
                    </a>
                </li>
            </ul>
        </section>

        <!-- Acordeón de Secciones -->
        <section class="accordion-section">
            <div class="accordion">
                <!-- Quién Soy -->
                <div class="accordion-item" v-if="card?.description">
                    <button
                        class="accordion-header section-1"
                        @click="toggleAccordion('about')"
                        :class="{ active: openAccordion === 'about' }"
                    >
                        <span>Quién soy</span>
                        <i class="bi" :class="openAccordion === 'about' ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                    </button>
                    <div class="accordion-content" v-show="openAccordion === 'about'">
                        <p>{{ card.description }}</p>
                    </div>
                </div>

                <!-- Información de Contacto -->
                <div class="accordion-item">
                    <button
                        class="accordion-header section-2"
                        @click="toggleAccordion('contact')"
                        :class="{ active: openAccordion === 'contact' }"
                    >
                        <span>Información de Contacto</span>
                        <i class="bi" :class="openAccordion === 'contact' ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                    </button>
                    <div class="accordion-content" v-show="openAccordion === 'contact'">
                        <div class="contact-item" v-if="company?.web">
                            <i class="bi bi-globe"></i>
                            <span>{{ company.web }}</span>
                        </div>
                        <div class="contact-item" v-if="card?.email">
                            <i class="bi bi-envelope"></i>
                            <span>{{ card.email }}</span>
                        </div>
                        <div class="contact-item" v-if="card?.mobile_phone">
                            <i class="bi bi-telephone"></i>
                            <span>{{ card.mobile_phone }}</span>
                        </div>
                        <div class="contact-item" v-if="company?.address">
                            <i class="bi bi-geo-alt"></i>
                            <span>{{ company.address }}</span>
                        </div>
                    </div>
                </div>

                <!-- Servicios -->
                <div class="accordion-item" v-if="services?.length">
                    <button
                        class="accordion-header section-3"
                        @click="toggleAccordion('services')"
                        :class="{ active: openAccordion === 'services' }"
                    >
                        <span>Servicios</span>
                        <i class="bi" :class="openAccordion === 'services' ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                    </button>
                    <div class="accordion-content" v-show="openAccordion === 'services'">
                        <button
                            v-for="service in services"
                            :key="service.id"
                            class="service-btn"
                            @click="openModal('service', service)"
                        >
                            {{ service.name }}
                        </button>
                    </div>
                </div>

                <!-- Productos -->
                <div class="accordion-item" v-if="products?.length">
                    <button
                        class="accordion-header section-4"
                        @click="toggleAccordion('products')"
                        :class="{ active: openAccordion === 'products' }"
                    >
                        <span>Productos</span>
                        <i class="bi" :class="openAccordion === 'products' ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                    </button>
                    <div class="accordion-content" v-show="openAccordion === 'products'">
                        <button
                            v-for="product in products"
                            :key="product.id"
                            class="product-btn"
                            @click="openModal('product', product)"
                        >
                            {{ product.name }}
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Pie de Página -->
        <footer class="template-footer" :style="footerBackgroundStyle">
            <p class="footer-company-name">{{ company?.name || 'Empresa' }}</p>
        </footer>

        <!-- Modal para Servicios/Productos -->
        <div class="modal-overlay" v-if="modalOpen" @click.self="closeModal">
            <div class="modal-content">
                <div class="modal-header">
                    <h5>{{ modalData?.name }}</h5>
                    <button class="modal-close" @click="closeModal">&times;</button>
                </div>
                <div class="modal-body">
                    <img
                        v-if="modalData?.image_path"
                        :src="modalData.image_path"
                        :alt="modalData.name"
                        class="modal-image"
                    >
                    <div v-if="modalType === 'product' && modalData?.price" class="price-section">
                        <p v-if="modalData.discount" class="original-price">
                            Antes: <span>${{ modalData.price }}</span>
                        </p>
                        <p class="current-price">
                            {{ modalData.discount ? 'Ahora:' : 'Precio:' }}
                            ${{ modalData.discount || modalData.price }}
                        </p>
                        <p v-if="modalData.comment" class="price-comment">{{ modalData.comment }}</p>
                    </div>
                    <p class="modal-description">{{ modalData?.description }}</p>
                </div>
                <div class="modal-footer">
                    <button class="btn-close-modal" @click="closeModal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'TemplateModern',

    props: {
        customization: {
            type: Object,
            default: () => ({}),
        },
        company: {
            type: Object,
            default: () => ({}),
        },
        card: {
            type: Object,
            default: () => ({}),
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
            openAccordion: null,
            modalOpen: false,
            modalType: null,
            modalData: null,
            showPlaceholder: true,
        }
    },

    computed: {
        whatsappLink() {
            if (!this.card?.whatsapp) return '#'
            const phone = this.card.whatsapp.replace(/\D/g, '')
            const message = encodeURIComponent(this.card.whatsapp_message || 'Hola, me gustaría obtener más información.')
            return `https://api.whatsapp.com/send?phone=${phone}&text=${message}`
        },

        // Qué mostrar en la cabecera: logo, nombre u oculto
        headerContent() {
            return this.customization?.header?.contenido || 'logo'
        },

        // Si mostrar la foto de perfil
        showPhoto() {
            const mostrar = this.customization?.photo?.mostrar
            return mostrar !== false && (this.card?.photo_path || this.showPlaceholder)
        },

        // Si mostrar la sección hero (cabecera visible O foto visible)
        showHeroSection() {
            return this.headerContent !== 'oculto' || this.showPhoto
        },

        // Si mostrar el fondo parcial
        showPhotoBackground() {
            const tipo = this.customization?.photoBackground?.tipo
            return tipo !== 'oculto' && this.showHeroSection
        },

        // Tamaño de la foto (para cálculos)
        photoSize() {
            return this.customization?.photo?.tamano || 120
        },

        // Estilo de la foto de perfil
        photoStyle() {
            const photo = this.customization?.photo || {}
            return {
                width: `${photo.tamano || 120}px`,
                height: `${photo.tamano || 120}px`,
                borderRadius: `${photo.radio ?? 15}px`,
                borderWidth: `${photo.tamanoBorde ?? 3}px`,
                borderStyle: photo.tipoBorde || 'solid',
                borderColor: photo.colorBorde || '#ffffff',
            }
        },

        // Estilo de la sección hero (contiene cabecera + foto)
        heroSectionStyle() {
            if (!this.showPhoto) {
                return { marginBottom: '0' }
            }
            // Margen inferior = 1/3 del tamaño de la foto (la parte que sobresale)
            const margenInferior = Math.round(this.photoSize / 3)
            return { marginBottom: `${margenInferior}px` }
        },

        // Estilo del logo
        logoStyle() {
            const header = this.customization?.header || {}
            return {
                width: `${header.anchoLogo || 150}px`,
                borderWidth: `${header.logoBorde || 0}px`,
                borderStyle: header.logoTipoBorde || 'solid',
                borderColor: header.logoColorBorde || '#ffffff',
                borderRadius: `${header.logoRedondeo || 0}px`,
            }
        },

        // Estilo del nombre de empresa en header
        companyNameStyle() {
            const header = this.customization?.header || {}
            return {
                color: header.colorFuente || '#ffffff',
                fontSize: `${header.tamanoFuente || 1.2}em`,
            }
        },

        // Estilo del nombre
        firstNameStyle() {
            const profile = this.customization?.profile || {}
            return {
                fontSize: `${profile.nombreTamano || 1.5}em`,
                color: profile.nombreColor || '#333333',
                fontWeight: profile.nombrePeso || '600',
            }
        },

        // Estilo del apellido
        lastNameStyle() {
            const profile = this.customization?.profile || {}
            return {
                fontSize: `${profile.apellidoTamano || 1.5}em`,
                color: profile.apellidoColor || '#555555',
                fontWeight: profile.apellidoPeso || '400',
            }
        },

        // Estilo del cargo
        jobTitleStyle() {
            const profile = this.customization?.profile || {}
            return {
                fontSize: `${profile.cargoTamano || 1}em`,
                color: profile.cargoColor || '#666666',
                fontWeight: profile.cargoPeso || '400',
            }
        },

        // Estilo de la sección de perfil
        profileSectionStyle() {
            const profile = this.customization?.profile || {}
            return {
                background: profile.colorFondo || 'transparent',
                borderWidth: `${profile.tamanoBorde || 0}px`,
                borderStyle: profile.tipoBorde || 'none',
                borderColor: profile.colorBorde || 'transparent',
                borderRadius: `${profile.radio || 0}px`,
            }
        },

        // Estilos base de botones sociales
        socialBtnBaseStyle() {
            const social = this.customization?.social || {}
            return {
                borderRadius: `${social.radio ?? 50}%`,
                color: social.colorIcono || '#ffffff',
            }
        },

        // Estilo del fondo parcial
        photoBackgroundStyle() {
            const bg = this.customization?.photoBackground || {}
            const tipo = bg.tipo || 'degradado'
            const color1 = bg.color1 || '#6366f1'
            const color2 = bg.color2 || '#8b5cf6'
            const direccion = bg.direccion || 'diagonal'
            const mostrarPatron = bg.mostrarPatron || false
            const patron = bg.patron || 'circulos'

            const styles = {}

            // Usar bottom en lugar de height para ajuste automático
            // El fondo se extiende desde top:0 hasta 1/3 de la foto desde la base
            if (this.showPhoto) {
                const bottomOffset = Math.round(this.photoSize / 3)
                styles.bottom = `${bottomOffset}px`
            } else {
                // Si no hay foto, el fondo cubre todo el hero-section
                styles.bottom = '0'
            }

            // Definir patrones SVG
            const patrones = {
                circulos: `url("data:image/svg+xml,%3Csvg width='20' height='20' xmlns='http://www.w3.org/2000/svg'%3E%3Ccircle cx='10' cy='10' r='3' fill='rgba(255,255,255,0.15)'/%3E%3C/svg%3E")`,
                lineas: `url("data:image/svg+xml,%3Csvg width='20' height='20' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M0 10h20' stroke='rgba(255,255,255,0.15)' stroke-width='1'/%3E%3C/svg%3E")`,
                puntos: `url("data:image/svg+xml,%3Csvg width='10' height='10' xmlns='http://www.w3.org/2000/svg'%3E%3Ccircle cx='5' cy='5' r='1' fill='rgba(255,255,255,0.2)'/%3E%3C/svg%3E")`,
                ondas: `url("data:image/svg+xml,%3Csvg width='40' height='20' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M0 10 Q10 0 20 10 T40 10' fill='none' stroke='rgba(255,255,255,0.12)' stroke-width='1'/%3E%3C/svg%3E")`,
                geometrico: `url("data:image/svg+xml,%3Csvg width='24' height='24' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M0 0h12v12H0zM12 12h12v12H12z' fill='rgba(255,255,255,0.08)'/%3E%3C/svg%3E")`
            }

            // Construir el fondo base según el tipo
            let fondoBase = ''

            if (tipo === 'oculto') {
                styles.background = 'transparent'
                return styles
            } else if (tipo === 'solido') {
                fondoBase = color1
            } else if (tipo === 'degradado') {
                const dir = {
                    horizontal: 'to right',
                    vertical: 'to bottom',
                    diagonal: '135deg'
                }[direccion] || '135deg'
                fondoBase = `linear-gradient(${dir}, ${color1}, ${color2})`
            }

            // Aplicar patrón si está activado
            if (mostrarPatron && tipo !== 'oculto') {
                const patronSvg = patrones[patron] || patrones.circulos
                styles.background = `${patronSvg}, ${fondoBase}`
            } else {
                styles.background = fondoBase
            }

            return styles
        },

        // Estilo del fondo del pie de página
        footerBackgroundStyle() {
            const footer = this.customization?.footer || {}
            const tipo = footer.tipoFondo || 'solido'
            const color1 = footer.color1 || '#f5f5f5'
            const color2 = footer.color2 || '#e0e0e0'
            const direccion = footer.direccion || 'horizontal'
            const mostrarPatron = footer.mostrarPatron || false
            const patron = footer.patron || 'circulos'
            const alturaMinima = footer.alturaMinima || 60

            const styles = {
                minHeight: `${alturaMinima}px`,
            }

            // Definir patrones SVG (mismo que photoBackground)
            const patrones = {
                circulos: `url("data:image/svg+xml,%3Csvg width='20' height='20' xmlns='http://www.w3.org/2000/svg'%3E%3Ccircle cx='10' cy='10' r='3' fill='rgba(0,0,0,0.08)'/%3E%3C/svg%3E")`,
                lineas: `url("data:image/svg+xml,%3Csvg width='20' height='20' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M0 10h20' stroke='rgba(0,0,0,0.08)' stroke-width='1'/%3E%3C/svg%3E")`,
                puntos: `url("data:image/svg+xml,%3Csvg width='10' height='10' xmlns='http://www.w3.org/2000/svg'%3E%3Ccircle cx='5' cy='5' r='1' fill='rgba(0,0,0,0.1)'/%3E%3C/svg%3E")`,
                ondas: `url("data:image/svg+xml,%3Csvg width='40' height='20' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M0 10 Q10 0 20 10 T40 10' fill='none' stroke='rgba(0,0,0,0.06)' stroke-width='1'/%3E%3C/svg%3E")`,
                geometrico: `url("data:image/svg+xml,%3Csvg width='24' height='24' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M0 0h12v12H0zM12 12h12v12H12z' fill='rgba(0,0,0,0.05)'/%3E%3C/svg%3E")`
            }

            // Construir el fondo base según el tipo
            let fondoBase = ''

            if (tipo === 'transparente') {
                styles.background = 'transparent'
                return styles
            } else if (tipo === 'solido') {
                fondoBase = color1
            } else if (tipo === 'degradado') {
                const dir = {
                    horizontal: 'to right',
                    vertical: 'to bottom',
                    diagonal: '135deg'
                }[direccion] || 'to right'
                fondoBase = `linear-gradient(${dir}, ${color1}, ${color2})`
            }

            // Aplicar patrón si está activado
            if (mostrarPatron && tipo !== 'transparente') {
                const patronSvg = patrones[patron] || patrones.circulos
                styles.background = `${patronSvg}, ${fondoBase}`
            } else {
                styles.background = fondoBase
            }

            return styles
        },
    },

    methods: {
        toggleAccordion(section) {
            this.openAccordion = this.openAccordion === section ? null : section
        },

        openModal(type, data) {
            this.modalType = type
            this.modalData = data
            this.modalOpen = true
        },

        closeModal() {
            this.modalOpen = false
            this.modalType = null
            this.modalData = null
        },

        // Obtener estilo de botón social por tipo
        getSocialBtnStyle(type) {
            const social = this.customization?.social || {}
            // Mapeo de tipo a nombre de propiedad y color por defecto
            const mapping = {
                facebook: { key: 'fondoFacebook', default: '#3b5998' },
                instagram: { key: 'fondoInstagram', default: '#e4405f' },
                twitter: { key: 'fondoTwitter', default: '#1da1f2' },
                linkedin: { key: 'fondoLinkedin', default: '#0077b5' },
                youtube: { key: 'fondoYoutube', default: '#ff0000' },
                whatsapp: { key: 'fondoWhatsapp', default: '#25d366' },
                email: { key: 'fondoEmail', default: '#ea4335' },
                phone: { key: 'fondoCelular', default: '#34b7f1' },
                web: { key: 'fondoWeb', default: '#4285f4' },
                location: { key: 'fondoUbicacion', default: '#ff5722' },
            }
            const config = mapping[type] || { key: '', default: '#666666' }
            return {
                borderRadius: `${social.radio ?? 50}%`,
                color: social.colorIcono || '#ffffff',
                background: social[config.key] || config.default,
            }
        },
    },
}
</script>

<style scoped>
/**
 * TemplateModern - Estilos con CSS Variables
 *
 * Todas las propiedades personalizables usan CSS Variables
 * que son inyectadas por el componente padre (LivePreview).
 * Esto permite actualización en tiempo real sin re-render.
 */

.card-template.modern {
    font-family: var(--general-font-family, 'Montserrat', sans-serif);
    color: var(--general-color-fuente, #333333);
    background-color: var(--general-color-fondo, #ffffff);
    min-height: 100%;
    display: flex;
    flex-direction: column;
}

/* Sección Hero (Cabecera + Fondo + Foto) */
.hero-section {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.photo-background {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    z-index: 0;
}

/* Espaciador cuando cabecera está oculta */
.header-spacer {
    height: 30px;
    position: relative;
    z-index: 1;
}

/* Cabecera */
.template-header {
    position: relative;
    z-index: 1;
    padding: 1rem;
    text-align: center;
    width: 100%;
}

.company-logo {
    width: var(--header-ancho-logo, 150px);
    height: auto;
    max-width: 100%;
}

.company-name {
    color: var(--header-color-fuente, #ffffff);
    font-size: var(--header-tamano-fuente, 1.2em);
    margin: 0;
}

/* Contenedor de Foto */
.photo-container {
    position: relative;
    z-index: 1;
}

.profile-photo,
.photo-placeholder {
    width: var(--photo-tamano, 150px);
    height: var(--photo-tamano, 150px);
    border-radius: var(--photo-radio, 15px);
    border-width: var(--photo-tamano-borde, 3px);
    border-style: var(--photo-tipo-borde, solid);
    border-color: var(--photo-color-borde, #ffffff);
    object-fit: cover;
}

.photo-placeholder {
    background: #e0e0e0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3rem;
    color: #999;
}

.profile-photo.with-shadow,
.photo-placeholder.with-shadow {
    box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.2);
}

/* Datos Personales */
.profile-section {
    padding: 1rem;
    text-align: center;
    background: var(--profile-color-fondo, transparent);
    border:
        var(--profile-tamano-borde, 0)
        var(--profile-tipo-borde, none)
        var(--profile-color-borde, transparent);
    border-radius: var(--profile-radio, 0);
    margin: 0 0.5rem;
}

.full-name {
    margin: 0 0 0.25rem 0;
}

.first-name {
    font-size: var(--profile-nombre-tamano, 1.5em);
    color: var(--profile-nombre-color, #333333);
    font-weight: var(--profile-nombre-peso, 600);
}

.last-name {
    font-size: var(--profile-apellido-tamano, 1.5em);
    color: var(--profile-apellido-color, #555555);
    font-weight: var(--profile-apellido-peso, 400);
    margin-left: 0.25rem;
}

.job-title {
    font-size: var(--profile-cargo-tamano, 1em);
    color: var(--profile-cargo-color, #666666);
    font-weight: var(--profile-cargo-peso, 400);
    margin: 0;
}

.profile-data.with-text-shadow .first-name,
.profile-data.with-text-shadow .last-name {
    text-shadow: 0px 2px 5px rgba(0, 0, 0, 0.3);
}

/* Redes Sociales */
.social-section {
    padding: 1rem;
    text-align: center;
}

.social-icons {
    list-style: none;
    padding: 0;
    margin: 0 0 0.75rem 0;
    display: flex;
    justify-content: center;
    gap: 0.25rem;
}

.social-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border-radius: var(--social-radio, 50%);
    color: var(--social-color-icono, #ffffff);
    text-decoration: none;
    font-size: 1.1rem;
    transition: transform 0.2s, opacity 0.2s;
}

.social-btn:hover {
    transform: scale(1.1);
    opacity: 0.9;
}

.social-btn.facebook { background: var(--social-fondo-facebook, #3b5998); }
.social-btn.instagram { background: var(--social-fondo-instagram, #e4405f); }
.social-btn.twitter { background: var(--social-fondo-twitter, #1da1f2); }
.social-btn.linkedin { background: var(--social-fondo-linkedin, #0077b5); }
.social-btn.youtube { background: var(--social-fondo-youtube, #ff0000); }
.social-btn.whatsapp { background: var(--social-fondo-whatsapp, #25d366); }
.social-btn.email { background: var(--social-fondo-email, #ea4335); }
.social-btn.phone { background: var(--social-fondo-celular, #34b7f1); }
.social-btn.web { background: var(--social-fondo-web, #4285f4); }
.social-btn.location { background: var(--social-fondo-ubicacion, #ff5722); }

.social-with-shadow .social-btn {
    box-shadow: 2px 2px 15px rgba(0, 0, 0, 0.3) inset;
}

/* Acordeón */
.accordion-section {
    padding: 0.5rem;
    margin: 0 0.25rem;
}

.accordion {
    border-radius: var(--accordion-radio, 8px);
    overflow: hidden;
}

.accordion-item {
    overflow: hidden;
}

/* Separador sutil entre items (excepto el primero) */
.accordion-item + .accordion-item .accordion-header {
    border-top: 1px solid rgba(255, 255, 255, 0.2);
}

.accordion-header {
    width: 100%;
    padding: 0.75rem 1rem;
    border: none;
    background: var(--accordion-color-seccion1, #6366f1);
    color: var(--accordion-color-fuente-enlace, #ffffff);
    font-size: var(--accordion-tamano-fuente-enlace, 1rem);
    font-weight: var(--accordion-peso-fuente-enlace, 500);
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: background-color 0.2s;
}

.accordion-header.section-1 { background: var(--accordion-color-seccion1, #6366f1); }
.accordion-header.section-2 { background: var(--accordion-color-seccion2, #8b5cf6); }
.accordion-header.section-3 { background: var(--accordion-color-seccion3, #a855f7); }
.accordion-header.section-4 { background: var(--accordion-color-seccion4, #d946ef); }
.accordion-header.section-5 { background: var(--accordion-color-seccion5, #ec4899); }

/* Redondeo en el primer elemento */
.accordion-item:first-child .accordion-header {
    border-top-left-radius: var(--accordion-radio, 8px);
    border-top-right-radius: var(--accordion-radio, 8px);
}

/* Redondeo en el último elemento (header cuando está cerrado) */
.accordion-item:last-child .accordion-header:not(.active) {
    border-bottom-left-radius: var(--accordion-radio, 8px);
    border-bottom-right-radius: var(--accordion-radio, 8px);
}

.accordion-content {
    background: var(--accordion-color-cuerpo, #ffffff);
    padding: 1rem;
    color: var(--accordion-color-fuente, #333333);
    border:
        var(--accordion-tamano-borde, 1px)
        var(--accordion-tipo-borde, solid)
        var(--accordion-color-borde, #e0e0e0);
    border-top: none;
}

/* Redondeo en el contenido del último elemento (cuando está abierto) */
.accordion-item:last-child .accordion-content {
    border-bottom-left-radius: var(--accordion-radio, 8px);
    border-bottom-right-radius: var(--accordion-radio, 8px);
}

.contact-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.5rem 0;
    border-bottom: 1px solid #eee;
}

.contact-item:last-child {
    border-bottom: none;
}

.contact-item i {
    width: 20px;
    text-align: center;
    color: var(--accordion-color-seccion2, #8b5cf6);
}

.service-btn,
.product-btn {
    display: block;
    width: 100%;
    padding: 0.75rem;
    margin-bottom: 0.5rem;
    background: #f8f9fa;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    text-align: left;
    cursor: pointer;
    transition: background-color 0.2s;
}

.service-btn:hover,
.product-btn:hover {
    background: #e9ecef;
}

.service-btn:last-child,
.product-btn:last-child {
    margin-bottom: 0;
}

/* Pie de Página */
.template-footer {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    margin-top: auto;
    border-top:
        var(--footer-tamano-borde, 1px)
        var(--footer-tipo-borde, solid)
        var(--footer-color-borde, #e0e0e0);
}

.footer-company-name {
    margin: 0;
    color: var(--footer-color-fuente, #666666);
    font-size: var(--footer-tamano-fuente, 0.9em);
    font-weight: var(--footer-peso-fuente, 400);
    text-align: center;
}

/* Modal */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    padding: 1rem;
}

.modal-content {
    background: white;
    border-radius: 12px;
    max-width: 400px;
    width: 100%;
    max-height: 80vh;
    overflow-y: auto;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    border-bottom: 1px solid #eee;
}

.modal-header h5 {
    margin: 0;
    font-weight: 600;
}

.modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: #666;
}

.modal-body {
    padding: 1rem;
}

.modal-image {
    width: 100%;
    border-radius: 8px;
    margin-bottom: 1rem;
}

.price-section {
    margin-bottom: 1rem;
}

.original-price {
    color: #666;
    font-style: italic;
}

.original-price span {
    text-decoration: line-through;
}

.current-price {
    background: #28a745;
    color: white;
    padding: 0.75rem;
    border-radius: 6px;
    font-size: 1.25rem;
    font-weight: bold;
    text-align: center;
}

.price-comment {
    text-align: center;
    font-style: italic;
    color: #666;
    margin-top: 0.5rem;
}

.modal-description {
    color: #333;
    line-height: 1.6;
}

.modal-footer {
    padding: 1rem;
    border-top: 1px solid #eee;
}

.btn-close-modal {
    width: 100%;
    padding: 0.75rem;
    background: #333;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 1rem;
    cursor: pointer;
}

.btn-close-modal:hover {
    background: #444;
}
</style>
