<template>
    <div class="pw-page">
        <div class="pw-card">
            <img :src="'/images/logo/logo-atzion.svg'" alt="Ascensores Tzion" class="pw-logo" />

            <template v-if="!sent">
                <h1 class="pw-title">Recuperar contraseña</h1>
                <p class="pw-subtitle">Ingresa tu correo y te enviaremos un enlace para restablecerla.</p>

                <form @submit.prevent="submit">
                    <div class="pw-field">
                        <label>Correo electrónico</label>
                        <input
                            v-model="email"
                            type="email"
                            placeholder="tu@correo.com"
                            :class="{ 'is-invalid': error }"
                            required
                        />
                        <span v-if="error" class="pw-err">{{ error }}</span>
                    </div>
                    <button type="submit" class="pw-btn" :disabled="loading || !email.trim()">
                        <span v-if="loading" class="pw-spinner"></span>
                        {{ loading ? 'Enviando…' : 'Enviar enlace' }}
                    </button>
                </form>
            </template>

            <div v-else class="pw-success">
                <div class="pw-success-icon"><i class="fa fa-paper-plane"></i></div>
                <h1 class="pw-title">Revisa tu correo</h1>
                <p class="pw-subtitle">{{ message }}</p>
            </div>

            <router-link to="/login" class="pw-back">
                <i class="fa fa-arrow-left"></i> Volver al inicio de sesión
            </router-link>
        </div>
    </div>
</template>

<script>
import passwordService from '@/services/passwordService.js';

export default {
    name: 'ForgotPassword',

    data() {
        return {
            email: '',
            loading: false,
            sent: false,
            message: '',
            error: '',
        };
    },

    methods: {
        async submit() {
            this.loading = true;
            this.error = '';
            try {
                const { data } = await passwordService.forgot(this.email.trim());
                this.message = data.message;
                this.sent = true;
            } catch (e) {
                if (e.response?.status === 422) {
                    this.error = e.response.data.errors?.email?.[0] || 'Correo inválido.';
                } else if (e.response?.status === 429) {
                    this.error = 'Demasiados intentos. Espera un momento e intenta de nuevo.';
                } else {
                    this.error = 'No se pudo enviar el enlace. Intenta de nuevo.';
                }
            } finally {
                this.loading = false;
            }
        },
    },
};
</script>

<style scoped>
.pw-page {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1.5rem;
    background: linear-gradient(135deg, #1a2e14 0%, #1b5e10 50%, #227a0c 100%);
}

.pw-card {
    width: 100%;
    max-width: 420px;
    background: white;
    border-radius: 18px;
    padding: 2rem 1.75rem;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.25);
    text-align: center;
}

.pw-logo {
    height: 52px;
    width: auto;
    margin-bottom: 1.25rem;
}

.pw-title {
    font-size: 1.3rem;
    font-weight: 700;
    color: #1e293b;
    margin: 0 0 0.4rem;
}

.pw-subtitle {
    font-size: 0.88rem;
    color: #64748b;
    margin: 0 0 1.5rem;
    line-height: 1.4;
}

.pw-field {
    text-align: left;
    margin-bottom: 1.25rem;
}

.pw-field label {
    display: block;
    font-size: 0.82rem;
    font-weight: 600;
    color: #475569;
    margin-bottom: 0.4rem;
}

.pw-field input {
    width: 100%;
    padding: 0.7rem 0.9rem;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    font-size: 0.95rem;
    transition: border-color 0.2s;
}

.pw-field input:focus {
    outline: none;
    border-color: #30ab0a;
}

.is-invalid {
    border-color: #ba2831 !important;
}

.pw-err {
    display: block;
    color: #ba2831;
    font-size: 0.78rem;
    margin-top: 0.35rem;
}

.pw-btn {
    width: 100%;
    padding: 0.75rem;
    border: none;
    border-radius: 10px;
    background: linear-gradient(135deg, #30ab0a, #279208);
    color: white;
    font-size: 0.95rem;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.pw-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.pw-spinner {
    width: 16px;
    height: 16px;
    border: 2px solid rgba(255, 255, 255, 0.5);
    border-top-color: white;
    border-radius: 50%;
    animation: pw-spin 0.7s linear infinite;
}

@keyframes pw-spin {
    to { transform: rotate(360deg); }
}

.pw-success-icon {
    font-size: 2.5rem;
    color: #30ab0a;
    margin-bottom: 0.75rem;
}

.pw-back {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    margin-top: 1.5rem;
    font-size: 0.85rem;
    color: #30ab0a;
    text-decoration: none;
    font-weight: 500;
}

.pw-back:hover {
    text-decoration: underline;
}
</style>
