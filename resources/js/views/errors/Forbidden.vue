<template>
    <div class="forbidden-container">
        <div class="forbidden-card">
            <div class="forbidden-icon">
                <svg viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="40" cy="40" r="36" stroke="url(#forbiddenGradient)" stroke-width="3" fill="none" opacity="0.2"/>
                    <circle cx="40" cy="40" r="28" fill="url(#forbiddenGradient)" opacity="0.1"/>
                    <path d="M40 24V44" stroke="url(#forbiddenGradient)" stroke-width="3" stroke-linecap="round"/>
                    <circle cx="40" cy="52" r="2.5" fill="url(#forbiddenGradient)"/>
                    <defs>
                        <linearGradient id="forbiddenGradient" x1="0" y1="0" x2="80" y2="80">
                            <stop stop-color="#8b5cf6"/>
                            <stop offset="1" stop-color="#ec4899"/>
                        </linearGradient>
                    </defs>
                </svg>
            </div>

            <h2 class="forbidden-title">Acceso denegado</h2>
            <p class="forbidden-message">No tienes permisos para acceder a esta seccion.</p>

            <p v-if="isGuest" class="forbidden-upgrade">
                Mejora tu plan para desbloquear mas funciones.
            </p>

            <div class="forbidden-actions">
                <router-link :to="{ name: 'home' }" class="btn-back">
                    <i class="fa fa-arrow-left me-2"></i> Volver al panel
                </router-link>
            </div>
        </div>
    </div>
</template>

<script>
import { useAuth } from '@/stores/auth';

export default {
    name: 'Forbidden',
    computed: {
        isGuest() {
            const auth = useAuth();
            return auth.hasRole('Guest');
        },
    },
};
</script>

<style scoped>
.forbidden-container {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 60vh;
    padding: 2rem;
}

.forbidden-card {
    text-align: center;
    max-width: 420px;
}

.forbidden-icon {
    width: 80px;
    height: 80px;
    margin: 0 auto 1.5rem;
}

.forbidden-title {
    font-size: 1.75rem;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 0.75rem;
}

.forbidden-message {
    font-size: 1rem;
    color: #64748b;
    line-height: 1.6;
    margin-bottom: 0.5rem;
}

.forbidden-upgrade {
    font-size: 0.95rem;
    color: #8b5cf6;
    font-weight: 500;
    margin-bottom: 1.5rem;
}

.forbidden-actions {
    margin-top: 1.5rem;
}

.btn-back {
    display: inline-flex;
    align-items: center;
    padding: 0.75rem 1.5rem;
    background: linear-gradient(135deg, #8b5cf6, #ec4899);
    color: white;
    font-weight: 600;
    border-radius: 10px;
    text-decoration: none;
    transition: all 0.2s ease;
    box-shadow: 0 4px 12px rgba(139, 92, 246, 0.3);
}

.btn-back:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(139, 92, 246, 0.4);
    color: white;
}
</style>
