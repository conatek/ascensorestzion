<template>
    <div class="services-page">
        <PublicNavbar />

        <!-- Hero Banner -->
        <section class="sv-hero">
            <div class="sv-container">
                <h1 class="sv-hero-title">Nuestros Servicios</h1>
                <p class="sv-hero-subtitle">Nuestro equipo de expertos trabaja con precisión y compromiso para ofrecer soluciones confiables que aseguren la seguridad, comodidad y eficiencia en cada detalle de su experiencia.</p>
            </div>
        </section>

        <!-- Servicio 1: Instalación (white bg, text left / visual right) -->
        <section class="sv-service sv-bg-white">
            <div class="sv-container">
                <div class="sv-service-grid">
                    <div class="sv-service-text">
                        <div class="sv-service-icon-wrap">
                            <svg viewBox="0 0 64 64" fill="none" class="sv-service-icon">
                                <rect x="20" y="12" width="24" height="44" rx="2" stroke="#30ab0a" stroke-width="2.5" fill="none"/>
                                <rect x="26" y="18" width="12" height="8" rx="1" fill="#30ab0a" opacity="0.2"/>
                                <rect x="26" y="30" width="12" height="8" rx="1" fill="#30ab0a" opacity="0.2"/>
                                <rect x="26" y="42" width="12" height="8" rx="1" fill="#30ab0a" opacity="0.2"/>
                                <line x1="32" y1="4" x2="32" y2="12" stroke="#30ab0a" stroke-width="2.5"/>
                                <line x1="28" y1="4" x2="36" y2="4" stroke="#30ab0a" stroke-width="2.5"/>
                                <path d="M12 56h40" stroke="#606060" stroke-width="2"/>
                                <path d="M16 56v4M48 56v4" stroke="#606060" stroke-width="2"/>
                            </svg>
                        </div>
                        <h2 class="sv-service-title">Instalación</h2>
                        <p class="sv-service-desc">Nos encargamos de la instalación completa de ascensores, escaleras eléctricas y montacargas, adaptándonos a las necesidades de cada proyecto. Desde la planificación hasta la puesta en marcha, garantizamos un proceso seguro y eficiente con los más altos estándares de calidad.</p>
                        <div class="sv-features">
                            <div class="sv-feature-item">
                                <svg class="sv-check" viewBox="0 0 20 20" fill="#30ab0a"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                <span>Diseño e ingeniería de proyecto</span>
                            </div>
                            <div class="sv-feature-item">
                                <svg class="sv-check" viewBox="0 0 20 20" fill="#30ab0a"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                <span>Montaje de guías, cabina y contrapeso</span>
                            </div>
                            <div class="sv-feature-item">
                                <svg class="sv-check" viewBox="0 0 20 20" fill="#30ab0a"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                <span>Instalación de sistema de tracción</span>
                            </div>
                            <div class="sv-feature-item">
                                <svg class="sv-check" viewBox="0 0 20 20" fill="#30ab0a"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                <span>Cableado eléctrico y conexiones</span>
                            </div>
                            <div class="sv-feature-item">
                                <svg class="sv-check" viewBox="0 0 20 20" fill="#30ab0a"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                <span>Pruebas de seguridad y puesta en marcha</span>
                            </div>
                            <div class="sv-feature-item">
                                <svg class="sv-check" viewBox="0 0 20 20" fill="#30ab0a"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                <span>Capacitación al personal del edificio</span>
                            </div>
                        </div>
                    </div>
                    <div class="sv-service-visual">
                        <div class="sv-placeholder">
                            <svg viewBox="0 0 48 48" fill="none" class="sv-placeholder-icon">
                                <rect x="6" y="10" width="36" height="28" rx="4" stroke="#94a3b8" stroke-width="2"/>
                                <circle cx="18" cy="22" r="4" stroke="#94a3b8" stroke-width="2"/>
                                <path d="M6 34l10-8 6 4 10-10 10 8" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span class="sv-placeholder-text">Fotografía del servicio</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Servicio 2: Modernización (bg #f8fafc, visual left / text right) -->
        <section class="sv-service sv-bg-alt">
            <div class="sv-container">
                <div class="sv-service-grid sv-reverse">
                    <div class="sv-service-text">
                        <div class="sv-service-icon-wrap">
                            <svg viewBox="0 0 64 64" fill="none" class="sv-service-icon">
                                <path d="M32 8v8M32 48v8M8 32h8M48 32h8" stroke="#30ab0a" stroke-width="2.5" stroke-linecap="round"/>
                                <circle cx="32" cy="32" r="16" stroke="#30ab0a" stroke-width="2.5" fill="none"/>
                                <path d="M26 32l4 4 8-8" stroke="#30ab0a" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M14 14l4 4M46 14l-4 4M14 50l4-4M46 50l-4-4" stroke="#30ab0a" stroke-width="2" stroke-linecap="round" opacity="0.5"/>
                            </svg>
                        </div>
                        <h2 class="sv-service-title">Modernización</h2>
                        <p class="sv-service-desc">Actualizamos sus equipos existentes con tecnología de última generación, mejorando su rendimiento, seguridad y eficiencia energética. La modernización extiende la vida útil de sus ascensores y escaleras eléctricas sin necesidad de reemplazarlos por completo.</p>
                        <div class="sv-features">
                            <div class="sv-feature-item">
                                <svg class="sv-check" viewBox="0 0 20 20" fill="#30ab0a"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                <span>Actualización de sistemas de control</span>
                            </div>
                            <div class="sv-feature-item">
                                <svg class="sv-check" viewBox="0 0 20 20" fill="#30ab0a"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                <span>Renovación de cabinas y puertas</span>
                            </div>
                            <div class="sv-feature-item">
                                <svg class="sv-check" viewBox="0 0 20 20" fill="#30ab0a"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                <span>Mejora de eficiencia energética</span>
                            </div>
                            <div class="sv-feature-item">
                                <svg class="sv-check" viewBox="0 0 20 20" fill="#30ab0a"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                <span>Implementación de variadores de frecuencia</span>
                            </div>
                            <div class="sv-feature-item">
                                <svg class="sv-check" viewBox="0 0 20 20" fill="#30ab0a"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                <span>Adecuación a normativa vigente</span>
                            </div>
                            <div class="sv-feature-item">
                                <svg class="sv-check" viewBox="0 0 20 20" fill="#30ab0a"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                <span>Instalación de sistemas de rescate automático</span>
                            </div>
                        </div>
                    </div>
                    <div class="sv-service-visual">
                        <div class="sv-placeholder">
                            <svg viewBox="0 0 48 48" fill="none" class="sv-placeholder-icon">
                                <rect x="6" y="10" width="36" height="28" rx="4" stroke="#94a3b8" stroke-width="2"/>
                                <circle cx="18" cy="22" r="4" stroke="#94a3b8" stroke-width="2"/>
                                <path d="M6 34l10-8 6 4 10-10 10 8" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span class="sv-placeholder-text">Fotografía del servicio</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Servicio 3: Mantenimiento (white bg, text left / visual right) -->
        <section class="sv-service sv-bg-white">
            <div class="sv-container">
                <div class="sv-service-grid">
                    <div class="sv-service-text">
                        <div class="sv-service-icon-wrap">
                            <svg viewBox="0 0 64 64" fill="none" class="sv-service-icon">
                                <path d="M38 16l10-10 10 10-10 10z" stroke="#30ab0a" stroke-width="2.5" fill="none"/>
                                <path d="M38 16L20 34" stroke="#30ab0a" stroke-width="2.5"/>
                                <path d="M14 40l-4 4a4 4 0 000 5.66l4.34 4.34a4 4 0 005.66 0l4-4" stroke="#30ab0a" stroke-width="2.5" fill="none"/>
                                <path d="M24 40l-10 10" stroke="#30ab0a" stroke-width="2.5"/>
                            </svg>
                        </div>
                        <h2 class="sv-service-title">Mantenimiento</h2>
                        <p class="sv-service-desc">Ofrecemos programas de mantenimiento preventivo y correctivo diseñados para garantizar el funcionamiento óptimo de sus equipos. Nuestros técnicos calificados realizan inspecciones periódicas que previenen fallas y aseguran la continuidad operativa.</p>
                        <div class="sv-features">
                            <div class="sv-feature-item">
                                <svg class="sv-check" viewBox="0 0 20 20" fill="#30ab0a"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                <span>Inspección mensual programada</span>
                            </div>
                            <div class="sv-feature-item">
                                <svg class="sv-check" viewBox="0 0 20 20" fill="#30ab0a"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                <span>Lubricación de componentes mecánicos</span>
                            </div>
                            <div class="sv-feature-item">
                                <svg class="sv-check" viewBox="0 0 20 20" fill="#30ab0a"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                <span>Verificación de sistemas de seguridad</span>
                            </div>
                            <div class="sv-feature-item">
                                <svg class="sv-check" viewBox="0 0 20 20" fill="#30ab0a"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                <span>Ajuste de frenos y nivelación</span>
                            </div>
                            <div class="sv-feature-item">
                                <svg class="sv-check" viewBox="0 0 20 20" fill="#30ab0a"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                <span>Reportes técnicos detallados</span>
                            </div>
                            <div class="sv-feature-item">
                                <svg class="sv-check" viewBox="0 0 20 20" fill="#30ab0a"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                <span>Atención de emergencias 24/7</span>
                            </div>
                        </div>
                    </div>
                    <div class="sv-service-visual">
                        <div class="sv-placeholder">
                            <svg viewBox="0 0 48 48" fill="none" class="sv-placeholder-icon">
                                <rect x="6" y="10" width="36" height="28" rx="4" stroke="#94a3b8" stroke-width="2"/>
                                <circle cx="18" cy="22" r="4" stroke="#94a3b8" stroke-width="2"/>
                                <path d="M6 34l10-8 6 4 10-10 10 8" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span class="sv-placeholder-text">Fotografía del servicio</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Servicio 4: Reparación (bg #f8fafc, visual left / text right) -->
        <section class="sv-service sv-bg-alt">
            <div class="sv-container">
                <div class="sv-service-grid sv-reverse">
                    <div class="sv-service-text">
                        <div class="sv-service-icon-wrap">
                            <svg viewBox="0 0 64 64" fill="none" class="sv-service-icon">
                                <circle cx="32" cy="32" r="20" stroke="#30ab0a" stroke-width="2.5" fill="none"/>
                                <path d="M32 20v12l8 8" stroke="#30ab0a" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M20 8l-4 8M44 8l4 8" stroke="#30ab0a" stroke-width="2" stroke-linecap="round"/>
                                <path d="M28 4h8" stroke="#30ab0a" stroke-width="2.5" stroke-linecap="round"/>
                            </svg>
                        </div>
                        <h2 class="sv-service-title">Reparación</h2>
                        <p class="sv-service-desc">Contamos con un equipo técnico especializado para diagnosticar y reparar cualquier falla en sus equipos de transporte vertical. Trabajamos con repuestos originales y ofrecemos tiempos de respuesta rápidos para minimizar la interrupción del servicio.</p>
                        <div class="sv-features">
                            <div class="sv-feature-item">
                                <svg class="sv-check" viewBox="0 0 20 20" fill="#30ab0a"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                <span>Diagnóstico técnico especializado</span>
                            </div>
                            <div class="sv-feature-item">
                                <svg class="sv-check" viewBox="0 0 20 20" fill="#30ab0a"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                <span>Reparación de motores y reductores</span>
                            </div>
                            <div class="sv-feature-item">
                                <svg class="sv-check" viewBox="0 0 20 20" fill="#30ab0a"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                <span>Cambio de cables de tracción</span>
                            </div>
                            <div class="sv-feature-item">
                                <svg class="sv-check" viewBox="0 0 20 20" fill="#30ab0a"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                <span>Reparación de tarjetas electrónicas</span>
                            </div>
                            <div class="sv-feature-item">
                                <svg class="sv-check" viewBox="0 0 20 20" fill="#30ab0a"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                <span>Suministro de repuestos originales</span>
                            </div>
                            <div class="sv-feature-item">
                                <svg class="sv-check" viewBox="0 0 20 20" fill="#30ab0a"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                <span>Respuesta rápida ante emergencias</span>
                            </div>
                        </div>
                    </div>
                    <div class="sv-service-visual">
                        <div class="sv-placeholder">
                            <svg viewBox="0 0 48 48" fill="none" class="sv-placeholder-icon">
                                <rect x="6" y="10" width="36" height="28" rx="4" stroke="#94a3b8" stroke-width="2"/>
                                <circle cx="18" cy="22" r="4" stroke="#94a3b8" stroke-width="2"/>
                                <path d="M6 34l10-8 6 4 10-10 10 8" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span class="sv-placeholder-text">Fotografía del servicio</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Tipos de Equipo -->
        <section class="sv-equipos">
            <div class="sv-container">
                <h2 class="sv-section-title sv-title-light">Equipos que Atendemos</h2>
                <div class="sv-equipos-grid">
                    <!-- Ascensores -->
                    <div class="sv-equipo-card">
                        <div class="sv-equipo-icon">
                            <svg viewBox="0 0 64 64" fill="none">
                                <rect x="14" y="6" width="36" height="52" rx="4" stroke="#1e293b" stroke-width="2"/>
                                <rect x="20" y="12" width="10" height="14" rx="2" fill="#30ab0a" opacity="0.15" stroke="#30ab0a" stroke-width="1.5"/>
                                <rect x="34" y="12" width="10" height="14" rx="2" fill="#30ab0a" opacity="0.15" stroke="#30ab0a" stroke-width="1.5"/>
                                <rect x="24" y="32" width="16" height="20" rx="2" stroke="#30ab0a" stroke-width="2"/>
                                <line x1="32" y1="32" x2="32" y2="52" stroke="#30ab0a" stroke-width="1.5"/>
                                <path d="M28 8l4-4 4 4" stroke="#30ab0a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <h3 class="sv-equipo-title">Ascensores</h3>
                        <p class="sv-equipo-desc">Equipos de transporte vertical para edificios residenciales, comerciales e industriales de cualquier altura.</p>
                    </div>
                    <!-- Escaleras Eléctricas -->
                    <div class="sv-equipo-card">
                        <div class="sv-equipo-icon">
                            <svg viewBox="0 0 64 64" fill="none">
                                <path d="M8 18h14v10h12v10h12v10h10" stroke="#30ab0a" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M8 24h14v10h12v10h12v10h10" stroke="#30ab0a" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" opacity="0.3"/>
                                <circle cx="16" cy="14" r="3" stroke="#1e293b" stroke-width="1.5"/>
                                <line x1="16" y1="17" x2="16" y2="18" stroke="#1e293b" stroke-width="1.5"/>
                                <path d="M6 18h4M6 24h4" stroke="#606060" stroke-width="1.5" stroke-linecap="round"/>
                                <path d="M54 48h4M54 54h4" stroke="#606060" stroke-width="1.5" stroke-linecap="round"/>
                            </svg>
                        </div>
                        <h3 class="sv-equipo-title">Escaleras Eléctricas</h3>
                        <p class="sv-equipo-desc">Sistemas de transporte continuo para centros comerciales, estaciones, aeropuertos y edificios de alto tráfico.</p>
                    </div>
                    <!-- Montacargas -->
                    <div class="sv-equipo-card">
                        <div class="sv-equipo-icon">
                            <svg viewBox="0 0 64 64" fill="none">
                                <rect x="12" y="14" width="28" height="34" rx="3" stroke="#1e293b" stroke-width="2"/>
                                <rect x="18" y="20" width="16" height="10" rx="2" fill="#30ab0a" opacity="0.15" stroke="#30ab0a" stroke-width="1.5"/>
                                <rect x="18" y="34" width="16" height="8" rx="2" fill="#30ab0a" opacity="0.1" stroke="#30ab0a" stroke-width="1"/>
                                <path d="M44 28h8v22h-8" stroke="#30ab0a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <line x1="48" y1="32" x2="48" y2="46" stroke="#30ab0a" stroke-width="2"/>
                                <path d="M22 52v4M30 52v4" stroke="#606060" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        </div>
                        <h3 class="sv-equipo-title">Montacargas</h3>
                        <p class="sv-equipo-desc">Equipos de carga para transporte de materiales y mercancías en edificios industriales y comerciales.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Proceso de Trabajo -->
        <section class="sv-proceso">
            <div class="sv-container">
                <h2 class="sv-section-title">Nuestro Proceso</h2>
                <div class="sv-proceso-grid">
                    <div class="sv-paso">
                        <div class="sv-paso-number">1</div>
                        <div class="sv-paso-line"></div>
                        <h3 class="sv-paso-title">Diagnóstico</h3>
                        <p class="sv-paso-desc">Evaluamos el estado actual del equipo</p>
                    </div>
                    <div class="sv-paso">
                        <div class="sv-paso-number">2</div>
                        <div class="sv-paso-line"></div>
                        <h3 class="sv-paso-title">Propuesta</h3>
                        <p class="sv-paso-desc">Presentamos soluciones personalizadas</p>
                    </div>
                    <div class="sv-paso">
                        <div class="sv-paso-number">3</div>
                        <div class="sv-paso-line"></div>
                        <h3 class="sv-paso-title">Ejecución</h3>
                        <p class="sv-paso-desc">Implementamos con precisión y seguridad</p>
                    </div>
                    <div class="sv-paso">
                        <div class="sv-paso-number">4</div>
                        <h3 class="sv-paso-title">Seguimiento</h3>
                        <p class="sv-paso-desc">Monitoreo continuo post-servicio</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA -->
        <section class="sv-cta">
            <div class="sv-container sv-cta-content">
                <h2 class="sv-cta-title">¿Necesita un servicio para sus equipos?</h2>
                <router-link to="/contacto" class="sv-cta-btn">Solicitar cotización</router-link>
            </div>
        </section>

        <PublicFooter />
    </div>
</template>

<script>
import PublicNavbar from '@/components/PublicNavbar.vue';
import PublicFooter from '@/components/PublicFooter.vue';

export default {
    name: 'Services',
    components: { PublicNavbar, PublicFooter },
};
</script>

<style scoped>
.services-page {
    font-family: 'Poppins', sans-serif;
    color: #1e293b;
}

/* Hero */
.sv-hero {
    background: #1e293b;
    color: #fff;
    padding: 120px 2rem 4rem;
    text-align: center;
}

.sv-hero-title {
    font-size: 2.75rem;
    font-weight: 700;
    margin-bottom: 1.25rem;
}

.sv-hero-subtitle {
    font-size: 1.1rem;
    color: #cbd5e1;
    max-width: 720px;
    margin: 0 auto;
    line-height: 1.8;
}

/* Container */
.sv-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 2rem;
}

/* Service Sections */
.sv-service {
    padding: 5rem 0;
}

.sv-bg-white {
    background: #fff;
}

.sv-bg-alt {
    background: #f8fafc;
}

.sv-service-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 4rem;
    align-items: center;
}

.sv-service-grid.sv-reverse {
    direction: rtl;
}

.sv-service-grid.sv-reverse > * {
    direction: ltr;
}

.sv-service-icon-wrap {
    width: 72px;
    height: 72px;
    background: #e8f5e4;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1.5rem;
}

.sv-service-icon {
    width: 40px;
    height: 40px;
}

.sv-service-title {
    font-size: 1.75rem;
    font-weight: 700;
    margin-bottom: 1rem;
    color: #1e293b;
}

.sv-service-desc {
    font-size: 1rem;
    color: #606060;
    line-height: 1.8;
    margin-bottom: 1.5rem;
}

.sv-features {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.75rem;
}

.sv-feature-item {
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
    font-size: 0.9rem;
    color: #475569;
}

.sv-check {
    width: 18px;
    height: 18px;
    flex-shrink: 0;
    margin-top: 2px;
}

/* Placeholder */
.sv-service-visual {
    display: flex;
    align-items: center;
    justify-content: center;
}

.sv-placeholder {
    width: 100%;
    max-width: 420px;
    height: 320px;
    border: 2px dashed #cbd5e1;
    border-radius: 16px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 1rem;
    background: #f8fafc;
}

.sv-placeholder-icon {
    width: 48px;
    height: 48px;
}

.sv-placeholder-text {
    font-size: 0.9rem;
    color: #94a3b8;
    font-weight: 500;
}

/* Equipos Section */
.sv-equipos {
    background: #1e293b;
    color: #fff;
    padding: 5rem 0;
}

.sv-section-title {
    font-size: 2rem;
    font-weight: 700;
    text-align: center;
    margin-bottom: 3rem;
    color: #1e293b;
}

.sv-title-light {
    color: #fff;
}

.sv-equipos-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 2rem;
}

.sv-equipo-card {
    background: #fff;
    border-radius: 16px;
    padding: 2rem;
    text-align: center;
    transition: transform 0.3s;
}

.sv-equipo-card:hover {
    transform: translateY(-4px);
}

.sv-equipo-icon {
    width: 80px;
    height: 80px;
    margin: 0 auto 1.25rem;
}

.sv-equipo-icon svg {
    width: 100%;
    height: 100%;
}

.sv-equipo-title {
    font-size: 1.15rem;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 0.75rem;
}

.sv-equipo-desc {
    font-size: 0.9rem;
    color: #606060;
    line-height: 1.6;
    margin: 0;
}

/* Proceso Section */
.sv-proceso {
    background: #fff;
    padding: 5rem 0;
}

.sv-proceso-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 0;
    position: relative;
}

.sv-paso {
    text-align: center;
    position: relative;
    padding: 0 1rem;
}

.sv-paso-number {
    width: 52px;
    height: 52px;
    background: #30ab0a;
    color: #fff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    font-weight: 700;
    margin: 0 auto 1.25rem;
    position: relative;
    z-index: 2;
}

.sv-paso-line {
    position: absolute;
    top: 26px;
    left: calc(50% + 26px);
    right: calc(-50% + 26px);
    height: 2px;
    background: #30ab0a;
    z-index: 1;
}

.sv-paso:last-child .sv-paso-line {
    display: none;
}

.sv-paso-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 0.5rem;
}

.sv-paso-desc {
    font-size: 0.9rem;
    color: #606060;
    line-height: 1.5;
    margin: 0;
}

/* CTA Section */
.sv-cta {
    background: #30ab0a;
    padding: 4rem 0;
}

.sv-cta-content {
    text-align: center;
}

.sv-cta-title {
    font-size: 1.75rem;
    font-weight: 700;
    color: #fff;
    margin-bottom: 1.5rem;
}

.sv-cta-btn {
    display: inline-block;
    padding: 0.875rem 2.5rem;
    background: #fff;
    color: #30ab0a;
    font-weight: 600;
    font-size: 1rem;
    border-radius: 10px;
    text-decoration: none;
    transition: all 0.2s;
}

.sv-cta-btn:hover {
    background: #f0faf0;
    transform: translateY(-2px);
}

/* Responsive: 992px */
@media (max-width: 992px) {
    .sv-service-grid {
        grid-template-columns: 1fr;
        gap: 2.5rem;
    }

    .sv-service-grid.sv-reverse {
        direction: ltr;
    }

    .sv-service-visual {
        order: -1;
    }

    .sv-equipos-grid {
        grid-template-columns: repeat(3, 1fr);
    }

    .sv-proceso-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 2rem;
    }

    .sv-paso-line {
        display: none;
    }
}

/* Responsive: 768px */
@media (max-width: 768px) {
    .sv-hero-title {
        font-size: 2rem;
    }

    .sv-hero-subtitle {
        font-size: 1rem;
    }

    .sv-service {
        padding: 3rem 0;
    }

    .sv-features {
        grid-template-columns: 1fr;
    }

    .sv-equipos-grid {
        grid-template-columns: 1fr;
        max-width: 400px;
        margin: 0 auto;
    }

    .sv-section-title {
        font-size: 1.6rem;
    }
}

/* Responsive: 576px */
@media (max-width: 576px) {
    .sv-hero {
        padding: 100px 1rem 3rem;
    }

    .sv-hero-title {
        font-size: 1.75rem;
    }

    .sv-container {
        padding: 0 1rem;
    }

    .sv-service-title {
        font-size: 1.4rem;
    }

    .sv-placeholder {
        height: 240px;
    }

    .sv-proceso-grid {
        grid-template-columns: 1fr;
    }

    .sv-cta-title {
        font-size: 1.35rem;
    }
}
</style>
