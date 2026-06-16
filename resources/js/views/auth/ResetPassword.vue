<template>
    <div class="pw-page">
        <div class="pw-card">
            <img :src="'/images/logo/logo-atzion.svg'" alt="Ascensores Tzion" class="pw-logo" />

            <h1 class="pw-title">Nueva contraseña</h1>
            <p class="pw-subtitle">
                <span v-if="email">Para <strong>{{ email }}</strong></span>
                <span v-else>Define una nueva contraseña para tu cuenta.</span>
            </p>

            <form @submit.prevent="submit">
                <div class="pw-field">
                    <label>Nueva contraseña</label>
                    <input
                        v-model="form.password"
                        type="password"
                        autocomplete="new-password"
                        :class="{ 'is-invalid': errors.password }"
                        required
                    />
                    <span v-if="errors.password" class="pw-err">{{ errors.password[0] }}</span>
                </div>
                <div class="pw-field">
                    <label>Confirmar contraseña</label>
                    <input
                        v-model="form.password_confirmation"
                        type="password"
                        autocomplete="new-password"
                        required
                    />
                </div>

                <p v-if="generalError" class="pw-banner-err">{{ generalError }}</p>
                <p class="pw-hint">Mínimo 8 caracteres.</p>

                <button type="submit" class="pw-btn" :disabled="loading">
                    <span v-if="loading" class="pw-spinner"></span>
                    {{ loading ? 'Guardando…' : 'Restablecer contraseña' }}
                </button>
            </form>

            <router-link to="/login" class="pw-back">
                <i class="fa fa-arrow-left"></i> Volver al inicio de sesión
            </router-link>
        </div>
    </div>
</template>

<script>
import passwordService from '@/services/passwordService.js';

export default {
    name: 'ResetPassword',

    data() {
        return {
            form: { password: '', password_confirmation: '' },
            errors: {},
            generalError: '',
            loading: false,
        };
    },

    computed: {
        token() {
            return this.$route.params.token;
        },
        email() {
            return this.$route.query.email || '';
        },
    },

    methods: {
        async submit() {
            this.loading = true;
            this.errors = {};
            this.generalError = '';
            try {
                const { data } = await passwordService.reset({
                    token: this.token,
                    email: this.email,
                    password: this.form.password,
                    password_confirmation: this.form.password_confirmation,
                });
                await this.$swal.fire({
                    icon: 'success',
                    title: 'Contraseña restablecida',
                    text: data.message,
                    confirmButtonText: 'Iniciar sesión',
                });
                this.$router.push('/login');
            } catch (e) {
                if (e.response?.status === 422) {
                    const errs = e.response.data.errors || {};
                    this.errors = errs;
                    // token/email inválido o expirado vienen bajo 'email'
                    if (errs.email) this.generalError = errs.email[0];
                } else if (e.response?.status === 429) {
                    this.generalError = 'Demasiados intentos. Espera un momento e intenta de nuevo.';
                } else {
                    this.generalError = 'No se pudo restablecer la contraseña. Intenta de nuevo.';
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
    word-break: break-word;
}

.pw-field {
    text-align: left;
    margin-bottom: 1rem;
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

.pw-banner-err {
    background: #fef2f2;
    color: #ba2831;
    border: 1px solid #fecaca;
    border-radius: 10px;
    padding: 0.6rem 0.75rem;
    font-size: 0.82rem;
    margin: 0 0 0.75rem;
    text-align: left;
}

.pw-hint {
    font-size: 0.78rem;
    color: #94a3b8;
    text-align: left;
    margin: 0 0 1rem;
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
