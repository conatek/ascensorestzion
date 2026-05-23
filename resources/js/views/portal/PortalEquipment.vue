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
                        Mis Equipos
                        <div class="page-title-subheading text-muted">
                            Consulta el estado de tus equipos registrados
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="filter-bar">
            <div class="filter-row">
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
                    </svg>
                </div>
                <h3 class="empty-title">No hay equipos registrados</h3>
                <p class="empty-description">
                    No se encontraron equipos asociados a tu cuenta.
                </p>
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
                            <td>
                                <span class="status-badge" :class="'status-' + eq.status">
                                    {{ statusLabels[eq.status] || eq.status }}
                                </span>
                            </td>
                            <td>
                                <div class="item-actions">
                                    <router-link :to="'/equipos/' + eq.id"
                                                 class="action-btn" title="Ver Detalle">
                                        <i class="fa fa-eye"></i>
                                    </router-link>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script>
import portalService from '@/services/portalService.js';

export default {
    name: 'PortalEquipment',

    data() {
        return {
            equipments: [],
            loading: true,
            searchTimeout: null,
            filters: {
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
        await this.load();
    },

    methods: {
        async load() {
            this.loading = true;
            try {
                const params = {};
                if (this.filters.equipment_type) params.equipment_type = this.filters.equipment_type;
                if (this.filters.status) params.status = this.filters.status;
                if (this.filters.search) params.search = this.filters.search;
                const { data } = await portalService.equipment(params);
                this.equipments = data;
            } finally {
                this.loading = false;
            }
        },

        onSearch() {
            clearTimeout(this.searchTimeout);
            this.searchTimeout = setTimeout(() => {
                this.load();
            }, 400);
        },
    },
};
</script>

<style scoped>
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
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
}

@media (max-width: 768px) {
    .filter-row {
        grid-template-columns: 1fr;
    }
}

.filter-label {
    display: block;
    font-family: 'Poppins', sans-serif;
    font-size: 0.8rem;
    font-weight: 500;
    color: #64748b;
    margin-bottom: 0.375rem;
}

.filter-select,
.filter-input {
    width: 100%;
    padding: 0.5rem 0.75rem;
    font-family: 'Poppins', sans-serif;
    font-size: 0.875rem;
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
</style>
