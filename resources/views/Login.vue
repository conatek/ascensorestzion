<template>
    <div class="auth-page">
        <!-- Left Side - Illustration -->
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

                    <!-- Lock/Security Icon -->
                    <g transform="translate(140, 100)">
                        <!-- Lock Body -->
                        <rect x="20" y="80" width="80" height="70" rx="10" fill="white" opacity="0.95"/>
                        <rect x="20" y="80" width="80" height="70" rx="10" stroke="white" stroke-width="2"/>

                        <!-- Lock Arc -->
                        <path d="M35 80V60C35 40 50 25 60 25C70 25 85 40 85 60V80" stroke="white" stroke-width="8" stroke-linecap="round" fill="none"/>

                        <!-- Keyhole -->
                        <circle cx="60" cy="110" r="10" fill="#8b5cf6"/>
                        <rect x="56" y="115" width="8" height="15" rx="2" fill="#8b5cf6"/>
                    </g>

                    <!-- Floating Cards -->
                    <g class="floating-card" style="animation-delay: 0s">
                        <rect x="50" y="250" width="80" height="50" rx="8" fill="white" opacity="0.2"/>
                        <rect x="60" y="265" width="40" height="6" rx="3" fill="white" opacity="0.5"/>
                        <rect x="60" y="280" width="25" height="4" rx="2" fill="white" opacity="0.3"/>
                    </g>

                    <g class="floating-card" style="animation-delay: 0.5s">
                        <rect x="270" y="200" width="80" height="50" rx="8" fill="white" opacity="0.2"/>
                        <circle cx="290" cy="225" r="10" fill="white" opacity="0.3"/>
                        <rect x="310" y="220" width="30" height="4" rx="2" fill="white" opacity="0.4"/>
                        <rect x="310" y="230" width="20" height="3" rx="1.5" fill="white" opacity="0.3"/>
                    </g>

                    <!-- Decorative Dots -->
                    <circle cx="100" cy="150" r="4" fill="white" opacity="0.4"/>
                    <circle cx="300" cy="120" r="5" fill="white" opacity="0.3"/>
                    <circle cx="330" cy="300" r="3" fill="white" opacity="0.5"/>
                    <circle cx="80" cy="320" r="4" fill="white" opacity="0.3"/>

                    <!-- Connection Lines -->
                    <path d="M100 200 Q 150 180 200 200" stroke="white" stroke-width="1" opacity="0.2" fill="none"/>
                    <path d="M200 200 Q 250 220 300 200" stroke="white" stroke-width="1" opacity="0.2" fill="none"/>
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
                    <h2>Bienvenido de vuelta</h2>
                    <p>Gestiona tus tarjetas digitales y haz crecer tu presencia en linea.</p>
                </div>
            </div>
        </div>

        <!-- Right Side - Form -->
        <div class="auth-form-container">
            <div class="auth-form-wrapper">
                <!-- Mobile Logo -->
                <div class="mobile-logo">
                    <svg viewBox="0 0 40 40" fill="none">
                        <rect width="40" height="40" rx="10" fill="url(#mobile-logo-gradient)"/>
                        <path d="M12 20C12 15.58 15.58 12 20 12C24.42 12 28 15.58 28 20C28 24.42 24.42 28 20 28" stroke="white" stroke-width="2.5" stroke-linecap="round"/>
                        <circle cx="20" cy="20" r="4" fill="white"/>
                        <defs>
                            <linearGradient id="mobile-logo-gradient" x1="0" y1="0" x2="40" y2="40">
                                <stop stop-color="#8b5cf6"/>
                                <stop offset="1" stop-color="#ec4899"/>
                            </linearGradient>
                        </defs>
                    </svg>
                </div>

                <div class="auth-header">
                    <h1>Iniciar Sesion</h1>
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
                                autofocus
                            >
                        </div>
                        <span v-if="errors.email" class="error-text">{{ errors.email[0] }}</span>
                    </div>

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
                                placeholder="Tu contrasena"
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

                <!-- Register Link -->
                <div class="auth-footer">
                    <p>
                        No tienes una cuenta?
                        <router-link to="/register" class="auth-link">Registrate aqui</router-link>
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
                        this.errorMessage = error.response.data.message || 'Credenciales invalidas';
                    } else {
                        this.errorMessage = 'Error al iniciar sesion. Intenta de nuevo.';
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

/* Left Side - Illustration */
.auth-illustration {
    flex: 0 0 45%;
    background: linear-gradient(135deg, #8b5cf6 0%, #ec4899 100%);
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
    left: -100px;
}

.shape-2 {
    width: 300px;
    height: 300px;
    bottom: -50px;
    right: -50px;
}

.shape-3 {
    width: 200px;
    height: 200px;
    top: 50%;
    right: 10%;
}

.auth-svg {
    width: 100%;
    max-width: 350px;
    height: auto;
    margin-bottom: 2rem;
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

/* Right Side - Form */
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
    max-width: 400px;
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
}
</style>
