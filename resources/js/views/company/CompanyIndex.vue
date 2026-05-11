<template>
    <div>
        <!-- Cabecera -->
        <div class="app-page-title">
            <div class="page-title-wrapper">
                <div class="page-title-heading">
                    <div class="page-title-icon">
                        <i class="fa fa-building icon-gradient bg-mean-fruit"></i>
                    </div>
                    <div>
                        Mis Empresas
                        <div class="page-title-subheading text-muted">
                            Gestiona tus empresas y sus tarjetas digitales
                        </div>
                    </div>
                </div>
                <div class="page-title-actions" v-if="canCreate">
                    <router-link :to="{ name: 'companies.create' }" class="btn-create">
                        <i class="fa fa-plus me-2"></i> Nueva Empresa
                    </router-link>
                </div>
            </div>
        </div>

        <!-- Estado de carga -->
        <div v-if="loading" class="loading-state">
            <div class="spinner-border text-primary"></div>
            <p class="text-muted mt-3">Cargando empresas...</p>
        </div>

        <!-- Sin empresas - Estado vacío -->
        <div v-else-if="companies.length === 0" class="empty-state">
            <div class="empty-state-content">
                <div class="empty-illustration">
                    <svg viewBox="0 0 200 160" class="empty-svg">
                        <!-- Building base -->
                        <rect x="50" y="60" width="100" height="80" rx="8" fill="#e2e8f0"/>
                        <rect x="60" y="75" width="25" height="20" rx="4" fill="#cbd5e1"/>
                        <rect x="115" y="75" width="25" height="20" rx="4" fill="#cbd5e1"/>
                        <rect x="60" y="105" width="25" height="20" rx="4" fill="#cbd5e1"/>
                        <rect x="115" y="105" width="25" height="20" rx="4" fill="#cbd5e1"/>
                        <rect x="87" y="100" width="26" height="40" rx="4" fill="#94a3b8"/>

                        <!-- Roof -->
                        <path d="M40 65 L100 25 L160 65 Z" fill="#8b5cf6"/>

                        <!-- Plus circle -->
                        <circle cx="160" cy="40" r="25" fill="url(#emptyGradient)"/>
                        <path d="M150 40 L170 40 M160 30 L160 50" stroke="white" stroke-width="4" stroke-linecap="round"/>

                        <!-- Floating elements -->
                        <circle cx="30" cy="50" r="6" fill="#fbbf24" opacity="0.8">
                            <animate attributeName="cy" values="50;40;50" dur="2s" repeatCount="indefinite"/>
                        </circle>
                        <circle cx="180" cy="100" r="4" fill="#10b981" opacity="0.8">
                            <animate attributeName="cy" values="100;110;100" dur="2.5s" repeatCount="indefinite"/>
                        </circle>

                        <defs>
                            <linearGradient id="emptyGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" style="stop-color:#8b5cf6"/>
                                <stop offset="100%" style="stop-color:#ec4899"/>
                            </linearGradient>
                        </defs>
                    </svg>
                </div>
                <h3 class="empty-title">Aun no tienes empresas</h3>
                <p class="empty-description">
                    Crea tu primera empresa para comenzar a generar tarjetas de presentacion digitales para tu equipo.
                </p>
                <router-link v-if="canCreate" :to="{ name: 'companies.create' }" class="btn-create">
                    <i class="fa fa-plus me-2"></i> Crear mi primera empresa
                </router-link>
            </div>
        </div>

        <!-- Lista de empresas -->
        <div v-else class="companies-grid">
            <div v-for="company in companies" :key="company.id" class="company-card">
                <!-- Header con nombre y enlace -->
                <div class="company-header">
                    <div class="company-info">
                        <h3 class="company-name">{{ company.name }}</h3>
                        <a :href="'/' + company.slug" target="_blank" class="company-slug">
                            <i class="fa fa-external-link-alt me-1"></i>/{{ company.slug }}
                        </a>
                    </div>
                    <img v-if="company.logo_path" :src="company.logo_path" class="company-logo" />
                </div>

                <!-- Estadísticas -->
                <div class="company-body">
                    <div class="company-stats">
                        <div class="stat-item">
                            <div class="stat-value stat-purple">{{ company.cards_count ?? 0 }}</div>
                            <div class="stat-label">Tarjetas</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value stat-green">{{ company.services_count ?? 0 }}</div>
                            <div class="stat-label">Servicios</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value stat-amber">{{ company.products_count ?? 0 }}</div>
                            <div class="stat-label">Productos</div>
                        </div>
                    </div>
                </div>

                <!-- Acciones -->
                <div class="company-actions">
                    <router-link :to="{ name: 'companies.show', params: { id: company.id } }"
                                 class="action-btn action-primary">
                        <i class="fa fa-eye me-1"></i> Gestionar
                    </router-link>
                    <router-link v-if="canEdit" :to="{ name: 'companies.edit', params: { id: company.id } }"
                                 class="action-btn action-secondary">
                        <i class="fa fa-edit"></i>
                    </router-link>
                    <button v-if="canDelete" @click="confirmDelete(company)" class="action-btn action-danger">
                        <i class="fa fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal confirmación de borrado -->
        <div v-if="toDelete" class="modal-overlay" @click.self="toDelete = null">
            <div class="modal-container">
                <div class="modal-icon-wrapper">
                    <div class="modal-icon">
                        <i class="fa fa-exclamation-triangle"></i>
                    </div>
                </div>
                <h4 class="modal-title">Eliminar empresa</h4>
                <p class="modal-message">
                    ¿Estas seguro de eliminar <strong>{{ toDelete.name }}</strong>?
                    Se eliminaran tambien todas sus tarjetas, servicios y productos.
                </p>
                <div class="modal-actions">
                    <button class="modal-btn modal-btn-cancel" @click="toDelete = null">
                        Cancelar
                    </button>
                    <button class="modal-btn modal-btn-danger" @click="deleteCompany" :disabled="deleting">
                        <span v-if="deleting" class="spinner-border spinner-border-sm me-2"></span>
                        {{ deleting ? 'Eliminando...' : 'Eliminar' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import companyService from '@/services/companyService.js';
import { useAuth } from '@/stores/auth';

export default {
    name: 'CompanyIndex',

    data() {
        return {
            companies: [],
            loading: true,
            toDelete: null,
            deleting: false,
        };
    },

    computed: {
        canCreate() {
            const auth = useAuth();
            return auth.can('create_company');
        },
        canEdit() {
            const auth = useAuth();
            return auth.can('edit_company');
        },
        canDelete() {
            const auth = useAuth();
            return auth.can('delete_company');
        },
    },

    async created() {
        await this.load();
    },

    methods: {
        async load() {
            this.loading = true;
            try {
                const { data } = await companyService.all();
                this.companies = data;
            } finally {
                this.loading = false;
            }
        },

        confirmDelete(company) {
            this.toDelete = company;
        },

        async deleteCompany() {
            this.deleting = true;
            try {
                await companyService.destroy(this.toDelete.id);
                this.companies = this.companies.filter(c => c.id !== this.toDelete.id);
                this.toDelete = null;
            } finally {
                this.deleting = false;
            }
        },
    },
};
</script>

<style scoped>
/* Botón crear */
.btn-create {
    display: inline-flex;
    align-items: center;
    padding: 0.625rem 1.25rem;
    background: #7c3aed;
    color: white;
    font-weight: 600;
    border-radius: 10px;
    text-decoration: none;
    transition: all 0.2s ease;
    box-shadow: 0 2px 8px rgba(124, 58, 237, 0.25);
    border: none;
    cursor: pointer;
}

.btn-create:hover {
    background: #6d28d9;
    box-shadow: 0 4px 12px rgba(124, 58, 237, 0.35);
    color: white;
}

/* Estado de carga */
.loading-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 4rem 2rem;
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border-radius: 20px;
}

/* Estado vacío */
.empty-state {
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border-radius: 20px;
    padding: 4rem 2rem;
}

.empty-state-content {
    max-width: 400px;
    margin: 0 auto;
    text-align: center;
}

.empty-illustration {
    margin-bottom: 2rem;
}

.empty-svg {
    width: 200px;
    height: 160px;
}

.empty-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 0.75rem;
}

.empty-description {
    color: #64748b;
    line-height: 1.6;
    margin-bottom: 1.5rem;
}

/* Grid de empresas */
.companies-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
}

/* Card de empresa */
.company-card {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08), 0 4px 20px rgba(0, 0, 0, 0.06);
    border: 1px solid #e2e8f0;
    transition: all 0.3s ease;
}

.company-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12), 0 4px 10px rgba(0, 0, 0, 0.08);
}

/* Header de empresa */
.company-header {
    padding: 1rem 1.25rem;
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
}

.company-info {
    flex: 1;
    min-width: 0;
}

.company-name {
    font-size: 1.1rem;
    font-weight: 700;
    color: #1e293b;
    margin: 0 0 0.25rem 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.company-slug {
    font-size: 0.8rem;
    color: #64748b;
    text-decoration: none;
    transition: color 0.2s ease;
    display: inline-flex;
    align-items: center;
}

.company-slug:hover {
    color: #8b5cf6;
}

.company-logo {
    width: 48px;
    height: 48px;
    object-fit: contain;
    border-radius: 8px;
    flex-shrink: 0;
}

/* Body de empresa */
.company-body {
    padding: 1.25rem;
}

/* Estadísticas */
.company-stats {
    display: flex;
    gap: 0.75rem;
}

.stat-item {
    flex: 1;
    text-align: center;
    padding: 0.75rem 0.5rem;
    background: #f8fafc;
    border-radius: 10px;
    border: 1px solid #e2e8f0;
}

.stat-value {
    font-size: 1.35rem;
    font-weight: 700;
}

.stat-purple { color: #7c3aed; }
.stat-green { color: #059669; }
.stat-amber { color: #d97706; }

.stat-label {
    font-size: 0.75rem;
    color: #64748b;
    margin-top: 0.2rem;
}

/* Acciones */
.company-actions {
    display: flex;
    gap: 0.5rem;
    padding: 1rem 1.25rem;
    background: white;
    border-top: 1px solid #e2e8f0;
}

.action-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    font-size: 0.85rem;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.2s ease;
    border: none;
    cursor: pointer;
}

.action-primary {
    flex: 1;
    background: #7c3aed;
    color: white;
}

.action-primary:hover {
    background: #6d28d9;
    color: white;
}

.action-secondary {
    background: #f1f5f9;
    color: #475569;
    border: 1px solid #e2e8f0;
}

.action-secondary:hover {
    background: #e2e8f0;
    color: #334155;
}

.action-danger {
    background: #fef2f2;
    color: #dc2626;
    border: 1px solid #fecaca;
}

.action-danger:hover {
    background: #fee2e2;
    color: #b91c1c;
}

/* Modal */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(15, 23, 42, 0.6);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    padding: 1rem;
}

.modal-container {
    background: white;
    border-radius: 20px;
    padding: 2rem;
    max-width: 400px;
    width: 100%;
    text-align: center;
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
}

.modal-icon-wrapper {
    margin-bottom: 1.5rem;
}

.modal-icon {
    width: 64px;
    height: 64px;
    background: linear-gradient(135deg, #fef2f2, #fee2e2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    color: #ef4444;
    font-size: 1.5rem;
}

.modal-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 0.75rem;
}

.modal-message {
    color: #64748b;
    line-height: 1.6;
    margin-bottom: 1.5rem;
}

.modal-actions {
    display: flex;
    gap: 0.75rem;
}

.modal-btn {
    flex: 1;
    padding: 0.75rem 1.5rem;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.95rem;
    border: none;
    cursor: pointer;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.modal-btn-cancel {
    background: #f1f5f9;
    color: #64748b;
}

.modal-btn-cancel:hover {
    background: #e2e8f0;
}

.modal-btn-danger {
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: white;
}

.modal-btn-danger:hover:not(:disabled) {
    background: linear-gradient(135deg, #dc2626, #b91c1c);
}

.modal-btn-danger:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

/* Responsive */
@media (max-width: 576px) {
    .companies-grid {
        grid-template-columns: 1fr;
    }

    .company-actions {
        flex-wrap: wrap;
    }

    .action-primary {
        flex: 1 1 100%;
    }

    .action-secondary,
    .action-danger {
        flex: 1;
    }
}
</style>
