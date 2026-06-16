<template>
    <div>
        <div class="app-page-title">
            <div class="page-title-wrapper">
                <div class="page-title-heading">
                    <div class="page-title-icon">
                        <i class="fa fa-users icon-gradient bg-mean-fruit"></i>
                    </div>
                    <div>
                        Usuarios
                        <div class="page-title-subheading text-muted">
                            Gestiona los usuarios de la plataforma
                        </div>
                    </div>
                </div>
                <div class="page-title-actions">
                    <button class="btn-new-user" @click="openCreate">
                        <i class="fa fa-plus me-1"></i> Nuevo Usuario
                    </button>
                </div>
            </div>
        </div>

        <div v-if="loading" class="loading-state">
            <div class="spinner-border text-primary"></div>
            <p class="text-muted mt-3">Cargando usuarios...</p>
        </div>

        <template v-else>
            <!-- Filtros -->
            <div class="filters-bar">
                <div class="filter-search">
                    <i class="fa fa-search filter-search-icon"></i>
                    <input v-model="search" type="text" class="filter-input" placeholder="Buscar por nombre o email..." />
                </div>
                <select v-model="filterRole" class="filter-select">
                    <option value="">Todos los roles</option>
                    <option value="master">Master</option>
                    <option value="super">Super</option>
                    <option value="coordinator">Coordinador</option>
                    <option value="technician">Tecnico</option>
                    <option value="admin">Admin</option>
                </select>
                <select v-model="filterClient" class="filter-select">
                    <option value="">Todos los clientes</option>
                    <option value="__none__">Sin cliente</option>
                    <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.business_name }}</option>
                </select>
            </div>

            <!-- Tabla -->
            <div class="section-card">
                <vue-good-table
                    :columns="columns"
                    :rows="filteredRows"
                    :search-options="{ enabled: false }"
                    :sort-options="{ enabled: false }"
                    :pagination-options="paginationOptions"
                    styleClass="vgt-table striped"
                >
                    <template #table-row="props">
                        <span v-if="props.column.field === 'name'">
                            <div class="cell-primary">{{ props.row.name }}</div>
                            <div class="cell-email">{{ props.row.email }}</div>
                        </span>

                        <span v-else-if="props.column.field === 'role'">
                            <span class="role-badge" :class="'role-' + roleName(props.row)">
                                {{ roleLabel(props.row) }}
                            </span>
                        </span>

                        <span v-else-if="props.column.field === 'company'">
                            {{ displayCompany(props.row) }}
                        </span>

                        <span v-else-if="props.column.field === 'created_at'">
                            {{ formatDate(props.row.created_at) }}
                        </span>

                        <span v-else-if="props.column.field === 'actions'">
                            <div class="action-group">
                                <button @click="openDetail(props.row)" class="action-btn" title="Ver detalle">
                                    <i class="fa fa-eye"></i>
                                </button>
                                <button @click="openEdit(props.row)" class="action-btn" title="Editar">
                                    <i class="fa fa-edit"></i>
                                </button>
                                <button @click="confirmDelete(props.row)" class="action-btn action-danger" title="Eliminar" :disabled="deletingId === props.row.id">
                                    <i v-if="deletingId === props.row.id" class="fa fa-spinner fa-spin"></i>
                                    <i v-else class="fa fa-trash"></i>
                                </button>
                            </div>
                        </span>

                        <span v-else>
                            {{ props.formattedRow[props.column.field] }}
                        </span>
                    </template>

                    <template #emptystate>
                        <div class="empty-state">
                            <i class="fa fa-users empty-icon"></i>
                            <p>No se encontraron usuarios.</p>
                        </div>
                    </template>
                </vue-good-table>
            </div>
        </template>

        <!-- Modal detalle usuario -->
        <div v-if="selectedUser" class="modal-overlay" @click.self="closeDetail">
            <div class="modal-container">
                <!-- Header -->
                <div class="modal-header">
                    <div class="modal-user-info">
                        <div class="modal-avatar">
                            <i class="fa fa-user"></i>
                        </div>
                        <div>
                            <h4 class="modal-name">{{ selectedUser.name }}</h4>
                            <p class="modal-email">{{ selectedUser.email }}</p>
                        </div>
                    </div>
                    <button @click="closeDetail" class="modal-close">
                        <i class="fa fa-times"></i>
                    </button>
                </div>

                <!-- Body -->
                <div class="modal-body">
                    <div class="detail-row">
                        <span class="detail-label">Rol</span>
                        <span class="role-badge" :class="'role-' + roleName(selectedUser)">
                            {{ roleLabel(selectedUser) }}
                        </span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Cliente</span>
                        <span class="detail-value">{{ displayCompany(selectedUser) }}</span>
                    </div>
                    <div v-if="selectedUser.phone" class="detail-row">
                        <span class="detail-label">Telefono</span>
                        <span class="detail-value">{{ selectedUser.phone }}</span>
                    </div>
                    <div v-if="selectedUser.document_number" class="detail-row">
                        <span class="detail-label">Documento</span>
                        <span class="detail-value">{{ selectedUser.document_type }} {{ selectedUser.document_number }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Estado</span>
                        <span class="status-badge" :class="selectedUser.active ? 'status-active' : 'status-inactive'">
                            {{ selectedUser.active ? 'Activo' : 'Inactivo' }}
                        </span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Registro</span>
                        <span class="detail-value">{{ formatDate(selectedUser.created_at) }}</span>
                    </div>
                </div>

                <!-- Footer -->
                <div v-if="selectedUser.client" class="modal-footer">
                    <router-link :to="{ name: 'clients.show', params: { id: selectedUser.client.id } }"
                                 class="modal-link">
                        <i class="fa fa-building me-1"></i> Ver cliente
                    </router-link>
                </div>
            </div>
        </div>

        <!-- Modal crear/editar usuario -->
        <div v-if="showForm" class="modal-overlay" @click.self="closeForm">
            <div class="modal-container modal-form">
                <div class="modal-header">
                    <div class="modal-user-info">
                        <div class="modal-avatar">
                            <i :class="editingId ? 'fa fa-user-edit' : 'fa fa-user-plus'"></i>
                        </div>
                        <div>
                            <h4 class="modal-name">{{ editingId ? 'Editar usuario' : 'Nuevo usuario' }}</h4>
                            <p class="modal-email">{{ editingId ? 'Actualiza los datos del usuario' : 'Crea un usuario para la plataforma' }}</p>
                        </div>
                    </div>
                    <button @click="closeForm" class="modal-close">
                        <i class="fa fa-times"></i>
                    </button>
                </div>

                <form @submit.prevent="submitForm">
                    <div class="modal-body">
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">Nombre <span class="req">*</span></label>
                                <input v-model="form.name" type="text" class="form-input" :class="{ 'has-error': formErrors.name }" />
                                <span v-if="formErrors.name" class="error-text">{{ formErrors.name[0] }}</span>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Email <span class="req">*</span></label>
                                <input v-model="form.email" type="email" class="form-input" :class="{ 'has-error': formErrors.email }" />
                                <span v-if="formErrors.email" class="error-text">{{ formErrors.email[0] }}</span>
                            </div>
                            <div class="form-group">
                                <label class="form-label">
                                    Contraseña <span v-if="!editingId" class="req">*</span>
                                    <span v-else class="optional">(dejar vacío para no cambiar)</span>
                                </label>
                                <input v-model="form.password" type="password" class="form-input" :class="{ 'has-error': formErrors.password }" autocomplete="new-password" />
                                <span v-if="formErrors.password" class="error-text">{{ formErrors.password[0] }}</span>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Rol <span class="req">*</span></label>
                                <select v-model="form.role" class="form-input" :class="{ 'has-error': formErrors.role }">
                                    <option value="master">Master</option>
                                    <option value="super">Super</option>
                                    <option value="coordinator">Coordinador</option>
                                    <option value="technician">Técnico</option>
                                    <option value="admin">Admin (cliente)</option>
                                </select>
                                <span v-if="formErrors.role" class="error-text">{{ formErrors.role[0] }}</span>
                            </div>

                            <div v-if="form.role === 'admin'" class="form-group form-group-full">
                                <label class="form-label">Cliente <span class="req">*</span></label>
                                <select v-model="form.client_id" class="form-input" :class="{ 'has-error': formErrors.client_id }">
                                    <option :value="null" disabled>Selecciona un cliente</option>
                                    <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.business_name }}</option>
                                </select>
                                <span v-if="formErrors.client_id" class="error-text">{{ formErrors.client_id[0] }}</span>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Teléfono</label>
                                <input v-model="form.phone" type="text" class="form-input" />
                            </div>
                            <div class="form-group form-group-doc">
                                <label class="form-label">Documento</label>
                                <div class="doc-row">
                                    <select v-model="form.document_type" class="form-input doc-type">
                                        <option :value="null">Tipo</option>
                                        <option value="CC">CC</option>
                                        <option value="CE">CE</option>
                                        <option value="NIT">NIT</option>
                                        <option value="PP">PP</option>
                                    </select>
                                    <input v-model="form.document_number" type="text" class="form-input" placeholder="Número" />
                                </div>
                            </div>

                            <div v-if="editingId" class="form-group form-group-full">
                                <label class="form-label">Estado</label>
                                <label class="active-toggle">
                                    <input v-model="form.active" type="checkbox" />
                                    <span>{{ form.active ? 'Activo' : 'Inactivo' }}</span>
                                </label>
                            </div>
                        </div>

                        <div v-if="formGeneralError" class="form-general-error">
                            <i class="fa fa-exclamation-circle me-1"></i> {{ formGeneralError }}
                        </div>
                    </div>

                    <div class="modal-footer modal-footer-actions">
                        <button type="button" class="btn-cancel" @click="closeForm">Cancelar</button>
                        <button type="submit" class="btn-save" :disabled="saving">
                            <span v-if="saving" class="spinner-sm"></span>
                            <i v-else class="fa fa-check me-1"></i>
                            {{ saving ? 'Guardando...' : (editingId ? 'Guardar cambios' : 'Crear usuario') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script>
import { VueGoodTable } from 'vue-good-table-next';
import 'vue-good-table-next/dist/vue-good-table-next.css';
import adminService from '@/services/adminService.js';
import clientService from '@/services/clientService.js';
import { useAuth } from '@/stores/auth';

export default {
    name: 'AdminUsers',

    components: {
        VueGoodTable,
    },

    data() {
        return {
            users: [],
            clients: [],
            loading: true,
            search: '',
            filterRole: '',
            filterClient: '',
            selectedUser: null,
            showForm: false,
            editingId: null,
            saving: false,
            deletingId: null,
            formErrors: {},
            formGeneralError: null,
            form: this.emptyForm(),
            roleLabels: {
                master: 'Master',
                super: 'Super',
                coordinator: 'Coordinador',
                technician: 'Tecnico',
                admin: 'Admin',
            },
            columns: [
                { label: 'Usuario', field: 'name', sortable: false },
                { label: 'Rol', field: 'role', sortable: false, width: '130px' },
                { label: 'Cliente', field: 'company', sortable: false },
                { label: 'Registro', field: 'created_at', sortable: false },
                { label: '', field: 'actions', sortable: false, tdClass: 'text-center', width: '130px' },
            ],
            paginationOptions: {
                enabled: true,
                perPage: 15,
                perPageDropdown: [10, 15, 25, 50],
                nextLabel: '',
                prevLabel: '',
                rowsPerPageLabel: 'Filas',
                ofLabel: 'de',
                allLabel: 'Todos',
            },
        };
    },

    computed: {
        auth() {
            return useAuth();
        },

        filteredRows() {
            let rows = this.users;

            if (this.filterRole) {
                rows = rows.filter(r => this.roleName(r) === this.filterRole);
            }

            if (this.filterClient === '__none__') {
                rows = rows.filter(r => !r.client);
            } else if (this.filterClient) {
                rows = rows.filter(r => r.client?.id === Number(this.filterClient));
            }

            if (this.search) {
                const term = this.search.toLowerCase();
                rows = rows.filter(r =>
                    (r.name && r.name.toLowerCase().includes(term)) ||
                    (r.email && r.email.toLowerCase().includes(term))
                );
            }

            return rows;
        },
    },

    async created() {
        try {
            const { data } = await clientService.all({ per_page: 999 });
            this.clients = (data.data || data).sort((a, b) => (a.business_name || '').localeCompare(b.business_name || ''));
        } catch { /* clientes opcionales */ }
        await this.load();
    },

    methods: {
        async load() {
            this.loading = true;
            try {
                const { data } = await adminService.getUsers({ per_page: 999 });
                this.users = data.data || data;
            } finally {
                this.loading = false;
            }
        },

        roleName(user) {
            return user.roles?.[0]?.name || 'sin';
        },

        roleLabel(user) {
            const name = this.roleName(user);
            return this.roleLabels[name] || 'Sin rol';
        },

        displayCompany(user) {
            if (user.client?.business_name) return user.client.business_name;
            return '-';
        },

        formatDate(d) {
            if (!d) return '-';
            return new Date(d).toLocaleDateString('es-CO', { year: 'numeric', month: 'short', day: 'numeric' });
        },

        openDetail(user) {
            this.selectedUser = user;
        },

        closeDetail() {
            this.selectedUser = null;
        },

        emptyForm() {
            return {
                name: '',
                email: '',
                password: '',
                role: 'technician',
                phone: '',
                document_type: null,
                document_number: '',
                client_id: null,
                active: true,
            };
        },

        openCreate() {
            this.editingId = null;
            this.formErrors = {};
            this.formGeneralError = null;
            this.form = this.emptyForm();
            this.showForm = true;
        },

        openEdit(user) {
            this.editingId = user.id;
            this.formErrors = {};
            this.formGeneralError = null;
            this.form = {
                name: user.name || '',
                email: user.email || '',
                password: '',
                role: this.roleName(user),
                phone: user.phone || '',
                document_type: user.document_type || null,
                document_number: user.document_number || '',
                client_id: user.client?.id || null,
                active: user.active ?? true,
            };
            this.showForm = true;
        },

        closeForm() {
            this.showForm = false;
        },

        async submitForm() {
            this.saving = true;
            this.formErrors = {};
            this.formGeneralError = null;

            // Payload: roles internos -> company de la empresa; admin -> cliente
            const isAdmin = this.form.role === 'admin';
            const payload = {
                name: this.form.name,
                email: this.form.email,
                role: this.form.role,
                phone: this.form.phone || null,
                document_type: this.form.document_type || null,
                document_number: this.form.document_number || null,
                client_id: isAdmin ? this.form.client_id : null,
                company_id: isAdmin ? null : (this.auth.state.user?.company_id || null),
            };
            if (this.form.password) payload.password = this.form.password;
            if (this.editingId) payload.active = this.form.active;

            try {
                if (this.editingId) {
                    await adminService.updateUser(this.editingId, payload);
                } else {
                    await adminService.createUser(payload);
                }
                this.showForm = false;
                await this.load();
            } catch (err) {
                if (err.response?.status === 422) {
                    this.formErrors = err.response.data.errors || {};
                    this.formGeneralError = err.response.data.message || 'Revisa los campos marcados.';
                } else {
                    this.formGeneralError = err.response?.data?.message || 'Error al guardar el usuario.';
                }
            } finally {
                this.saving = false;
            }
        },

        async confirmDelete(user) {
            if (!confirm(`¿Eliminar al usuario "${user.name}"? Esta acción no se puede deshacer.`)) return;
            this.deletingId = user.id;
            try {
                await adminService.deleteUser(user.id);
                await this.load();
            } catch (err) {
                alert(err.response?.data?.message || 'No se pudo eliminar el usuario.');
            } finally {
                this.deletingId = null;
            }
        },
    },
};
</script>

<style scoped>
/* Etiquetas por columna para las cards en móvil (≤768px) — scoped a esta vista.
   Cols: 1 Usuario(título) · 2 Rol · 3 Cliente · 4 Registro · 5 Acciones */
@media (max-width: 768px) {
    :deep(.vgt-table tbody td:nth-of-type(2))::before { content: 'Rol'; }
    :deep(.vgt-table tbody td:nth-of-type(3))::before { content: 'Cliente'; }
    :deep(.vgt-table tbody td:nth-of-type(4))::before { content: 'Registro'; }

    /* Contenedor transparente: las cards van sobre el fondo del body */
    .section-card {
        background: transparent;
        border: none;
        box-shadow: none;
    }
}

/* Filtros */
.filters-bar {
    display: flex;
    gap: 0.75rem;
    margin-bottom: 1rem;
    flex-wrap: wrap;
}

.filter-search {
    position: relative;
    flex: 1;
    min-width: 220px;
}

.filter-search-icon {
    position: absolute;
    left: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    font-size: 0.85rem;
    pointer-events: none;
}

.filter-input {
    width: 100%;
    padding: 0.5rem 0.75rem 0.5rem 2.25rem;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-size: 0.9rem;
    background: white;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.filter-input:focus {
    outline: none;
    border-color: #279208;
    box-shadow: 0 0 0 3px rgba(39, 146, 8, 0.1);
}

.filter-select {
    padding: 0.5rem 0.75rem;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-size: 0.9rem;
    background: white;
    color: #334155;
    cursor: pointer;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.filter-select:focus {
    outline: none;
    border-color: #279208;
    box-shadow: 0 0 0 3px rgba(39, 146, 8, 0.1);
}

/* Card */
.section-card {
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    overflow: hidden;
}

/* Celdas */
.cell-primary { font-weight: 600; color: #1e293b; }
.cell-email { font-size: 0.8rem; color: #94a3b8; }
.text-center { text-align: center; }

/* Badges rol */
.role-badge {
    display: inline-block;
    padding: 0.2rem 0.6rem;
    border-radius: 6px;
    font-size: 0.8rem;
    font-weight: 600;
    white-space: nowrap;
}

.role-master {
    background: linear-gradient(135deg, #d4edda, #e8f5e4);
    color: #279208;
}

.role-coordinator {
    background: linear-gradient(135deg, #ede9fe, #e8e0fb);
    color: #7c3aed;
}

.role-technician {
    background: linear-gradient(135deg, #fef3c7, #fde68a);
    color: #b45309;
}

.role-admin {
    background: linear-gradient(135deg, #dbeafe, #e0f2fe);
    color: #2563eb;
}

.role-super {
    background: linear-gradient(135deg, #fee2e2, #fde4e4);
    color: #ba2831;
}

/* Botón nuevo usuario */
.btn-new-user {
    display: inline-flex;
    align-items: center;
    padding: 0.55rem 1.1rem;
    font-size: 0.9rem;
    font-weight: 500;
    background: #279208;
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: background 0.2s;
}

.btn-new-user:hover { background: #1f7506; }

/* Grupo de acciones en tabla */
.action-group {
    display: inline-flex;
    gap: 0.35rem;
    justify-content: center;
}

.action-danger { color: #dc2626; }
.action-danger:hover { background: #fef2f2 !important; color: #b91c1c !important; }
.action-btn:disabled { opacity: 0.5; cursor: not-allowed; }

.role-sin {
    background: #fef2f2;
    color: #dc2626;
}

/* Status badge */
.status-badge {
    display: inline-block;
    font-size: 0.8rem;
    font-weight: 500;
    padding: 0.2rem 0.6rem;
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

/* Boton accion tabla */
.action-btn {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
    background: white;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
    color: #64748b;
    text-decoration: none;
    transition: all 0.2s;
}

.action-btn:hover { background: #f1f5f9; color: #279208; }

/* Empty & loading */
.empty-state { text-align: center; padding: 3rem 1rem; color: #94a3b8; }
.empty-icon { font-size: 2rem; margin-bottom: 0.75rem; display: block; opacity: 0.4; }
.empty-state p { margin: 0; font-size: 0.95rem; }
.loading-state { text-align: center; padding: 4rem 0; }

/* ───── Modal detalle ───── */
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.6);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 99999;
    padding: 1rem;
}

.modal-container {
    background: white;
    border-radius: 16px;
    width: min(460px, 95vw);
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
    overflow: hidden;
}

.modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.25rem 1.5rem;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
}

.modal-user-info {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.modal-avatar {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: linear-gradient(135deg, #d4edda, #e8f5e4);
    color: #279208;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    flex-shrink: 0;
}

.modal-name {
    font-size: 1rem;
    font-weight: 600;
    color: #1e293b;
    margin: 0;
    line-height: 1.3;
}

.modal-email {
    font-size: 0.8rem;
    color: #94a3b8;
    margin: 0;
}

.modal-close {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    border: none;
    background: #f1f5f9;
    color: #64748b;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.modal-close:hover { background: #e2e8f0; color: #475569; }

.modal-body { padding: 1.25rem 1.5rem; }

.detail-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.6rem 0;
    border-bottom: 1px solid #f1f5f9;
    font-size: 0.9rem;
}

.detail-row:last-child { border-bottom: none; }

.detail-label { color: #64748b; }
.detail-value { font-weight: 500; color: #1e293b; }

.modal-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem 1.5rem;
    border-top: 1px solid #e2e8f0;
    background: #f8fafc;
}

.modal-link {
    font-size: 0.85rem;
    color: #279208;
    text-decoration: none;
    font-weight: 500;
    transition: color 0.2s;
}

.modal-link:hover { color: #1f7506; text-decoration: underline; }

/* ───── Modal formulario ───── */
.modal-form { width: min(600px, 95vw); }

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.form-group { display: flex; flex-direction: column; }
.form-group-full { grid-column: 1 / -1; }

.form-label {
    font-size: 0.85rem;
    font-weight: 500;
    color: #374151;
    margin-bottom: 0.4rem;
}

.req { color: #ba2831; }
.optional { color: #94a3b8; font-weight: 400; font-size: 0.78rem; }

.form-input {
    width: 100%;
    padding: 0.55rem 0.75rem;
    font-size: 0.9rem;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    background: white;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.form-input:focus {
    outline: none;
    border-color: #279208;
    box-shadow: 0 0 0 3px rgba(39, 146, 8, 0.1);
}

.form-input.has-error { border-color: #ba2831; }

.error-text {
    font-size: 0.78rem;
    color: #ba2831;
    margin-top: 0.3rem;
}

.doc-row { display: flex; gap: 0.5rem; }
.doc-type { max-width: 90px; }

.active-toggle {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.9rem;
    color: #334155;
    cursor: pointer;
}

.form-general-error {
    margin-top: 1rem;
    padding: 0.7rem 0.9rem;
    background: #fef2f2;
    border: 1px solid #fecaca;
    border-radius: 8px;
    color: #dc2626;
    font-size: 0.85rem;
}

.modal-footer-actions {
    justify-content: flex-end;
    gap: 0.6rem;
}

.btn-cancel {
    padding: 0.55rem 1.1rem;
    font-size: 0.88rem;
    font-weight: 500;
    background: #f1f5f9;
    color: #475569;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    cursor: pointer;
}

.btn-cancel:hover { background: #e2e8f0; }

.btn-save {
    display: inline-flex;
    align-items: center;
    padding: 0.55rem 1.2rem;
    font-size: 0.88rem;
    font-weight: 500;
    background: #279208;
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
}

.btn-save:hover:not(:disabled) { background: #1f7506; }
.btn-save:disabled { opacity: 0.7; cursor: not-allowed; }

.spinner-sm {
    width: 14px;
    height: 14px;
    border: 2px solid rgba(255, 255, 255, 0.4);
    border-top-color: white;
    border-radius: 50%;
    animation: spin 0.7s linear infinite;
    margin-right: 0.5rem;
}

@keyframes spin { to { transform: rotate(360deg); } }

@media (max-width: 640px) {
    .filter-select { flex: 1; min-width: 0; }
    .form-grid { grid-template-columns: 1fr; }
}
</style>

<style>
/* vue-good-table overrides */
.vgt-wrap,
.vgt-inner-wrap { border-radius: 0 !important; box-shadow: none !important; }
.vgt-responsive { overflow-x: auto; }
.vgt-table.striped { font-size: 0.9rem; border-collapse: collapse; border: none !important; }

.vgt-table.striped thead th {
    background: #f8fafc !important;
    color: #64748b !important;
    font-size: 0.75rem !important;
    font-weight: 600 !important;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    border-bottom: 1px solid #e2e8f0 !important;
    padding: 1rem 1.25rem !important;
    cursor: default !important;
}

.vgt-table.striped thead th span::after { display: none !important; }
.vgt-table.striped thead th.sorting-asc span::after,
.vgt-table.striped thead th.sorting-desc span::after { display: none !important; }

.vgt-table.striped td {
    padding: 0.75rem 1.25rem !important;
    color: #334155 !important;
    border-bottom: 1px solid #f1f5f9 !important;
}

.vgt-table.striped tbody tr:last-child td {
    border-bottom: 1px solid #e2e8f0 !important;
}

.vgt-table.striped tbody tr:hover td {
    background: #fafbfc !important;
}

.vgt-wrap__footer {
    border-top: none !important;
    border-bottom: none !important;
    background: #f8fafc !important;
    padding: 0.75rem 1.25rem !important;
    font-size: 0.85rem !important;
    color: #64748b !important;
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
}

.vgt-wrap__footer .footer__row-count {
    position: relative;
    display: flex;
    align-items: center;
}
.vgt-wrap__footer .footer__row-count::after { display: none !important; }
.vgt-wrap__footer .footer__row-count__label {
    font-size: 0.85rem !important;
    font-weight: 400 !important;
    color: #64748b !important;
}
.vgt-wrap__footer .footer__row-count__select {
    font-size: 0.85rem !important;
    font-weight: 400 !important;
    color: #334155 !important;
    background: white !important;
    border: 1px solid #e2e8f0 !important;
    border-radius: 6px !important;
    padding: 0.25rem 0.5rem !important;
    margin-left: 0.4rem;
    cursor: pointer;
    -webkit-appearance: auto !important;
    -moz-appearance: auto !important;
    appearance: auto !important;
}
.vgt-wrap__footer .footer__row-count__select:focus {
    border-color: #279208 !important;
    outline: none;
}

.vgt-wrap__footer .footer__navigation {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.vgt-wrap__footer .footer__navigation > button:first-of-type {
    margin-right: 0 !important;
}
.vgt-wrap__footer .footer__navigation__page-info {
    font-size: 0.85rem !important;
    font-weight: 400 !important;
    color: #64748b !important;
    margin: 0 !important;
}
.vgt-wrap__footer .footer__navigation__page-info__current-entry {
    width: 32px !important;
    text-align: center !important;
    font-weight: 500 !important;
    color: #1e293b !important;
    background: white !important;
    border: 1px solid #e2e8f0 !important;
    border-radius: 6px !important;
    padding: 0.2rem 0 !important;
    margin: 0 0.25rem !important;
}
.vgt-wrap__footer .footer__navigation__page-info__current-entry:focus {
    border-color: #279208 !important;
    outline: none;
    box-shadow: 0 0 0 2px rgba(39, 146, 8, 0.1);
}

.vgt-wrap__footer .footer__navigation__page-btn {
    background: white !important;
    border: 1px solid #e2e8f0 !important;
    border-radius: 6px !important;
    padding: 0.2rem 0.45rem !important;
    cursor: pointer;
    transition: all 0.15s;
    line-height: 1 !important;
}
.vgt-wrap__footer .footer__navigation__page-btn .chevron {
    width: 18px !important;
    height: 18px !important;
}
.vgt-wrap__footer .footer__navigation__page-btn .chevron::after {
    margin-top: -5px !important;
    border-top-width: 5px !important;
    border-bottom-width: 5px !important;
}
.vgt-wrap__footer .footer__navigation__page-btn .chevron.left::after {
    border-right-width: 5px !important;
    border-right-color: #279208 !important;
}
.vgt-wrap__footer .footer__navigation__page-btn .chevron.right::after {
    border-left-width: 5px !important;
    border-left-color: #279208 !important;
}
.vgt-wrap__footer .footer__navigation__page-btn:hover:not(.disabled) {
    background: #e8f5e4 !important;
    border-color: #c4b5fd !important;
}
.vgt-wrap__footer .footer__navigation__page-btn.disabled {
    opacity: 0.4 !important;
    cursor: not-allowed !important;
}
.vgt-wrap__footer .footer__navigation__page-btn.disabled .chevron.left::after {
    border-right-color: #94a3b8 !important;
}
.vgt-wrap__footer .footer__navigation__page-btn.disabled .chevron.right::after {
    border-left-color: #94a3b8 !important;
}

.vgt-global-search { display: none !important; }
</style>
