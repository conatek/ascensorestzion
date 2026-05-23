<template>
    <div class="contact-page">
        <PublicNavbar />

        <!-- Hero Banner -->
        <section class="ct-hero">
            <div class="ct-container">
                <h1 class="ct-hero-title">Contáctenos</h1>
                <p class="ct-hero-subtitle">Estamos listos para atender sus necesidades. Comuníquese con nosotros.</p>
            </div>
        </section>

        <!-- Contact Content -->
        <section class="ct-content">
            <div class="ct-container">
                <div class="ct-grid">
                    <!-- Left: Contact Form -->
                    <div class="ct-form-col">
                        <h2 class="ct-col-title">Envíenos un mensaje</h2>
                        <form class="ct-form" @submit.prevent>
                            <div class="ct-field">
                                <label class="ct-label" for="ct-nombre">Nombre</label>
                                <input id="ct-nombre" type="text" class="ct-input" placeholder="Su nombre completo" />
                            </div>
                            <div class="ct-field">
                                <label class="ct-label" for="ct-email">Email</label>
                                <input id="ct-email" type="email" class="ct-input" placeholder="correo@ejemplo.com" />
                            </div>
                            <div class="ct-field">
                                <label class="ct-label" for="ct-telefono">Teléfono</label>
                                <input id="ct-telefono" type="tel" class="ct-input" placeholder="+57 300 000 0000" />
                            </div>
                            <div class="ct-field">
                                <label class="ct-label" for="ct-asunto">Asunto</label>
                                <select id="ct-asunto" class="ct-input ct-select">
                                    <option value="" disabled selected>Seleccione un asunto</option>
                                    <option value="mantenimiento">Mantenimiento</option>
                                    <option value="reparacion">Reparación</option>
                                    <option value="modernizacion">Modernización</option>
                                    <option value="instalacion">Instalación</option>
                                    <option value="cotizacion">Cotización</option>
                                    <option value="otro">Otro</option>
                                </select>
                            </div>
                            <div class="ct-field">
                                <label class="ct-label" for="ct-mensaje">Mensaje</label>
                                <textarea id="ct-mensaje" class="ct-input ct-textarea" rows="5" placeholder="Describa su solicitud..."></textarea>
                            </div>
                            <button type="submit" class="ct-submit-btn">Enviar mensaje</button>
                        </form>
                    </div>

                    <!-- Right: Contact Info -->
                    <div class="ct-info-col">
                        <h2 class="ct-col-title">Información de contacto</h2>
                        <div class="ct-info-cards">
                            <!-- Dirección -->
                            <div class="ct-info-card">
                                <div class="ct-info-icon">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="#30ab0a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/>
                                        <circle cx="12" cy="10" r="3"/>
                                    </svg>
                                </div>
                                <div class="ct-info-text">
                                    <h4 class="ct-info-label">Dirección</h4>
                                    <p>Carrera 78 # 41-32, Laureles Lorena<br>Medellín, Antioquia</p>
                                </div>
                            </div>
                            <!-- Teléfono -->
                            <div class="ct-info-card">
                                <div class="ct-info-icon">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="#30ab0a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/>
                                    </svg>
                                </div>
                                <div class="ct-info-text">
                                    <h4 class="ct-info-label">Teléfono</h4>
                                    <p>PBX: +57 (604) 322 5315<br>+57 302 311 9169<br>+57 300 401 9483</p>
                                </div>
                            </div>
                            <!-- Email -->
                            <div class="ct-info-card">
                                <div class="ct-info-icon">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="#30ab0a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                        <polyline points="22,6 12,13 2,6"/>
                                    </svg>
                                </div>
                                <div class="ct-info-text">
                                    <h4 class="ct-info-label">Email</h4>
                                    <p>operaciones@ascensorestzion.com</p>
                                </div>
                            </div>
                            <!-- Horario -->
                            <div class="ct-info-card">
                                <div class="ct-info-icon">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="#30ab0a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10"/>
                                        <polyline points="12 6 12 12 16 14"/>
                                    </svg>
                                </div>
                                <div class="ct-info-text">
                                    <h4 class="ct-info-label">Horario</h4>
                                    <p>Lunes a Viernes 7:00 AM - 6:00 PM<br>Sábados 8:00 AM - 1:00 PM</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Map Placeholder -->
        <section class="ct-map-section">
            <div class="ct-container">
                <div class="ct-map-placeholder">
                    <svg viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="ct-map-icon">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/>
                        <circle cx="12" cy="10" r="3"/>
                    </svg>
                    <span class="ct-map-text">Mapa - Carrera 78 # 41-32, Laureles Lorena, Medellín</span>
                </div>
            </div>
        </section>

        <PublicFooter />
    </div>
</template>

<script>
import PublicNavbar from '@/components/PublicNavbar.vue';
import PublicFooter from '@/components/PublicFooter.vue';

export default {
    name: 'Contact',
    components: { PublicNavbar, PublicFooter },
};
</script>

<style scoped>
.contact-page {
    font-family: 'Poppins', sans-serif;
    color: #1e293b;
}

/* Hero */
.ct-hero {
    background: #1e293b;
    color: #fff;
    padding: 120px 2rem 4rem;
    text-align: center;
}

.ct-hero-title {
    font-size: 2.75rem;
    font-weight: 700;
    margin-bottom: 1rem;
}

.ct-hero-subtitle {
    font-size: 1.1rem;
    color: #cbd5e1;
    max-width: 600px;
    margin: 0 auto;
    line-height: 1.7;
}

/* Container */
.ct-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 2rem;
}

/* Contact Content */
.ct-content {
    background: #fff;
    padding: 5rem 0;
}

.ct-grid {
    display: grid;
    grid-template-columns: 3fr 2fr;
    gap: 3rem;
}

.ct-col-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 1.75rem;
}

/* Form */
.ct-form {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.ct-field {
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
}

.ct-label {
    font-size: 0.9rem;
    font-weight: 600;
    color: #475569;
}

.ct-input {
    font-family: 'Poppins', sans-serif;
    font-size: 0.95rem;
    padding: 0.75rem 1rem;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    color: #1e293b;
    background: #fff;
    transition: border-color 0.2s;
    outline: none;
    width: 100%;
}

.ct-input:focus {
    border-color: #30ab0a;
}

.ct-input::placeholder {
    color: #94a3b8;
}

.ct-select {
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 1rem center;
    padding-right: 2.5rem;
}

.ct-textarea {
    resize: vertical;
    min-height: 120px;
}

.ct-submit-btn {
    font-family: 'Poppins', sans-serif;
    padding: 0.875rem 2rem;
    background: #30ab0a;
    color: #fff;
    font-size: 1rem;
    font-weight: 600;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s;
    align-self: flex-start;
}

.ct-submit-btn:hover {
    background: #279208;
    transform: translateY(-1px);
}

/* Info Cards */
.ct-info-cards {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.ct-info-card {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    background: #f8fafc;
    padding: 1.25rem;
    border-radius: 12px;
}

.ct-info-icon {
    width: 44px;
    height: 44px;
    background: #e8f5e4;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.ct-info-icon svg {
    width: 20px;
    height: 20px;
}

.ct-info-text {
    flex: 1;
}

.ct-info-label {
    font-size: 0.95rem;
    font-weight: 600;
    color: #1e293b;
    margin: 0 0 0.35rem;
}

.ct-info-text p {
    font-size: 0.9rem;
    color: #606060;
    line-height: 1.6;
    margin: 0;
}

/* Map Placeholder */
.ct-map-section {
    background: #fff;
    padding: 0 0 5rem;
}

.ct-map-placeholder {
    background: #e2e8f0;
    height: 300px;
    border-radius: 16px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 1rem;
}

.ct-map-icon {
    width: 40px;
    height: 40px;
}

.ct-map-text {
    font-size: 0.95rem;
    color: #64748b;
    font-weight: 500;
}

/* Responsive: 992px */
@media (max-width: 992px) {
    .ct-grid {
        grid-template-columns: 1fr 1fr;
    }
}

/* Responsive: 768px */
@media (max-width: 768px) {
    .ct-hero-title {
        font-size: 2rem;
    }

    .ct-grid {
        grid-template-columns: 1fr;
    }

    .ct-content {
        padding: 3rem 0;
    }

    .ct-map-placeholder {
        height: 220px;
    }
}

/* Responsive: 576px */
@media (max-width: 576px) {
    .ct-hero {
        padding: 100px 1rem 3rem;
    }

    .ct-hero-title {
        font-size: 1.75rem;
    }

    .ct-container {
        padding: 0 1rem;
    }

    .ct-submit-btn {
        width: 100%;
        text-align: center;
    }

    .ct-map-placeholder {
        height: 180px;
        border-radius: 12px;
    }

    .ct-map-text {
        font-size: 0.85rem;
        text-align: center;
        padding: 0 1rem;
    }
}
</style>
