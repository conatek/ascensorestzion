<template>
    <div class="auth-page">
        <!-- Left Side - Illustration -->
        <div class="auth-illustration">
            <!-- Fondo decorativo -->
            <div class="ai-glow ai-glow--1"></div>
            <div class="ai-glow ai-glow--2"></div>
            <div class="ai-grid"></div>

            <div class="illustration-content">
                <svg class="auth-svg" viewBox="0 0 420 460" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <!-- Halo -->
                    <circle cx="210" cy="210" r="160" fill="url(#halo)" opacity="0.6"/>

                    <!-- Edificio -->
                    <rect x="120" y="60" width="180" height="320" rx="10" fill="#0f2417" stroke="#30ab0a" stroke-width="1.5" opacity="0.95"/>

                    <!-- Pisos -->
                    <line x1="120" y1="140" x2="300" y2="140" stroke="#2f6b3a" stroke-width="1" opacity="0.5"/>
                    <line x1="120" y1="220" x2="300" y2="220" stroke="#2f6b3a" stroke-width="1" opacity="0.5"/>
                    <line x1="120" y1="300" x2="300" y2="300" stroke="#2f6b3a" stroke-width="1" opacity="0.5"/>

                    <!-- Ventanas izquierda -->
                    <rect x="138" y="78" width="34" height="44" rx="3" fill="#13351f" stroke="#37c20c" stroke-width="1" opacity="0.7"/>
                    <rect x="138" y="158" width="34" height="44" rx="3" fill="#13351f" stroke="#37c20c" stroke-width="1" opacity="0.7"/>
                    <rect x="138" y="238" width="34" height="44" rx="3" fill="#13351f" stroke="#37c20c" stroke-width="1" opacity="0.7"/>
                    <rect x="138" y="318" width="34" height="44" rx="3" fill="#13351f" stroke="#37c20c" stroke-width="1" opacity="0.7"/>

                    <!-- Ventanas derecha -->
                    <rect x="248" y="78" width="34" height="44" rx="3" fill="#13351f" stroke="#37c20c" stroke-width="1" opacity="0.7"/>
                    <rect x="248" y="158" width="34" height="44" rx="3" fill="#13351f" stroke="#37c20c" stroke-width="1" opacity="0.7"/>
                    <rect x="248" y="238" width="34" height="44" rx="3" fill="#13351f" stroke="#37c20c" stroke-width="1" opacity="0.7"/>
                    <rect x="248" y="318" width="34" height="44" rx="3" fill="#13351f" stroke="#37c20c" stroke-width="1" opacity="0.7"/>

                    <!-- Ducto del ascensor -->
                    <rect x="185" y="66" width="50" height="308" rx="4" fill="#08130c" stroke="#37c20c" stroke-width="1.5"/>
                    <line x1="200" y1="66" x2="200" y2="150" stroke="#37c20c" stroke-width="1" stroke-dasharray="5 4" opacity="0.8"/>
                    <line x1="220" y1="66" x2="220" y2="150" stroke="#37c20c" stroke-width="1" stroke-dasharray="5 4" opacity="0.8"/>

                    <!-- Cabina -->
                    <rect x="190" y="150" width="40" height="58" rx="4" fill="#30ab0a" stroke="#9bf06f" stroke-width="1.5"/>
                    <line x1="210" y1="154" x2="210" y2="204" stroke="#08130c" stroke-width="1.5"/>
                    <polygon points="204,170 210,162 216,170" fill="#0b1120"/>
                    <polygon points="204,188 210,196 216,188" fill="#0b1120"/>

                    <!-- Entrada -->
                    <rect x="192" y="350" width="36" height="24" rx="2" fill="#08130c" stroke="#37c20c" stroke-width="1"/>

                    <!-- Tarjetas flotantes -->
                    <g opacity="0.9">
                        <rect x="40" y="150" width="74" height="50" rx="10" fill="#101a2e" stroke="#30ab0a" stroke-width="1"/>
                        <rect x="52" y="164" width="34" height="6" rx="3" fill="#37c20c" opacity="0.8"/>
                        <rect x="52" y="178" width="50" height="5" rx="2.5" fill="#3a4a63"/>
                        <circle cx="98" cy="178" r="6" fill="#ba2831" opacity="0.85"/>
                    </g>
                    <g opacity="0.9">
                        <rect x="306" y="250" width="74" height="50" rx="10" fill="#101a2e" stroke="#30ab0a" stroke-width="1"/>
                        <circle cx="324" cy="275" r="9" fill="none" stroke="#37c20c" stroke-width="2"/>
                        <path d="M320 275 l3 3 l6 -6" stroke="#37c20c" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                        <rect x="340" y="270" width="30" height="5" rx="2.5" fill="#3a4a63"/>
                        <rect x="340" y="280" width="20" height="4" rx="2" fill="#2a3850"/>
                    </g>

                    <!-- Puntos decorativos -->
                    <circle cx="70" cy="90" r="4" fill="#37c20c" opacity="0.6"/>
                    <circle cx="350" cy="120" r="5" fill="#37c20c" opacity="0.4"/>
                    <circle cx="360" cy="380" r="4" fill="#ba2831" opacity="0.5"/>
                    <circle cx="60" cy="360" r="4" fill="#37c20c" opacity="0.5"/>

                    <!-- Líneas de conexión -->
                    <path d="M114 175 Q 150 175 185 185" stroke="#30ab0a" stroke-width="1.2" opacity="0.4" fill="none"/>
                    <path d="M306 275 Q 270 275 235 250" stroke="#30ab0a" stroke-width="1.2" opacity="0.4" fill="none"/>

                    <defs>
                        <radialGradient id="halo" cx="0.5" cy="0.5" r="0.5">
                            <stop offset="0%" stop-color="#30ab0a" stop-opacity="0.35"/>
                            <stop offset="100%" stop-color="#30ab0a" stop-opacity="0"/>
                        </radialGradient>
                    </defs>
                </svg>

                <div class="illustration-text">
                    <h2>Bienvenido</h2>
                    <p>Sistema integral de gestión de equipos de transporte vertical.</p>
                </div>
            </div>
        </div>

        <!-- Right Side - Form -->
        <div class="auth-form-container">
            <div class="auth-form-wrapper">
                <!-- Logo (versión original) -->
                <div class="auth-logo">
                    <img :src="'/images/logo/logo-atzion.svg'" alt="Ascensores Tzion" class="auth-logo-img" />
                </div>

                <div class="auth-header">
                    <h1>Iniciar Sesión</h1>
                    <p>Ingresa tus credenciales para acceder a tu cuenta</p>
                </div>

                <!-- Error Alert -->
                <div v-if="errorMessage" class="error-alert">
                    <svg class="alert-icon" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <span>{{ errorMessage }}</span>
                </div>

                <form @submit.prevent="handleLogin" class="auth-form">
                    <!-- Email Field -->
                    <div class="form-group">
                        <label for="email" class="form-label">Correo electrónico</label>
                        <div class="input-wrapper">
                            <svg class="input-icon" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                                <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                            </svg>
                            <input
                                v-model="form.email"
                                id="email"
                                type="email"
                                class="form-input"
                                :class="{ 'has-error': errors.email }"
                                placeholder="tu@correo.com"
                                required
                                autofocus
                            >
                        </div>
                        <span v-if="errors.email" class="error-text">{{ errors.email[0] }}</span>
                    </div>

                    <!-- Password Field -->
                    <div class="form-group">
                        <label for="password" class="form-label">Contraseña</label>
                        <div class="input-wrapper">
                            <svg class="input-icon" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                            </svg>
                            <input
                                v-model="form.password"
                                id="password"
                                :type="showPassword ? 'text' : 'password'"
                                class="form-input"
                                :class="{ 'has-error': errors.password }"
                                placeholder="Tu contraseña"
                                required
                            >
                            <button type="button" class="toggle-password" @click="showPassword = !showPassword">
                                <svg v-if="!showPassword" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                                    <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                                </svg>
                                <svg v-else viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2.003 2.003 0 012.45 2.45l1.514 1.514a4 4 0 00-5.478-5.478z" clip-rule="evenodd"/>
                                    <path d="M12.454 16.697L9.75 13.992a4 4 0 01-3.742-3.741L2.335 6.578A9.98 9.98 0 00.458 10c1.274 4.057 5.065 7 9.542 7 .847 0 1.669-.105 2.454-.303z"/>
                                </svg>
                            </button>
                        </div>
                        <span v-if="errors.password" class="error-text">{{ errors.password[0] }}</span>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn-submit" :disabled="loading">
                        <span v-if="loading" class="spinner"></span>
                        <span>{{ loading ? 'Ingresando...' : 'Ingresar' }}</span>
                    </button>
                </form>

                <!-- ¿Olvidó su contraseña? -->
                <div class="auth-footer">
                    <p>
                        <router-link to="/recuperar-contrasena" class="auth-link">¿Olvidaste tu contraseña?</router-link>
                    </p>
                </div>

                <!-- Volver al inicio -->
                <router-link to="/inicio" class="back-link">
                    <svg viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"/>
                    </svg>
                    Volver al inicio
                </router-link>
            </div>
        </div>
    </div>
</template>

<script>
import { useAuth } from '@/stores/auth';

export default {
    data() {
        return {
            form: {
                email: '',
                password: '',
            },
            errors: {},
            errorMessage: '',
            loading: false,
            showPassword: false,
        };
    },
    methods: {
        async handleLogin() {
            this.errors = {};
            this.errorMessage = '';
            this.loading = true;

            try {
                const auth = useAuth();
                await auth.login(this.form);
                this.$router.push({ name: 'home' });
            } catch (error) {
                if (error.response) {
                    if (error.response.status === 422) {
                        this.errors = error.response.data.errors || {};
                    } else if (error.response.status === 401) {
                        this.errorMessage = error.response.data.message || 'Credenciales inválidas';
                    } else {
                        this.errorMessage = 'Error al iniciar sesión. Intenta de nuevo.';
                    }
                } else {
                    this.errorMessage = 'Error de conexión. Verifica tu red.';
                }
            } finally {
                this.loading = false;
            }
        },
    },
};
</script>

<style scoped>
/* Base Layout */
.auth-page {
    display: flex;
    min-height: 100vh;
    background: #0b1120;
    font-family: 'Poppins', -apple-system, BlinkMacSystemFont, sans-serif;
}

/* ===== Left Side - Illustration ===== */
.auth-illustration {
    flex: 0 0 48%;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 3rem;
    overflow: hidden;
    background:
        radial-gradient(circle at 28% 22%, rgba(48, 171, 10, 0.16), transparent 42%),
        linear-gradient(165deg, #0a1f12 0%, #0b1120 52%, #0a1626 100%);
}

.ai-glow {
    position: absolute;
    border-radius: 50%;
    filter: blur(80px);
    z-index: 0;
}
.ai-glow--1 {
    width: 360px; height: 360px;
    top: -80px; left: -60px;
    background: rgba(48, 171, 10, 0.25);
}
.ai-glow--2 {
    width: 300px; height: 300px;
    bottom: -60px; right: -40px;
    background: rgba(186, 40, 49, 0.16);
}
.ai-grid {
    position: absolute;
    inset: 0;
    background-image:
        linear-gradient(rgba(255, 255, 255, 0.035) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255, 255, 255, 0.035) 1px, transparent 1px);
    background-size: 44px 44px;
    mask-image: radial-gradient(circle at 50% 45%, #000 30%, transparent 75%);
    -webkit-mask-image: radial-gradient(circle at 50% 45%, #000 30%, transparent 75%);
    z-index: 0;
}

.illustration-content {
    position: relative;
    z-index: 1;
    text-align: center;
    color: #e8eef7;
    width: 100%;
    max-width: 440px;
}

.auth-svg {
    width: 100%;
    max-width: 360px;
    height: auto;
    margin: 0 auto 1.5rem;
    display: block;
    filter: drop-shadow(0 20px 40px rgba(0, 0, 0, 0.45));
}

.illustration-text h2 {
    font-size: 2rem;
    font-weight: 800;
    margin: 0 0 0.6rem;
    color: #f1f5f9;
}
.illustration-text p {
    font-size: 1rem;
    line-height: 1.65;
    color: #93a3bb;
    margin: 0 auto;
    max-width: 360px;
}

/* ===== Right Side - Form ===== */
.auth-form-container {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2.5rem;
    background: #0b1120;
}

.auth-form-wrapper {
    width: 100%;
    max-width: 400px;
}

.auth-logo {
    display: flex;
    justify-content: center;
    margin-bottom: 1.5rem;
}
.auth-logo-img {
    height: 58px;
    width: auto;
}

.auth-header {
    text-align: center;
    margin-bottom: 2rem;
}
.auth-header h1 {
    font-size: 1.8rem;
    font-weight: 700;
    color: #f1f5f9;
    margin: 0 0 0.5rem;
}
.auth-header p {
    font-size: 0.95rem;
    color: #93a3bb;
    margin: 0;
}

/* Error Alert */
.error-alert {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    background: rgba(186, 40, 49, 0.12);
    border: 1px solid rgba(186, 40, 49, 0.35);
    color: #f1909a;
    padding: 0.8rem 1rem;
    border-radius: 10px;
    margin-bottom: 1.25rem;
    font-size: 0.88rem;
}
.alert-icon {
    width: 18px;
    height: 18px;
    flex-shrink: 0;
}

/* Form */
.form-group {
    margin-bottom: 1.25rem;
}
.form-label {
    display: block;
    font-size: 0.85rem;
    font-weight: 600;
    color: #c3cfe0;
    margin-bottom: 0.5rem;
}

.input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}
.input-icon {
    position: absolute;
    left: 0.9rem;
    width: 18px;
    height: 18px;
    color: #5d6f8a;
    pointer-events: none;
}
.form-input {
    width: 100%;
    padding: 0.8rem 0.9rem 0.8rem 2.6rem;
    background: #111c30;
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 10px;
    font-size: 0.95rem;
    color: #e8eef7;
    transition: border-color 0.2s, box-shadow 0.2s;
}
.form-input::placeholder { color: #54637c; }
.form-input:focus {
    outline: none;
    border-color: #30ab0a;
    box-shadow: 0 0 0 3px rgba(48, 171, 10, 0.18);
}
.form-input.has-error {
    border-color: #ba2831;
}

.toggle-password {
    position: absolute;
    right: 0.75rem;
    background: none;
    border: none;
    cursor: pointer;
    color: #5d6f8a;
    padding: 0.25rem;
    display: flex;
}
.toggle-password svg { width: 18px; height: 18px; }
.toggle-password:hover { color: #30ab0a; }

.error-text {
    display: block;
    color: #f1909a;
    font-size: 0.78rem;
    margin-top: 0.35rem;
}

.btn-submit {
    width: 100%;
    padding: 0.85rem;
    background: linear-gradient(135deg, #30ab0a, #248007);
    color: #fff;
    border: none;
    border-radius: 10px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    transition: transform 0.2s, box-shadow 0.2s;
    box-shadow: 0 8px 24px rgba(48, 171, 10, 0.25);
}
.btn-submit:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 10px 28px rgba(48, 171, 10, 0.35);
}
.btn-submit:disabled { opacity: 0.7; cursor: not-allowed; }

.spinner {
    width: 16px;
    height: 16px;
    border: 2px solid rgba(255, 255, 255, 0.4);
    border-top-color: #fff;
    border-radius: 50%;
    animation: spin 0.7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

.auth-footer {
    text-align: center;
    margin-top: 1.5rem;
}
.auth-link {
    color: #4cc40f;
    font-size: 0.9rem;
    font-weight: 500;
    text-decoration: none;
}
.auth-link:hover { text-decoration: underline; }

.back-link {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    margin-top: 1.5rem;
    color: #6b7c96;
    font-size: 0.85rem;
    text-decoration: none;
    transition: color 0.2s;
}
.back-link svg { width: 16px; height: 16px; }
.back-link:hover { color: #93a3bb; }

/* ===== Responsive ===== */
@media (max-width: 900px) {
    .auth-illustration {
        display: none;
    }
    .auth-form-container {
        flex: 1;
    }
}
</style>
