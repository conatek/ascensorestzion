<template>
    <div class="card-template cristal" :class="{ 'glass-on': glassEnabled }">
        <!-- Luces de ambiente: van detrás del cristal. Sin ellas el backdrop-filter
             no tiene nada que desenfocar y el efecto no se percibe. -->
        <template v-if="glassEnabled">
            <div class="cr-ambient cr-ambient-1" :style="ambient1Style"></div>
            <div class="cr-ambient cr-ambient-2" :style="ambient2Style"></div>
            <div class="cr-ambient cr-ambient-3" :style="ambient3Style"></div>
        </template>

        <!-- Panel de cristal: una sola lámina que envuelve TODO el contenido,
             con un margen uniforme por los cuatro lados. -->
        <div class="cr-glass-panel" :style="glassPanelStyle">

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
                <!-- Logo de marca (SVG inline): rojo y verde fijos; grises
                     adaptados al fondo. -->
                <div
                    v-if="headerContent === 'logo'"
                    class="company-logo cr-logo"
                    :style="logoStyle"
                    role="img"
                    :aria-label="company?.name || 'Ascensores Tzion'"
                    v-html="logoSvg"
                ></div>
                <h4
                    v-else-if="company?.name && headerContent === 'nombre'"
                    class="company-name"
                    :style="companyNameStyle"
                >
                    {{ company.name }}
                </h4>
            </header>

            <!-- Contenedor de la foto: solo si hay foto real (sin placeholder) -->
            <div class="photo-container" v-if="showPhoto && card?.photo_path">
                <img
                    :src="card.photo_path"
                    :alt="[card.first_name, card.last_name].filter(Boolean).join(' ')"
                    class="profile-photo"
                    :class="{ 'with-shadow': customization?.photo?.sombra }"
                    :style="photoStyle"
                >
            </div>
        </section>

        <!-- Datos Personales -->
        <section class="profile-section" :style="profileSectionStyle"
                 v-if="card?.first_name || card?.last_name || card?.job_title || showVideo">
            <div class="profile-data" :class="{ 'with-text-shadow': customization?.profile?.sombra }">
                <p class="full-name" v-if="card?.first_name || card?.last_name">
                    <span v-if="card?.first_name" class="first-name" :style="firstNameStyle">{{ card.first_name }}</span>
                    <span v-if="card?.last_name" class="last-name" :style="lastNameStyle">{{ card.last_name }}</span>
                </p>
                <p class="job-title" v-if="card?.job_title" :style="jobTitleStyle">{{ card.job_title }}</p>
            </div>

            <!-- Botón de video: abre un modal vertical (9:16) con el embed de YouTube -->
            <button v-if="showVideo" type="button" class="cr-video-btn" :style="glassSurface" @click="openVideo">
                <i class="bi bi-play-circle-fill"></i>
                <span>{{ videoButtonText }}</span>
            </button>
        </section>

        <!-- Redes Sociales -->
        <section class="social-section" :class="{ 'social-with-shadow': customization?.social?.sombra }"
                 v-if="hasSocialLinks">
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
        <section class="accordion-section"
                 v-if="card?.description || hasContactInfo || services?.length || products?.length">
            <div class="accordion">
                <!-- Quién Soy -->
                <div class="accordion-item" v-if="card?.description">
                    <button
                        class="accordion-header section-1"
                        :style="accordionHeaderStyle(1)"
                        @click="toggleAccordion('about')"
                        :class="{ active: openAccordion === 'about' }"
                    >
                        <span>Quién soy</span>
                        <i class="bi" :class="openAccordion === 'about' ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                    </button>
                    <div class="accordion-content" v-show="openAccordion === 'about'" v-html="card.description">
                    </div>
                </div>

                <!-- Información de Contacto -->
                <div class="accordion-item" v-if="hasContactInfo">
                    <button
                        class="accordion-header section-2"
                        :style="accordionHeaderStyle(2)"
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
                        :style="accordionHeaderStyle(3)"
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
                        :style="accordionHeaderStyle(4)"
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
        <footer class="template-footer" v-if="company?.name">
            <p class="footer-company-name" :style="footerNameStyle">{{ company.name }}</p>
        </footer>

        </div><!-- /cr-glass-panel -->

        <!-- Modal para Servicios/Productos -->
        <Teleport to="body">
        <div class="dm-overlay" v-if="modalOpen" @click.self="closeModal">
            <div class="dm-box">
                <div class="dm-header">
                    <h5>{{ modalData?.name }}</h5>
                    <button class="dm-close" @click="closeModal">&times;</button>
                </div>
                <div class="dm-body">
                    <img
                        v-if="modalData?.image_path"
                        :src="modalData.image_path"
                        :alt="modalData.name"
                        class="dm-image"
                    >
                    <div v-if="modalType === 'product' && modalData?.price" class="dm-price-section">
                        <p v-if="modalData.discount" class="dm-original-price">
                            Antes: <span>${{ modalData.price }}</span>
                        </p>
                        <p class="dm-current-price">
                            {{ modalData.discount ? 'Ahora:' : 'Precio:' }}
                            ${{ modalData.discount || modalData.price }}
                        </p>
                        <p v-if="modalData.comment" class="dm-price-comment">{{ modalData.comment }}</p>
                    </div>
                    <div class="dm-description" v-html="modalData?.description"></div>
                </div>
                <div class="dm-footer">
                    <button class="dm-btn-close" @click="closeModal">Cerrar</button>
                </div>
            </div>
        </div>
        </Teleport>

        <!-- Modal de video vertical (9:16) -->
        <Teleport to="body">
        <div class="cr-video-overlay" v-if="videoOpen" @click.self="closeVideo">
            <div class="cr-video-modal">
                <button class="cr-video-close" @click="closeVideo" aria-label="Cerrar"><i class="bi bi-x-lg"></i></button>
                <div class="cr-video-frame">
                    <!-- YouTube (si se indicó una URL) o el video vertical por defecto -->
                    <iframe
                        v-if="videoOpen && videoIsYouTube"
                        :src="`https://www.youtube.com/embed/${videoId}?autoplay=1&rel=0&playsinline=1`"
                        title="Video"
                        frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen
                    ></iframe>
                    <video
                        v-else-if="videoOpen"
                        :src="defaultVideoUrl"
                        controls
                        autoplay
                        playsinline
                        preload="metadata"
                    ></video>
                </div>
            </div>
        </div>
        </Teleport>
    </div>
</template>

<script>
import { extractYouTubeId } from '@/utils/youtube.js'
// Logo de marca con eslogan, en SVG (curvas). Se inyecta inline para poder
// recolorear SOLO los grises según el fondo; el rojo y el verde se conservan.
import atzionLogoRaw from '@/assets/atzion-logo-eslogan.svg?raw'

// Colores por defecto de los slots del acordeón. Deben coincidir con los
// defaults del schema (config/templates.php → schemas.cristal.accordion).
const ACCORDION_DEFAULTS = {
    1: '#30ab0a',
    2: '#279208',
    3: '#3dbf1a',
    4: '#ba2831',
    5: '#606060',
}

export default {
    name: 'TemplateCristal',

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
            videoOpen: false,
        }
    },

    computed: {
        /* ---------- Efecto cristal (glassmorphism con luces) ---------- */
        // Activo por defecto: si el cliente nunca tocó la opción, se asume true.
        glassEnabled() {
            return this.customization?.glass?.activar ?? true
        },

        // Opacidad y desenfoque fijos: definen la identidad visual del cristal y no se
        // exponen en el editor (como en la plantilla Impulso). El cliente solo activa el
        // efecto y elige los dos colores de luz.
        glassOpacity() {
            return 0.15
        },

        glassBlur() {
            return 14
        },

        // Lámina de cristal que envuelve TODO el contenido.
        glassPanelStyle() {
            if (!this.glassEnabled) return {}
            return {
                background: `rgba(255, 255, 255, ${this.glassOpacity})`,
                backdropFilter: `blur(${this.glassBlur}px)`,
                WebkitBackdropFilter: `blur(${this.glassBlur}px)`,
                border: '1px solid rgba(255, 255, 255, 0.18)',
            }
        },

        // Superficie interna (botón de video, etc.): va DENTRO del cristal, así que se
        // oscurece en vez de aclararse para no fundirse con el fondo.
        glassSurface() {
            if (!this.glassEnabled) return {}
            return {
                background: 'rgba(0, 0, 0, 0.25)',
                backdropFilter: `blur(${this.glassBlur}px)`,
                WebkitBackdropFilter: `blur(${this.glassBlur}px)`,
                border: '1px solid rgba(255, 255, 255, 0.14)',
            }
        },

        // ¿Hay al menos un enlace social/contacto? Para no pintar la sección vacía.
        hasSocialLinks() {
            const c = this.company || {}
            const k = this.card || {}
            return !!(c.facebook || c.instagram || c.twitter || c.youtube || c.web || c.my_business
                || k.linkedin || k.mobile_phone || k.whatsapp || k.email)
        },

        // ¿Hay datos para el acordeón de contacto?
        hasContactInfo() {
            const c = this.company || {}
            const k = this.card || {}
            return !!(c.web || k.email || k.mobile_phone || c.address)
        },

        // Slot (1..4) de la ÚLTIMA sección visible del acordeón. Como las secciones
        // se pintan en orden, el slot más alto presente es el último.
        lastAccordionSlot() {
            let last = 0
            if (this.card?.description) last = 1
            if (this.hasContactInfo) last = 2
            if (this.services?.length) last = 3
            if (this.products?.length) last = 4
            return last
        },

        ambient1Style() {
            return { background: this.customization?.glass?.colorLuz1 || '#30ab0a' }
        },

        ambient2Style() {
            return { background: this.customization?.glass?.colorLuz2 || '#ba2831' }
        },

        // Tercera luz: retoma el primer color para que el centro no quede muerto.
        ambient3Style() {
            return { background: this.customization?.glass?.colorLuz1 || '#30ab0a' }
        },

        /* ---------- Video ---------- */
        // Acepta la URL tal como la copia el usuario (watch, youtu.be, shorts, embed)
        // o el ID pelado. Ver @/utils/youtube.js
        videoId() {
            return extractYouTubeId(this.customization?.video?.urlId)
        },

        videoIsYouTube() {
            return !!this.videoId
        },

        // Video por defecto (vertical) servido como estático desde public/.
        // Se usa cuando "Mostrar video" está activo pero no se indicó ninguna URL.
        defaultVideoUrl() {
            return '/videos/atzion-intro.mp4'
        },

        // El botón aparece siempre que "Mostrar video" esté activo; si no hay URL,
        // se reproduce el video por defecto.
        showVideo() {
            return this.customization?.video?.mostrar !== false
        },

        videoButtonText() {
            return this.customization?.video?.textoBoton || 'Ver video'
        },

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
            const colorFondo = this.customization?.header?.colorFondo
            return colorFondo && colorFondo !== 'transparent' && this.showHeroSection
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
        // ¿Es oscuro el fondo detrás del logo? (fondo propio de la cabecera si se
        // definió; si no, el del cristal). Decide qué versión del logo usar.
        logoBgIsDark() {
            const header = this.customization?.header || {}
            const hb = header.colorFondo
            if (hb && hb !== 'transparent') {
                return this.isDarkColor(this.glassEnabled ? this.blendWhite(hb, this.glassOpacity) : hb)
            }
            return this.profileIsDark
        },

        // Logo inline con colores adaptativos: rojo (#BA2831) y verde (#30AB0A)
        // siempre; los dos grises se aclaran sobre fondo oscuro y conservan su tono
        // sobre fondo claro. Se reescriben los fills del <style> del propio SVG.
        logoSvg() {
            const grisMedio = this.logoBgIsDark ? '#E5E7EB' : '#606060'
            const grisClaro = this.logoBgIsDark ? '#AEB4BE' : '#A5A5A5'
            const svg = atzionLogoRaw
                .replace(/#606060/gi, grisMedio)
                .replace(/#A5A5A5/gi, grisClaro)
                // El lienzo original es 1000x1000 pero el arte solo ocupa la franja
                // central (y≈314→805): se recorta el viewBox a su contenido para
                // quitar el gran margen superior que desplazaba el logo hacia abajo.
                .replace('viewBox="0 0 1000 1000"', 'viewBox="14 313 976 493"')
            // Quita el prólogo XML y comentarios para que v-html renderice el <svg>.
            const start = svg.indexOf('<svg')
            return start >= 0 ? svg.slice(start) : svg
        },

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

        // ¿El fondo detrás del perfil es oscuro? Sirve para elegir un color de texto
        // por defecto legible: sin esto, el nombre en gris oscuro sobre un fondo negro
        // queda casi ilegible. Solo afecta a los valores por defecto; si el usuario
        // fijó un color propio, se respeta.
        // Fondo real detrás del texto (nombre, cargo, pie): parte del color del
        // perfil o del general (base OSCURA por defecto en Cristal) y, si el cristal
        // está activo, mezcla el velo blanco que este pone encima. Con esto el color
        // de texto por defecto sale siempre legible.
        effectiveBg() {
            const profile = this.customization?.profile || {}
            const general = this.customization?.general || {}
            let bg = (profile.colorFondo && profile.colorFondo !== 'transparent')
                ? profile.colorFondo
                : (general.colorFondo || '#0f172a')
            if (this.glassEnabled) bg = this.blendWhite(bg, this.glassOpacity)
            return bg
        },

        profileIsDark() {
            return this.isDarkColor(this.effectiveBg)
        },

        // Color del nombre de la empresa en el pie, con contraste según el fondo.
        footerNameStyle() {
            return { color: this.readableTextColor(this.customization?.footer?.colorFuente, '#e5e7eb', '#333333') }
        },

        // Estilo del nombre
        firstNameStyle() {
            const profile = this.customization?.profile || {}
            return {
                fontSize: `${profile.nombreTamano || 1.5}em`,
                color: this.readableTextColor(profile.nombreColor, '#f5f5f5', '#333333'),
                fontWeight: profile.nombrePeso || '600',
            }
        },

        // Estilo del apellido
        lastNameStyle() {
            const profile = this.customization?.profile || {}
            return {
                fontSize: `${profile.apellidoTamano || 1.5}em`,
                color: this.readableTextColor(profile.apellidoColor, '#e2e8f0', '#555555'),
                fontWeight: profile.apellidoPeso || '400',
            }
        },

        // Estilo del cargo
        jobTitleStyle() {
            const profile = this.customization?.profile || {}
            return {
                fontSize: `${profile.cargoTamano || 1}em`,
                color: this.readableTextColor(profile.cargoColor, '#cbd5e1', '#606060'),
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
            const header = this.customization?.header || {}
            const colorFondo = header.colorFondo || 'transparent'

            const styles = {
                background: colorFondo,
            }

            if (this.showPhoto) {
                const bottomOffset = Math.round(this.photoSize / 3)
                styles.bottom = `${bottomOffset}px`
            } else {
                styles.bottom = '0'
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
        // Luminancia percibida (0..255) de un color hex/rgb(); null si no se interpreta.
        luminance(color) {
            if (!color || typeof color !== 'string') return null
            let c = color.trim().toLowerCase()
            if (c === 'transparent' || c === 'none') return null

            let r, g, b
            if (c[0] === '#') {
                c = c.slice(1)
                if (c.length === 3) c = c.split('').map(ch => ch + ch).join('')
                if (c.length !== 6) return null
                r = parseInt(c.slice(0, 2), 16)
                g = parseInt(c.slice(2, 4), 16)
                b = parseInt(c.slice(4, 6), 16)
            } else {
                const m = c.match(/rgba?\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)/)
                if (!m) return null
                r = +m[1]; g = +m[2]; b = +m[3]
            }
            if ([r, g, b].some(n => Number.isNaN(n))) return null

            return 0.299 * r + 0.587 * g + 0.114 * b
        },

        // Oscuro por debajo de 140. Un valor no interpretable se asume claro.
        isDarkColor(color) {
            const lum = this.luminance(color)
            return lum !== null && lum < 140
        },

        // Garantiza legibilidad: respeta el color elegido si contrasta lo suficiente
        // con el fondo real; si no (p. ej. gris oscuro sobre el cristal oscuro), lo
        // sustituye por el claro/oscuro adecuado. `onDark`/`onLight` son los colores
        // a usar según sea oscuro o claro el fondo.
        readableTextColor(chosen, onDark, onLight) {
            const bgLum = this.luminance(this.effectiveBg) ?? 20
            const fgLum = this.luminance(chosen)
            if (fgLum !== null && Math.abs(fgLum - bgLum) >= 90) return chosen
            return bgLum < 140 ? onDark : onLight
        },

        // Mezcla un color hex con blanco por un factor alpha (0..1). Se usa para
        // calcular el fondo real cuando el cristal pone un velo blanco encima.
        blendWhite(hex, alpha) {
            const c = (hex || '').trim().replace('#', '')
            const full = c.length === 3 ? c.split('').map(ch => ch + ch).join('') : c
            if (full.length !== 6) return hex
            const mix = (v) => Math.round(v + (255 - v) * alpha)
            const r = mix(parseInt(full.slice(0, 2), 16))
            const g = mix(parseInt(full.slice(2, 4), 16))
            const b = mix(parseInt(full.slice(4, 6), 16))
            const h = (n) => n.toString(16).padStart(2, '0')
            return `#${h(r)}${h(g)}${h(b)}`
        },

        // Color de un slot del acordeón (1..5): el elegido por el usuario o el
        // valor por defecto. ACCORDION_DEFAULTS es solo el respaldo si faltara.
        slotColor(n) {
            const chosen = this.customization?.accordion?.[`colorSeccion${n}`]
            return chosen || ACCORDION_DEFAULTS[n]
        },

        // Fondo de una cabecera del acordeón según su slot natural (1 Quién soy,
        // 2 Contacto, 3 Servicios, 4 Productos). La ÚLTIMA sección visible toma el
        // color de cierre (seccion4, rojo): así "Servicios" sale rojo cuando no hay
        // "Productos", y con Productos presente el rojo vuelve a Productos. Las demás
        // usan el color de su posición. La paleta (seccion1..4) es editable y
        // reseteable desde el editor.
        accordionHeaderStyle(slot) {
            const n = slot === this.lastAccordionSlot ? 4 : slot
            return { background: this.slotColor(n) }
        },

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

        openVideo() {
            this.videoOpen = true
        },

        closeVideo() {
            this.videoOpen = false
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
            const config = mapping[type] || { key: '', default: '#606060' }
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
 * TemplateCristal - Derivada de Moderna con efecto cristal (glassmorphism con
 * luces ambientales) y modal de video vertical.
 *
 * Las propiedades personalizables usan CSS Variables inyectadas por el padre
 * (LivePreview / CardPublic). Los estilos de cristal se activan con .glass-on.
 */

.card-template.cristal {
    font-family: var(--general-font-family, 'Montserrat', sans-serif);
    color: var(--general-color-fuente, #606060);
    background-color: var(--general-color-fondo, #0f172a);
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    position: relative;
    overflow: hidden;
}

/* ============ EFECTO CRISTAL ============ */

/* Luces de ambiente: blobs de color muy desenfocados detrás del contenido. */
.cristal .cr-ambient {
    position: absolute;
    width: 60%;
    aspect-ratio: 1 / 1;
    border-radius: 50%;
    filter: blur(70px);
    opacity: 0.55;
    z-index: 0;
    pointer-events: none;
    animation: cr-float 14s ease-in-out infinite;
}

.cristal .cr-ambient-1 { top: -8%; left: -12%; }
.cristal .cr-ambient-2 { bottom: -6%; right: -14%; animation-delay: -5s; }
.cristal .cr-ambient-3 { top: 40%; left: 30%; width: 45%; opacity: 0.4; animation-delay: -9s; }

@keyframes cr-float {
    0%, 100% { transform: translate(0, 0) scale(1); }
    50%      { transform: translate(6%, 8%) scale(1.12); }
}

@media (prefers-reduced-motion: reduce) {
    .cristal .cr-ambient { animation: none; }
}

/* Panel de cristal: una sola lámina sobre las luces, ocupando el alto disponible
   con un margen uniforme por los cuatro lados. */
.card-template.cristal > .cr-glass-panel {
    position: relative;
    z-index: 1;
    flex: 1;
    display: flex;
    flex-direction: column;
}

.cristal.glass-on > .cr-glass-panel {
    margin: 16px;
    border-radius: 22px;
    overflow: hidden;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.28);
}

/* ============ BOTÓN DE VIDEO ============ */
.cr-video-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    margin: 0.9rem auto 0.2rem;
    padding: 0.6rem 1.2rem;
    border-radius: 999px;
    color: #fff;
    font-size: 0.95rem;
    font-weight: 600;
    cursor: pointer;
    background: rgba(0, 0, 0, 0.25);
    border: 1px solid rgba(255, 255, 255, 0.14);
    transition: transform 0.15s ease, box-shadow 0.15s ease;
}

.cr-video-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.28);
}

.cr-video-btn i {
    font-size: 1.25rem;
    line-height: 1;
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

/* Logo SVG inyectado con v-html (necesita :deep, no recibe el scope) */
/* Es un <div> (bloque): se centra con margen auto, ya que text-align del
   header solo centraría contenido inline como el <img> anterior. */
.cr-logo {
    line-height: 0;
    margin-left: auto;
    margin-right: auto;
}
.cr-logo :deep(svg) {
    width: 100%;
    height: auto;
    display: block;
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
    color: var(--profile-nombre-color, #333);
    font-weight: var(--profile-nombre-peso, 600);
}

.last-name {
    font-size: var(--profile-apellido-tamano, 1.5em);
    color: var(--profile-apellido-color, #606060);
    font-weight: var(--profile-apellido-peso, 400);
    margin-left: 0.25rem;
}

.job-title {
    font-size: var(--profile-cargo-tamano, 1em);
    color: var(--profile-cargo-color, #606060);
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
    background: var(--accordion-color-seccion1, #30ab0a);
    color: var(--accordion-color-fuente-enlace, #ffffff);
    font-size: var(--accordion-tamano-fuente-enlace, 1rem);
    font-weight: var(--accordion-peso-fuente-enlace, 500);
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: background-color 0.2s;
}

.accordion-header.section-1 { background: var(--accordion-color-seccion1, #30ab0a); }
.accordion-header.section-2 { background: var(--accordion-color-seccion2, #279208); }
.accordion-header.section-3 { background: var(--accordion-color-seccion3, #3dbf1a); }
.accordion-header.section-4 { background: var(--accordion-color-seccion4, #ba2831); }
.accordion-header.section-5 { background: var(--accordion-color-seccion5, #606060); }

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
    color: var(--accordion-color-fuente, #606060);
    border:
        var(--accordion-tamano-borde, 1px)
        var(--accordion-tipo-borde, solid)
        var(--accordion-color-borde, #e0e0e0);
    border-top: none;
}

.accordion-content :deep(p),
.accordion-content :deep(li p) {
    margin-bottom: 0;
}

.accordion-content :deep(ul),
.accordion-content :deep(ol) {
    padding-left: 1.5em;
    margin: 1rem 0;
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
    color: var(--accordion-color-seccion2, #279208);
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

/* Pie de Página: sin color de fondo propio (toma el de la tarjeta) y estático,
   para no solaparse con el contenido al hacer scroll. */
.template-footer {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    margin-top: auto;
    background: transparent;
    border-top:
        var(--footer-tamano-borde, 1px)
        var(--footer-tipo-borde, solid)
        var(--footer-color-borde, rgba(255, 255, 255, 0.15));
}

.footer-company-name {
    margin: 0;
    color: var(--footer-color-fuente, #606060);
    font-size: var(--footer-tamano-fuente, 0.9em);
    font-weight: var(--footer-peso-fuente, 400);
    text-align: center;
}

</style>

<style>
/* Detail Modal (prefijo dm- para evitar conflicto con Bootstrap) */
.dm-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(15, 23, 42, 0.6);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 99999;
    padding: 3rem 1.5rem;
}

.dm-box {
    background: white;
    border-radius: 16px;
    max-width: 420px;
    width: 100%;
    max-height: calc(100vh - 6rem);
    overflow-y: auto;
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
}

.dm-box::-webkit-scrollbar {
    width: 6px;
}

.dm-box::-webkit-scrollbar-track {
    background: transparent;
}

.dm-box::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 3px;
}

.dm-box::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

.dm-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.25rem 1.25rem 1rem;
}

.dm-header h5 {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 600;
    color: #1e293b;
}

.dm-close {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f1f5f9;
    border: none;
    border-radius: 8px;
    font-size: 1.2rem;
    cursor: pointer;
    color: #64748b;
    transition: all 0.2s;
}

.dm-close:hover {
    background: #e2e8f0;
    color: #1e293b;
}

.dm-body {
    padding: 1rem 1.25rem 1.25rem;
}

.dm-image {
    width: 100%;
    border-radius: 10px;
    margin-bottom: 1rem;
}

.dm-price-section {
    margin-bottom: 1rem;
}

.dm-original-price {
    color: #64748b;
    font-style: italic;
}

.dm-original-price span {
    text-decoration: line-through;
}

.dm-current-price {
    background: #30ab0a;
    color: white;
    padding: 0.75rem;
    border-radius: 8px;
    font-size: 1.25rem;
    font-weight: bold;
    text-align: center;
}

.dm-price-comment {
    text-align: center;
    font-style: italic;
    color: #64748b;
    margin-top: 0.5rem;
}

.dm-description {
    color: #475569;
    line-height: 1.6;
    font-size: 0.95rem;
}

.dm-description p,
.dm-description li p {
    margin-bottom: 0;
}

.dm-description ul,
.dm-description ol {
    padding-left: 1.5em;
    margin: 1rem 0;
}

.dm-footer {
    padding: 1rem 1.25rem 1.25rem;
}

.dm-btn-close {
    width: 100%;
    padding: 0.7rem;
    background: #279208;
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 0.95rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
}

.dm-btn-close:hover {
    background: #1f7506;
}

/* ============ MODAL DE VIDEO VERTICAL (9:16) ============ */
/* Sin scope: el contenido va teleportado a <body>, como .dm-overlay. */
.cr-video-overlay {
    position: fixed;
    inset: 0;
    z-index: 100000;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    background: rgba(0, 0, 0, 0.82);
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
}

.cr-video-modal {
    position: relative;
    width: min(92vw, calc((100vh - 4rem) * 9 / 16));
    aspect-ratio: 9 / 16;
    max-height: calc(100vh - 2rem);
    border-radius: 18px;
    overflow: hidden;
    box-shadow: 0 25px 60px rgba(0, 0, 0, 0.5);
    background: #000;
}

.cr-video-frame {
    width: 100%;
    height: 100%;
}

.cr-video-frame iframe,
.cr-video-frame video {
    width: 100%;
    height: 100%;
    border: 0;
    display: block;
    /* El video local puede no ser exactamente 9:16: se contiene sin recortar. */
    object-fit: contain;
    background: #000;
}

.cr-video-close {
    position: absolute;
    top: 8px;
    right: 8px;
    z-index: 2;
    width: 38px;
    height: 38px;
    /* La regla global de custom-cntk.css pone min-height:44px a TODO <button> en
       móvil, lo que estira este botón redondo a un óvalo. Se anula aquí. */
    min-height: 0;
    padding: 0;
    border-radius: 50%;
    border: none;
    cursor: pointer;
    color: #fff;
    background: rgba(0, 0, 0, 0.55);
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.2s;
}

.cr-video-close i {
    display: block;
    font-size: 1.05rem;
    line-height: 1;
}

.cr-video-close:hover {
    background: rgba(0, 0, 0, 0.8);
}
</style>
