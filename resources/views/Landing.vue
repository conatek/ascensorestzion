<template>
    <div class="landing-page">
        <!-- Header -->
        <header class="landing-header">
            <div class="header-container">
                <div class="logo">
                    <svg class="logo-icon" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect width="40" height="40" rx="10" fill="url(#logo-gradient)"/>
                        <path d="M12 20C12 15.5817 15.5817 12 20 12C24.4183 12 28 15.5817 28 20C28 24.4183 24.4183 28 20 28" stroke="white" stroke-width="2.5" stroke-linecap="round"/>
                        <circle cx="20" cy="20" r="4" fill="white"/>
                        <defs>
                            <linearGradient id="logo-gradient" x1="0" y1="0" x2="40" y2="40" gradientUnits="userSpaceOnUse">
                                <stop stop-color="#8b5cf6"/>
                                <stop offset="1" stop-color="#ec4899"/>
                            </linearGradient>
                        </defs>
                    </svg>
                    <span class="logo-text">NexosCard</span>
                </div>
                <nav class="header-nav">
                    <router-link to="/login" class="btn-login">
                        Iniciar Sesion
                    </router-link>
                </nav>
            </div>
        </header>

        <!-- Hero Section -->
        <section class="hero-section">
            <div class="hero-container">
                <div class="hero-content">
                    <div class="hero-text">
                        <h1 class="hero-title">
                            Tarjetas de presentacion
                            <span class="gradient-text">digitales</span>
                            para tu negocio
                        </h1>
                        <p class="hero-description">
                            Crea minisitios profesionales para tu empresa y tus empleados.
                            Comparte tu informacion de contacto de manera moderna y elegante.
                        </p>
                        <div class="hero-actions">
                            <router-link to="/register" class="btn-gradient">
                                <svg class="btn-icon" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd"/>
                                </svg>
                                Comenzar Gratis
                            </router-link>
                            <a href="#features" class="btn-outline">
                                Conocer mas
                            </a>
                        </div>
                    </div>
                    <div class="hero-illustration">
                        <!-- SVG Illustration: Digital Card -->
                        <svg class="hero-svg" viewBox="0 0 400 320" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <!-- Background shapes -->
                            <circle cx="200" cy="160" r="120" fill="url(#hero-bg-gradient)" opacity="0.1"/>
                            <circle cx="320" cy="80" r="40" fill="#8b5cf6" opacity="0.1"/>
                            <circle cx="80" cy="240" r="30" fill="#ec4899" opacity="0.1"/>

                            <!-- Main Card -->
                            <g filter="url(#card-shadow)">
                                <rect x="100" y="60" width="200" height="240" rx="16" fill="white"/>
                                <rect x="100" y="60" width="200" height="240" rx="16" stroke="#e2e8f0" stroke-width="1"/>
                            </g>

                            <!-- Card Header Gradient -->
                            <rect x="100" y="60" width="200" height="80" rx="16" fill="url(#card-header-gradient)"/>
                            <rect x="100" y="120" width="200" height="20" fill="url(#card-header-gradient)"/>

                            <!-- Avatar Circle -->
                            <circle cx="200" cy="130" r="35" fill="white" stroke="#e2e8f0" stroke-width="2"/>
                            <circle cx="200" cy="130" r="28" fill="#f1f5f9"/>
                            <path d="M200 115c-4.418 0-8 3.582-8 8s3.582 8 8 8 8-3.582 8-8-3.582-8-8-8zm0 20c-6.627 0-12 2.686-12 6v2h24v-2c0-3.314-5.373-6-12-6z" fill="#94a3b8"/>

                            <!-- Name -->
                            <rect x="150" y="175" width="100" height="12" rx="6" fill="#1e293b"/>

                            <!-- Job Title -->
                            <rect x="160" y="195" width="80" height="8" rx="4" fill="#94a3b8"/>

                            <!-- Contact Icons -->
                            <g transform="translate(140, 220)">
                                <circle cx="15" cy="15" r="15" fill="#f3e8ff"/>
                                <path d="M15 8v14M8 15h14" stroke="#8b5cf6" stroke-width="2" stroke-linecap="round"/>
                            </g>
                            <g transform="translate(180, 220)">
                                <circle cx="15" cy="15" r="15" fill="#d1fae5"/>
                                <path d="M12 10l6 5-6 5" stroke="#059669" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </g>
                            <g transform="translate(220, 220)">
                                <circle cx="15" cy="15" r="15" fill="#fef3c7"/>
                                <circle cx="15" cy="15" r="5" stroke="#d97706" stroke-width="2"/>
                            </g>

                            <!-- Floating Elements -->
                            <g class="floating-element" style="animation-delay: 0s">
                                <circle cx="60" cy="100" r="20" fill="#8b5cf6" opacity="0.2"/>
                                <path d="M55 100l5 5 10-10" stroke="#8b5cf6" stroke-width="2" stroke-linecap="round"/>
                            </g>
                            <g class="floating-element" style="animation-delay: 0.5s">
                                <circle cx="340" cy="180" r="18" fill="#ec4899" opacity="0.2"/>
                                <path d="M334 180h12M340 174v12" stroke="#ec4899" stroke-width="2" stroke-linecap="round"/>
                            </g>
                            <g class="floating-element" style="animation-delay: 1s">
                                <circle cx="320" cy="280" r="15" fill="#f97316" opacity="0.2"/>
                                <path d="M315 280l5-5 5 5" stroke="#f97316" stroke-width="2" stroke-linecap="round"/>
                            </g>

                            <!-- Definitions -->
                            <defs>
                                <linearGradient id="hero-bg-gradient" x1="80" y1="40" x2="320" y2="280" gradientUnits="userSpaceOnUse">
                                    <stop stop-color="#8b5cf6"/>
                                    <stop offset="1" stop-color="#ec4899"/>
                                </linearGradient>
                                <linearGradient id="card-header-gradient" x1="100" y1="60" x2="300" y2="140" gradientUnits="userSpaceOnUse">
                                    <stop stop-color="#8b5cf6"/>
                                    <stop offset="1" stop-color="#ec4899"/>
                                </linearGradient>
                                <filter id="card-shadow" x="90" y="55" width="220" height="260" filterUnits="userSpaceOnUse">
                                    <feDropShadow dx="0" dy="4" stdDeviation="8" flood-color="#000" flood-opacity="0.1"/>
                                </filter>
                            </defs>
                        </svg>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section id="features" class="features-section">
            <div class="features-container">
                <div class="features-header">
                    <h2 class="features-title">Todo lo que necesitas</h2>
                    <p class="features-subtitle">Herramientas poderosas para crear tarjetas de presentacion digitales profesionales</p>
                </div>
                <div class="features-grid">
                    <!-- Feature 1 -->
                    <div class="feature-card">
                        <div class="feature-icon" style="background: linear-gradient(135deg, #f3e8ff, #ede9fe);">
                            <svg viewBox="0 0 24 24" fill="none" stroke="#8b5cf6" stroke-width="2">
                                <rect x="3" y="3" width="18" height="18" rx="2"/>
                                <circle cx="12" cy="10" r="3"/>
                                <path d="M7 21v-2a4 4 0 014-4h2a4 4 0 014 4v2"/>
                            </svg>
                        </div>
                        <h3>Multiples Tarjetas</h3>
                        <p>Crea tarjetas personalizadas para cada miembro de tu equipo con informacion unica.</p>
                    </div>

                    <!-- Feature 2 -->
                    <div class="feature-card">
                        <div class="feature-icon" style="background: linear-gradient(135deg, #d1fae5, #a7f3d0);">
                            <svg viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2">
                                <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                                <path d="M2 17l10 5 10-5"/>
                                <path d="M2 12l10 5 10-5"/>
                            </svg>
                        </div>
                        <h3>Plantillas Profesionales</h3>
                        <p>Elige entre 6 plantillas modernas y personaliza colores, fuentes y estilos.</p>
                    </div>

                    <!-- Feature 3 -->
                    <div class="feature-card">
                        <div class="feature-icon" style="background: linear-gradient(135deg, #fef3c7, #fde68a);">
                            <svg viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                        </div>
                        <h3>Productos y Servicios</h3>
                        <p>Muestra tu catalogo completo con imagenes, precios y descripciones.</p>
                    </div>

                    <!-- Feature 4 -->
                    <div class="feature-card">
                        <div class="feature-icon" style="background: linear-gradient(135deg, #fce7f3, #fbcfe8);">
                            <svg viewBox="0 0 24 24" fill="none" stroke="#ec4899" stroke-width="2">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                <path d="M22 6l-10 7L2 6"/>
                            </svg>
                        </div>
                        <h3>Comparte Facilmente</h3>
                        <p>Un enlace unico para compartir por WhatsApp, email o redes sociales.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="cta-section">
            <div class="cta-container">
                <div class="cta-content">
                    <h2 class="cta-title">Comienza a crear tu presencia digital</h2>
                    <p class="cta-description">Registrate gratis y crea tu primera tarjeta en minutos</p>
                    <router-link to="/register" class="btn-gradient btn-large">
                        Crear cuenta gratis
                    </router-link>
                </div>
                <!-- CTA Illustration -->
                <div class="cta-illustration">
                    <svg viewBox="0 0 300 200" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <!-- Phone outline -->
                        <rect x="100" y="20" width="100" height="180" rx="12" fill="white" opacity="0.1"/>
                        <rect x="100" y="20" width="100" height="180" rx="12" stroke="white" stroke-width="2" opacity="0.3"/>

                        <!-- Screen content -->
                        <rect x="110" y="40" width="80" height="140" rx="4" fill="white" opacity="0.05"/>

                        <!-- Mini card preview -->
                        <rect x="120" y="60" width="60" height="80" rx="6" fill="white" opacity="0.2"/>
                        <circle cx="150" cy="85" r="15" fill="white" opacity="0.3"/>
                        <rect x="135" y="110" width="30" height="6" rx="3" fill="white" opacity="0.3"/>
                        <rect x="130" y="122" width="40" height="4" rx="2" fill="white" opacity="0.2"/>

                        <!-- Decorative elements -->
                        <circle cx="60" cy="100" r="30" fill="white" opacity="0.05"/>
                        <circle cx="240" cy="150" r="25" fill="white" opacity="0.05"/>
                        <circle cx="230" cy="50" r="15" fill="white" opacity="0.08"/>
                    </svg>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="landing-footer">
            <div class="footer-container">
                <div class="footer-brand">
                    <svg class="footer-logo" viewBox="0 0 32 32" fill="none">
                        <rect width="32" height="32" rx="8" fill="url(#footer-logo-gradient)"/>
                        <path d="M10 16C10 12.686 12.686 10 16 10C19.314 10 22 12.686 22 16C22 19.314 19.314 22 16 22" stroke="white" stroke-width="2" stroke-linecap="round"/>
                        <circle cx="16" cy="16" r="3" fill="white"/>
                        <defs>
                            <linearGradient id="footer-logo-gradient" x1="0" y1="0" x2="32" y2="32">
                                <stop stop-color="#8b5cf6"/>
                                <stop offset="1" stop-color="#ec4899"/>
                            </linearGradient>
                        </defs>
                    </svg>
                    <span>NexosCard</span>
                </div>
                <p class="footer-copy">&copy; {{ currentYear }} NexosCard. Todos los derechos reservados.</p>
            </div>
        </footer>
    </div>
</template>

<script>
export default {
    name: 'Landing',
    computed: {
        currentYear() {
            return new Date().getFullYear();
        }
    }
};
</script>

<style scoped>
/* Base */
.landing-page {
    min-height: 100vh;
    background: #ffffff;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
}

/* Header */
.landing-header {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 100;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border-bottom: 1px solid #f1f5f9;
}

.header-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 1rem 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.logo {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    text-decoration: none;
}

.logo-icon {
    width: 40px;
    height: 40px;
}

.logo-text {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1e293b;
}

.btn-login {
    display: inline-flex;
    align-items: center;
    padding: 0.625rem 1.5rem;
    background: #7c3aed;
    color: white;
    font-weight: 600;
    border-radius: 10px;
    text-decoration: none;
    transition: all 0.2s ease;
    box-shadow: 0 2px 8px rgba(124, 58, 237, 0.25);
}

.btn-login:hover {
    background: #6d28d9;
    box-shadow: 0 4px 12px rgba(124, 58, 237, 0.35);
    transform: translateY(-1px);
}

/* Hero Section */
.hero-section {
    padding: 8rem 2rem 4rem;
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
}

.hero-container {
    max-width: 1200px;
    margin: 0 auto;
}

.hero-content {
    display: flex;
    align-items: center;
    gap: 4rem;
}

.hero-text {
    flex: 1;
}

.hero-title {
    font-size: 3rem;
    font-weight: 700;
    color: #1e293b;
    line-height: 1.2;
    margin-bottom: 1.5rem;
}

.gradient-text {
    background: linear-gradient(135deg, #8b5cf6, #ec4899);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.hero-description {
    font-size: 1.25rem;
    color: #64748b;
    line-height: 1.7;
    margin-bottom: 2rem;
    max-width: 500px;
}

.hero-actions {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.btn-gradient {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.875rem 2rem;
    background: linear-gradient(135deg, #8b5cf6, #ec4899);
    color: white;
    font-weight: 600;
    border-radius: 12px;
    text-decoration: none;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(139, 92, 246, 0.4);
}

.btn-gradient:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(139, 92, 246, 0.5);
}

.btn-icon {
    width: 20px;
    height: 20px;
}

.btn-outline {
    display: inline-flex;
    align-items: center;
    padding: 0.875rem 2rem;
    background: transparent;
    color: #475569;
    font-weight: 600;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    text-decoration: none;
    transition: all 0.2s ease;
}

.btn-outline:hover {
    border-color: #8b5cf6;
    color: #8b5cf6;
}

.hero-illustration {
    flex: 1;
    display: flex;
    justify-content: center;
}

.hero-svg {
    width: 100%;
    max-width: 400px;
    height: auto;
}

.floating-element {
    animation: float 3s ease-in-out infinite;
}

@keyframes float {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}

/* Features Section */
.features-section {
    padding: 5rem 2rem;
    background: white;
}

.features-container {
    max-width: 1200px;
    margin: 0 auto;
}

.features-header {
    text-align: center;
    margin-bottom: 3rem;
}

.features-title {
    font-size: 2.25rem;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 1rem;
}

.features-subtitle {
    font-size: 1.1rem;
    color: #64748b;
    max-width: 600px;
    margin: 0 auto;
}

.features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 1.5rem;
}

.feature-card {
    background: white;
    border-radius: 16px;
    padding: 2rem;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
    border: 1px solid #f1f5f9;
}

.feature-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
}

.feature-icon {
    width: 56px;
    height: 56px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1.25rem;
}

.feature-icon svg {
    width: 28px;
    height: 28px;
}

.feature-card h3 {
    font-size: 1.1rem;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 0.75rem;
}

.feature-card p {
    font-size: 0.95rem;
    color: #64748b;
    line-height: 1.6;
    margin: 0;
}

/* CTA Section */
.cta-section {
    padding: 5rem 2rem;
    background: linear-gradient(135deg, #8b5cf6, #ec4899);
    position: relative;
    overflow: hidden;
}

.cta-container {
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 3rem;
}

.cta-content {
    flex: 1;
    position: relative;
    z-index: 1;
}

.cta-title {
    font-size: 2rem;
    font-weight: 700;
    color: white;
    margin-bottom: 1rem;
}

.cta-description {
    font-size: 1.1rem;
    color: rgba(255, 255, 255, 0.9);
    margin-bottom: 2rem;
}

.cta-section .btn-gradient {
    background: white;
    color: #8b5cf6;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
}

.cta-section .btn-gradient:hover {
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
}

.btn-large {
    padding: 1rem 2.5rem;
    font-size: 1.05rem;
}

.cta-illustration {
    flex: 0 0 300px;
}

.cta-illustration svg {
    width: 100%;
    height: auto;
}

/* Footer */
.landing-footer {
    padding: 2rem;
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
}

.footer-container {
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}

.footer-brand {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 600;
    color: #1e293b;
}

.footer-logo {
    width: 32px;
    height: 32px;
}

.footer-copy {
    color: #64748b;
    font-size: 0.9rem;
    margin: 0;
}

/* Responsive */
@media (max-width: 992px) {
    .hero-content {
        flex-direction: column;
        text-align: center;
    }

    .hero-title {
        font-size: 2.25rem;
    }

    .hero-description {
        max-width: 100%;
    }

    .hero-actions {
        justify-content: center;
    }

    .hero-illustration {
        order: -1;
    }

    .hero-svg {
        max-width: 320px;
    }

    .cta-container {
        flex-direction: column;
        text-align: center;
    }

    .cta-illustration {
        display: none;
    }
}

@media (max-width: 576px) {
    .header-container {
        padding: 0.875rem 1rem;
    }

    .logo-text {
        font-size: 1.25rem;
    }

    .btn-login {
        padding: 0.5rem 1rem;
        font-size: 0.9rem;
    }

    .hero-section {
        padding: 6rem 1rem 3rem;
    }

    .hero-title {
        font-size: 1.875rem;
    }

    .hero-description {
        font-size: 1rem;
    }

    .btn-gradient,
    .btn-outline {
        padding: 0.75rem 1.5rem;
        font-size: 0.95rem;
    }

    .features-section {
        padding: 3rem 1rem;
    }

    .features-title {
        font-size: 1.75rem;
    }

    .cta-section {
        padding: 3rem 1rem;
    }

    .cta-title {
        font-size: 1.5rem;
    }

    .footer-container {
        flex-direction: column;
        text-align: center;
    }
}
</style>
