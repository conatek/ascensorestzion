<template>
    <div>
        <!-- Estado de carga -->
        <div v-if="loading" class="loading-state">
            <div class="spinner-border text-primary"></div>
            <p class="text-muted mt-3">Cargando cliente...</p>
        </div>

        <template v-else>
            <!-- Cabecera de cliente -->
            <div class="app-page-title">
                <div class="page-title-wrapper">
                    <div class="page-title-heading">
                        <div class="page-title-icon">
                            <i class="fa fa-user-tie icon-gradient bg-mean-fruit"></i>
                        </div>
                        <div>
                            {{ client.business_name }}
                            <div class="page-title-subheading text-muted">
                                NIT: {{ client.nit }}
                            </div>
                        </div>
                    </div>
                    <div class="page-title-actions d-flex gap-2">
                        <router-link :to="{ name: 'clients.index' }" class="btn-action btn-back">
                            <i class="fa fa-arrow-left me-1"></i> Volver
                        </router-link>
                        <router-link :to="{ name: 'clients.edit', params: { id: client.id } }"
                                     class="btn-action btn-edit">
                            <i class="fa fa-edit me-1"></i> Editar
                        </router-link>
                    </div>
                </div>
            </div>

            <!-- Resumen de estadisticas -->
            <div class="stats-grid">
                <div class="stat-card stat-green">
                    <div class="stat-icon">
                        <i class="fa fa-map-marker-alt"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number">{{ client.sites?.length ?? 0 }}</div>
                        <div class="stat-label">Sedes</div>
                    </div>
                </div>
                <div class="stat-card stat-blue">
                    <div class="stat-icon">
                        <i class="fa fa-cogs"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number">{{ totalEquipment }}</div>
                        <div class="stat-label">Equipos</div>
                    </div>
                </div>
                <div class="stat-card stat-status">
                    <div class="stat-icon">
                        <i class="fa fa-circle" :style="{ color: client.active ? '#059669' : '#94a3b8' }"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number">{{ client.active ? 'Activo' : 'Inactivo' }}</div>
                        <div class="stat-label">Estado</div>
                    </div>
                </div>
            </div>

            <!-- Informacion del cliente -->
            <div class="info-grid">
                <div class="info-card" v-if="hasGeneralInfo">
                    <div class="info-header">
                        <i class="fa fa-building"></i>
                        <span>Informacion General</span>
                    </div>
                    <div class="info-body">
                        <div v-if="client.address" class="info-item">
                            <i class="fa fa-map-marker-alt"></i>
                            <span>{{ client.address }}</span>
                        </div>
                        <div v-if="client.city || client.department" class="info-item">
                            <i class="fa fa-city"></i>
                            <span>{{ [client.city, client.department].filter(Boolean).join(', ') }}</span>
                        </div>
                    </div>
                </div>

                <div class="info-card" v-if="hasContactInfo">
                    <div class="info-header">
                        <i class="fa fa-address-book"></i>
                        <span>Contacto</span>
                    </div>
                    <div class="info-body">
                        <div v-if="client.contact_name" class="info-item">
                            <i class="fa fa-user"></i>
                            <span>{{ client.contact_name }}</span>
                        </div>
                        <div v-if="client.contact_email" class="info-item">
                            <i class="fa fa-envelope"></i>
                            <a :href="'mailto:' + client.contact_email">{{ client.contact_email }}</a>
                        </div>
                        <div v-if="client.contact_phone" class="info-item">
                            <i class="fa fa-phone"></i>
                            <span>{{ client.contact_phone }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notas -->
            <div v-if="client.notes" class="info-card notes-card">
                <div class="info-header">
                    <i class="fa fa-sticky-note"></i>
                    <span>Notas</span>
                </div>
                <div class="info-body">
                    <p class="notes-text">{{ client.notes }}</p>
                </div>
            </div>

            <!-- Sedes -->
            <div class="section-card">
                <div class="section-header">
                    <div class="section-title">
                        <i class="fa fa-map-marker-alt section-icon icon-green"></i>
                        <span>Sedes</span>
                    </div>
                    <router-link :to="{ name: 'sites.create', params: { clientId: client.id } }"
                                 class="btn-add btn-add-green">
                        <i class="fa fa-plus me-1"></i> Agregar Sede
                    </router-link>
                </div>
                <div class="section-body">
                    <div v-if="!client.sites?.length" class="empty-section">
                        <div class="empty-icon">
                            <i class="fa fa-map-marker-alt"></i>
                        </div>
                        <p>Sin sedes. Crea la primera.</p>
                    </div>

                    <div v-else class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Direccion</th>
                                    <th>Contacto en sitio</th>
                                    <th class="text-center">Equipos</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="site in client.sites" :key="site.id">
                                    <td class="td-name" data-label="Nombre">{{ site.name }}</td>
                                    <td data-label="Direccion">{{ site.address || '—' }}</td>
                                    <td data-label="Contacto">{{ site.contact_name_onsite || '—' }}</td>
                                    <td class="text-center" data-label="Equipos">
                                        <span class="count-badge">{{ site.equipment_count ?? 0 }}</span>
                                    </td>
                                    <td class="text-center cell-actions" data-label="Acciones">
                                        <div class="table-actions">
                                            <router-link :to="{ name: 'equipment.index', query: { client_id: client.id } }"
                                                         class="action-btn action-view" title="Ver Equipos">
                                                <i class="fa fa-eye"></i>
                                            </router-link>
                                            <router-link :to="{ name: 'sites.edit', params: { clientId: client.id, siteId: site.id } }"
                                                         class="action-btn action-edit" title="Editar">
                                                <i class="fa fa-edit"></i>
                                            </router-link>
                                            <button @click="confirmDeleteSite(site)" class="action-btn action-delete" title="Eliminar">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </template>

        <!-- Modal eliminar sede -->
        <div v-if="siteToDelete" class="modal-overlay" @click.self="siteToDelete = null">
            <div class="modal-container">
                <div class="modal-icon-wrapper">
                    <div class="modal-icon">
                        <i class="fa fa-exclamation-triangle"></i>
                    </div>
                </div>
                <h4 class="modal-title">Eliminar sede</h4>
                <p class="modal-message">
                    ¿Eliminar la sede <strong>{{ siteToDelete.name }}</strong>?
                    Se eliminaran tambien todos los equipos asociados.
                </p>
                <div class="modal-actions">
                    <button class="modal-btn modal-btn-cancel" @click="siteToDelete = null">Cancelar</button>
                    <button class="modal-btn modal-btn-danger" @click="deleteSite" :disabled="deleting">
                        <span v-if="deleting" class="spinner-border spinner-border-sm me-2"></span>
                        {{ deleting ? 'Eliminando...' : 'Eliminar' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import clientService from '@/services/clientService.js';
import siteService from '@/services/siteService.js';

export default {
    name: 'ClientShow',

    data() {
        return {
            client: {},
            loading: true,
            siteToDelete: null,
            deleting: false,
        };
    },

    computed: {
        hasGeneralInfo() {
            return this.client.address || this.client.city || this.client.department;
        },
        hasContactInfo() {
            return this.client.contact_name || this.client.contact_email || this.client.contact_phone;
        },
        totalEquipment() {
            if (!this.client.sites) return 0;
            return this.client.sites.reduce((sum, site) => sum + (site.equipment_count ?? 0), 0);
        },
    },

    async created() {
        await this.load();
    },

    methods: {
        async load() {
            this.loading = true;
            try {
                const { data } = await clientService.get(this.$route.params.id);
                this.client = data;
            } finally {
                this.loading = false;
            }
        },

        confirmDeleteSite(site) {
            this.siteToDelete = site;
        },

        async deleteSite() {
            this.deleting = true;
            try {
                await siteService.destroy(this.client.id, this.siteToDelete.id);
                this.client.sites = this.client.sites.filter(s => s.id !== this.siteToDelete.id);
                this.siteToDelete = null;
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

.stat-green .stat-icon { background: #d1fae5; color: #059669; }
.stat-blue .stat-icon { background: #dbeafe; color: #2563eb; }
.stat-status .stat-icon { background: #f1f5f9; color: #64748b; }

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

.notes-card {
    margin-bottom: 1.5rem;
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
    color: #279208;
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
    color: #279208;
    text-decoration: none;
}

.info-item a:hover {
    text-decoration: underline;
}

.notes-text {
    color: #475569;
    line-height: 1.6;
    margin: 0;
    white-space: pre-wrap;
}

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

.icon-green { background: #d1fae5; color: #059669; }

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

.btn-add-green { background: #279208; }
.btn-add-green:hover { background: #1f7506; color: white; }

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

/* Table */
.table-responsive {
    overflow-x: auto;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table thead {
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
}

.data-table th {
    padding: 0.875rem 1.25rem;
    font-size: 0.8rem;
    font-weight: 600;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    white-space: nowrap;
}

.data-table td {
    padding: 0.875rem 1.25rem;
    font-size: 0.9rem;
    color: #475569;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
}

.data-table tbody tr:last-child td {
    border-bottom: none;
}

.data-table tbody tr:hover {
    background: #fafafa;
}

.td-name {
    font-weight: 600;
    color: #1e293b;
}

.count-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 28px;
    height: 28px;
    padding: 0 0.5rem;
    background: #e8f5e4;
    color: #279208;
    font-weight: 600;
    font-size: 0.85rem;
    border-radius: 8px;
}

/* Table Actions */
.table-actions {
    display: inline-flex;
    gap: 0.5rem;
}

.action-btn {
    width: 32px;
    height: 32px;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #f1f5f9;
    color: #64748b;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
    text-decoration: none;
}

.action-view:hover {
    background: #e8f5e4;
    color: #279208;
}

.action-edit:hover {
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
    color: #ba2831;
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
    background: #ba2831;
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

    .data-table th:nth-child(2),
    .data-table td:nth-child(2),
    .data-table th:nth-child(3),
    .data-table td:nth-child(3) {
        display: none;
    }
}
</style>
