<template>
    <div class="card-template action" :style="containerStyle">

        <header class="hero-section" :style="heroStyle">
            <div v-if="showOverlay" class="hero-overlay" :style="heroOverlayStyle"></div>
            <div v-if="showLogo || showEslogan" class="hero-content">
                <template v-if="showLogo">
                    <img
                        v-if="company?.logo_path"
                        :src="company.logo_path"
                        :alt="company.name"
                        class="hero-logo"
                    >
                    <h2 v-else :style="heroTitleStyle">{{ company?.name }}</h2>
                </template>

                <p v-if="showEslogan" class="hero-slogan" :style="heroSloganStyle">
                    {{ customization?.hero?.eslogan || card?.job_title || 'Tu gimnasio de resultados' }}
                </p>
            </div>
        </header>

        <div class="main-cta-container">
            <button class="btn-main-cta" :style="btnCtaStyle" @click="handleCtaClick">
                {{ customization?.cta?.texto || 'Solicita informacion' }}
            </button>
        </div>

        <main class="main-content">
            <div class="quick-contact-row">
                <a v-if="card?.mobile_phone" :href="`tel:${card.mobile_phone}`" class="quick-btn" :style="quickBtnLlamarStyle">
                    <i class="bi bi-telephone-fill"></i> Llamar
                </a>
                <a v-if="card?.whatsapp" :href="whatsappLink" class="quick-btn" :style="quickBtnWhatsappStyle">
                    <i class="bi bi-whatsapp"></i> WhatsApp
                </a>
                <a v-if="card?.email" :href="`mailto:${card.email}`" class="quick-btn" :style="quickBtnEmailStyle">
                    <i class="bi bi-envelope-fill"></i> Email
                </a>
            </div>

            <div class="video-wrapper" v-if="customization?.video?.mostrar !== false" :style="{ borderRadius: (customization?.video?.radio ?? 8) + 'px' }">
                <iframe
                    width="100%"
                    height="100%"
                    :src="`https://www.youtube.com/embed/${customization?.video?.urlId || 'dQw4w9WgXcQ'}`"
                    title="YouTube video player"
                    frameborder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen>
                </iframe>
            </div>

            <div class="action-grid">
                <button class="action-block" :style="actionBlockStyle" @click="openSection('about')">
                    <span class="action-text" :style="actionTextStyle">{{ customization?.actionButtons?.textoBoton1 || 'Sobre nosotros' }}</span>
                    <i class="action-icon" :class="'bi ' + (customization?.actionButtons?.iconoBoton1 || 'bi-people-fill')" :style="actionIconStyle(customization?.actionButtons?.colorIconoBoton1 || '#1a237e')"></i>
                </button>
                <button class="action-block" :style="actionBlockStyle" @click="openSection('services')">
                    <span class="action-text" :style="actionTextStyle">{{ customization?.actionButtons?.textoBoton2 || 'Nuestros servicios' }}</span>
                    <i class="action-icon" :class="'bi ' + (customization?.actionButtons?.iconoBoton2 || 'bi-journal-text')" :style="actionIconStyle(customization?.actionButtons?.colorIconoBoton2 || '#455a64')"></i>
                </button>
                <button class="action-block" :style="actionBlockStyle" @click="openSection('qr')">
                    <span class="action-text" :style="actionTextStyle">{{ customization?.actionButtons?.textoBoton3 || 'Abrir QR' }}</span>
                    <i class="action-icon" :class="'bi ' + (customization?.actionButtons?.iconoBoton3 || 'bi-qr-code')" :style="actionIconStyle(customization?.actionButtons?.colorIconoBoton3 || '#000000')"></i>
                </button>
            </div>

            <div class="social-circles">
                <a v-if="company?.facebook && customization?.social?.mostrarFacebook !== false" :href="company.facebook" target="_blank" class="social-circle" :style="getSocialCircleStyle(customization?.social?.fondoFacebook || '#3b5998')">
                    <i class="bi bi-facebook"></i>
                </a>
                <a v-if="company?.instagram && customization?.social?.mostrarInstagram !== false" :href="company.instagram" target="_blank" class="social-circle" :style="getSocialCircleStyle(customization?.social?.fondoInstagram || '#e1306c')">
                    <i class="bi bi-instagram"></i>
                </a>
                <a v-if="company?.youtube && customization?.social?.mostrarYoutube !== false" :href="company.youtube" target="_blank" class="social-circle" :style="getSocialCircleStyle(customization?.social?.fondoYoutube || '#ff0000')">
                    <i class="bi bi-youtube"></i>
                </a>
                <a v-if="company?.tiktok && customization?.social?.mostrarTiktok !== false" :href="company.tiktok" target="_blank" class="social-circle" :style="getSocialCircleStyle(customization?.social?.fondoTiktok || '#000000')">
                    <i class="bi bi-tiktok"></i>
                </a>
                <a v-if="card?.linkedin && customization?.social?.mostrarLinkedin !== false" :href="card.linkedin" target="_blank" class="social-circle" :style="getSocialCircleStyle(customization?.social?.fondoLinkedin || '#0077b5')">
                    <i class="bi bi-linkedin"></i>
                </a>
                <a v-if="company?.address && customization?.social?.mostrarUbicacion !== false" :href="`https://maps.google.com/?q=${company.address}`" target="_blank" class="social-circle" :style="getSocialCircleStyle(customization?.social?.fondoUbicacion || '#0000ed')">
                    <i class="bi bi-geo-alt-fill"></i>
                </a>
                <a v-if="company?.web && customization?.social?.mostrarWeb !== false" :href="company.web" target="_blank" class="social-circle" :style="getSocialCircleStyle(customization?.social?.fondoWeb || '#0000ed')">
                    <i class="bi bi-globe"></i>
                </a>
                <a v-if="customization?.social?.mostrarVcard !== false" href="#" class="social-circle" :style="getSocialCircleStyle(customization?.social?.fondoVcard || '#87ceeb')" @click.prevent="downloadVcard">
                    <i class="bi bi-person-lines-fill"></i>
                </a>
            </div>
        </main>

        <footer class="template-footer" :style="footerStyle">
            <p style="margin: 0">{{ customization?.footer?.texto || 'by NexosCard.com' }}</p>
        </footer>

        <!-- Modal Sobre Nosotros -->
        <div v-if="activeModal === 'about'" class="modal-overlay" @click.self="activeModal = null">
            <div class="modal-container">
                <div class="modal-header">
                    <h3 class="modal-title">{{ customization?.actionButtons?.textoBoton1 || 'Sobre nosotros' }}</h3>
                    <button class="modal-close" @click="activeModal = null">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div v-if="company?.logo_path" class="modal-about-logo">
                        <img :src="company.logo_path" :alt="company.name">
                    </div>
                    <h4 class="modal-about-name">{{ company?.name }}</h4>
                    <p v-if="card?.description" class="modal-about-desc">{{ card.description }}</p>
                    <div v-if="company?.address" class="modal-about-item">
                        <i class="bi bi-geo-alt-fill"></i>
                        <span>{{ company.address }}</span>
                    </div>
                    <div v-if="company?.web" class="modal-about-item">
                        <i class="bi bi-globe"></i>
                        <a :href="company.web" target="_blank">{{ company.web }}</a>
                    </div>
                    <div v-if="company?.my_business" class="modal-about-item">
                        <i class="bi bi-google"></i>
                        <a :href="company.my_business" target="_blank">Google My Business</a>
                    </div>
                    <div v-if="card?.mobile_phone" class="modal-about-item">
                        <i class="bi bi-telephone-fill"></i>
                        <a :href="`tel:${card.mobile_phone}`">{{ card.mobile_phone }}</a>
                    </div>
                    <div v-if="card?.email" class="modal-about-item">
                        <i class="bi bi-envelope-fill"></i>
                        <a :href="`mailto:${card.email}`">{{ card.email }}</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Servicios -->
        <div v-if="activeModal === 'services'" class="modal-overlay" @click.self="activeModal = null">
            <div class="modal-container">
                <div class="modal-header">
                    <h3 class="modal-title">{{ customization?.actionButtons?.textoBoton2 || 'Nuestros servicios' }}</h3>
                    <button class="modal-close" @click="activeModal = null">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div v-if="services.length === 0" class="modal-empty">
                        <i class="bi bi-journal-text"></i>
                        <p>No hay servicios disponibles</p>
                    </div>
                    <div v-for="service in services" :key="service.id" class="modal-service-item">
                        <img v-if="service.image_path" :src="service.image_path" :alt="service.name" class="modal-service-img">
                        <div class="modal-service-info">
                            <h5 class="modal-service-name">{{ service.name }}</h5>
                            <p v-if="service.description" class="modal-service-desc">{{ service.description }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal QR -->
        <div v-if="activeModal === 'qr'" class="modal-overlay" @click.self="activeModal = null">
            <div class="modal-container modal-container-sm">
                <div class="modal-header">
                    <h3 class="modal-title">{{ customization?.actionButtons?.textoBoton3 || 'Codigo QR' }}</h3>
                    <button class="modal-close" @click="activeModal = null">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <div class="modal-body modal-qr-body">
                    <img :src="qrCodeUrl" alt="Codigo QR" class="modal-qr-img">
                    <p class="modal-qr-hint">Escanea este codigo para acceder a la tarjeta</p>
                </div>
            </div>
        </div>

    </div>
</template>

<script>
export default {
    name: 'TemplateAction',

    props: {
        customization: { type: Object, default: () => ({}) },
        company: { type: Object, default: () => ({}) },
        card: { type: Object, default: () => ({}) },
        services: { type: Array, default: () => [] },
        products: { type: Array, default: () => [] }
    },

    data() {
        return {
            activeModal: null,
        }
    },

    computed: {
        qrCodeUrl() {
            const slug = this.company?.slug || 'mi-empresa'
            const cardSlug = this.card?.slug || ''
            const baseUrl = window.location.origin
            const url = cardSlug ? `${baseUrl}/${slug}/${cardSlug}` : `${baseUrl}/${slug}`
            return `https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=${encodeURIComponent(url)}`
        },

        showLogo() {
            return this.customization?.hero?.mostrarLogo !== false
        },

        showEslogan() {
            return this.customization?.hero?.mostrarEslogan !== false
        },

        showOverlay() {
            return this.showLogo || this.showEslogan
        },

        whatsappLink() {
            if (!this.card?.whatsapp) return '#'
            const phone = this.card.whatsapp.replace(/\D/g, '')
            return `https://api.whatsapp.com/send?phone=${phone}`
        },

        containerStyle() {
            const gen = this.customization?.general || {}
            return {
                backgroundColor: gen.colorFondo || '#ffffff',
                fontFamily: `'${gen.fuentePrincipal || 'Montserrat'}', sans-serif`,
                minHeight: '100vh',
                display: 'flex',
                flexDirection: 'column',
            }
        },

        heroStyle() {
            const heroImage = this.customization?.hero?.imagenFondo
            const bgImage = heroImage ? `url(${heroImage})` : (this.card?.photo_path ? `url(${this.card.photo_path})` : 'none');
            return {
                backgroundImage: bgImage,
                backgroundSize: 'cover',
                backgroundPosition: 'center',
                backgroundColor: '#2c3e50',
                position: 'relative',
                textAlign: 'center',
                aspectRatio: '16 / 9',
                padding: this.showOverlay ? '3rem 1rem 4rem 1rem' : '0',
            }
        },

        heroOverlayStyle() {
            const opacity = this.customization?.hero?.opacidadOverlay ?? 0.6;
            return {
                position: 'absolute',
                top: 0,
                left: 0,
                right: 0,
                bottom: 0,
                backgroundColor: `rgba(0, 0, 0, ${opacity})`,
                zIndex: 1,
            }
        },

        heroTitleStyle() {
            return {
                color: '#ffffff',
                fontSize: '2rem',
                fontWeight: 'bold',
                margin: '0 0 0.5rem 0',
                position: 'relative',
                zIndex: 2,
            }
        },

        heroSloganStyle() {
            return {
                color: '#ffffff',
                fontSize: '1.2rem',
                fontWeight: '500',
                margin: '1rem 0 0 0',
                position: 'relative',
                zIndex: 2,
            }
        },

        btnCtaStyle() {
            const cta = this.customization?.cta || {};
            const tipoBorde = cta.tipoBorde || 'none';
            return {
                backgroundColor: cta.colorFondo || '#5cb85c',
                color: cta.colorTexto || '#ffffff',
                padding: '0.8rem 2rem',
                borderRadius: `${cta.radio ?? 50}px`,
                fontSize: `${cta.tamanoTexto ?? 1.2}rem`,
                fontWeight: 'bold',
                border: tipoBorde === 'none' ? 'none' : `${cta.grosorBorde ?? 2}px ${tipoBorde} ${cta.colorBorde || '#ffffff'}`,
                boxShadow: (cta.sombra !== false) ? '0 4px 6px rgba(0,0,0,0.2)' : 'none',
                cursor: 'pointer',
                width: '90%',
                maxWidth: '400px',
            }
        },

        actionBlockStyle() {
            const ab = this.customization?.actionButtons || {}
            return {
                backgroundColor: ab.colorFondo || '#ffffff',
                border: `2px solid ${ab.colorBorde || '#000000'}`,
                borderRadius: `${ab.radio ?? 8}px`,
                padding: '0.5rem',
                display: 'flex',
                justifyContent: 'space-between',
                alignItems: 'center',
                cursor: 'pointer',
                minHeight: '60px',
            }
        },

        actionTextStyle() {
            const ab = this.customization?.actionButtons || {}
            return {
                color: ab.colorTexto || '#000000',
            }
        },

        quickBtnBaseStyle() {
            const ct = this.customization?.contactoRapido || {}
            return {
                color: '#ffffff',
                padding: '0.6rem',
                borderRadius: `${ct.radio ?? 6}px`,
                textDecoration: 'none',
                fontWeight: '600',
                fontSize: `${ct.tamanoTexto ?? 0.9}rem`,
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                gap: '5px',
                flex: '1',
            }
        },

        quickBtnLlamarStyle() {
            const ct = this.customization?.contactoRapido || {}
            return { ...this.quickBtnBaseStyle, backgroundColor: ct.colorLlamar || '#5b7cfa' }
        },

        quickBtnWhatsappStyle() {
            const ct = this.customization?.contactoRapido || {}
            return { ...this.quickBtnBaseStyle, backgroundColor: ct.colorWhatsapp || '#10c469' }
        },

        quickBtnEmailStyle() {
            const ct = this.customization?.contactoRapido || {}
            return { ...this.quickBtnBaseStyle, backgroundColor: ct.colorEmail || '#f05050' }
        },

        footerStyle() {
            const f = this.customization?.footer || {}
            return {
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                padding: '1rem',
                marginTop: 'auto',
                fontSize: `${f.tamanoTexto ?? 0.9}rem`,
                color: f.colorTexto || '#666666',
                backgroundColor: f.colorFondo || '#ffffff',
            }
        }
    },

    methods: {
        getQuickBtnStyle(bgColor) {
            return {
                backgroundColor: bgColor,
                color: '#ffffff',
                padding: '0.6rem',
                borderRadius: '6px',
                textDecoration: 'none',
                fontWeight: '600',
                fontSize: '0.9rem',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                gap: '5px',
                flex: '1',
            }
        },

        actionIconStyle(color) {
            return {
                color: color,
                fontSize: '2rem',
                lineHeight: '1',
            }
        },

        getSocialCircleStyle(bgColor) {
            const s = this.customization?.social || {}
            const size = `${s.tamano ?? 40}px`
            return {
                backgroundColor: bgColor,
                color: s.colorIcono || '#ffffff',
                width: size,
                height: size,
                borderRadius: `${s.radio ?? 50}%`,
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                textDecoration: 'none',
                fontSize: `${s.tamanoIcono ?? 1.2}rem`,
                boxShadow: (s.sombra !== false) ? '0 3px 8px rgba(0,0,0,0.3)' : 'none',
            }
        },

        handleCtaClick() {
            alert("Acción de CTA Principal");
        },

        openSection(section) {
            this.activeModal = section;
        },

        downloadVcard() {
            console.log("Descargar contacto");
        }
    }
}
</script>

<style scoped>
/* ESTILOS ESTÁTICOS - Layout y estructura pura */

.card-template.action {
    position: relative;
    box-sizing: border-box;
}

.hero-section {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.hero-content {
    position: relative;
    z-index: 2;
    display: flex;
    flex-direction: column;
    align-items: center;
    width: 100%;
}

.hero-logo {
    max-width: 200px;
    height: auto;
    margin-bottom: 1rem;
}

.main-cta-container {
    display: flex;
    justify-content: center;
    margin-top: -1.5rem; /* Efecto de superposición */
    position: relative;
    z-index: 10;
    margin-bottom: 1.5rem;
}

.main-content {
    padding: 0 1rem;
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.quick-contact-row {
    display: flex;
    gap: 0.5rem;
    width: 100%;
    align-items: stretch;
}

.video-wrapper {
    position: relative;
    width: 100%;
    padding-bottom: 56.25%; /* Relación de aspecto 16:9 */
    background-color: #000;
    overflow: hidden;
}

.video-wrapper iframe {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
}

.action-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.5rem;
}

.action-text {
    font-size: 0.75rem;
    font-weight: 700;
    text-align: left;
    line-height: 1.1;
}

.action-icon {
    margin-left: auto;
}

.social-circles {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 0.8rem;
    margin-top: 1rem;
}

.social-circle:hover, .quick-btn:hover, .action-block:hover {
    filter: brightness(0.9);
    transform: scale(1.02);
    transition: all 0.2s ease;
}

/* Modales */
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.6);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    padding: 1rem;
}

.modal-container {
    background: white;
    border-radius: 16px;
    width: min(480px, 95vw);
    max-height: 85vh;
    overflow-y: auto;
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
}

.modal-container-sm {
    width: min(360px, 90vw);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid #e2e8f0;
}

.modal-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: #1e293b;
    margin: 0;
}

.modal-close {
    width: 32px;
    height: 32px;
    background: #f1f5f9;
    border: none;
    border-radius: 8px;
    color: #64748b;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
}

.modal-close:hover {
    background: #e2e8f0;
    color: #1e293b;
}

.modal-body {
    padding: 1.5rem;
}

/* Modal Sobre Nosotros */
.modal-about-logo {
    text-align: center;
    margin-bottom: 1rem;
}

.modal-about-logo img {
    max-width: 120px;
    height: auto;
    border-radius: 12px;
}

.modal-about-name {
    font-size: 1.15rem;
    font-weight: 700;
    color: #1e293b;
    text-align: center;
    margin: 0 0 0.75rem 0;
}

.modal-about-desc {
    font-size: 0.9rem;
    color: #475569;
    line-height: 1.6;
    margin: 0 0 1.25rem 0;
    text-align: center;
}

.modal-about-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.65rem 0;
    border-top: 1px solid #f1f5f9;
    font-size: 0.9rem;
    color: #334155;
}

.modal-about-item i {
    color: #8b5cf6;
    font-size: 1rem;
    width: 20px;
    text-align: center;
    flex-shrink: 0;
}

.modal-about-item a {
    color: #7c3aed;
    text-decoration: none;
    word-break: break-all;
}

.modal-about-item a:hover {
    text-decoration: underline;
}

/* Modal Servicios */
.modal-service-item {
    display: flex;
    gap: 1rem;
    padding: 1rem 0;
    border-bottom: 1px solid #f1f5f9;
}

.modal-service-item:first-child {
    padding-top: 0;
}

.modal-service-item:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.modal-service-img {
    width: 64px;
    height: 64px;
    border-radius: 10px;
    object-fit: cover;
    flex-shrink: 0;
}

.modal-service-info {
    min-width: 0;
}

.modal-service-name {
    font-size: 0.95rem;
    font-weight: 600;
    color: #1e293b;
    margin: 0 0 0.25rem 0;
}

.modal-service-desc {
    font-size: 0.85rem;
    color: #64748b;
    line-height: 1.5;
    margin: 0;
}

.modal-empty {
    text-align: center;
    padding: 2rem 0;
    color: #94a3b8;
}

.modal-empty i {
    font-size: 2.5rem;
    display: block;
    margin-bottom: 0.75rem;
}

.modal-empty p {
    font-size: 0.9rem;
    margin: 0;
}

/* Modal QR */
.modal-qr-body {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 2rem 1.5rem;
}

.modal-qr-img {
    width: 200px;
    height: 200px;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
}

.modal-qr-hint {
    font-size: 0.85rem;
    color: #64748b;
    margin: 1rem 0 0 0;
    text-align: center;
}
</style>
