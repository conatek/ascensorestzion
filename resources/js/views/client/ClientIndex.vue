<template>
    <div>
        <!-- Cabecera -->
        <div class="app-page-title">
            <div class="page-title-wrapper">
                <div class="page-title-heading">
                    <div class="page-title-icon">
                        <i class="fa fa-users icon-gradient bg-mean-fruit"></i>
                    </div>
                    <div>
                        Clientes
                        <div class="page-title-subheading text-muted">
                            Gestiona tus clientes y sus sedes
                        </div>
                    </div>
                </div>
                <div class="page-title-actions">
                    <router-link :to="{ name: 'clients.create' }" class="btn-create">
                        <i class="fa fa-plus me-2"></i> Nuevo Cliente
                    </router-link>
                </div>
            </div>
        </div>

        <!-- Barra de busqueda -->
        <div class="search-bar">
            <div class="search-input-wrapper">
                <i class="fa fa-search search-icon"></i>
                <input
                    v-model="search"
                    type="text"
                    class="search-input"
                    placeholder="Buscar por razon social, NIT o contacto..."
                    @input="filterClients"
                />
                <button v-if="search" class="search-clear" @click="search = ''; filterClients()">
                    <i class="fa fa-times"></i>
                </button>
            </div>
        </div>

        <!-- Estado de carga -->
        <div v-if="loading" class="loading-state">
            <div class="spinner-border text-primary"></div>
            <p class="text-muted mt-3">Cargando clientes...</p>
        </div>

        <!-- Sin clientes - Estado vacio -->
        <div v-else-if="filteredClients.length === 0 && !search" class="empty-state">
            <div class="empty-state-content">
                <div class="empty-illustration">
                    <svg viewBox="0 0 200 160" class="empty-svg">
                        <rect x="50" y="60" width="100" height="80" rx="8" fill="#e2e8f0"/>
                        <rect x="60" y="75" width="25" height="20" rx="4" fill="#cbd5e1"/>
                        <rect x="115" y="75" width="25" height="20" rx="4" fill="#cbd5e1"/>
                        <rect x="60" y="105" width="25" height="20" rx="4" fill="#cbd5e1"/>
                        <rect x="115" y="105" width="25" height="20" rx="4" fill="#cbd5e1"/>
                        <rect x="87" y="100" width="26" height="40" rx="4" fill="#94a3b8"/>
                        <circle cx="160" cy="40" r="25" fill="url(#emptyGradient)"/>
                        <path d="M150 40 L170 40 M160 30 L160 50" stroke="white" stroke-width="4" stroke-linecap="round"/>
                        <circle cx="30" cy="50" r="6" fill="#fbbf24" opacity="0.8">
                            <animate attributeName="cy" values="50;40;50" dur="2s" repeatCount="indefinite"/>
                        </circle>
                        <circle cx="180" cy="100" r="4" fill="#10b981" opacity="0.8">
                            <animate attributeName="cy" values="100;110;100" dur="2.5s" repeatCount="indefinite"/>
                        </circle>
                        <defs>
                            <linearGradient id="emptyGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" style="stop-color:#30ab0a"/>
                                <stop offset="100%" style="stop-color:#ba2831"/>
                            </linearGradient>
                        </defs>
                    </svg>
                </div>
                <h3 class="empty-title">Aun no tienes clientes</h3>
                <p class="empty-description">
                    Crea tu primer cliente para comenzar a gestionar sus sedes y equipos.
                </p>
                <router-link :to="{ name: 'clients.create' }" class="btn-create">
                    <i class="fa fa-plus me-2"></i> Crear mi primer cliente
                </router-link>
            </div>
        </div>

        <!-- Sin resultados de busqueda -->
        <div v-else-if="filteredClients.length === 0 && search" class="empty-state">
            <div class="empty-state-content">
                <div class="empty-icon-search">
                    <i class="fa fa-search"></i>
                </div>
                <h3 class="empty-title">Sin resultados</h3>
                <p class="empty-description">
                    No se encontraron clientes que coincidan con "<strong>{{ search }}</strong>".
                </p>
            </div>
        </div>

        <!-- Tabla de clientes -->
        <div v-else class="table-card">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Razon Social</th>
                            <th>NIT</th>
                            <th>Contacto</th>
                            <th>Ciudad</th>
                            <th class="text-center">Sedes</th>
                            <th class="text-center">Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="client in filteredClients" :key="client.id">
                            <td class="td-name" data-label="Razon Social">{{ client.business_name }}</td>
                            <td data-label="NIT">{{ client.nit }}</td>
                            <td data-label="Contacto">
                                <div v-if="client.contact_name" class="contact-name">{{ client.contact_name }}</div>
                                <div v-if="client.contact_phone" class="contact-meta">{{ client.contact_phone }}</div>
                            </td>
                            <td data-label="Ciudad">{{ client.city || '—' }}</td>
                            <td class="text-center" data-label="Sedes">
                                <span class="count-badge">{{ client.sites_count ?? 0 }}</span>
                            </td>
                            <td class="text-center" data-label="Estado">
                                <span class="status-badge" :class="client.active ? 'status-active' : 'status-inactive'">
                                    {{ client.active ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="text-center cell-actions" data-label="Acciones">
                                <div class="table-actions">
                                    <router-link :to="{ name: 'clients.show', params: { id: client.id } }"
                                                 class="action-btn action-view" title="Ver">
                                        <i class="fa fa-eye"></i>
                                    </router-link>
                                    <router-link :to="{ name: 'clients.edit', params: { id: client.id } }"
                                                 class="action-btn action-edit" title="Editar">
                                        <i class="fa fa-edit"></i>
                                    </router-link>
                                    <button @click="confirmDelete(client)" class="action-btn action-danger" title="Eliminar">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                    <button
                                        v-if="auth.isMasterOrSuper()"
                                        @click="impersonateClient(client)"
                                        class="action-btn action-impersonate"
                                        :title="'Acceder como ' + client.business_name"
                                        :disabled="impersonating === client.id"
                                    >
                                        <i v-if="impersonating === client.id" class="fa fa-spinner fa-spin"></i>
                                        <i v-else class="fa fa-user-secret"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal confirmacion de borrado -->
        <div v-if="toDelete" class="modal-overlay" @click.self="toDelete = null">
            <div class="modal-container">
                <div class="modal-icon-wrapper">
                    <div class="modal-icon">
                        <i class="fa fa-exclamation-triangle"></i>
                    </div>
                </div>
                <h4 class="modal-title">Eliminar cliente</h4>
                <p class="modal-message">
                    ¿Estas seguro de eliminar <strong>{{ toDelete.business_name }}</strong>?
                    Se eliminaran tambien todas sus sedes y equipos asociados.
                </p>
                <div class="modal-actions">
                    <button class="modal-btn modal-btn-cancel" @click="toDelete = null">
                        Cancelar
                    </button>
                    <button class="modal-btn modal-btn-danger" @click="deleteClient" :disabled="deleting">
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
import { useAuth } from '@/stores/auth.js';

export default {
    name: 'ClientIndex',

    data() {
        return {
            auth: useAuth(),
            clients: [],
            filteredClients: [],
            loading: true,
            search: '',
            toDelete: null,
            deleting: false,
            impersonating: null,
        };
    },

    async created() {
        await this.load();
    },

    methods: {
        async load() {
            this.loading = true;
            try {
                const { data } = await clientService.all();
                this.clients = data;
                this.filteredClients = data;
            } finally {
                this.loading = false;
            }
        },

        filterClients() {
            const q = this.search.toLowerCase().trim();
            if (!q) {
                this.filteredClients = this.clients;
                return;
            }
            this.filteredClients = this.clients.filter(c =>
                (c.business_name || '').toLowerCase().includes(q) ||
                (c.nit || '').toLowerCase().includes(q) ||
                (c.contact_name || '').toLowerCase().includes(q) ||
                (c.city || '').toLowerCase().includes(q)
            );
        },

        confirmDelete(client) {
            this.toDelete = client;
        },

        async deleteClient() {
            this.deleting = true;
            try {
                await clientService.destroy(this.toDelete.id);
                this.clients = this.clients.filter(c => c.id !== this.toDelete.id);
                this.filterClients();
                this.toDelete = null;
            } finally {
                this.deleting = false;
            }
        },

        async impersonateClient(client) {
            this.impersonating = client.id;
            try {
                await this.auth.impersonate(client.id);
                this.$router.push('/portal');
            } catch (e) {
                const msg = e.response?.data?.message || 'No se pudo acceder como este cliente.';
                alert(msg);
            } finally {
                this.impersonating = null;
            }
        },
    },
};
</script>

<style scoped>
/* Boton crear */
.btn-create {
    display: inline-flex;
    align-items: center;
    padding: 0.625rem 1.25rem;
    background: #279208;
    color: white;
    font-weight: 600;
    border-radius: 10px;
    text-decoration: none;
    transition: all 0.2s ease;
    box-shadow: 0 2px 8px rgba(39, 146, 8, 0.25);
    border: none;
    cursor: pointer;
}

.btn-create:hover {
    background: #1f7506;
    box-shadow: 0 4px 12px rgba(39, 146, 8, 0.35);
    color: white;
}

/* Barra de busqueda */
.search-bar {
    margin-bottom: 1.5rem;
}

.search-input-wrapper {
    position: relative;
    max-width: 480px;
}

.search-icon {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    font-size: 0.9rem;
}

.search-input {
    width: 100%;
    padding: 0.75rem 2.5rem 0.75rem 2.75rem;
    font-size: 0.95rem;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    background: white;
    transition: all 0.2s;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

.search-input:focus {
    outline: none;
    border-color: #279208;
    box-shadow: 0 0 0 3px rgba(39, 146, 8, 0.1);
}

.search-input::placeholder {
    color: #94a3b8;
}

.search-clear {
    position: absolute;
    right: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #94a3b8;
    cursor: pointer;
    padding: 0.25rem;
    transition: color 0.2s;
}

.search-clear:hover {
    color: #64748b;
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

/* Estado vacio */
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

.empty-icon-search {
    width: 64px;
    height: 64px;
    background: #f1f5f9;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
    font-size: 1.5rem;
    color: #94a3b8;
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

/* Tabla */
.table-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    border: 1px solid #e2e8f0;
    overflow: hidden;
}

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

.contact-name {
    font-weight: 500;
    color: #1e293b;
}

.contact-meta {
    font-size: 0.8rem;
    color: #64748b;
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

/* Acciones de tabla */
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

.action-danger:hover {
    background: #fef2f2;
    color: #dc2626;
}

.action-impersonate:hover {
    background: #ede9fe;
    color: #7c3aed;
}

.action-impersonate:disabled {
    opacity: 0.5;
    cursor: not-allowed;
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
    background: linear-gradient(135deg, #ba2831, #dc2626);
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
@media (max-width: 768px) {
    .data-table th:nth-child(4),
    .data-table td:nth-child(4) {
        display: none;
    }
}

@media (max-width: 576px) {
    .data-table th:nth-child(3),
    .data-table td:nth-child(3),
    .data-table th:nth-child(5),
    .data-table td:nth-child(5) {
        display: none;
    }
}
</style>
