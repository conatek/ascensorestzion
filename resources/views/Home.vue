<template>
    <div>
        <div class="app-page-title">
            <div class="page-title-wrapper">
                <div class="page-title-heading">
                    <div class="page-title-icon">
                        <i class="fa fa-chart-line icon-gradient bg-mean-fruit"></i>
                    </div>
                    <div>
                        Panel de Control
                        <div class="page-title-subheading text-muted">
                            Bienvenido a NexosCard
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Banner upgrade para Guest -->
        <div v-if="isGuest" class="upgrade-banner">
            <div class="banner-content">
                <div class="banner-text">
                    <i class="fa fa-info-circle me-2"></i>
                    <span>Estas en <strong>modo prueba</strong>. Mejora tu plan para desbloquear todas las funcionalidades.</span>
                </div>
                <router-link :to="{ name: 'subscription.plans' }" class="banner-btn">
                    Ver planes
                </router-link>
            </div>
        </div>

        <!-- Hero Section -->
        <div class="welcome-hero">
            <div class="hero-content">
                <div class="hero-text">
                    <h1 class="hero-title">
                        Tu identidad digital,
                        <span class="gradient-text">al alcance de todos</span>
                    </h1>
                    <p class="hero-description">
                        Crea tarjetas de presentacion digitales impactantes para tu empresa y equipo.
                        Comparte tu informacion de contacto de forma moderna, ecologica y memorable.
                    </p>
                    <router-link :to="{ name: 'companies.index' }" class="btn-start">
                        <i class="fa fa-rocket me-2"></i>
                        Comenzar ahora
                    </router-link>
                </div>
                <div class="hero-illustration">
                    <!-- SVG Illustration -->
                    <svg viewBox="0 0 400 300" class="cards-illustration">
                        <!-- Background circles -->
                        <circle cx="200" cy="150" r="120" fill="url(#gradient1)" opacity="0.1">
                            <animate attributeName="r" values="120;130;120" dur="4s" repeatCount="indefinite"/>
                        </circle>
                        <circle cx="200" cy="150" r="80" fill="url(#gradient2)" opacity="0.15">
                            <animate attributeName="r" values="80;90;80" dur="3s" repeatCount="indefinite"/>
                        </circle>

                        <!-- Card 1 (back) -->
                        <g transform="translate(100, 60) rotate(-8)">
                            <rect x="0" y="0" width="160" height="100" rx="12" fill="#6366f1" opacity="0.9"/>
                            <rect x="15" y="15" width="40" height="40" rx="20" fill="white" opacity="0.3"/>
                            <rect x="65" y="20" width="70" height="8" rx="4" fill="white" opacity="0.5"/>
                            <rect x="65" y="35" width="50" height="6" rx="3" fill="white" opacity="0.3"/>
                            <rect x="15" y="70" width="130" height="6" rx="3" fill="white" opacity="0.2"/>
                            <rect x="15" y="82" width="100" height="6" rx="3" fill="white" opacity="0.2"/>
                        </g>

                        <!-- Card 2 (middle) -->
                        <g transform="translate(120, 90) rotate(3)">
                            <rect x="0" y="0" width="160" height="100" rx="12" fill="#8b5cf6"/>
                            <circle cx="35" cy="35" r="20" fill="white" opacity="0.9"/>
                            <circle cx="35" cy="35" r="15" fill="#8b5cf6" opacity="0.5"/>
                            <rect x="65" y="20" width="80" height="10" rx="5" fill="white" opacity="0.9"/>
                            <rect x="65" y="38" width="60" height="6" rx="3" fill="white" opacity="0.5"/>
                            <rect x="15" y="65" width="50" height="6" rx="3" fill="white" opacity="0.3"/>
                            <rect x="15" y="78" width="130" height="6" rx="3" fill="white" opacity="0.3"/>
                            <!-- Social icons -->
                            <circle cx="100" cy="68" r="8" fill="white" opacity="0.4"/>
                            <circle cx="122" cy="68" r="8" fill="white" opacity="0.4"/>
                            <circle cx="144" cy="68" r="8" fill="white" opacity="0.4"/>
                        </g>

                        <!-- Card 3 (front) -->
                        <g transform="translate(140, 120) rotate(-2)">
                            <defs>
                                <linearGradient id="cardGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" style="stop-color:#ec4899"/>
                                    <stop offset="100%" style="stop-color:#f97316"/>
                                </linearGradient>
                            </defs>
                            <rect x="0" y="0" width="160" height="100" rx="12" fill="url(#cardGradient)"/>
                            <!-- Profile photo -->
                            <circle cx="35" cy="35" r="22" fill="white"/>
                            <circle cx="35" cy="30" r="10" fill="#fbbf24"/>
                            <ellipse cx="35" cy="48" rx="14" ry="8" fill="#fbbf24"/>
                            <!-- Text lines -->
                            <rect x="68" y="18" width="75" height="12" rx="6" fill="white"/>
                            <rect x="68" y="36" width="55" height="8" rx="4" fill="white" opacity="0.7"/>
                            <!-- Contact info -->
                            <rect x="15" y="68" width="8" height="8" rx="2" fill="white" opacity="0.8"/>
                            <rect x="28" y="70" width="60" height="4" rx="2" fill="white" opacity="0.6"/>
                            <rect x="15" y="82" width="8" height="8" rx="2" fill="white" opacity="0.8"/>
                            <rect x="28" y="84" width="80" height="4" rx="2" fill="white" opacity="0.6"/>
                            <!-- QR Code hint -->
                            <rect x="120" y="60" width="30" height="30" rx="4" fill="white" opacity="0.9"/>
                            <rect x="125" y="65" width="8" height="8" fill="#ec4899" opacity="0.8"/>
                            <rect x="137" y="65" width="8" height="8" fill="#ec4899" opacity="0.8"/>
                            <rect x="125" y="77" width="8" height="8" fill="#ec4899" opacity="0.8"/>
                            <rect x="137" y="77" width="5" height="5" fill="#ec4899" opacity="0.8"/>
                        </g>

                        <!-- Floating elements -->
                        <circle cx="320" cy="60" r="8" fill="#fbbf24" opacity="0.8">
                            <animate attributeName="cy" values="60;50;60" dur="2s" repeatCount="indefinite"/>
                        </circle>
                        <circle cx="80" cy="200" r="6" fill="#10b981" opacity="0.8">
                            <animate attributeName="cy" values="200;210;200" dur="2.5s" repeatCount="indefinite"/>
                        </circle>
                        <circle cx="350" cy="180" r="10" fill="#6366f1" opacity="0.6">
                            <animate attributeName="cy" values="180;170;180" dur="3s" repeatCount="indefinite"/>
                        </circle>

                        <!-- Sparkles -->
                        <g fill="#fbbf24">
                            <path d="M60 80 L63 86 L60 92 L57 86 Z" opacity="0.8">
                                <animate attributeName="opacity" values="0.8;0.3;0.8" dur="1.5s" repeatCount="indefinite"/>
                            </path>
                            <path d="M340 120 L343 126 L340 132 L337 126 Z" opacity="0.8">
                                <animate attributeName="opacity" values="0.3;0.8;0.3" dur="2s" repeatCount="indefinite"/>
                            </path>
                        </g>

                        <!-- Gradients definitions -->
                        <defs>
                            <linearGradient id="gradient1" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" style="stop-color:#8b5cf6"/>
                                <stop offset="100%" style="stop-color:#ec4899"/>
                            </linearGradient>
                            <linearGradient id="gradient2" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" style="stop-color:#6366f1"/>
                                <stop offset="100%" style="stop-color:#8b5cf6"/>
                            </linearGradient>
                        </defs>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Features Section -->
        <div class="features-section">
            <div class="feature-card">
                <div class="feature-icon" style="background: linear-gradient(135deg, #6366f1, #8b5cf6);">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                        <rect x="3" y="4" width="18" height="16" rx="2"/>
                        <circle cx="9" cy="10" r="2"/>
                        <path d="M15 8h2M15 12h2M7 16h10"/>
                    </svg>
                </div>
                <h3>Tarjetas Personalizadas</h3>
                <p>Diseña tarjetas unicas con multiples plantillas y estilos para cada miembro de tu equipo.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon" style="background: linear-gradient(135deg, #ec4899, #f97316);">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                        <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                        <path d="M2 17l10 5 10-5"/>
                        <path d="M2 12l10 5 10-5"/>
                    </svg>
                </div>
                <h3>Multiples Plantillas</h3>
                <p>Elige entre 6 plantillas profesionales: Modern, Classic, Minimal, Creative, Cyber y Vibrant.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon" style="background: linear-gradient(135deg, #10b981, #34d399);">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                        <path d="M4 12v8a2 2 0 002 2h12a2 2 0 002-2v-8"/>
                        <polyline points="16 6 12 2 8 6"/>
                        <line x1="12" y1="2" x2="12" y2="15"/>
                    </svg>
                </div>
                <h3>Comparte al Instante</h3>
                <p>Genera enlaces unicos y codigos QR para compartir tu tarjeta en segundos.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon" style="background: linear-gradient(135deg, #f59e0b, #fbbf24);">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                        <circle cx="12" cy="12" r="3"/>
                        <path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-2 2 2 2 0 01-2-2v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83 0 2 2 0 010-2.83l.06-.06a1.65 1.65 0 00.33-1.82 1.65 1.65 0 00-1.51-1H3a2 2 0 01-2-2 2 2 0 012-2h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 010-2.83 2 2 0 012.83 0l.06.06a1.65 1.65 0 001.82.33H9a1.65 1.65 0 001-1.51V3a2 2 0 012-2 2 2 0 012 2v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 0 2 2 0 010 2.83l-.06.06a1.65 1.65 0 00-.33 1.82V9a1.65 1.65 0 001.51 1H21a2 2 0 012 2 2 2 0 01-2 2h-.09a1.65 1.65 0 00-1.51 1z"/>
                    </svg>
                </div>
                <h3>Personaliza Todo</h3>
                <p>Ajusta colores, tipografias, sombras y mas con nuestro editor visual en tiempo real.</p>
            </div>
        </div>
    </div>
</template>

<script>
import { useAuth } from '@/stores/auth';

export default {
    name: 'Home',
    computed: {
        isGuest() {
            const auth = useAuth();
            return auth.hasRole('Guest');
        },
    },
}
</script>

<style scoped>
/* Upgrade Banner */
.upgrade-banner {
    background: linear-gradient(135deg, #ede9fe, #fce7f3);
    border: 1px solid #ddd6fe;
    border-radius: 12px;
    padding: 1rem 1.5rem;
    margin-bottom: 1.5rem;
}

.banner-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
}

.banner-text {
    font-size: 0.95rem;
    color: #5b21b6;
}

.banner-btn {
    white-space: nowrap;
    padding: 0.5rem 1.25rem;
    background: linear-gradient(135deg, #8b5cf6, #ec4899);
    color: white;
    font-weight: 600;
    font-size: 0.85rem;
    border-radius: 8px;
    text-decoration: none;
    transition: all 0.2s;
}

.banner-btn:hover {
    transform: translateY(-1px);
    color: white;
    box-shadow: 0 4px 12px rgba(139, 92, 246, 0.3);
}

@media (max-width: 576px) {
    .banner-content {
        flex-direction: column;
        text-align: center;
    }
}

/* Hero Section */
.welcome-hero {
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border-radius: 20px;
    padding: 3rem;
    margin-bottom: 2rem;
    overflow: hidden;
}

.hero-content {
    display: flex;
    align-items: center;
    gap: 3rem;
}

.hero-text {
    flex: 1;
}

.hero-title {
    font-size: 2.5rem;
    font-weight: 700;
    color: #1e293b;
    line-height: 1.2;
    margin-bottom: 1.5rem;
}

.gradient-text {
    background: linear-gradient(135deg, #8b5cf6, #ec4899, #f97316);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.hero-description {
    font-size: 1.1rem;
    color: #64748b;
    line-height: 1.7;
    margin-bottom: 2rem;
}

.btn-start {
    display: inline-flex;
    align-items: center;
    padding: 0.875rem 2rem;
    background: linear-gradient(135deg, #8b5cf6, #ec4899);
    color: white;
    font-weight: 600;
    border-radius: 12px;
    text-decoration: none;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(139, 92, 246, 0.4);
}

.btn-start:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(139, 92, 246, 0.5);
    color: white;
}

.hero-illustration {
    flex: 1;
    display: flex;
    justify-content: center;
}

.cards-illustration {
    width: 100%;
    max-width: 400px;
    height: auto;
}

/* Features Section */
.features-section {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
}

.feature-card {
    background: white;
    border-radius: 16px;
    padding: 1.75rem;
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

/* Responsive */
@media (max-width: 992px) {
    .hero-content {
        flex-direction: column;
        text-align: center;
    }

    .hero-title {
        font-size: 2rem;
    }

    .hero-illustration {
        order: -1;
    }

    .cards-illustration {
        max-width: 300px;
    }
}

@media (max-width: 576px) {
    .welcome-hero {
        padding: 2rem 1.5rem;
    }

    .hero-title {
        font-size: 1.75rem;
    }

    .hero-description {
        font-size: 1rem;
    }
}
</style>
