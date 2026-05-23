<template>
    <div>
        <!-- Cabecera -->
        <div class="app-page-title">
            <div class="page-title-wrapper">
                <div class="page-title-heading">
                    <div class="page-title-icon">
                        <i class="fa fa-arrows-alt-v icon-gradient bg-mean-fruit"></i>
                    </div>
                    <div>
                        Equipos
                        <div class="page-title-subheading text-muted">
                            Gestiona los equipos registrados en el sistema
                        </div>
                    </div>
                </div>
                <div class="page-title-actions">
                    <router-link :to="{ name: 'equipment.create' }" class="btn-create">
                        <i class="fa fa-plus me-2"></i> Nuevo Equipo
                    </router-link>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="filter-bar">
            <div class="filter-row">
                <div class="filter-group">
                    <label class="filter-label">Cliente</label>
                    <select v-model="filters.client_id" class="filter-select" @change="load">
                        <option value="">Todos</option>
                        <option v-for="client in clients" :key="client.id" :value="client.id">
                            {{ client.business_name }}
                        </option>
                    </select>
                </div>
                <div class="filter-group">
                    <label class="filter-label">Tipo</label>
                    <select v-model="filters.equipment_type" class="filter-select" @change="load">
                        <option value="">Todos</option>
                        <option v-for="(label, key) in typeLabels" :key="key" :value="key">
                            {{ label }}
                        </option>
                    </select>
                </div>
                <div class="filter-group">
                    <label class="filter-label">Estado</label>
                    <select v-model="filters.status" class="filter-select" @change="load">
                        <option value="">Todos</option>
                        <option v-for="(label, key) in statusLabels" :key="key" :value="key">
                            {{ label }}
                        </option>
                    </select>
                </div>
                <div class="filter-group filter-search">
                    <label class="filter-label">Buscar</label>
                    <input v-model="filters.search" type="text" class="filter-input"
                           placeholder="Codigo, marca, modelo..." @input="onSearch" />
                </div>
            </div>
        </div>

        <!-- Estado de carga -->
        <div v-if="loading" class="loading-state">
            <div class="spinner-border text-primary"></div>
            <p class="text-muted mt-3">Cargando equipos...</p>
        </div>

        <!-- Sin equipos -->
        <div v-else-if="equipments.length === 0" class="empty-state">
            <div class="empty-state-content">
                <div class="empty-illustration">
                    <svg viewBox="0 0 200 160" class="empty-svg">
                        <rect x="60" y="30" width="80" height="110" rx="6" fill="#e2e8f0"/>
                        <rect x="72" y="45" width="20" height="15" rx="3" fill="#cbd5e1"/>
                        <rect x="108" y="45" width="20" height="15" rx="3" fill="#cbd5e1"/>
                        <rect x="72" y="70" width="20" height="15" rx="3" fill="#cbd5e1"/>
                        <rect x="108" y="70" width="20" height="15" rx="3" fill="#cbd5e1"/>
                        <rect x="90" y="100" width="20" height="40" rx="3" fill="#94a3b8"/>
                        <path d="M90 30 L100 15 L110 30" fill="#30ab0a"/>
                        <circle cx="155" cy="35" r="22" fill="url(#eqGradient)"/>
                        <path d="M146 35 L164 35 M155 26 L155 44" stroke="white" stroke-width="3.5" stroke-linecap="round"/>
                        <defs>
                            <linearGradient id="eqGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" style="stop-color:#30ab0a"/>
                                <stop offset="100%" style="stop-color:#ba2831"/>
                            </linearGradient>
                        </defs>
                    </svg>
                </div>
                <h3 class="empty-title">No hay equipos registrados</h3>
                <p class="empty-description">
                    Registra el primer equipo para comenzar a gestionar el mantenimiento.
                </p>
                <router-link :to="{ name: 'equipment.create' }" class="btn-create">
                    <i class="fa fa-plus me-2"></i> Registrar primer equipo
                </router-link>
            </div>
        </div>

        <!-- Tabla de equipos -->
        <div v-else class="table-card">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Codigo Interno</th>
                            <th>Tipo</th>
                            <th>Marca / Modelo</th>
                            <th>Sede</th>
                            <th>Cliente</th>
                            <th>Estado</th>
                            <th class="th-actions">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="eq in equipments" :key="eq.id">
                            <td>
                                <span class="code-text">{{ eq.internal_code }}</span>
                            </td>
                            <td>
                                <span class="type-badge">{{ typeLabels[eq.equipment_type] || eq.equipment_type }}</span>
                            </td>
                            <td>
                                <span class="brand-text">{{ eq.brand }}</span>
                                <span v-if="eq.model" class="model-text">{{ eq.model }}</span>
                            </td>
                            <td>{{ eq.site?.name || '-' }}</td>
                            <td>{{ eq.site?.client?.business_name || '-' }}</td>
                            <td>
                                <span class="status-badge" :class="'status-' + eq.status">
                                    {{ statusLabels[eq.status] || eq.status }}
                                </span>
                            </td>
                            <td>
                                <div class="item-actions">
                                    <router-link :to="{ name: 'equipment.show', params: { id: eq.id } }"
                                                 class="action-btn" title="Ver">
                                        <i class="fa fa-eye"></i>
                                    </router-link>
                                    <router-link :to="{ name: 'equipment.edit', params: { id: eq.id } }"
                                                 class="action-btn" title="Editar">
                                        <i class="fa fa-edit"></i>
                                    </router-link>
                                    <button @click="confirmDelete(eq)" class="action-btn action-delete" title="Eliminar">
                                        <i class="fa fa-trash"></i>
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
                <h4 class="modal-title">Eliminar equipo</h4>
                <p class="modal-message">
                    ¿Estas seguro de eliminar el equipo <strong>{{ toDelete.internal_code }}</strong>?
                    Esta accion no se puede deshacer.
                </p>
                <div class="modal-actions">
                    <button class="modal-btn modal-btn-cancel" @click="toDelete = null">
                        Cancelar
                    </button>
                    <button class="modal-btn modal-btn-danger" @click="deleteEquipment" :disabled="deleting">
                        <span v-if="deleting" class="spinner-border spinner-border-sm me-2"></span>
                        {{ deleting ? 'Eliminando...' : 'Eliminar' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import equipmentService from '@/services/equipmentService.js';
import clientService from '@/services/clientService.js';

export default {
    name: 'EquipmentIndex',

    data() {
        return {
            equipments: [],
            clients: [],
            loading: true,
            toDelete: null,
            deleting: false,
            searchTimeout: null,
            filters: {
                client_id: '',
                equipment_type: '',
                status: '',
                search: '',
            },
            typeLabels: {
                ascensor: 'Ascensor',
                escalera_electrica: 'Escalera Electrica',
                montacargas: 'Montacargas',
                otro: 'Otro',
            },
            statusLabels: {
                activo: 'Activo',
                fuera_servicio: 'Fuera de Servicio',
                modernizacion: 'Modernizacion',
                retirado: 'Retirado',
            },
        };
    },

    async created() {
        if (this.$route.query.client_id) {
            this.filters.client_id = this.$route.query.client_id;
        }
        await Promise.all([this.load(), this.loadClients()]);
    },

    methods: {
        async load() {
            this.loading = true;
            try {
                const params = {};
                if (this.filters.client_id) params.client_id = this.filters.client_id;
                if (this.filters.equipment_type) params.equipment_type = this.filters.equipment_type;
                if (this.filters.status) params.status = this.filters.status;
                if (this.filters.search) params.search = this.filters.search;
                const { data } = await equipmentService.all(params);
                this.equipments = data;
            } finally {
                this.loading = false;
            }
        },

        async loadClients() {
            try {
                const { data } = await clientService.all();
                this.clients = data;
            } catch {
                this.clients = [];
            }
        },

        onSearch() {
            clearTimeout(this.searchTimeout);
            this.searchTimeout = setTimeout(() => {
                this.load();
            }, 400);
        },

        confirmDelete(equipment) {
            this.toDelete = equipment;
        },

        async deleteEquipment() {
            this.deleting = true;
            try {
                await equipmentService.destroy(this.toDelete.id);
                this.equipments = this.equipments.filter(e => e.id !== this.toDelete.id);
                this.toDelete = null;
            } finally {
                this.deleting = false;
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

/* Filtros */
.filter-bar {
    background: white;
    border-radius: 12px;
    padding: 1.25rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    border: 1px solid #e2e8f0;
}

.filter-row {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
}

@media (max-width: 992px) {
    .filter-row {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 576px) {
    .filter-row {
        grid-template-columns: 1fr;
    }
}

.filter-label {
    display: block;
    font-size: 0.8rem;
    font-weight: 500;
    color: #64748b;
    margin-bottom: 0.375rem;
}

.filter-select,
.filter-input {
    width: 100%;
    padding: 0.5rem 0.75rem;
    font-size: 0.9rem;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    background: white;
    transition: all 0.2s;
    color: #1e293b;
}

.filter-select:focus,
.filter-input:focus {
    outline: none;
    border-color: #279208;
    box-shadow: 0 0 0 3px rgba(39, 146, 8, 0.1);
}

.filter-input::placeholder {
    color: #94a3b8;
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
    text-align: left;
    white-space: nowrap;
}

.th-actions {
    text-align: center;
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

.code-text {
    font-weight: 600;
    color: #1e293b;
}

.brand-text {
    font-weight: 500;
    color: #1e293b;
}

.model-text {
    color: #64748b;
    margin-left: 0.25rem;
}

.model-text::before {
    content: '/ ';
}

/* Badges */
.type-badge {
    display: inline-block;
    font-size: 0.8rem;
    font-weight: 500;
    padding: 0.25rem 0.6rem;
    border-radius: 6px;
    background: #e8f5e4;
    color: #279208;
}

.status-badge {
    display: inline-block;
    font-size: 0.75rem;
    font-weight: 500;
    padding: 0.3rem 0.6rem;
    border-radius: 20px;
    white-space: nowrap;
}

.status-activo {
    background: #d1fae5;
    color: #059669;
}

.status-fuera_servicio {
    background: #fef2f2;
    color: #ba2831;
}

.status-modernizacion {
    background: #fff7ed;
    color: #d97706;
}

.status-retirado {
    background: #f1f5f9;
    color: #606060;
}

/* Acciones */
.item-actions {
    display: flex;
    gap: 0.5rem;
    justify-content: center;
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
    color: #ba2831;
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
</style>
