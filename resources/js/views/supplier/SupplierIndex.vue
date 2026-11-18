<template>
    <div>
        <!-- Cabecera -->
        <div class="app-page-title">
            <div class="page-title-wrapper">
                <div class="page-title-heading">
                    <div class="page-title-icon">
                        <i class="fa fa-truck icon-gradient bg-mean-fruit"></i>
                    </div>
                    <div>
                        Proveedores
                        <div class="page-title-subheading text-muted">
                            Sedes, asesores, productos y precios en un solo lugar
                        </div>
                    </div>
                </div>
                <div class="page-title-actions">
                    <router-link :to="{ name: 'suppliers.create' }" class="btn-create">
                        <i class="fa fa-plus me-2"></i> Nuevo proveedor
                    </router-link>
                </div>
            </div>
        </div>

        <!-- Aviso de datos de ejemplo -->
        <div v-if="exampleCount > 0" class="example-banner">
            <i class="fa fa-lightbulb"></i>
            <div>
                <strong>{{ exampleCount === 1 ? 'Hay 1 proveedor de ejemplo.' : `Hay ${exampleCount} proveedores de ejemplo.` }}</strong>
                Son ficticios y sirven para conocer el módulo. Registra los proveedores reales y elimina los de ejemplo
                con el botón <i class="fa fa-trash"></i> cuando ya no hagan falta.
            </div>
        </div>

        <!-- Filtros -->
        <div class="filter-bar">
            <div class="search-input-wrapper">
                <i class="fa fa-search search-icon"></i>
                <input
                    v-model="search"
                    type="text"
                    class="search-input"
                    placeholder="Buscar por nombre, NIT, ciudad, asesor o producto que vende..."
                    @input="onSearch"
                />
                <button v-if="search" class="search-clear" title="Limpiar" @click="search = ''; load()">
                    <i class="fa fa-times"></i>
                </button>
            </div>
            <select v-model="filterType" class="filter-select" @change="load()">
                <option value="">Todos los tipos</option>
                <option v-for="t in types" :key="t.value" :value="t.value">{{ t.label }}</option>
            </select>
            <select v-model="filterCity" class="filter-select" @change="load()">
                <option value="">Todas las ciudades</option>
                <option v-for="c in cities" :key="c" :value="c">{{ c }}</option>
            </select>
        </div>

        <!-- Estado de carga -->
        <div v-if="loading" class="loading-state">
            <div class="spinner-border text-primary"></div>
            <p class="text-muted mt-3">Cargando proveedores...</p>
        </div>

        <!-- Sin proveedores -->
        <div v-else-if="suppliers.length === 0 && !hasFilters" class="empty-state">
            <div class="empty-state-content">
                <div class="empty-icon"><i class="fa fa-truck"></i></div>
                <h3 class="empty-title">Aún no hay proveedores</h3>
                <p class="empty-description">
                    Registra el primero con sus sedes, asesores y los productos que maneja.
                </p>
                <router-link :to="{ name: 'suppliers.create' }" class="btn-create">
                    <i class="fa fa-plus me-2"></i> Crear el primer proveedor
                </router-link>
            </div>
        </div>

        <!-- Sin resultados -->
        <div v-else-if="suppliers.length === 0" class="empty-state">
            <div class="empty-state-content">
                <div class="empty-icon"><i class="fa fa-search"></i></div>
                <h3 class="empty-title">Sin resultados</h3>
                <p class="empty-description">Ningún proveedor coincide con lo que buscas.</p>
                <button class="btn-clear-filters" @click="clearFilters">Quitar filtros</button>
            </div>
        </div>

        <!-- Tabla -->
        <div v-else class="table-card">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Proveedor</th>
                            <th>Tipo</th>
                            <th>Ciudad</th>
                            <th>Asesor principal</th>
                            <th class="text-center">Productos</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="s in suppliers" :key="s.id" :class="{ 'row-inactive': !s.active }">
                            <td data-label="Proveedor">
                                <div class="supplier-cell">
                                    <router-link :to="{ name: 'suppliers.show', params: { id: s.id } }" class="supplier-name">
                                        {{ s.trade_name || s.business_name }}
                                    </router-link>
                                    <span v-if="s.is_example" class="badge-example">Ejemplo</span>
                                    <span v-if="!s.active" class="badge-inactive">Inactivo</span>
                                    <div v-if="s.trade_name" class="supplier-legal">{{ s.business_name }}</div>
                                    <div v-if="s.categories?.length" class="supplier-tags">
                                        <span v-for="c in s.categories.slice(0, 3)" :key="c" class="mini-tag">{{ c }}</span>
                                        <span v-if="s.categories.length > 3" class="mini-tag mini-tag--more">+{{ s.categories.length - 3 }}</span>
                                    </div>
                                </div>
                            </td>
                            <td data-label="Tipo">{{ typeLabel(s.supplier_type) }}</td>
                            <td data-label="Ciudad">
                                <span v-if="s.main_branch?.city">{{ s.main_branch.city }}</span>
                                <span v-else class="muted">—</span>
                                <span v-if="s.branches_count > 1" class="more-count" :title="`${s.branches_count} sedes`">
                                    +{{ s.branches_count - 1 }}
                                </span>
                            </td>
                            <td data-label="Asesor principal">
                                <div v-if="s.primary_contact" class="advisor-cell">
                                    <div class="advisor-text">
                                        <div class="advisor-name">{{ s.primary_contact.name }}</div>
                                        <div class="advisor-meta">{{ s.primary_contact.mobile || s.primary_contact.phone || s.primary_contact.email }}</div>
                                    </div>
                                    <ContactActions
                                        :phone="s.primary_contact.phone"
                                        :mobile="s.primary_contact.mobile"
                                        :whatsapp-number="s.primary_contact.whatsapp"
                                        :email="s.primary_contact.email"
                                    />
                                </div>
                                <span v-else class="muted">Sin asesor</span>
                            </td>
                            <td class="text-center" data-label="Productos">
                                <span class="count-badge">{{ s.items_count ?? 0 }}</span>
                            </td>
                            <td class="text-center cell-actions" data-label="Acciones">
                                <div class="table-actions">
                                    <router-link :to="{ name: 'suppliers.show', params: { id: s.id } }" class="action-btn action-view" title="Ver ficha">
                                        <i class="fa fa-eye"></i>
                                    </router-link>
                                    <router-link :to="{ name: 'suppliers.edit', params: { id: s.id } }" class="action-btn action-edit" title="Editar">
                                        <i class="fa fa-edit"></i>
                                    </router-link>
                                    <button class="action-btn action-danger" title="Eliminar" @click="confirmDelete(s)">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <p v-if="!loading && suppliers.length" class="result-count">
            {{ suppliers.length === 1 ? '1 proveedor' : `${suppliers.length} proveedores` }}
        </p>
    </div>
</template>

<script>
import supplierService from '@/services/supplierService.js';
import ContactActions from '@/components/supplier/ContactActions.vue';
import { SUPPLIER_TYPES, typeLabel } from '@/services/supplierOptions.js';
import { onSupplyChange } from '@/services/mastersChannel.js';

export default {
    name: 'SupplierIndex',
    components: { ContactActions },

    data() {
        return {
            suppliers: [],
            cities: [],
            loading: true,
            search: '',
            filterType: '',
            filterCity: '',
            types: SUPPLIER_TYPES,
            searchTimer: null,
            stopListening: null,
            requestId: 0,
        };
    },

    computed: {
        hasFilters() {
            return !!(this.search.trim() || this.filterType || this.filterCity);
        },
        exampleCount() {
            return this.suppliers.filter((s) => s.is_example).length;
        },
    },

    async created() {
        await this.load();
        // Otro master creó, cambió o borró algo: se refresca sin parpadeo.
        this.stopListening = onSupplyChange(() => this.load({ silent: true }));
    },

    beforeUnmount() {
        clearTimeout(this.searchTimer);
        this.stopListening?.();
    },

    methods: {
        typeLabel(value) {
            return typeLabel(SUPPLIER_TYPES, value);
        },

        async load({ silent = false } = {}) {
            if (!silent) this.loading = true;
            const id = ++this.requestId;

            const params = {};
            if (this.search.trim()) params.search = this.search.trim();
            if (this.filterType) params.type = this.filterType;
            if (this.filterCity) params.city = this.filterCity;

            try {
                const { data } = await supplierService.all(params);
                if (id !== this.requestId) return; // llegó una respuesta vieja
                this.suppliers = data;

                // Las ciudades del filtro salen del listado completo.
                if (!this.hasFilters) {
                    this.cities = [...new Set(data.map((s) => s.main_branch?.city).filter(Boolean))]
                        .sort((a, b) => a.localeCompare(b, 'es'));
                }
            } finally {
                if (id === this.requestId) this.loading = false;
            }
        },

        onSearch() {
            clearTimeout(this.searchTimer);
            this.searchTimer = setTimeout(() => this.load({ silent: true }), 300);
        },

        clearFilters() {
            this.search = '';
            this.filterType = '';
            this.filterCity = '';
            this.load();
        },

        async confirmDelete(supplier) {
            const name = supplier.trade_name || supplier.business_name;
            const res = await this.$swal.fire({
                icon: 'warning',
                title: 'Eliminar proveedor',
                text: `¿Eliminar "${name}"? Se eliminarán también sus sedes, asesores y precios.`
                    + (supplier.is_example ? ' Al ser de ejemplo, también se quitan sus productos de ejemplo.' : ''),
                showCancelButton: true,
                confirmButtonText: 'Eliminar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#ba2831',
            });
            if (!res.isConfirmed) return;

            try {
                await supplierService.destroy(supplier.id);
                this.suppliers = this.suppliers.filter((s) => s.id !== supplier.id);
                this.$swal.fire({ icon: 'success', title: 'Proveedor eliminado', timer: 1600, showConfirmButton: false });
            } catch (err) {
                this.$swal.fire({
                    icon: 'error',
                    title: 'No se pudo eliminar',
                    text: err.response?.data?.message || 'No se pudo eliminar el proveedor.',
                });
            }
        },
    },
};
</script>

<style scoped>
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

/* Aviso de ejemplos */
.example-banner {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    padding: 0.9rem 1.1rem;
    margin-bottom: 1.25rem;
    background: #fffbeb;
    border: 1px solid #fde68a;
    border-radius: 12px;
    color: #92400e;
    font-size: 0.9rem;
    line-height: 1.5;
}

.example-banner > i { margin-top: 0.2rem; color: #d97706; }

/* Filtros */
.filter-bar {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    margin-bottom: 1.5rem;
}

.search-input-wrapper {
    position: relative;
    flex: 1 1 320px;
    max-width: 560px;
}

.search-icon {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    font-size: 0.9rem;
}

.search-input,
.filter-select {
    padding: 0.75rem 1rem;
    font-size: 0.95rem;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    background: white;
    transition: all 0.2s;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

.search-input {
    width: 100%;
    padding-left: 2.75rem;
    padding-right: 2.5rem;
}

.filter-select {
    flex: 0 1 200px;
    color: #475569;
    cursor: pointer;
}

.search-input:focus,
.filter-select:focus {
    outline: none;
    border-color: #279208;
    box-shadow: 0 0 0 3px rgba(39, 146, 8, 0.1);
}

.search-input::placeholder { color: #94a3b8; }

.search-clear {
    position: absolute;
    right: 0.5rem;
    top: 50%;
    transform: translateY(-50%);
    min-height: 0;
    background: none;
    border: none;
    color: #94a3b8;
    cursor: pointer;
    padding: 0.35rem 0.5rem;
}

.search-clear:hover { color: #64748b; }

/* Estados */
.loading-state,
.empty-state {
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border-radius: 20px;
    padding: 4rem 2rem;
}

.loading-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.empty-state-content {
    max-width: 420px;
    margin: 0 auto;
    text-align: center;
}

.empty-icon {
    width: 72px;
    height: 72px;
    background: #e8f5e4;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
    font-size: 1.6rem;
    color: #279208;
}

.empty-title {
    font-size: 1.4rem;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 0.75rem;
}

.empty-description {
    color: #64748b;
    line-height: 1.6;
    margin-bottom: 1.5rem;
}

.btn-clear-filters {
    padding: 0.55rem 1.1rem;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    color: #475569;
    font-weight: 500;
    cursor: pointer;
}

.btn-clear-filters:hover { background: #f1f5f9; }

/* Tabla */
.table-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    border: 1px solid #e2e8f0;
    overflow: hidden;
}

.table-responsive { overflow-x: auto; }

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

.data-table tbody tr:last-child td { border-bottom: none; }
.data-table tbody tr:hover { background: #fafafa; }
.row-inactive { opacity: 0.6; }

.supplier-cell { min-width: 0; }

.supplier-name {
    font-weight: 600;
    color: #1e293b;
    text-decoration: none;
}

.supplier-name:hover { color: #279208; }

.supplier-legal {
    font-size: 0.8rem;
    color: #64748b;
    font-weight: 400;
}

.badge-example,
.badge-inactive {
    display: inline-block;
    margin-left: 0.4rem;
    padding: 0.1rem 0.5rem;
    border-radius: 10px;
    font-size: 0.68rem;
    font-weight: 700;
    letter-spacing: 0.03em;
    text-transform: uppercase;
    vertical-align: middle;
}

.badge-example { background: #fef3c7; color: #b45309; }
.badge-inactive { background: #f1f5f9; color: #64748b; }

.supplier-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 0.3rem;
    margin-top: 0.35rem;
}

.mini-tag {
    padding: 0.1rem 0.5rem;
    background: #f1f5f9;
    color: #475569;
    border-radius: 10px;
    font-size: 0.72rem;
    font-weight: 500;
    white-space: nowrap;
}

.mini-tag--more { background: transparent; color: #94a3b8; }

.muted { color: #94a3b8; }

.more-count {
    display: inline-block;
    white-space: nowrap;
    margin-left: 0.3rem;
    padding: 0.05rem 0.4rem;
    background: #f1f5f9;
    border-radius: 8px;
    font-size: 0.72rem;
    color: #64748b;
}

.advisor-cell {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
}

.advisor-text { min-width: 0; }
.advisor-name { font-weight: 500; color: #1e293b; }
.advisor-meta { font-size: 0.8rem; color: #64748b; }

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

.action-view:hover { background: #e8f5e4; color: #279208; }
.action-edit:hover { background: #e2e8f0; color: #475569; }
.action-danger:hover { background: #fef2f2; color: #dc2626; }

.result-count {
    margin: 0.75rem 0.25rem 0;
    font-size: 0.82rem;
    color: #94a3b8;
}

@media (max-width: 768px) {
    .search-input-wrapper { flex-basis: 100%; max-width: none; }
    .filter-select { flex: 1 1 140px; }

    .supplier-cell { text-align: left; }
    .advisor-cell { justify-content: flex-end; text-align: right; }
}
</style>
