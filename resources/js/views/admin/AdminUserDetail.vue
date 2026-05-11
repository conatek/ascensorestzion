<template>
    <div>
        <div v-if="loading" class="loading-state"><div class="spinner-border text-primary"></div></div>

        <template v-else-if="user">
            <div class="app-page-title">
                <div class="page-title-wrapper">
                    <div class="page-title-heading">
                        <div class="page-title-icon">
                            <i class="fa fa-user icon-gradient bg-mean-fruit"></i>
                        </div>
                        <div>
                            {{ user.name }}
                            <div class="page-title-subheading text-muted">{{ user.email }}</div>
                        </div>
                    </div>
                    <div class="page-title-actions">
                        <router-link :to="{ name: 'admin.users' }" class="btn-action btn-back">
                            <i class="fa fa-arrow-left me-1"></i> Volver
                        </router-link>
                    </div>
                </div>
            </div>

            <div v-if="successMsg" class="success-alert">
                <i class="fa fa-check-circle"></i> {{ successMsg }}
            </div>

            <div class="detail-grid">
                <!-- Info usuario -->
                <div class="section-card">
                    <div class="section-header">
                        <i class="fa fa-info-circle section-icon icon-purple"></i>
                        <span>Informacion del usuario</span>
                    </div>
                    <div class="section-body">
                        <div class="detail-row">
                            <span>Nombre</span>
                            <span class="fw-600">{{ user.name }}</span>
                        </div>
                        <div class="detail-row">
                            <span>Email</span>
                            <span>{{ user.email }}</span>
                        </div>
                        <div class="detail-row">
                            <span>Registro</span>
                            <span>{{ formatDate(user.created_at) }}</span>
                        </div>
                        <div class="detail-row">
                            <span>Empresa</span>
                            <span class="fw-600">{{ user.company?.name || 'Sin empresa' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Rol -->
                <div class="section-card">
                    <div class="section-header">
                        <i class="fa fa-user-shield section-icon icon-purple"></i>
                        <span>Rol</span>
                    </div>
                    <div class="section-body">
                        <div class="form-group">
                            <label class="form-label">Rol actual</label>
                            <div class="current-value">
                                <span class="role-badge" :class="'role-' + currentRole.toLowerCase()">{{ currentRole }}</span>
                            </div>
                        </div>

                        <div v-if="currentRole === 'Guest'" class="form-group">
                            <label class="form-label">Promover a Admin</label>
                            <div class="inline-action">
                                <span class="promote-label">Cambiar rol de Guest a Admin</span>
                                <button @click="promoteToAdmin" class="btn-sm btn-primary" :disabled="saving">
                                    {{ saving ? 'Cambiando...' : 'Promover' }}
                                </button>
                            </div>
                        </div>

                        <div v-if="user.company" class="mt-1">
                            <router-link :to="{ name: 'companies.show', params: { id: user.company_id }, query: { from: 'admin' } }" class="link-company">
                                <i class="fa fa-external-link-alt me-1"></i> Ver empresa
                            </router-link>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</template>

<script>
import adminService from '@/services/adminService.js';

export default {
    name: 'AdminUserDetail',

    data() {
        return {
            user: null,
            loading: true,
            saving: false,
            successMsg: null,
        };
    },

    computed: {
        currentRole() {
            return this.user?.roles?.[0]?.name || 'Sin rol';
        },
    },

    async created() {
        await this.load();
    },

    methods: {
        async load() {
            this.loading = true;
            try {
                const { data } = await adminService.getUser(this.$route.params.id);
                this.user = data;
            } finally { this.loading = false; }
        },

        formatDate(d) {
            if (!d) return '-';
            return new Date(d).toLocaleDateString('es-CO', { year: 'numeric', month: 'short', day: 'numeric' });
        },

        async promoteToAdmin() {
            this.saving = true;
            this.successMsg = null;
            try {
                const { data } = await adminService.updateUser(this.user.id, { role: 'Admin' });
                this.user = data;
                this.successMsg = 'Usuario promovido a Admin correctamente.';
            } finally { this.saving = false; }
        },
    },
};
</script>

<style scoped>
.detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; align-items: start; }
.section-card { background: white; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 2px 8px rgba(0,0,0,0.04); }
.section-header { padding: 1rem 1.25rem; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; gap: 0.75rem; font-weight: 600; color: #1e293b; border-radius: 12px 12px 0 0; }
.section-icon { width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 8px; }
.icon-purple { background: #f3e8ff; color: #7c3aed; }
.section-body { padding: 1.25rem; }

.detail-row { display: flex; justify-content: space-between; padding: 0.6rem 0; border-bottom: 1px solid #f1f5f9; font-size: 0.9rem; }
.detail-row span:first-child { color: #64748b; }
.fw-600 { font-weight: 600; color: #1e293b; }

.form-group { margin-bottom: 1.25rem; }
.form-label { display: block; font-size: 0.8rem; font-weight: 600; color: #64748b; margin-bottom: 0.35rem; text-transform: uppercase; letter-spacing: 0.04em; }
.form-input { width: 100%; padding: 0.5rem 0.75rem; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 0.9rem; }
.form-input:focus { outline: none; border-color: #7c3aed; }

.current-value { padding: 0.25rem 0; }

.role-badge { display: inline-block; padding: 0.2rem 0.6rem; border-radius: 6px; font-size: 0.8rem; font-weight: 600; }
.role-master { background: linear-gradient(135deg, #ede9fe, #f3e8ff); color: #7c3aed; }
.role-admin { background: linear-gradient(135deg, #dbeafe, #e0f2fe); color: #2563eb; }
.role-guest { background: #f1f5f9; color: #64748b; }

.inline-action { display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; }
.promote-label { font-size: 0.9rem; color: #334155; }

.btn-sm { padding: 0.5rem 0.75rem; border-radius: 6px; font-size: 0.85rem; font-weight: 600; cursor: pointer; border: none; white-space: nowrap; }
.btn-primary { background: #7c3aed; color: white; }
.btn-primary:hover:not(:disabled) { background: #6d28d9; }
.btn-sm:disabled { opacity: 0.6; cursor: not-allowed; }

.link-company { font-size: 0.85rem; color: #7c3aed; text-decoration: none; }
.link-company:hover { text-decoration: underline; }
.mt-1 { margin-top: 0.5rem; }

.success-alert { display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; background: #d1fae5; border: 1px solid #a7f3d0; border-radius: 8px; color: #059669; font-size: 0.9rem; margin-bottom: 1.25rem; }

.btn-action.btn-back { padding: 0.5rem 1rem; background: #f1f5f9; color: #475569; border-radius: 8px; text-decoration: none; font-size: 0.9rem; font-weight: 500; border: 1px solid #e2e8f0; }
.loading-state { text-align: center; padding: 4rem 0; }

@media (max-width: 768px) { .detail-grid { grid-template-columns: 1fr; } }
</style>
