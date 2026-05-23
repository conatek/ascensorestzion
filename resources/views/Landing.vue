<template>
    <div class="landing-page">
        <PublicNavbar />

        <!-- Hero Section -->
        <section class="hero">
            <div class="hero-container">
                <div class="hero-text">
                    <h1 class="hero-title">Expertos en transporte vertical</h1>
                    <p class="hero-subtitle">
                        Mantenimiento, reparación, modernización e instalación de ascensores
                        y escaleras eléctricas en Medellín y Antioquia.
                    </p>
                    <div class="hero-actions">
                        <router-link to="/servicios" class="btn-primary">Nuestros Servicios</router-link>
                        <router-link to="/contacto" class="btn-outline">Contáctenos</router-link>
                    </div>
                </div>
                <div class="hero-illustration">
                    <svg class="hero-svg" viewBox="0 0 380 420" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <!-- Building -->
                        <rect x="60" y="40" width="260" height="360" rx="4" fill="#e2e8f0" stroke="#606060" stroke-width="2"/>
                        <!-- Floors -->
                        <line x1="60" y1="120" x2="320" y2="120" stroke="#606060" stroke-width="1"/>
                        <line x1="60" y1="200" x2="320" y2="200" stroke="#606060" stroke-width="1"/>
                        <line x1="60" y1="280" x2="320" y2="280" stroke="#606060" stroke-width="1"/>
                        <line x1="60" y1="360" x2="320" y2="360" stroke="#606060" stroke-width="1"/>
                        <!-- Windows left -->
                        <rect x="80" y="55" width="40" height="50" rx="2" fill="#fff" stroke="#606060" stroke-width="1.5"/>
                        <rect x="80" y="135" width="40" height="50" rx="2" fill="#fff" stroke="#606060" stroke-width="1.5"/>
                        <rect x="80" y="215" width="40" height="50" rx="2" fill="#fff" stroke="#606060" stroke-width="1.5"/>
                        <rect x="80" y="295" width="40" height="50" rx="2" fill="#fff" stroke="#606060" stroke-width="1.5"/>
                        <!-- Windows right -->
                        <rect x="260" y="55" width="40" height="50" rx="2" fill="#fff" stroke="#606060" stroke-width="1.5"/>
                        <rect x="260" y="135" width="40" height="50" rx="2" fill="#fff" stroke="#606060" stroke-width="1.5"/>
                        <rect x="260" y="215" width="40" height="50" rx="2" fill="#fff" stroke="#606060" stroke-width="1.5"/>
                        <rect x="260" y="295" width="40" height="50" rx="2" fill="#fff" stroke="#606060" stroke-width="1.5"/>
                        <!-- Elevator shaft -->
                        <rect x="155" y="45" width="70" height="350" rx="2" fill="#f8fafc" stroke="#30ab0a" stroke-width="2"/>
                        <!-- Shaft lines (cables) -->
                        <line x1="180" y1="45" x2="180" y2="140" stroke="#30ab0a" stroke-width="1.5" stroke-dasharray="6 4"/>
                        <line x1="200" y1="45" x2="200" y2="140" stroke="#30ab0a" stroke-width="1.5" stroke-dasharray="6 4"/>
                        <!-- Elevator cab -->
                        <rect x="160" y="140" width="60" height="70" rx="3" fill="#30ab0a" stroke="#1e293b" stroke-width="2"/>
                        <!-- Cab door split -->
                        <line x1="190" y1="145" x2="190" y2="205" stroke="#1e293b" stroke-width="1.5"/>
                        <!-- Cab detail arrows -->
                        <polygon points="183,165 190,155 197,165" fill="#fff"/>
                        <polygon points="183,185 190,195 197,185" fill="#fff"/>
                        <!-- Floor labels -->
                        <text x="45" y="85" font-size="12" fill="#606060" font-family="Poppins, sans-serif" text-anchor="end">4</text>
                        <text x="45" y="165" font-size="12" fill="#606060" font-family="Poppins, sans-serif" text-anchor="end">3</text>
                        <text x="45" y="245" font-size="12" fill="#606060" font-family="Poppins, sans-serif" text-anchor="end">2</text>
                        <text x="45" y="325" font-size="12" fill="#606060" font-family="Poppins, sans-serif" text-anchor="end">1</text>
                        <!-- Ground entrance -->
                        <rect x="165" y="365" width="50" height="30" rx="2" fill="#1e293b"/>
                        <rect x="170" y="367" width="18" height="28" rx="1" fill="#606060"/>
                        <rect x="192" y="367" width="18" height="28" rx="1" fill="#606060"/>
                    </svg>
                </div>
            </div>
        </section>

        <!-- Cifras Section -->
        <section class="cifras">
            <div class="cifras-container">
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

        <!-- Servicios Section -->
        <section class="servicios">
            <div class="servicios-container">
                <div class="section-header">
                    <h2 class="section-title">Nuestros Servicios</h2>
                    <p class="section-subtitle">Soluciones integrales para equipos de transporte vertical con los más altos estándares de calidad y seguridad.</p>
                </div>
                <div class="servicios-grid">
                    <div class="servicio-card">
                        <div class="servicio-icon">
                            <!-- Wrench icon -->
                            <svg viewBox="0 0 24 24" fill="none" stroke="#30ab0a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/>
                            </svg>
                        </div>
                        <h3 class="servicio-title">Instalación</h3>
                        <p class="servicio-desc">Instalamos ascensores con precisión, seguridad y confort.</p>
                    </div>
                    <div class="servicio-card">
                        <div class="servicio-icon">
                            <!-- Trending up icon -->
                            <svg viewBox="0 0 24 24" fill="none" stroke="#30ab0a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/>
                                <polyline points="17 6 23 6 23 12"/>
                            </svg>
                        </div>
                        <h3 class="servicio-title">Modernización</h3>
                        <p class="servicio-desc">Actualizamos sus equipos con tecnología de última generación.</p>
                    </div>
                    <div class="servicio-card">
                        <div class="servicio-icon">
                            <!-- Shield check icon -->
                            <svg viewBox="0 0 24 24" fill="none" stroke="#30ab0a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                                <path d="M9 12l2 2 4-4"/>
                            </svg>
                        </div>
                        <h3 class="servicio-title">Mantenimiento</h3>
                        <p class="servicio-desc">Planes preventivos y correctivos para máxima disponibilidad.</p>
                    </div>
                    <div class="servicio-card">
                        <div class="servicio-icon">
                            <!-- Tools icon -->
                            <svg viewBox="0 0 24 24" fill="none" stroke="#30ab0a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/>
                                <path d="M10 13l-2 2"/>
                                <path d="M16 8l-1.5 1.5"/>
                            </svg>
                        </div>
                        <h3 class="servicio-title">Reparación</h3>
                        <p class="servicio-desc">Diagnóstico y solución rápida de fallas en cualquier equipo.</p>
                    </div>
                </div>
                <div class="servicios-link-wrap">
                    <router-link to="/servicios" class="servicios-link">
                        Ver todos los servicios
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 12h14"/>
                            <path d="M12 5l7 7-7 7"/>
                        </svg>
                    </router-link>
                </div>
            </div>
        </section>

        <!-- Por qué elegirnos -->
        <section class="elegir">
            <div class="elegir-container">
                <div class="section-header">
                    <h2 class="section-title">Innovación y Confianza en Cada Proyecto</h2>
                </div>
                <div class="elegir-grid">
                    <div class="elegir-card elegir-green">
                        <div class="elegir-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="#30ab0a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                            </svg>
                        </div>
                        <h3 class="elegir-title">Compromiso con la seguridad</h3>
                        <p class="elegir-desc">Cumplimos con todas las normativas y estándares de seguridad vigentes, garantizando la protección de los usuarios y la tranquilidad de nuestros clientes en cada proyecto.</p>
                    </div>
                    <div class="elegir-card elegir-red">
                        <div class="elegir-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="#ba2831" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 7h-3a2 2 0 01-2-2V2"/>
                                <path d="M9 18a2 2 0 01-2-2V4a2 2 0 012-2h5l4 4v10a2 2 0 01-2 2z"/>
                                <path d="M3 7v10a2 2 0 002 2h4"/>
                                <path d="M9 13h6"/>
                                <path d="M12 10v6"/>
                            </svg>
                        </div>
                        <h3 class="elegir-title">Soluciones personalizadas</h3>
                        <p class="elegir-desc">Cada edificio es único, por eso diseñamos soluciones a la medida que se adaptan a las necesidades específicas de su infraestructura y presupuesto.</p>
                    </div>
                    <div class="elegir-card elegir-gray">
                        <div class="elegir-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="#606060" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"/>
                                <polyline points="12 6 12 12 16 14"/>
                            </svg>
                        </div>
                        <h3 class="elegir-title">Respuesta rápida y eficiente</h3>
                        <p class="elegir-desc">Contamos con un equipo técnico disponible 24/7 para atender emergencias y minimizar los tiempos de inactividad de sus equipos de transporte vertical.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Slogan + CTA -->
        <section class="slogan">
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
/* Base */
.landing-page {
    min-height: 100vh;
    background: #fff;
    font-family: 'Poppins', -apple-system, BlinkMacSystemFont, sans-serif;
    color: #1e293b;
}

/* ===== Hero ===== */
.hero {
    padding: 120px 2rem 5rem;
    background: #f8fafc;
}

.hero-container {
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    gap: 4rem;
}

.hero-text {
    flex: 1;
}

.hero-title {
    font-size: 3rem;
    font-weight: 800;
    color: #1e293b;
    line-height: 1.15;
    margin: 0 0 1.25rem;
}

.hero-subtitle {
    font-size: 1.15rem;
    color: #606060;
    line-height: 1.75;
    margin: 0 0 2rem;
    max-width: 520px;
}

.hero-actions {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.btn-primary {
    display: inline-flex;
    align-items: center;
    padding: 0.85rem 2rem;
    background: #30ab0a;
    color: #fff;
    font-weight: 600;
    font-size: 1rem;
    border-radius: 10px;
    text-decoration: none;
    transition: background 0.2s, transform 0.2s;
}
.btn-primary:hover {
    background: #279208;
    transform: translateY(-2px);
}

.btn-outline {
    display: inline-flex;
    align-items: center;
    padding: 0.85rem 2rem;
    background: transparent;
    color: #1e293b;
    font-weight: 600;
    font-size: 1rem;
    border: 2px solid #1e293b;
    border-radius: 10px;
    text-decoration: none;
    transition: all 0.2s;
}
.btn-outline:hover {
    border-color: #30ab0a;
    color: #30ab0a;
}

.hero-illustration {
    flex: 1;
    display: flex;
    justify-content: center;
}

.hero-svg {
    width: 100%;
    max-width: 340px;
    height: auto;
}

/* ===== Cifras ===== */
.cifras {
    padding: 4rem 2rem;
    background: #fff;
}

.cifras-container {
    max-width: 1200px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1.5rem;
}

.cifra-card {
    text-align: center;
    padding: 2rem 1rem;
    border-top: 4px solid #30ab0a;
    background: #fff;
    border-radius: 0 0 12px 12px;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
}

.cifra-number {
    display: block;
    font-size: 2.75rem;
    font-weight: 800;
    color: #1e293b;
    line-height: 1.1;
    margin-bottom: 0.5rem;
}

.cifra-label {
    display: block;
    font-size: 0.95rem;
    color: #606060;
    font-weight: 500;
}

/* ===== Servicios ===== */
.servicios {
    padding: 5rem 2rem;
    background: #f8fafc;
}

.servicios-container {
    max-width: 1200px;
    margin: 0 auto;
}

.section-header {
    text-align: center;
    margin-bottom: 3rem;
}

.section-title {
    font-size: 2.25rem;
    font-weight: 700;
    color: #1e293b;
    margin: 0 0 0.75rem;
}

.section-subtitle {
    font-size: 1.05rem;
    color: #606060;
    max-width: 620px;
    margin: 0 auto;
    line-height: 1.7;
}

.servicios-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1.5rem;
    margin-bottom: 2.5rem;
}

.servicio-card {
    background: #fff;
    border-radius: 14px;
    padding: 2rem 1.5rem;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.05);
    border: 1px solid #e2e8f0;
    transition: transform 0.25s, box-shadow 0.25s;
}
.servicio-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
}

.servicio-icon {
    width: 52px;
    height: 52px;
    background: #edf9ea;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1.25rem;
}
.servicio-icon svg {
    width: 26px;
    height: 26px;
}

.servicio-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: #1e293b;
    margin: 0 0 0.5rem;
}

.servicio-desc {
    font-size: 0.92rem;
    color: #606060;
    line-height: 1.6;
    margin: 0;
}

.servicios-link-wrap {
    text-align: center;
}

.servicios-link {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    color: #30ab0a;
    font-weight: 600;
    font-size: 1rem;
    text-decoration: none;
    transition: gap 0.2s;
}
.servicios-link:hover {
    gap: 0.75rem;
}

/* ===== Por qué elegirnos ===== */
.elegir {
    padding: 5rem 2rem;
    background: #fff;
}

.elegir-container {
    max-width: 1200px;
    margin: 0 auto;
}

.elegir-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.5rem;
}

.elegir-card {
    background: #fff;
    border-radius: 14px;
    padding: 2rem;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.05);
    border: 1px solid #e2e8f0;
    border-left: 4px solid transparent;
    transition: transform 0.25s, box-shadow 0.25s;
}
.elegir-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
}

.elegir-green { border-left-color: #30ab0a; }
.elegir-red   { border-left-color: #ba2831; }
.elegir-gray  { border-left-color: #606060; }

.elegir-icon {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1rem;
}
.elegir-icon svg {
    width: 32px;
    height: 32px;
}

.elegir-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: #1e293b;
    margin: 0 0 0.5rem;
}

.elegir-desc {
    font-size: 0.92rem;
    color: #606060;
    line-height: 1.7;
    margin: 0;
}

/* ===== Slogan + CTA ===== */
.slogan {
    padding: 5rem 2rem;
    background: #30ab0a;
    text-align: center;
}

.slogan-container {
    max-width: 800px;
    margin: 0 auto;
}

.slogan-text {
    font-size: 1.5rem;
    font-weight: 500;
    font-style: italic;
    color: #fff;
    line-height: 1.7;
    margin: 0 0 2.5rem;
    border: none;
    padding: 0;
}

.btn-slogan {
    display: inline-flex;
    align-items: center;
    padding: 0.9rem 2.5rem;
    background: #fff;
    color: #30ab0a;
    font-weight: 700;
    font-size: 1.05rem;
    border-radius: 10px;
    text-decoration: none;
    transition: transform 0.2s, box-shadow 0.2s;
}
.btn-slogan:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
}

/* ===== Responsive ===== */

/* Tablet landscape */
@media (max-width: 992px) {
    .hero-container {
        flex-direction: column;
        text-align: center;
        gap: 2.5rem;
    }
    .hero-subtitle {
        max-width: 100%;
    }
    .hero-actions {
        justify-content: center;
    }
    .hero-title {
        font-size: 2.5rem;
    }
    .hero-svg {
        max-width: 280px;
    }
    .cifras-container {
        grid-template-columns: repeat(2, 1fr);
    }
    .servicios-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    .elegir-grid {
        grid-template-columns: 1fr;
        max-width: 560px;
        margin: 0 auto;
    }
}

/* Tablet portrait */
@media (max-width: 768px) {
    .hero {
        padding: 110px 1.5rem 3.5rem;
    }
    .hero-title {
        font-size: 2rem;
    }
    .hero-subtitle {
        font-size: 1rem;
    }
    .section-title {
        font-size: 1.75rem;
    }
    .slogan-text {
        font-size: 1.25rem;
    }
    .cifras-container {
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
    }
    .cifra-number {
        font-size: 2.25rem;
    }
}

/* Mobile */
@media (max-width: 576px) {
    .hero {
        padding: 100px 1rem 3rem;
    }
    .hero-title {
        font-size: 1.75rem;
    }
    .hero-actions {
        flex-direction: column;
        align-items: center;
    }
    .btn-primary, .btn-outline {
        width: 100%;
        justify-content: center;
    }
    .cifras-container {
        grid-template-columns: 1fr 1fr;
        gap: 0.75rem;
    }
    .cifra-card {
        padding: 1.25rem 0.75rem;
    }
    .cifra-number {
        font-size: 2rem;
    }
    .servicios-grid {
        grid-template-columns: 1fr;
    }
    .servicios, .elegir, .cifras {
        padding: 3rem 1rem;
    }
    .slogan {
        padding: 3rem 1rem;
    }
    .slogan-text {
        font-size: 1.1rem;
    }
}
</style>
