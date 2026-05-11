<template>
    <div class="auth-page">
        <!-- Left Side - Form -->
        <div class="auth-form-container">
            <div class="auth-form-wrapper">
                <!-- Mobile Logo -->
                <div class="mobile-logo">
                    <svg viewBox="0 0 40 40" fill="none">
                        <rect width="40" height="40" rx="10" fill="url(#mobile-logo-gradient-reg)"/>
                        <path d="M12 20C12 15.58 15.58 12 20 12C24.42 12 28 15.58 28 20C28 24.42 24.42 28 20 28" stroke="white" stroke-width="2.5" stroke-linecap="round"/>
                        <circle cx="20" cy="20" r="4" fill="white"/>
                        <defs>
                            <linearGradient id="mobile-logo-gradient-reg" x1="0" y1="0" x2="40" y2="40">
                                <stop stop-color="#8b5cf6"/>
                                <stop offset="1" stop-color="#ec4899"/>
                            </linearGradient>
                        </defs>
                    </svg>
                </div>

                <div class="auth-header">
                    <h1>Crear Cuenta</h1>
                    <p>Registrate y comienza a crear tus tarjetas digitales</p>
                </div>

                <!-- Error Alert -->
                <div v-if="errorMessage" class="error-alert">
                    <svg class="alert-icon" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <span>{{ errorMessage }}</span>
                </div>

                <form @submit.prevent="handleRegister" class="auth-form">
                    <!-- Name Field -->
                    <div class="form-group">
                        <label for="name" class="form-label">
                            Nombre completo
                        </label>
                        <div class="input-wrapper">
                            <svg class="input-icon" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                            </svg>
                            <input
                                v-model="form.name"
                                id="name"
                                type="text"
                                class="form-input"
                                :class="{ 'has-error': errors.name }"
                                placeholder="Tu nombre"
                                required
                                autofocus
                            >
                        </div>
                        <span v-if="errors.name" class="error-text">{{ errors.name[0] }}</span>
                    </div>

                    <!-- Email Field -->
                    <div class="form-group">
                        <label for="email" class="form-label">
                            Correo electronico
                        </label>
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
                                placeholder="tu@email.com"
                                required
                            >
                        </div>
                        <span v-if="errors.email" class="error-text">{{ errors.email[0] }}</span>
                    </div>

                    <!-- Password Fields Row -->
                    <div class="form-row">
                        <!-- Password Field -->
                        <div class="form-group">
                            <label for="password" class="form-label">
                                Contrasena
                            </label>
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
                                    placeholder="Minimo 8 caracteres"
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

                        <!-- Confirm Password Field -->
                        <div class="form-group">
                            <label for="password_confirmation" class="form-label">
                                Confirmar contrasena
                            </label>
                            <div class="input-wrapper">
                                <svg class="input-icon" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <input
                                    v-model="form.password_confirmation"
                                    id="password_confirmation"
                                    :type="showPassword ? 'text' : 'password'"
                                    class="form-input"
                                    placeholder="Repetir contrasena"
                                    required
                                >
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn-submit" :disabled="loading">
                        <span v-if="loading" class="spinner"></span>
                        <span>{{ loading ? 'Creando cuenta...' : 'Crear cuenta' }}</span>
                    </button>
                </form>

                <!-- Login Link -->
                <div class="auth-footer">
                    <p>
                        Ya tienes una cuenta?
                        <router-link to="/login" class="auth-link">Inicia sesion</router-link>
                    </p>
                </div>

                <!-- Back to Home -->
                <router-link to="/inicio" class="back-link">
                    <svg viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"/>
                    </svg>
                    Volver al inicio
                </router-link>
            </div>
        </div>

        <!-- Right Side - Illustration -->
        <div class="auth-illustration">
            <div class="illustration-content">
                <!-- Decorative Background Elements -->
                <div class="bg-shapes">
                    <div class="shape shape-1"></div>
                    <div class="shape shape-2"></div>
                    <div class="shape shape-3"></div>
                </div>

                <!-- Main SVG Illustration -->
                <svg class="auth-svg" viewBox="0 0 400 400" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <!-- Background Circle -->
                    <circle cx="200" cy="200" r="150" fill="white" opacity="0.1"/>

                    <!-- User Profile/Creation Icon -->
                    <g transform="translate(130, 80)">
                        <!-- Card Background -->
                        <rect x="10" y="30" width="120" height="160" rx="12" fill="white" opacity="0.95"/>

                        <!-- Avatar Circle -->
                        <circle cx="70" cy="80" r="35" fill="#f3e8ff"/>
                        <circle cx="70" cy="70" r="15" fill="#8b5cf6"/>
                        <ellipse cx="70" cy="95" rx="20" ry="12" fill="#8b5cf6"/>

                        <!-- Name lines -->
                        <rect x="35" y="130" width="70" height="8" rx="4" fill="#e2e8f0"/>
                        <rect x="45" y="148" width="50" height="6" rx="3" fill="#f1f5f9"/>

                        <!-- Plus badge -->
                        <circle cx="115" cy="45" r="20" fill="#ec4899"/>
                        <path d="M108 45h14M115 38v14" stroke="white" stroke-width="3" stroke-linecap="round"/>
                    </g>

                    <!-- Floating Elements -->
                    <g class="floating-element" style="animation-delay: 0s">
                        <circle cx="70" cy="180" r="25" fill="white" opacity="0.2"/>
                        <path d="M62 180l6 6 12-12" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </g>

                    <g class="floating-element" style="animation-delay: 0.3s">
                        <circle cx="330" cy="150" r="22" fill="white" opacity="0.2"/>
                        <path d="M322 150h16M330 142v16" stroke="white" stroke-width="2.5" stroke-linecap="round"/>
                    </g>

                    <g class="floating-element" style="animation-delay: 0.6s">
                        <circle cx="320" cy="280" r="20" fill="white" opacity="0.2"/>
                        <path d="M312 280l8-8m0 0l8 8m-8-8v16" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </g>

                    <!-- Small Cards -->
                    <g class="floating-card" style="animation-delay: 0.2s">
                        <rect x="40" y="280" width="70" height="45" rx="8" fill="white" opacity="0.15"/>
                        <circle cx="60" cy="295" r="8" fill="white" opacity="0.3"/>
                        <rect x="75" y="290" width="25" height="4" rx="2" fill="white" opacity="0.3"/>
                        <rect x="75" y="300" width="18" height="3" rx="1.5" fill="white" opacity="0.2"/>
                    </g>

                    <g class="floating-card" style="animation-delay: 0.5s">
                        <rect x="290" cy="220" width="70" height="45" rx="8" fill="white" opacity="0.15"/>
                        <circle cx="310" cy="235" r="8" fill="white" opacity="0.3"/>
                        <rect x="325" y="230" width="25" height="4" rx="2" fill="white" opacity="0.3"/>
                        <rect x="325" y="240" width="18" height="3" rx="1.5" fill="white" opacity="0.2"/>
                    </g>

                    <!-- Decorative Dots -->
                    <circle cx="90" cy="120" r="4" fill="white" opacity="0.4"/>
                    <circle cx="310" cy="100" r="5" fill="white" opacity="0.3"/>
                    <circle cx="100" cy="320" r="3" fill="white" opacity="0.5"/>
                    <circle cx="350" cy="200" r="4" fill="white" opacity="0.3"/>

                    <!-- Connection arcs -->
                    <path d="M100 250 Q 150 230 200 250" stroke="white" stroke-width="1" opacity="0.15" fill="none"/>
                    <path d="M200 320 Q 250 300 300 320" stroke="white" stroke-width="1" opacity="0.15" fill="none"/>
                </svg>

                <!-- Brand -->
                <div class="illustration-brand">
                    <svg class="brand-logo" viewBox="0 0 40 40" fill="none">
                        <rect width="40" height="40" rx="10" fill="white" opacity="0.2"/>
                        <path d="M12 20C12 15.58 15.58 12 20 12C24.42 12 28 15.58 28 20C28 24.42 24.42 28 20 28" stroke="white" stroke-width="2.5" stroke-linecap="round"/>
                        <circle cx="20" cy="20" r="4" fill="white"/>
                    </svg>
                    <span class="brand-name">NexosCard</span>
                </div>

                <div class="illustration-text">
                    <h2>Unete a nosotros</h2>
                    <p>Crea tarjetas de presentacion digitales profesionales para tu empresa y equipo.</p>
                </div>
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
                name: '',
                email: '',
                password: '',
                password_confirmation: '',
            },
            errors: {},
            errorMessage: '',
            loading: false,
            showPassword: false,
        };
    },
    methods: {
        async handleRegister() {
            this.errors = {};
            this.errorMessage = '';
            this.loading = true;

            try {
                const auth = useAuth();
                await auth.register(this.form);
                this.$router.push({ name: 'home' });
            } catch (error) {
                if (error.response) {
                    if (error.response.status === 422) {
                        this.errors = error.response.data.errors || {};
                    } else {
                        this.errorMessage = error.response.data.message || 'Error al registrar. Intenta de nuevo.';
                    }
                } else {
                    this.errorMessage = 'Error de conexion. Verifica tu red.';
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
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
}

/* Left Side - Form */
.auth-form-container {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 3rem;
    background: white;
}

.auth-form-wrapper {
    width: 100%;
    max-width: 480px;
}

.mobile-logo {
    display: none;
    margin-bottom: 2rem;
}

.mobile-logo svg {
    width: 48px;
    height: 48px;
}

.auth-header {
    margin-bottom: 2rem;
}

.auth-header h1 {
    font-size: 1.875rem;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 0.5rem;
}

.auth-header p {
    color: #64748b;
    font-size: 0.95rem;
}

/* Error Alert */
.error-alert {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem;
    background: #fef2f2;
    border: 1px solid #fecaca;
    border-radius: 10px;
    color: #dc2626;
    font-size: 0.9rem;
    margin-bottom: 1.5rem;
}

.alert-icon {
    width: 20px;
    height: 20px;
    flex-shrink: 0;
}

/* Form */
.auth-form {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.form-label {
    font-size: 0.875rem;
    font-weight: 500;
    color: #374151;
}

.input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.input-icon {
    position: absolute;
    left: 1rem;
    width: 18px;
    height: 18px;
    color: #94a3b8;
    pointer-events: none;
}

.form-input {
    width: 100%;
    padding: 0.75rem 1rem 0.75rem 2.75rem;
    font-size: 0.95rem;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    background: white;
    transition: all 0.2s;
}

.form-input:focus {
    outline: none;
    border-color: #8b5cf6;
    box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
}

.form-input.has-error {
    border-color: #ef4444;
}

.form-input::placeholder {
    color: #94a3b8;
}

.toggle-password {
    position: absolute;
    right: 1rem;
    background: none;
    border: none;
    cursor: pointer;
    padding: 0;
    color: #94a3b8;
    transition: color 0.2s;
}

.toggle-password:hover {
    color: #64748b;
}

.toggle-password svg {
    width: 18px;
    height: 18px;
}

.error-text {
    font-size: 0.8rem;
    color: #ef4444;
}

/* Submit Button */
.btn-submit {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    width: 100%;
    padding: 0.875rem;
    background: linear-gradient(135deg, #8b5cf6, #7c3aed);
    color: white;
    font-size: 1rem;
    font-weight: 600;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: 0 2px 8px rgba(124, 58, 237, 0.25);
    margin-top: 0.5rem;
}

.btn-submit:hover:not(:disabled) {
    box-shadow: 0 4px 12px rgba(124, 58, 237, 0.35);
    transform: translateY(-1px);
}

.btn-submit:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

.spinner {
    width: 18px;
    height: 18px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-top-color: white;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Footer */
.auth-footer {
    text-align: center;
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid #f1f5f9;
}

.auth-footer p {
    color: #64748b;
    font-size: 0.95rem;
}

.auth-link {
    color: #8b5cf6;
    font-weight: 600;
    text-decoration: none;
    transition: color 0.2s;
}

.auth-link:hover {
    color: #7c3aed;
    text-decoration: underline;
}

.back-link {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    margin-top: 1.5rem;
    color: #64748b;
    font-size: 0.9rem;
    text-decoration: none;
    transition: color 0.2s;
}

.back-link:hover {
    color: #8b5cf6;
}

.back-link svg {
    width: 16px;
    height: 16px;
}

/* Right Side - Illustration */
.auth-illustration {
    flex: 0 0 45%;
    background: linear-gradient(135deg, #ec4899 0%, #8b5cf6 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 3rem;
    position: relative;
    overflow: hidden;
}

.illustration-content {
    position: relative;
    z-index: 1;
    text-align: center;
    color: white;
}

/* Background Shapes */
.bg-shapes {
    position: absolute;
    inset: 0;
    overflow: hidden;
}

.shape {
    position: absolute;
    border-radius: 50%;
    background: white;
    opacity: 0.05;
}

.shape-1 {
    width: 400px;
    height: 400px;
    top: -100px;
    right: -100px;
}

.shape-2 {
    width: 300px;
    height: 300px;
    bottom: -50px;
    left: -50px;
}

.shape-3 {
    width: 200px;
    height: 200px;
    top: 50%;
    left: 10%;
}

.auth-svg {
    width: 100%;
    max-width: 350px;
    height: auto;
    margin-bottom: 2rem;
}

.floating-element {
    animation: float 3s ease-in-out infinite;
}

.floating-card {
    animation: float 3s ease-in-out infinite;
}

@keyframes float {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}

.illustration-brand {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    margin-bottom: 1.5rem;
}

.brand-logo {
    width: 40px;
    height: 40px;
}

.brand-name {
    font-size: 1.5rem;
    font-weight: 700;
}

.illustration-text h2 {
    font-size: 1.75rem;
    font-weight: 700;
    margin-bottom: 0.75rem;
}

.illustration-text p {
    font-size: 1rem;
    opacity: 0.9;
    max-width: 300px;
    margin: 0 auto;
    line-height: 1.6;
}

/* Responsive */
@media (max-width: 992px) {
    .auth-illustration {
        display: none;
    }

    .auth-form-container {
        padding: 2rem;
    }

    .mobile-logo {
        display: block;
    }
}

@media (max-width: 576px) {
    .auth-form-container {
        padding: 1.5rem;
    }

    .auth-header h1 {
        font-size: 1.5rem;
    }

    .form-row {
        grid-template-columns: 1fr;
    }
}
</style>
