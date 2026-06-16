<template>
    <div class="landing-page">
        <PublicNavbar />

        <!-- Hero -->
        <section class="hero">
            <div class="hero-bg">
                <div class="orb orb--green"></div>
                <div class="orb orb--red"></div>
                <div class="grid-texture"></div>
            </div>
            <div class="hero-container">
                <div class="hero-text">
                    <span class="hero-badge">
                        <span class="dot"></span> Transporte vertical · Medellín &amp; Antioquia
                    </span>
                    <h1 class="hero-title">Expertos en <span class="accent">transporte vertical</span></h1>
                    <p class="hero-subtitle">
                        Mantenimiento, reparación, modernización e instalación de ascensores
                        y escaleras eléctricas, con los más altos estándares de seguridad.
                    </p>
                    <div class="hero-actions">
                        <router-link to="/servicios" class="btn-primary">Nuestros Servicios</router-link>
                        <router-link to="/contacto" class="btn-outline">Contáctenos</router-link>
                    </div>
                </div>
                <div class="hero-illustration">
                    <svg class="hero-svg" viewBox="0 0 420 460" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="210" cy="220" r="170" fill="url(#hglow)"/>
                        <rect x="120" y="60" width="180" height="330" rx="10" fill="#0f2417" stroke="#30ab0a" stroke-width="1.5"/>
                        <line x1="120" y1="145" x2="300" y2="145" stroke="#2f6b3a" stroke-width="1" opacity="0.5"/>
                        <line x1="120" y1="225" x2="300" y2="225" stroke="#2f6b3a" stroke-width="1" opacity="0.5"/>
                        <line x1="120" y1="305" x2="300" y2="305" stroke="#2f6b3a" stroke-width="1" opacity="0.5"/>
                        <rect x="138" y="80" width="34" height="46" rx="3" fill="#13351f" stroke="#37c20c" stroke-width="1" opacity="0.7"/>
                        <rect x="138" y="162" width="34" height="46" rx="3" fill="#13351f" stroke="#37c20c" stroke-width="1" opacity="0.7"/>
                        <rect x="138" y="244" width="34" height="46" rx="3" fill="#13351f" stroke="#37c20c" stroke-width="1" opacity="0.7"/>
                        <rect x="138" y="326" width="34" height="46" rx="3" fill="#13351f" stroke="#37c20c" stroke-width="1" opacity="0.7"/>
                        <rect x="248" y="80" width="34" height="46" rx="3" fill="#13351f" stroke="#37c20c" stroke-width="1" opacity="0.7"/>
                        <rect x="248" y="162" width="34" height="46" rx="3" fill="#13351f" stroke="#37c20c" stroke-width="1" opacity="0.7"/>
                        <rect x="248" y="244" width="34" height="46" rx="3" fill="#13351f" stroke="#37c20c" stroke-width="1" opacity="0.7"/>
                        <rect x="248" y="326" width="34" height="46" rx="3" fill="#13351f" stroke="#37c20c" stroke-width="1" opacity="0.7"/>
                        <rect x="185" y="66" width="50" height="318" rx="4" fill="#08130c" stroke="#37c20c" stroke-width="1.5"/>
                        <line x1="200" y1="66" x2="200" y2="155" stroke="#37c20c" stroke-width="1" stroke-dasharray="5 4" opacity="0.8"/>
                        <line x1="220" y1="66" x2="220" y2="155" stroke="#37c20c" stroke-width="1" stroke-dasharray="5 4" opacity="0.8"/>
                        <rect x="190" y="155" width="40" height="60" rx="4" fill="#30ab0a" stroke="#9bf06f" stroke-width="1.5"/>
                        <line x1="210" y1="159" x2="210" y2="211" stroke="#08130c" stroke-width="1.5"/>
                        <polygon points="204,176 210,168 216,176" fill="#0b1120"/>
                        <polygon points="204,194 210,202 216,194" fill="#0b1120"/>
                        <rect x="192" y="360" width="36" height="24" rx="2" fill="#08130c" stroke="#37c20c" stroke-width="1"/>
                        <circle cx="70" cy="100" r="4" fill="#37c20c" opacity="0.6"/>
                        <circle cx="352" cy="130" r="5" fill="#37c20c" opacity="0.4"/>
                        <circle cx="360" cy="380" r="4" fill="#ba2831" opacity="0.5"/>
                        <defs>
                            <radialGradient id="hglow" cx="0.5" cy="0.5" r="0.5">
                                <stop offset="0%" stop-color="#30ab0a" stop-opacity="0.3"/>
                                <stop offset="100%" stop-color="#30ab0a" stop-opacity="0"/>
                            </radialGradient>
                        </defs>
                    </svg>
                </div>
            </div>
        </section>

        <!-- Cifras (banda superpuesta) -->
        <section class="cifras">
            <div class="cifras-band">
                <div class="cifra-card">
                    <span class="cifra-number">+4</span>
                    <span class="cifra-label">años de experiencia</span>
                </div>
                <div class="cifra-card">
                    <span class="cifra-number">+200</span>
                    <span class="cifra-label">equipos atendidos</span>
                </div>
                <div class="cifra-card">
                    <span class="cifra-number">+50</span>
                    <span class="cifra-label">clientes</span>
                </div>
                <div class="cifra-card">
                    <span class="cifra-number">24/7</span>
                    <span class="cifra-label">emergencias</span>
                </div>
            </div>
        </section>

        <!-- Servicios (bento) -->
        <section class="servicios">
            <div class="container">
                <div class="section-head">
                    <span class="eyebrow">Qué hacemos</span>
                    <h2 class="section-title">Soluciones integrales</h2>
                    <p class="section-subtitle">Cubrimos todo el ciclo de vida de sus equipos de transporte vertical con los más altos estándares de calidad y seguridad.</p>
                </div>
                <div class="bento">
                    <div class="bento-card bento-feature">
                        <div class="servicio-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="#37c20c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                                <path d="M9 12l2 2 4-4"/>
                            </svg>
                        </div>
                        <h3 class="servicio-title">Mantenimiento preventivo y correctivo</h3>
                        <p class="servicio-desc">Planes a la medida que maximizan la disponibilidad y la vida útil de cada equipo, con técnicos certificados disponibles 24/7.</p>
                    </div>
                    <div class="bento-card">
                        <div class="servicio-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="#37c20c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/>
                            </svg>
                        </div>
                        <h3 class="servicio-title">Instalación</h3>
                        <p class="servicio-desc">Ascensores nuevos con precisión, seguridad y confort.</p>
                    </div>
                    <div class="bento-card">
                        <div class="servicio-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="#37c20c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/>
                                <polyline points="17 6 23 6 23 12"/>
                            </svg>
                        </div>
                        <h3 class="servicio-title">Modernización</h3>
                        <p class="servicio-desc">Tecnología de última generación para equipos existentes.</p>
                    </div>
                    <div class="bento-card bento-wide">
                        <div class="servicio-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="#37c20c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/>
                            </svg>
                        </div>
                        <h3 class="servicio-title">Reparación</h3>
                        <p class="servicio-desc">Diagnóstico y solución rápida de fallas en cualquier equipo, minimizando los tiempos de inactividad.</p>
                    </div>
                </div>
                <div class="servicios-link-wrap">
                    <router-link to="/servicios" class="servicios-link">
                        Ver todos los servicios
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 12h14"/><path d="M12 5l7 7-7 7"/>
                        </svg>
                    </router-link>
                </div>
            </div>
        </section>

        <!-- Por qué elegirnos (escalonado) -->
        <section class="elegir">
            <div class="container">
                <div class="section-head">
                    <span class="eyebrow">Por qué Tzion</span>
                    <h2 class="section-title">Innovación y confianza en cada proyecto</h2>
                </div>
                <div class="elegir-grid">
                    <div class="elegir-card elegir-green">
                        <div class="elegir-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="#37c20c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                            </svg>
                        </div>
                        <h3 class="elegir-title">Compromiso con la seguridad</h3>
                        <p class="elegir-desc">Cumplimos todas las normativas y estándares vigentes, garantizando la protección de los usuarios y la tranquilidad de nuestros clientes.</p>
                    </div>
                    <div class="elegir-card elegir-red">
                        <div class="elegir-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="#e8505b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 7h-3a2 2 0 01-2-2V2"/>
                                <path d="M9 18a2 2 0 01-2-2V4a2 2 0 012-2h5l4 4v10a2 2 0 01-2 2z"/>
                                <path d="M3 7v10a2 2 0 002 2h4"/><path d="M9 13h6"/><path d="M12 10v6"/>
                            </svg>
                        </div>
                        <h3 class="elegir-title">Soluciones personalizadas</h3>
                        <p class="elegir-desc">Cada edificio es único: diseñamos soluciones a la medida que se adaptan a las necesidades de su infraestructura y presupuesto.</p>
                    </div>
                    <div class="elegir-card elegir-gray">
                        <div class="elegir-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="#93a3bb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                            </svg>
                        </div>
                        <h3 class="elegir-title">Respuesta rápida y eficiente</h3>
                        <p class="elegir-desc">Equipo técnico disponible 24/7 para atender emergencias y minimizar los tiempos de inactividad de sus equipos.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Slogan + CTA -->
        <section class="slogan">
            <div class="slogan-glow"></div>
            <div class="slogan-container">
                <blockquote class="slogan-text">
                    "El éxito no está en elevarnos sobre los demás, sino en elevar a todos a nuevas alturas"
                </blockquote>
                <router-link to="/contacto" class="btn-slogan">Contáctenos</router-link>
            </div>
        </section>

        <PublicFooter />
    </div>
</template>

<script>
import PublicNavbar from '@/components/PublicNavbar.vue';
import PublicFooter from '@/components/PublicFooter.vue';

export default {
    name: 'Landing',
    components: { PublicNavbar, PublicFooter },
};
</script>

<style scoped>
.landing-page {
    min-height: 100vh;
    background: #0b1120;
    font-family: 'Poppins', -apple-system, BlinkMacSystemFont, sans-serif;
    color: #e8eef7;
}

.container { max-width: 1200px; margin: 0 auto; padding: 0 2rem; }

/* ===== Hero ===== */
.hero {
    position: relative;
    padding: 150px 2rem 6rem;
    overflow: hidden;
}
.hero-bg { position: absolute; inset: 0; z-index: 0; }
.orb {
    position: absolute; border-radius: 50%; filter: blur(90px);
}
.orb--green { width: 460px; height: 460px; top: -120px; right: -60px; background: rgba(48, 171, 10, 0.22); }
.orb--red { width: 320px; height: 320px; bottom: -120px; left: -80px; background: rgba(186, 40, 49, 0.12); }
.grid-texture {
    position: absolute; inset: 0;
    background-image:
        linear-gradient(rgba(255, 255, 255, 0.03) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
    background-size: 48px 48px;
    mask-image: radial-gradient(circle at 60% 35%, #000 20%, transparent 70%);
    -webkit-mask-image: radial-gradient(circle at 60% 35%, #000 20%, transparent 70%);
}

.hero-container {
    position: relative; z-index: 1;
    max-width: 1200px; margin: 0 auto;
    display: flex; align-items: center; gap: 4rem;
}
.hero-text { flex: 1.1; }

.hero-badge {
    display: inline-flex; align-items: center; gap: 0.5rem;
    background: rgba(48, 171, 10, 0.1); border: 1px solid rgba(48, 171, 10, 0.3);
    color: #9bdc83; font-size: 0.82rem; font-weight: 500;
    padding: 0.4rem 0.9rem; border-radius: 30px; margin-bottom: 1.5rem;
}
.hero-badge .dot {
    width: 8px; height: 8px; border-radius: 50%; background: #37c20c;
    box-shadow: 0 0 10px #37c20c;
}

.hero-title {
    font-size: 3.2rem; font-weight: 800; color: #f8fafc;
    line-height: 1.12; margin: 0 0 1.25rem;
}
.hero-title .accent {
    background: linear-gradient(120deg, #37c20c, #9bf06f);
    -webkit-background-clip: text; background-clip: text; color: transparent;
}

.hero-subtitle {
    font-size: 1.15rem; color: #93a3bb; line-height: 1.7;
    margin: 0 0 2rem; max-width: 540px;
}

.hero-actions { display: flex; gap: 1rem; flex-wrap: wrap; }

.btn-primary {
    display: inline-flex; align-items: center; padding: 0.85rem 2rem;
    background: linear-gradient(135deg, #30ab0a, #248007); color: #fff;
    font-weight: 600; font-size: 1rem; border-radius: 10px; text-decoration: none;
    box-shadow: 0 10px 28px rgba(48, 171, 10, 0.3); transition: transform 0.2s, box-shadow 0.2s;
}
.btn-primary:hover { transform: translateY(-2px); box-shadow: 0 14px 34px rgba(48, 171, 10, 0.42); }

.btn-outline {
    display: inline-flex; align-items: center; padding: 0.85rem 2rem;
    background: rgba(255, 255, 255, 0.03); color: #e8eef7; font-weight: 600; font-size: 1rem;
    border: 1px solid rgba(255, 255, 255, 0.18); border-radius: 10px; text-decoration: none;
    transition: all 0.2s;
}
.btn-outline:hover { border-color: #30ab0a; color: #4cc40f; }

.hero-illustration { flex: 1; display: flex; justify-content: center; }
.hero-svg { width: 100%; max-width: 360px; height: auto; filter: drop-shadow(0 20px 50px rgba(0, 0, 0, 0.5)); }

/* ===== Cifras (banda superpuesta) ===== */
.cifras { padding: 0 2rem; margin-top: -3rem; position: relative; z-index: 2; }
.cifras-band {
    max-width: 1100px; margin: 0 auto;
    display: grid; grid-template-columns: repeat(4, 1fr);
    background: linear-gradient(180deg, #131f36, #0f1830);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 18px; overflow: hidden;
    box-shadow: 0 24px 60px rgba(0, 0, 0, 0.45);
}
.cifra-card {
    text-align: center; padding: 2rem 1rem; position: relative;
}
.cifra-card:not(:last-child)::after {
    content: ''; position: absolute; right: 0; top: 25%; bottom: 25%;
    width: 1px; background: rgba(255, 255, 255, 0.08);
}
.cifra-number {
    display: block; font-size: 2.6rem; font-weight: 800; line-height: 1.1; margin-bottom: 0.4rem;
    background: linear-gradient(120deg, #37c20c, #9bf06f);
    -webkit-background-clip: text; background-clip: text; color: transparent;
}
.cifra-label { display: block; font-size: 0.9rem; color: #93a3bb; font-weight: 500; }

/* ===== Section head ===== */
.section-head { max-width: 640px; margin-bottom: 3rem; }
.eyebrow {
    display: inline-block; font-size: 0.8rem; font-weight: 700; letter-spacing: 0.08em;
    text-transform: uppercase; color: #37c20c; margin-bottom: 0.75rem;
}
.section-title { font-size: 2.3rem; font-weight: 700; color: #f8fafc; margin: 0 0 0.75rem; }
.section-subtitle { font-size: 1.05rem; color: #93a3bb; line-height: 1.7; margin: 0; }

/* ===== Servicios (bento) ===== */
.servicios { padding: 6rem 2rem 5rem; }
.bento {
    display: grid; grid-template-columns: 1.4fr 1fr 1fr;
    grid-auto-rows: 1fr; gap: 1.25rem; margin-bottom: 2.5rem;
}
.bento-feature { grid-row: span 2; }
.bento-wide { grid-column: span 2; }

.bento-card {
    position: relative; background: rgba(255, 255, 255, 0.025);
    border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 16px;
    padding: 1.75rem; transition: transform 0.25s, border-color 0.25s, background 0.25s;
    overflow: hidden;
}
.bento-card::before {
    content: ''; position: absolute; inset: 0; border-radius: 16px; padding: 1px;
    background: linear-gradient(135deg, rgba(48, 171, 10, 0.5), transparent 40%);
    -webkit-mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
    -webkit-mask-composite: xor; mask-composite: exclude;
    opacity: 0; transition: opacity 0.25s;
}
.bento-card:hover { transform: translateY(-4px); background: rgba(255, 255, 255, 0.04); }
.bento-card:hover::before { opacity: 1; }
.bento-feature { display: flex; flex-direction: column; justify-content: flex-end; }

.servicio-icon {
    width: 54px; height: 54px; border-radius: 14px;
    background: rgba(48, 171, 10, 0.12); border: 1px solid rgba(48, 171, 10, 0.25);
    display: flex; align-items: center; justify-content: center; margin-bottom: 1.25rem;
}
.servicio-icon svg { width: 26px; height: 26px; }
.servicio-title { font-size: 1.15rem; font-weight: 600; color: #f1f5f9; margin: 0 0 0.5rem; }
.servicio-desc { font-size: 0.92rem; color: #93a3bb; line-height: 1.65; margin: 0; }
.bento-feature .servicio-title { font-size: 1.4rem; }

.servicios-link-wrap { text-align: center; }
.servicios-link {
    display: inline-flex; align-items: center; gap: 0.4rem; color: #4cc40f;
    font-weight: 600; font-size: 1rem; text-decoration: none; transition: gap 0.2s;
}
.servicios-link:hover { gap: 0.75rem; }

/* ===== Elegir (escalonado) ===== */
.elegir { padding: 5rem 2rem; background: #0d1424; border-top: 1px solid rgba(255, 255, 255, 0.05); border-bottom: 1px solid rgba(255, 255, 255, 0.05); }
.elegir-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; }
.elegir-card {
    background: rgba(255, 255, 255, 0.025); border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 16px; padding: 2rem; border-top: 3px solid transparent;
    transition: transform 0.25s, box-shadow 0.25s;
}
.elegir-card:hover { transform: translateY(-4px); box-shadow: 0 16px 40px rgba(0, 0, 0, 0.4); }
.elegir-card:nth-child(2) { transform: translateY(28px); }
.elegir-card:nth-child(2):hover { transform: translateY(24px); }
.elegir-green { border-top-color: #30ab0a; }
.elegir-red { border-top-color: #ba2831; }
.elegir-gray { border-top-color: #6b7c96; }
.elegir-icon {
    width: 50px; height: 50px; border-radius: 12px; background: rgba(255, 255, 255, 0.04);
    display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;
}
.elegir-icon svg { width: 28px; height: 28px; }
.elegir-title { font-size: 1.1rem; font-weight: 600; color: #f1f5f9; margin: 0 0 0.5rem; }
.elegir-desc { font-size: 0.92rem; color: #93a3bb; line-height: 1.7; margin: 0; }

/* ===== Slogan ===== */
.slogan { position: relative; padding: 6rem 2rem; text-align: center; overflow: hidden; }
.slogan-glow {
    position: absolute; width: 600px; height: 300px; left: 50%; top: 50%;
    transform: translate(-50%, -50%); background: rgba(48, 171, 10, 0.16); filter: blur(100px);
}
.slogan-container { position: relative; z-index: 1; max-width: 820px; margin: 0 auto; }
.slogan-text {
    font-size: 1.6rem; font-weight: 500; font-style: italic; color: #f1f5f9;
    line-height: 1.6; margin: 0 0 2.5rem; border: none; padding: 0;
}
.btn-slogan {
    display: inline-flex; align-items: center; padding: 0.9rem 2.5rem;
    background: linear-gradient(135deg, #30ab0a, #248007); color: #fff;
    font-weight: 700; font-size: 1.05rem; border-radius: 10px; text-decoration: none;
    box-shadow: 0 12px 30px rgba(48, 171, 10, 0.35); transition: transform 0.2s, box-shadow 0.2s;
}
.btn-slogan:hover { transform: translateY(-2px); box-shadow: 0 16px 38px rgba(48, 171, 10, 0.5); }

/* ===== Responsive ===== */
@media (max-width: 992px) {
    .hero-container { flex-direction: column; text-align: center; gap: 2.5rem; }
    .hero-subtitle { max-width: 100%; }
    .hero-actions { justify-content: center; }
    .hero-title { font-size: 2.6rem; }
    .hero-badge { margin-left: auto; margin-right: auto; }
    .hero-svg { max-width: 290px; }
    .section-head { margin-left: auto; margin-right: auto; text-align: center; }
    .bento { grid-template-columns: 1fr 1fr; }
    .bento-feature { grid-row: auto; }
    .bento-wide { grid-column: span 2; }
    .elegir-grid { grid-template-columns: 1fr; max-width: 560px; margin: 0 auto; }
    .elegir-card:nth-child(2) { transform: none; }
    .elegir-card:nth-child(2):hover { transform: translateY(-4px); }
}

@media (max-width: 768px) {
    .hero { padding: 130px 1.5rem 4rem; }
    .hero-title { font-size: 2.1rem; }
    .hero-subtitle { font-size: 1rem; }
    .section-title { font-size: 1.8rem; }
    .cifras-band { grid-template-columns: 1fr 1fr; }
    .cifra-card:nth-child(2)::after { display: none; }
    .cifra-card:nth-child(1), .cifra-card:nth-child(2) { border-bottom: 1px solid rgba(255, 255, 255, 0.08); }
    .slogan-text { font-size: 1.3rem; }
    .servicios, .elegir, .slogan { padding-left: 1rem; padding-right: 1rem; }
}

@media (max-width: 576px) {
    .hero-title { font-size: 1.85rem; }
    .hero-actions { flex-direction: column; align-items: center; }
    .btn-primary, .btn-outline { width: 100%; justify-content: center; }
    .bento { grid-template-columns: 1fr; }
    .bento-wide { grid-column: auto; }
    .cifras-band { grid-template-columns: 1fr 1fr; }
    .cifra-number { font-size: 2.1rem; }
}
</style>
