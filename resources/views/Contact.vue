<template>
    <div class="contact-page">
        <PublicNavbar />

        <!-- Hero Banner -->
        <section class="ct-hero">
            <div class="ct-hero-bg">
                <div class="orb orb--green"></div>
                <div class="grid-texture"></div>
            </div>
            <div class="ct-container ct-hero-inner">
                <span class="eyebrow">Hablemos</span>
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

                        <div v-if="sent" class="ct-success">
                            <i class="fa fa-check-circle"></i>
                            <span>{{ successMsg }}</span>
                        </div>

                        <form v-else class="ct-form" @submit.prevent="submit" novalidate>
                            <div class="ct-field">
                                <label class="ct-label" for="ct-nombre">Nombre</label>
                                <input id="ct-nombre" v-model="form.nombre" type="text" class="ct-input" :class="{ 'is-invalid': errors.nombre }" placeholder="Su nombre completo" />
                                <span v-if="errors.nombre" class="ct-err">{{ errors.nombre[0] }}</span>
                            </div>
                            <div class="ct-field">
                                <label class="ct-label" for="ct-email">Email</label>
                                <input id="ct-email" v-model="form.email" type="email" class="ct-input" :class="{ 'is-invalid': errors.email }" placeholder="correo@ejemplo.com" />
                                <span v-if="errors.email" class="ct-err">{{ errors.email[0] }}</span>
                            </div>
                            <div class="ct-field">
                                <label class="ct-label" for="ct-telefono">Teléfono</label>
                                <input id="ct-telefono" v-model="form.telefono" type="tel" class="ct-input" :class="{ 'is-invalid': errors.telefono }" placeholder="+57 300 000 0000" />
                                <span v-if="errors.telefono" class="ct-err">{{ errors.telefono[0] }}</span>
                            </div>
                            <div class="ct-field">
                                <label class="ct-label" for="ct-asunto">Asunto</label>
                                <select id="ct-asunto" v-model="form.asunto" class="ct-input ct-select" :class="{ 'is-invalid': errors.asunto }">
                                    <option value="" disabled>Seleccione un asunto</option>
                                    <option value="mantenimiento">Mantenimiento</option>
                                    <option value="reparacion">Reparación</option>
                                    <option value="modernizacion">Modernización</option>
                                    <option value="instalacion">Instalación</option>
                                    <option value="cotizacion">Cotización</option>
                                    <option value="otro">Otro</option>
                                </select>
                                <span v-if="errors.asunto" class="ct-err">{{ errors.asunto[0] }}</span>
                            </div>
                            <div class="ct-field">
                                <label class="ct-label" for="ct-mensaje">Mensaje</label>
                                <textarea id="ct-mensaje" v-model="form.mensaje" class="ct-input ct-textarea" :class="{ 'is-invalid': errors.mensaje }" rows="5" placeholder="Describa su solicitud..."></textarea>
                                <span v-if="errors.mensaje" class="ct-err">{{ errors.mensaje[0] }}</span>
                            </div>

                            <!-- Honeypot anti-bots (oculto a usuarios) -->
                            <input v-model="form.website" type="text" class="ct-honeypot" tabindex="-1" autocomplete="off" aria-hidden="true" />

                            <p v-if="generalError" class="ct-general-err">{{ generalError }}</p>

                            <button type="submit" class="ct-submit-btn" :disabled="sending">
                                <span v-if="sending" class="ct-spinner"></span>
                                {{ sending ? 'Enviando…' : 'Enviar mensaje' }}
                            </button>
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
import contactService from '@/services/contactService.js';

export default {
    name: 'Contact',
    components: { PublicNavbar, PublicFooter },

    data() {
        return {
            form: { nombre: '', email: '', telefono: '', asunto: '', mensaje: '', website: '' },
            errors: {},
            generalError: '',
            sending: false,
            sent: false,
            successMsg: '',
        };
    },

    methods: {
        async submit() {
            this.sending = true;
            this.errors = {};
            this.generalError = '';
            try {
                const { data } = await contactService.send(this.form);
                this.successMsg = data.message;
                this.sent = true;
            } catch (e) {
                const status = e.response?.status;
                if (status === 422) {
                    this.errors = e.response.data.errors || {};
                } else if (status === 429) {
                    this.generalError = 'Demasiados envíos. Espera un momento e intenta de nuevo.';
                } else {
                    this.generalError = e.response?.data?.message || 'No se pudo enviar el mensaje. Intenta más tarde.';
                }
            } finally {
                this.sending = false;
            }
        },
    },
};
</script>

<style scoped>
.contact-page {
    font-family: 'Poppins', sans-serif;
    background: #0b1120;
    color: #e8eef7;
}

.eyebrow {
    display: inline-block; font-size: 0.8rem; font-weight: 700; letter-spacing: 0.08em;
    text-transform: uppercase; color: #37c20c; margin-bottom: 0.75rem;
}
.orb { position: absolute; border-radius: 50%; filter: blur(90px); }
.orb--green { width: 480px; height: 480px; top: -150px; left: 50%; transform: translateX(-50%); background: rgba(48, 171, 10, 0.18); }
.grid-texture {
    position: absolute; inset: 0;
    background-image:
        linear-gradient(rgba(255, 255, 255, 0.03) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
    background-size: 48px 48px;
    mask-image: radial-gradient(circle at 50% 30%, #000 20%, transparent 70%);
    -webkit-mask-image: radial-gradient(circle at 50% 30%, #000 20%, transparent 70%);
}

/* Hero */
.ct-hero {
    position: relative;
    padding: 150px 2rem 4rem;
    text-align: center;
    overflow: hidden;
}
.ct-hero-bg { position: absolute; inset: 0; z-index: 0; }
.ct-hero-inner { position: relative; z-index: 1; }

.ct-hero-title {
    font-size: 2.9rem;
    font-weight: 800;
    color: #f8fafc;
    margin-bottom: 1rem;
}

.ct-hero-subtitle {
    font-size: 1.1rem;
    color: #93a3bb;
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
    background: #0b1120;
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
    color: #f8fafc;
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
    color: #c3cfe0;
}

.ct-input {
    font-family: 'Poppins', sans-serif;
    font-size: 0.95rem;
    padding: 0.75rem 1rem;
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 10px;
    color: #e8eef7;
    background: #111c30;
    transition: border-color 0.2s, box-shadow 0.2s;
    outline: none;
    width: 100%;
}

.ct-input:focus {
    border-color: #30ab0a;
    box-shadow: 0 0 0 3px rgba(48, 171, 10, 0.18);
}

.ct-input::placeholder {
    color: #54637c;
}

.ct-select {
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2354637c' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 1rem center;
    padding-right: 2.5rem;
}
.ct-select option { background: #131f36; color: #e8eef7; }
.ct-select option:disabled { color: #6b7c96; }

.ct-textarea {
    resize: vertical;
    min-height: 120px;
}

.ct-submit-btn {
    font-family: 'Poppins', sans-serif;
    padding: 0.9rem 2.25rem;
    background: linear-gradient(135deg, #30ab0a, #248007);
    color: #fff;
    font-size: 1rem;
    font-weight: 600;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    transition: transform 0.2s, box-shadow 0.2s;
    align-self: flex-start;
    box-shadow: 0 10px 26px rgba(48, 171, 10, 0.3);
}

.ct-submit-btn:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 14px 32px rgba(48, 171, 10, 0.42);
}
.ct-submit-btn:disabled { opacity: 0.65; cursor: not-allowed; }

.ct-spinner {
    width: 16px; height: 16px; border: 2px solid rgba(255, 255, 255, 0.4);
    border-top-color: #fff; border-radius: 50%; display: inline-block;
    margin-right: 0.5rem; vertical-align: -2px; animation: ct-spin 0.7s linear infinite;
}
@keyframes ct-spin { to { transform: rotate(360deg); } }

.is-invalid { border-color: #ba2831 !important; }
.ct-err { color: #f1909a; font-size: 0.78rem; margin-top: 0.3rem; }
.ct-general-err {
    background: rgba(186, 40, 49, 0.12); border: 1px solid rgba(186, 40, 49, 0.35);
    color: #f1909a; padding: 0.65rem 0.9rem; border-radius: 10px; font-size: 0.85rem; margin: 0;
}

/* Honeypot: oculto a usuarios reales, visible para bots */
.ct-honeypot {
    position: absolute; left: -9999px; width: 1px; height: 1px;
    opacity: 0; pointer-events: none;
}

.ct-success {
    display: flex; align-items: center; gap: 0.75rem;
    background: rgba(48, 171, 10, 0.1); border: 1px solid rgba(48, 171, 10, 0.35);
    color: #9bdc83; padding: 1.1rem 1.25rem; border-radius: 12px; font-size: 0.95rem;
}
.ct-success i { font-size: 1.3rem; color: #4cc40f; }

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
    background: rgba(255, 255, 255, 0.025);
    border: 1px solid rgba(255, 255, 255, 0.08);
    padding: 1.25rem;
    border-radius: 14px;
    transition: transform 0.2s, background 0.2s;
}
.ct-info-card:hover {
    transform: translateY(-3px);
    background: rgba(255, 255, 255, 0.045);
}

.ct-info-icon {
    width: 46px;
    height: 46px;
    background: rgba(48, 171, 10, 0.12);
    border: 1px solid rgba(48, 171, 10, 0.25);
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
    color: #f1f5f9;
    margin: 0 0 0.35rem;
}

.ct-info-text p {
    font-size: 0.9rem;
    color: #93a3bb;
    line-height: 1.6;
    margin: 0;
}

/* Map Placeholder */
.ct-map-section {
    background: #0b1120;
    padding: 0 0 5rem;
}

.ct-map-placeholder {
    background: radial-gradient(circle at 50% 40%, rgba(48, 171, 10, 0.08), transparent 70%), rgba(255, 255, 255, 0.02);
    border: 1px dashed rgba(48, 171, 10, 0.3);
    height: 300px;
    border-radius: 18px;
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
    color: #6b7c96;
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
