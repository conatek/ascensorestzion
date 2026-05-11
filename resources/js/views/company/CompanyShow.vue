<template>
    <div>
        <!-- Estado de carga -->
        <div v-if="loading" class="loading-state">
            <div class="spinner-border text-primary"></div>
            <p class="text-muted mt-3">Cargando empresa...</p>
        </div>

        <template v-else>
            <!-- Cabecera de empresa -->
            <div class="app-page-title">
                <div class="page-title-wrapper">
                    <div class="page-title-heading">
                        <div class="page-title-icon">
                            <img v-if="company.logo_path"
                                 :src="company.logo_path"
                                 class="company-logo-header" />
                            <i v-else class="fa fa-building icon-gradient bg-mean-fruit"></i>
                        </div>
                        <div>
                            {{ company.name }}
                            <div class="page-title-subheading">
                                <a :href="'/' + company.slug" target="_blank" class="slug-link">
                                    <i class="fa fa-external-link-alt me-1"></i>/{{ company.slug }}
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="page-title-actions d-flex gap-2">
                        <router-link v-if="isMaster" :to="backRoute" class="btn-action btn-back">
                            <i class="fa fa-arrow-left me-1"></i> Volver
                        </router-link>
                        <router-link v-if="canEditSettings" :to="{ name: 'settings.editor', params: { companyId: company.id }, query: fromAdmin ? { from: 'admin' } : {} }"
                                     class="btn-action btn-template">
                            <i class="fa fa-palette me-1"></i> Plantilla
                        </router-link>
                        <router-link v-if="canEditCompany" :to="{ name: 'companies.edit', params: { id: company.id }, query: fromAdmin ? { from: 'admin' } : {} }"
                                     class="btn-action btn-edit">
                            <i class="fa fa-edit me-1"></i> Editar
                        </router-link>
                    </div>
                </div>
            </div>

            <!-- Resumen de estadísticas -->
            <div class="stats-grid">
                <div class="stat-card stat-purple">
                    <div class="stat-icon">
                        <i class="fa fa-id-card"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number">{{ company.cards?.length ?? 0 }}</div>
                        <div class="stat-label">Tarjetas</div>
                    </div>
                </div>
                <div class="stat-card stat-green">
                    <div class="stat-icon">
                        <i class="fa fa-concierge-bell"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number">{{ company.services?.length ?? 0 }}</div>
                        <div class="stat-label">Servicios</div>
                    </div>
                </div>
                <div class="stat-card stat-amber">
                    <div class="stat-icon">
                        <i class="fa fa-box"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number">{{ company.products?.length ?? 0 }}</div>
                        <div class="stat-label">Productos</div>
                    </div>
                </div>
            </div>

            <!-- Información de contacto y redes -->
            <div class="info-grid" v-if="hasContactInfo || hasSocialLinks">
                <div class="info-card" v-if="hasContactInfo">
                    <div class="info-header">
                        <i class="fa fa-address-card"></i>
                        <span>Contacto</span>
                    </div>
                    <div class="info-body">
                        <div v-if="company.address" class="info-item">
                            <i class="fa fa-map-marker-alt"></i>
                            <span>{{ company.address }}</span>
                        </div>
                        <div v-if="company.web" class="info-item">
                            <i class="fa fa-globe"></i>
                            <a :href="company.web" target="_blank">{{ company.web }}</a>
                        </div>
                        <div v-if="company.my_business" class="info-item">
                            <i class="fa fa-store"></i>
                            <a :href="company.my_business" target="_blank">Google My Business</a>
                        </div>
                    </div>
                </div>

                <div class="info-card" v-if="hasSocialLinks">
                    <div class="info-header">
                        <i class="fa fa-share-alt"></i>
                        <span>Redes Sociales</span>
                    </div>
                    <div class="info-body">
                        <div class="social-links">
                            <a v-if="company.facebook" :href="company.facebook" target="_blank" class="social-btn social-facebook">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a v-if="company.instagram" :href="company.instagram" target="_blank" class="social-btn social-instagram">
                                <i class="fab fa-instagram"></i>
                            </a>
                            <a v-if="company.twitter" :href="company.twitter" target="_blank" class="social-btn social-twitter">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a v-if="company.youtube" :href="company.youtube" target="_blank" class="social-btn social-youtube">
                                <i class="fab fa-youtube"></i>
                            </a>
                            <a v-if="company.tiktok" :href="company.tiktok" target="_blank" class="social-btn social-tiktok">
                                <i class="fab fa-tiktok"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tarjetas de empleados -->
            <div class="section-card">
                <div class="section-header">
                    <div class="section-title">
                        <i class="fa fa-id-card section-icon icon-purple"></i>
                        <span>Tarjetas de presentacion</span>
                    </div>
                    <router-link v-if="canCreateCard" :to="{ name: 'cards.create', params: { companyId: company.id } }"
                                 class="btn-add btn-add-purple">
                        <i class="fa fa-plus me-1"></i> Nueva tarjeta
                    </router-link>
                </div>
                <div class="section-body">
                    <div v-if="!company.cards?.length" class="empty-section">
                        <div class="empty-icon">
                            <i class="fa fa-id-card"></i>
                        </div>
                        <p>Sin tarjetas. Crea la primera.</p>
                    </div>

                    <div class="items-list" v-else>
                        <div v-for="card in company.cards" :key="card.id" class="list-item">
                            <div class="item-avatar">
                                <img v-if="card.photo_path" :src="card.photo_path" />
                                <i v-else class="fa fa-user"></i>
                            </div>
                            <div class="item-info">
                                <div class="item-name">{{ card.first_name }} {{ card.last_name }}</div>
                                <div class="item-meta">
                                    <span v-if="card.job_title">{{ card.job_title }}</span>
                                    <span v-if="card.job_title && card.email" class="meta-separator">·</span>
                                    <span v-if="card.email">{{ card.email }}</span>
                                </div>
                            </div>
                            <div class="item-url">
                                <a :href="'/' + company.slug + '/' + card.slug" target="_blank">
                                    /{{ card.slug }}
                                </a>
                            </div>
                            <div class="item-status">
                                <span class="status-badge" :class="card.is_active ? 'status-active' : 'status-inactive'">
                                    {{ card.is_active ? 'Activa' : 'Inactiva' }}
                                </span>
                            </div>
                            <div class="item-actions">
                                <router-link v-if="canEditCard" :to="{ name: 'cards.edit', params: { companyId: company.id, cardId: card.id } }"
                                             class="action-btn">
                                    <i class="fa fa-edit"></i>
                                </router-link>
                                <button v-if="canDeleteCard" @click="confirmDeleteCard(card)" class="action-btn action-delete">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Servicios -->
            <div class="section-card">
                <div class="section-header">
                    <div class="section-title">
                        <i class="fa fa-concierge-bell section-icon icon-green"></i>
                        <span>Servicios</span>
                    </div>
                    <router-link v-if="canCreateService" :to="{ name: 'services.create', params: { companyId: company.id } }"
                                 class="btn-add btn-add-green">
                        <i class="fa fa-plus me-1"></i> Nuevo servicio
                    </router-link>
                </div>
                <div class="section-body">
                    <div v-if="!company.services?.length" class="empty-section">
                        <div class="empty-icon">
                            <i class="fa fa-concierge-bell"></i>
                        </div>
                        <p>Sin servicios. Crea el primero.</p>
                    </div>

                    <div class="items-list" v-else>
                        <div v-for="service in company.services" :key="service.id" class="list-item">
                            <div class="item-image">
                                <img v-if="service.image_path" :src="service.image_path" />
                                <i v-else class="fa fa-image"></i>
                            </div>
                            <div class="item-info">
                                <div class="item-name">{{ service.name }}</div>
                                <div class="item-meta" v-if="service.description">
                                    {{ service.description.substring(0, 60) }}{{ service.description.length > 60 ? '...' : '' }}
                                </div>
                            </div>
                            <div class="item-order">
                                <span class="order-badge">{{ service.order ?? 0 }}</span>
                            </div>
                            <div class="item-status">
                                <span class="status-badge" :class="service.is_active ? 'status-active' : 'status-inactive'">
                                    {{ service.is_active ? 'Activo' : 'Inactivo' }}
                                </span>
                            </div>
                            <div class="item-actions">
                                <router-link v-if="canEditService" :to="{ name: 'services.edit', params: { companyId: company.id, serviceId: service.id } }"
                                             class="action-btn">
                                    <i class="fa fa-edit"></i>
                                </router-link>
                                <button v-if="canDeleteService" @click="confirmDeleteService(service)" class="action-btn action-delete">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Productos -->
            <div class="section-card">
                <div class="section-header">
                    <div class="section-title">
                        <i class="fa fa-box section-icon icon-amber"></i>
                        <span>Productos</span>
                    </div>
                    <router-link v-if="canCreateProduct" :to="{ name: 'products.create', params: { companyId: company.id } }"
                                 class="btn-add btn-add-amber">
                        <i class="fa fa-plus me-1"></i> Nuevo producto
                    </router-link>
                </div>
                <div class="section-body">
                    <div v-if="!company.products?.length" class="empty-section">
                        <div class="empty-icon">
                            <i class="fa fa-box"></i>
                        </div>
                        <p>Sin productos. Crea el primero.</p>
                    </div>

                    <div class="items-list" v-else>
                        <div v-for="product in company.products" :key="product.id" class="list-item">
                            <div class="item-image">
                                <img v-if="product.image_path" :src="product.image_path" />
                                <i v-else class="fa fa-image"></i>
                            </div>
                            <div class="item-info">
                                <div class="item-name">{{ product.name }}</div>
                                <div class="item-meta" v-if="product.comment">{{ product.comment }}</div>
                            </div>
                            <div class="item-price">
                                <span class="price-value">${{ formatPrice(product.price) }}</span>
                                <span v-if="product.discount" class="discount-badge">-{{ product.discount }}%</span>
                            </div>
                            <div class="item-order">
                                <span class="order-badge">{{ product.order ?? 0 }}</span>
                            </div>
                            <div class="item-status">
                                <span class="status-badge" :class="product.is_active ? 'status-active' : 'status-inactive'">
                                    {{ product.is_active ? 'Activo' : 'Inactivo' }}
                                </span>
                            </div>
                            <div class="item-actions">
                                <router-link v-if="canEditProduct" :to="{ name: 'products.edit', params: { companyId: company.id, productId: product.id } }"
                                             class="action-btn">
                                    <i class="fa fa-edit"></i>
                                </router-link>
                                <button v-if="canDeleteProduct" @click="confirmDeleteProduct(product)" class="action-btn action-delete">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <!-- Modal eliminar tarjeta -->
        <div v-if="cardToDelete" class="modal-overlay" @click.self="cardToDelete = null">
            <div class="modal-container">
                <div class="modal-icon-wrapper">
                    <div class="modal-icon">
                        <i class="fa fa-exclamation-triangle"></i>
                    </div>
                </div>
                <h4 class="modal-title">Eliminar tarjeta</h4>
                <p class="modal-message">
                    ¿Eliminar la tarjeta de <strong>{{ cardToDelete.first_name }} {{ cardToDelete.last_name }}</strong>?
                </p>
                <div class="modal-actions">
                    <button class="modal-btn modal-btn-cancel" @click="cardToDelete = null">Cancelar</button>
                    <button class="modal-btn modal-btn-danger" @click="deleteCard" :disabled="deleting">
                        <span v-if="deleting" class="spinner-border spinner-border-sm me-2"></span>
                        {{ deleting ? 'Eliminando...' : 'Eliminar' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal eliminar servicio -->
        <div v-if="serviceToDelete" class="modal-overlay" @click.self="serviceToDelete = null">
            <div class="modal-container">
                <div class="modal-icon-wrapper">
                    <div class="modal-icon">
                        <i class="fa fa-exclamation-triangle"></i>
                    </div>
                </div>
                <h4 class="modal-title">Eliminar servicio</h4>
                <p class="modal-message">
                    ¿Eliminar el servicio <strong>{{ serviceToDelete.name }}</strong>?
                </p>
                <div class="modal-actions">
                    <button class="modal-btn modal-btn-cancel" @click="serviceToDelete = null">Cancelar</button>
                    <button class="modal-btn modal-btn-danger" @click="deleteService" :disabled="deleting">
                        <span v-if="deleting" class="spinner-border spinner-border-sm me-2"></span>
                        {{ deleting ? 'Eliminando...' : 'Eliminar' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal eliminar producto -->
        <div v-if="productToDelete" class="modal-overlay" @click.self="productToDelete = null">
            <div class="modal-container">
                <div class="modal-icon-wrapper">
                    <div class="modal-icon">
                        <i class="fa fa-exclamation-triangle"></i>
                    </div>
                </div>
                <h4 class="modal-title">Eliminar producto</h4>
                <p class="modal-message">
                    ¿Eliminar el producto <strong>{{ productToDelete.name }}</strong>?
                </p>
                <div class="modal-actions">
                    <button class="modal-btn modal-btn-cancel" @click="productToDelete = null">Cancelar</button>
                    <button class="modal-btn modal-btn-danger" @click="deleteProduct" :disabled="deleting">
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
import cardService    from '@/services/cardService.js';
import serviceService from '@/services/serviceService.js';
import productService from '@/services/productService.js';
import { useAuth } from '@/stores/auth';

export default {
    name: 'CompanyShow',

    data() {
        return {
            company: {},
            loading: true,
            cardToDelete: null,
            serviceToDelete: null,
            productToDelete: null,
            deleting: false,
        };
    },

    computed: {
        hasContactInfo() {
            return this.company.address || this.company.web || this.company.my_business;
        },
        hasSocialLinks() {
            return this.company.facebook || this.company.instagram ||
                   this.company.twitter || this.company.youtube || this.company.tiktok;
        },
        isMaster() {
            const auth = useAuth();
            return auth.isMaster();
        },
        fromAdmin() {
            return this.$route.query.from === 'admin';
        },
        backRoute() {
            if (this.fromAdmin) {
                return { name: 'admin.companies' };
            }
            return { name: 'companies.index' };
        },
        canEditCompany() {
            const auth = useAuth();
            return auth.can('edit_company');
        },
        canEditSettings() {
            const auth = useAuth();
            return auth.can('edit_settings');
        },
        canCreateCard() {
            const auth = useAuth();
            return auth.can('create_card');
        },
        canEditCard() {
            const auth = useAuth();
            return auth.can('edit_card');
        },
        canDeleteCard() {
            const auth = useAuth();
            return auth.can('delete_card');
        },
        canCreateService() {
            const auth = useAuth();
            return auth.can('create_service');
        },
        canEditService() {
            const auth = useAuth();
            return auth.can('edit_service');
        },
        canDeleteService() {
            const auth = useAuth();
            return auth.can('delete_service');
        },
        canCreateProduct() {
            const auth = useAuth();
            return auth.can('create_product');
        },
        canEditProduct() {
            const auth = useAuth();
            return auth.can('edit_product');
        },
        canDeleteProduct() {
            const auth = useAuth();
            return auth.can('delete_product');
        },
    },

    async created() {
        await this.load();
    },

    methods: {
        async load() {
            this.loading = true;
            try {
                const { data } = await companyService.get(this.$route.params.id);
                this.company = data;
            } finally {
                this.loading = false;
            }
        },

        formatPrice(price) {
            return parseFloat(price).toFixed(2);
        },

        confirmDeleteCard(card) {
            this.cardToDelete = card;
        },

        async deleteCard() {
            this.deleting = true;
            try {
                await cardService.destroy(this.company.id, this.cardToDelete.id);
                this.company.cards = this.company.cards.filter(c => c.id !== this.cardToDelete.id);
                this.cardToDelete = null;
            } finally {
                this.deleting = false;
            }
        },

        confirmDeleteService(service) {
            this.serviceToDelete = service;
        },

        async deleteService() {
            this.deleting = true;
            try {
                await serviceService.destroy(this.company.id, this.serviceToDelete.id);
                this.company.services = this.company.services.filter(s => s.id !== this.serviceToDelete.id);
                this.serviceToDelete = null;
            } finally {
                this.deleting = false;
            }
        },

        confirmDeleteProduct(product) {
            this.productToDelete = product;
        },

        async deleteProduct() {
            this.deleting = true;
            try {
                await productService.destroy(this.company.id, this.productToDelete.id);
                this.company.products = this.company.products.filter(p => p.id !== this.productToDelete.id);
                this.productToDelete = null;
            } finally {
                this.deleting = false;
            }
        },
    },
};
</script>

<style scoped>
/* Loading */
.loading-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 4rem 2rem;
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border-radius: 16px;
}

/* Header */
.company-logo-header {
    height: 42px;
    width: auto;
    max-width: 120px;
    object-fit: contain;
    border-radius: 6px;
}

.slug-link {
    color: #64748b;
    text-decoration: none;
    font-size: 0.9rem;
    transition: color 0.2s;
}

.slug-link:hover {
    color: #7c3aed;
}

/* Action buttons */
.btn-action {
    display: inline-flex;
    align-items: center;
    padding: 0.5rem 1rem;
    font-size: 0.85rem;
    font-weight: 500;
    border-radius: 8px;
    text-decoration: none;
    transition: all 0.2s;
}

.btn-back {
    background: #f1f5f9;
    color: #475569;
    border: 1px solid #e2e8f0;
}

.btn-back:hover {
    background: #e2e8f0;
    color: #334155;
}

.btn-template {
    background: #7c3aed;
    color: white;
}

.btn-template:hover {
    background: #6d28d9;
    color: white;
}

.btn-edit {
    background: #f1f5f9;
    color: #475569;
    border: 1px solid #e2e8f0;
}

.btn-edit:hover {
    background: #e2e8f0;
    color: #334155;
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.stat-card {
    background: white;
    border-radius: 12px;
    padding: 1.25rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    border: 1px solid #e2e8f0;
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
}

.stat-purple .stat-icon { background: #f3e8ff; color: #7c3aed; }
.stat-green .stat-icon { background: #d1fae5; color: #059669; }
.stat-amber .stat-icon { background: #fef3c7; color: #d97706; }

.stat-number {
    font-size: 1.75rem;
    font-weight: 700;
    color: #1e293b;
    line-height: 1;
}

.stat-label {
    font-size: 0.85rem;
    color: #64748b;
    margin-top: 0.25rem;
}

/* Info Grid */
.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.info-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    border: 1px solid #e2e8f0;
    overflow: hidden;
}

.info-header {
    padding: 0.875rem 1.25rem;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 600;
    color: #475569;
}

.info-header i {
    color: #7c3aed;
}

.info-body {
    padding: 1rem 1.25rem;
}

.info-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.5rem 0;
    color: #475569;
}

.info-item i {
    color: #94a3b8;
    width: 16px;
    text-align: center;
}

.info-item a {
    color: #7c3aed;
    text-decoration: none;
}

.info-item a:hover {
    text-decoration: underline;
}

/* Social Links */
.social-links {
    display: flex;
    gap: 0.75rem;
}

.social-btn {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    text-decoration: none;
    transition: transform 0.2s, opacity 0.2s;
}

.social-btn:hover {
    transform: scale(1.1);
    opacity: 0.9;
}

.social-facebook { background: #1877f2; }
.social-instagram { background: linear-gradient(45deg, #f09433, #e6683c, #dc2743, #cc2366, #bc1888); }
.social-twitter { background: #1da1f2; }
.social-youtube { background: #ff0000; }
.social-tiktok { background: #000000; }

/* Section Cards */
.section-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    border: 1px solid #e2e8f0;
    margin-bottom: 1.5rem;
    overflow: hidden;
}

.section-header {
    padding: 1rem 1.25rem;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.section-title {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-weight: 600;
    color: #1e293b;
}

.section-icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.9rem;
}

.icon-purple { background: #f3e8ff; color: #7c3aed; }
.icon-green { background: #d1fae5; color: #059669; }
.icon-amber { background: #fef3c7; color: #d97706; }

.btn-add {
    display: inline-flex;
    align-items: center;
    padding: 0.5rem 1rem;
    font-size: 0.85rem;
    font-weight: 500;
    border-radius: 8px;
    text-decoration: none;
    color: white;
    transition: all 0.2s;
}

.btn-add-purple { background: #7c3aed; }
.btn-add-purple:hover { background: #6d28d9; color: white; }
.btn-add-green { background: #059669; }
.btn-add-green:hover { background: #047857; color: white; }
.btn-add-amber { background: #d97706; }
.btn-add-amber:hover { background: #b45309; color: white; }

.section-body {
    padding: 0;
}

/* Empty Section */
.empty-section {
    padding: 3rem 2rem;
    text-align: center;
    color: #94a3b8;
}

.empty-icon {
    width: 56px;
    height: 56px;
    background: #f1f5f9;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
    font-size: 1.5rem;
    color: #cbd5e1;
}

.empty-section p {
    margin: 0;
    font-size: 0.95rem;
}

/* Items List */
.items-list {
    padding: 0.5rem 0;
}

.list-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.875rem 1.25rem;
    border-bottom: 1px solid #f1f5f9;
    transition: background 0.2s;
}

.list-item:last-child {
    border-bottom: none;
}

.list-item:hover {
    background: #fafafa;
}

.item-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    flex-shrink: 0;
}

.item-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.item-avatar i {
    color: #94a3b8;
}

.item-image {
    width: 48px;
    height: 48px;
    border-radius: 8px;
    background: #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    flex-shrink: 0;
}

.item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.item-image i {
    color: #94a3b8;
}

.item-info {
    flex: 1;
    min-width: 0;
}

.item-name {
    font-weight: 600;
    color: #1e293b;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.item-meta {
    font-size: 0.85rem;
    color: #64748b;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.meta-separator {
    margin: 0 0.35rem;
    color: #cbd5e1;
}

.item-url {
    flex-shrink: 0;
}

.item-url a {
    font-size: 0.8rem;
    color: #7c3aed;
    text-decoration: none;
    background: #f3e8ff;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
}

.item-url a:hover {
    background: #ede9fe;
}

.item-price {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-shrink: 0;
}

.price-value {
    font-weight: 600;
    color: #1e293b;
}

.discount-badge {
    background: #fef2f2;
    color: #dc2626;
    font-size: 0.75rem;
    font-weight: 600;
    padding: 0.2rem 0.4rem;
    border-radius: 4px;
}

.item-order {
    flex-shrink: 0;
}

.order-badge {
    background: #f1f5f9;
    color: #64748b;
    font-size: 0.8rem;
    font-weight: 500;
    padding: 0.25rem 0.6rem;
    border-radius: 4px;
}

.item-status {
    flex-shrink: 0;
}

.status-badge {
    font-size: 0.75rem;
    font-weight: 500;
    padding: 0.3rem 0.6rem;
    border-radius: 20px;
}

.status-active {
    background: #d1fae5;
    color: #059669;
}

.status-inactive {
    background: #f1f5f9;
    color: #64748b;
}

.item-actions {
    display: flex;
    gap: 0.5rem;
    flex-shrink: 0;
}

.action-btn {
    width: 32px;
    height: 32px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f1f5f9;
    color: #64748b;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
    text-decoration: none;
}

.action-btn:hover {
    background: #e2e8f0;
    color: #475569;
}

.action-delete:hover {
    background: #fef2f2;
    color: #dc2626;
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
    border-radius: 16px;
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
    background: #ef4444;
    color: white;
}

.modal-btn-danger:hover:not(:disabled) {
    background: #dc2626;
}

.modal-btn-danger:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

/* Responsive */
@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }

    .list-item {
        flex-wrap: wrap;
    }

    .item-info {
        flex: 1 1 60%;
    }

    .item-url,
    .item-price,
    .item-order {
        display: none;
    }

    .item-status {
        order: 1;
        flex: 0 0 auto;
    }

    .item-actions {
        order: 2;
    }
}
</style>
