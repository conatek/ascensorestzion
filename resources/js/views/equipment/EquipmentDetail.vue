<template>
    <div>
        <!-- Estado de carga -->
        <div v-if="loading" class="loading-state">
            <div class="spinner-border text-primary"></div>
            <p class="text-muted mt-3">Cargando equipo...</p>
        </div>

        <template v-else-if="equipment">
            <!-- Breadcrumb -->
            <nav class="breadcrumb-bar" v-if="equipment.site">
                <span class="breadcrumb-item">
                    <i class="fa fa-user-tie me-1"></i>
                    {{ equipment.site.client?.business_name || '-' }}
                </span>
                <i class="fa fa-chevron-right breadcrumb-sep"></i>
                <span class="breadcrumb-item">
                    <i class="fa fa-building me-1"></i>
                    {{ equipment.site.name }}
                </span>
                <i class="fa fa-chevron-right breadcrumb-sep"></i>
                <span class="breadcrumb-item breadcrumb-active">
                    <i class="fa fa-arrows-alt-v me-1"></i>
                    {{ equipment.internal_code }}
                </span>
            </nav>

            <!-- Header card -->
            <div class="detail-header-card">
                <div class="header-main">
                    <div class="header-icon">
                        <i class="fa fa-arrows-alt-v"></i>
                    </div>
                    <div class="header-info">
                        <h2 class="header-title">{{ equipment.internal_code }}</h2>
                        <div class="header-badges">
                            <span class="type-badge">{{ typeLabels[equipment.equipment_type] || equipment.equipment_type }}</span>
                            <span class="status-badge" :class="'status-' + equipment.status">
                                {{ statusLabels[equipment.status] || equipment.status }}
                            </span>
                            <span class="contract-badge" v-if="equipment.contract_type">
                                {{ contractLabels[equipment.contract_type] || equipment.contract_type }}
                            </span>
                        </div>
                        <p class="header-subtitle" v-if="equipment.brand || equipment.model">
                            {{ equipment.brand }}<span v-if="equipment.brand && equipment.model"> / </span>{{ equipment.model }}
                            <span v-if="equipment.serial_number" class="ms-2 text-muted">| S/N: {{ equipment.serial_number }}</span>
                        </p>
                    </div>
                </div>
                <div class="header-actions">
                    <div class="dropdown" v-if="auth.isInternal()">
                        <button class="btn-action btn-report dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="fa fa-file-medical me-1"></i> Nuevo Reporte
                        </button>
                        <ul class="dropdown-menu">
                            <li>
                                <router-link class="dropdown-item" :to="{ name: 'reports.create', query: { type: 'RSTP', equipment_id: equipment.id } }">
                                    <span class="report-type-dot dot-rstp"></span> RSTP - Preventivo
                                </router-link>
                            </li>
                            <li>
                                <router-link class="dropdown-item" :to="{ name: 'reports.create', query: { type: 'RSTC', equipment_id: equipment.id } }">
                                    <span class="report-type-dot dot-rstc"></span> RSTC - Correctivo
                                </router-link>
                            </li>
                            <li>
                                <router-link class="dropdown-item" :to="{ name: 'reports.create', query: { type: 'RSTE', equipment_id: equipment.id } }">
                                    <span class="report-type-dot dot-rste"></span> RSTE - Especial
                                </router-link>
                            </li>
                        </ul>
                    </div>
                    <router-link v-if="auth.isInternal()" :to="{ name: 'equipment.edit', params: { id: equipment.id } }" class="btn-action btn-edit-action">
                        <i class="fa fa-edit me-1"></i> Editar
                    </router-link>
                    <router-link :to="auth.isAdmin() ? '/portal/equipos' : { name: 'equipment.index' }" class="btn-action btn-back">
                        <i class="fa fa-arrow-left me-1"></i> Volver
                    </router-link>
                </div>
            </div>

            <!-- Tab navigation -->
            <div class="tab-nav">
                <button
                    v-for="tab in visibleTabs"
                    :key="tab.key"
                    class="tab-btn"
                    :class="{ active: activeTab === tab.key }"
                    @click="switchTab(tab.key)"
                >
                    <i :class="tab.icon" class="me-1"></i>
                    {{ tab.label }}
                </button>
            </div>

            <!-- Tab content -->
            <div class="tab-content-area">

                <!-- Tab 1: Ficha Tecnica -->
                <div v-if="activeTab === 'ficha'">
                    <div class="info-grid">
                        <!-- Identificacion -->
                        <div class="info-card">
                            <div class="info-header">
                                <i class="fa fa-fingerprint"></i>
                                <span>Identificacion</span>
                            </div>
                            <div class="info-body">
                                <div class="detail-item">
                                    <span class="detail-label">Codigo interno</span>
                                    <span class="detail-value">{{ equipment.internal_code }}</span>
                                </div>
                                <div class="detail-item" v-if="equipment.customer_code">
                                    <span class="detail-label">Codigo del cliente</span>
                                    <span class="detail-value">{{ equipment.customer_code }}</span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Tipo</span>
                                    <span class="detail-value">
                                        <span class="type-badge">{{ typeLabels[equipment.equipment_type] || equipment.equipment_type }}</span>
                                    </span>
                                </div>
                                <div class="detail-item" v-if="equipment.brand">
                                    <span class="detail-label">Marca</span>
                                    <span class="detail-value">{{ equipment.brand }}</span>
                                </div>
                                <div class="detail-item" v-if="equipment.model">
                                    <span class="detail-label">Modelo</span>
                                    <span class="detail-value">{{ equipment.model }}</span>
                                </div>
                                <div class="detail-item" v-if="equipment.serial_number">
                                    <span class="detail-label">Numero de serie</span>
                                    <span class="detail-value">{{ equipment.serial_number }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Ubicacion y Contrato -->
                        <div class="info-card">
                            <div class="info-header">
                                <i class="fa fa-map-marker-alt"></i>
                                <span>Ubicacion y Contrato</span>
                            </div>
                            <div class="info-body">
                                <div class="detail-item" v-if="equipment.site?.client">
                                    <span class="detail-label">Cliente</span>
                                    <span class="detail-value">{{ equipment.site.client.business_name }}</span>
                                </div>
                                <div class="detail-item" v-if="equipment.site">
                                    <span class="detail-label">Sede</span>
                                    <span class="detail-value">{{ equipment.site.name }}</span>
                                </div>
                                <div class="detail-item" v-if="equipment.contract_type">
                                    <span class="detail-label">Tipo de contrato</span>
                                    <span class="detail-value">{{ contractLabels[equipment.contract_type] || equipment.contract_type }}</span>
                                </div>
                                <div class="detail-item" v-if="equipment.contract_start">
                                    <span class="detail-label">Inicio de contrato</span>
                                    <span class="detail-value">{{ formatDate(equipment.contract_start) }}</span>
                                </div>
                                <div class="detail-item" v-if="equipment.contract_end">
                                    <span class="detail-label">Fin de contrato</span>
                                    <span class="detail-value">{{ formatDate(equipment.contract_end) }}</span>
                                </div>
                                <div class="detail-item" v-if="equipment.maintenance_frequency_days">
                                    <span class="detail-label">Frecuencia de mantenimiento</span>
                                    <span class="detail-value">{{ equipment.maintenance_frequency_days }} días</span>
                                </div>
                            </div>
                        </div>

                        <!-- Capacidad y Estado -->
                        <div class="info-card">
                            <div class="info-header">
                                <i class="fa fa-tachometer-alt"></i>
                                <span>Capacidad y Estado</span>
                            </div>
                            <div class="info-body">
                                <div class="detail-item" v-if="equipment.capacity_kg">
                                    <span class="detail-label">Capacidad (kg)</span>
                                    <span class="detail-value">{{ equipment.capacity_kg }} kg</span>
                                </div>
                                <div class="detail-item" v-if="equipment.capacity_persons">
                                    <span class="detail-label">Capacidad (personas)</span>
                                    <span class="detail-value">{{ equipment.capacity_persons }}</span>
                                </div>
                                <div class="detail-item" v-if="equipment.stops">
                                    <span class="detail-label">Paradas</span>
                                    <span class="detail-value">{{ equipment.stops }}</span>
                                </div>
                                <div class="detail-item" v-if="equipment.speed_mps">
                                    <span class="detail-label">Velocidad</span>
                                    <span class="detail-value">{{ equipment.speed_mps }} m/s</span>
                                </div>
                                <div class="detail-item" v-if="equipment.installation_date">
                                    <span class="detail-label">Fecha de instalacion</span>
                                    <span class="detail-value">{{ formatDate(equipment.installation_date) }}</span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Estado</span>
                                    <span class="detail-value">
                                        <span class="status-badge" :class="'status-' + equipment.status">
                                            {{ statusLabels[equipment.status] || equipment.status }}
                                        </span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notas -->
                    <div class="info-card" v-if="equipment.notes">
                        <div class="info-header">
                            <i class="fa fa-sticky-note"></i>
                            <span>Notas</span>
                        </div>
                        <div class="info-body">
                            <p class="notes-text">{{ equipment.notes }}</p>
                        </div>
                    </div>
                </div>

                <!-- Tab 2: Hoja de Vida -->
                <div v-if="activeTab === 'vida'">
                    <!-- Filtros -->
                    <div class="filter-bar">
                        <div class="filter-group">
                            <label class="filter-label">Tipo</label>
                            <select v-model="historyFilters.type" class="form-select form-select-sm" @change="applyHistoryFilters">
                                <option value="">Todos</option>
                                <option value="RSTP">RSTP - Preventivo</option>
                                <option value="RSTC">RSTC - Correctivo</option>
                                <option value="RSTE">RSTE - Emergencia</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label class="filter-label">Desde</label>
                            <input type="date" v-model="historyFilters.date_from" class="form-control form-control-sm" @change="applyHistoryFilters">
                        </div>
                        <div class="filter-group">
                            <label class="filter-label">Hasta</label>
                            <input type="date" v-model="historyFilters.date_to" class="form-control form-control-sm" @change="applyHistoryFilters">
                        </div>
                    </div>

                    <!-- Loading -->
                    <div v-if="historyLoading" class="loading-state loading-state-sm">
                        <div class="spinner-border spinner-border-sm text-primary"></div>
                        <p class="text-muted mt-2 mb-0">Cargando historial...</p>
                    </div>

                    <!-- Timeline -->
                    <div v-else-if="filteredHistory.length" class="timeline">
                        <div v-for="entry in filteredHistory" :key="entry.id" class="timeline-item">
                            <div class="timeline-dot" :class="'dot-' + (entry.report_type || '').toLowerCase()"></div>
                            <div class="timeline-content">
                                <div class="timeline-header">
                                    <span class="timeline-date">{{ formatDate(entry.service_date) }}</span>
                                    <span class="report-type-badge" :class="'badge-' + (entry.report_type || '').toLowerCase()">
                                        {{ entry.report_type }}
                                    </span>
                                    <router-link v-if="entry.id" :to="{ name: 'reports.show', params: { id: entry.id } }" class="timeline-report-link">
                                        #{{ entry.report_number || entry.id }}
                                    </router-link>
                                </div>
                                <div class="timeline-body">
                                    <span v-if="entry.technician?.name" class="timeline-tech">
                                        <i class="fa fa-hard-hat me-1"></i>{{ entry.technician?.name }}
                                    </span>
                                    <p v-if="entry.conclusion_notes" class="timeline-conclusion">{{ truncate(entry.conclusion_notes, 150) }}</p>
                                </div>
                                <div class="timeline-footer">
                                    <span v-if="entry.status" class="status-badge" :class="'status-' + entry.status">
                                        {{ entry.status }}
                                    </span>
                                    <span v-if="entry.equipment_functional !== undefined && entry.equipment_functional !== null" class="functional-badge" :class="entry.equipment_functional ? 'functional-yes' : 'functional-no'">
                                        <i :class="entry.equipment_functional ? 'fa fa-check-circle' : 'fa fa-times-circle'" class="me-1"></i>
                                        {{ entry.equipment_functional ? 'Funcional' : 'No funcional' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Empty state -->
                    <div v-else class="empty-state">
                        <i class="fa fa-clipboard-list empty-icon"></i>
                        <p>No se encontraron reportes en el historial.</p>
                    </div>
                </div>

                <!-- Tab 3: Mantenimientos -->
                <div v-if="activeTab === 'mantenimientos'">
                    <div v-if="historyLoading" class="loading-state loading-state-sm">
                        <div class="spinner-border spinner-border-sm text-primary"></div>
                        <p class="text-muted mt-2 mb-0">Cargando mantenimientos...</p>
                    </div>

                    <template v-else>
                        <!-- Resumen -->
                        <div class="maint-summary">
                            <div class="maint-summary-item">
                                <span class="maint-summary-label">Ultimo mantenimiento</span>
                                <span class="maint-summary-value">{{ lastRstpDate ? formatDateShort(lastRstpDate) : 'Sin registro' }}</span>
                            </div>
                            <div class="maint-summary-item">
                                <span class="maint-summary-label">Proximo esperado</span>
                                <span class="maint-summary-value" :class="{ 'text-danger': isOverdue }">{{ nextMaintenanceDate || 'N/A' }}</span>
                            </div>
                            <div class="maint-summary-item">
                                <span class="maint-summary-label">Frecuencia</span>
                                <span class="maint-summary-value">Cada {{ equipment.maintenance_frequency_days || '?' }} días</span>
                            </div>
                            <div class="maint-summary-item">
                                <span class="maint-summary-label">Cumplimiento este año</span>
                                <span class="maint-summary-value">
                                    <span class="compliance-badge" :class="complianceClass">
                                        {{ rstpThisYear }} de {{ expectedThisYear }} ({{ compliancePercent }}%)
                                    </span>
                                </span>
                            </div>
                        </div>

                        <!-- Tabla RSTP -->
                        <div class="info-card">
                            <div class="info-header">
                                <i class="fa fa-wrench"></i>
                                <span>Historial de Mantenimientos Preventivos</span>
                            </div>
                            <div class="info-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0 data-table" v-if="rstpReports.length">
                                        <thead>
                                            <tr>
                                                <th>Fecha</th>
                                                <th>Reporte</th>
                                                <th>Tecnico</th>
                                                <th>Estado</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="r in rstpReports" :key="r.id">
                                                <td data-label="Fecha">{{ formatDateShort(r.service_date) }}</td>
                                                <td data-label="Reporte">
                                                    <router-link v-if="r.id" :to="{ name: 'reports.show', params: { id: r.id } }" class="report-link">
                                                        {{ r.report_number || '#' + r.id }}
                                                    </router-link>
                                                    <span v-else>-</span>
                                                </td>
                                                <td data-label="Tecnico">{{ r.technician?.name || '-' }}</td>
                                                <td data-label="Estado">
                                                    <span v-if="r.status" class="report-status-badge" :class="'rs-' + r.status">
                                                        {{ reportStatusLabels[r.status] || r.status }}
                                                    </span>
                                                    <span v-else>-</span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <div v-else class="empty-state">
                                        <i class="fa fa-wrench empty-icon"></i>
                                        <p>No hay mantenimientos preventivos registrados.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Tab 4: Adjuntos -->
                <div v-if="activeTab === 'adjuntos'">
                    <!-- Upload form -->
                    <div class="info-card mb-3">
                        <div class="info-header">
                            <i class="fa fa-upload"></i>
                            <span>Subir Archivo</span>
                        </div>
                        <div class="info-body">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-5">
                                    <label class="form-label">Archivo</label>
                                    <input type="file" class="form-control form-control-sm" @change="onFileSelect" ref="fileInput">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Tipo</label>
                                    <select v-model="uploadForm.type" class="form-select form-select-sm">
                                        <option value="foto">Foto</option>
                                        <option value="plano">Plano</option>
                                        <option value="certificado">Certificado</option>
                                        <option value="manual">Manual</option>
                                        <option value="otro">Otro</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <button class="btn-action btn-upload w-100" :disabled="!uploadForm.file || uploading" @click="uploadAttachment">
                                        <i class="fa fa-cloud-upload-alt me-1"></i>
                                        {{ uploading ? 'Subiendo...' : 'Subir' }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Attachments table -->
                    <div class="info-card">
                        <div class="info-header">
                            <i class="fa fa-paperclip"></i>
                            <span>Archivos Adjuntos</span>
                        </div>
                        <div class="info-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0 data-table" v-if="attachments.length">
                                    <thead>
                                        <tr>
                                            <th>Nombre</th>
                                            <th>Tipo</th>
                                            <th>Fecha</th>
                                            <th>Subido por</th>
                                            <th class="text-end">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="att in attachments" :key="att.id">
                                            <td data-label="Nombre">
                                                <i class="fa fa-file me-1 text-muted"></i>{{ att.original_name || 'Archivo' }}
                                            </td>
                                            <td data-label="Tipo">
                                                <span class="att-type-badge" :class="'att-' + (att.type || 'otro')">
                                                    {{ attTypeLabels[att.type] || att.type || 'Otro' }}
                                                </span>
                                            </td>
                                            <td data-label="Fecha">{{ formatDateShort(att.created_at) }}</td>
                                            <td data-label="Subido por">{{ att.uploader?.name || '-' }}</td>
                                            <td class="text-end cell-actions" data-label="Acciones">
                                                <button class="btn btn-sm btn-outline-danger" @click="confirmDeleteAttachment(att)" :disabled="att.deleting">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div v-else class="empty-state">
                                    <i class="fa fa-paperclip empty-icon"></i>
                                    <p>No hay archivos adjuntos.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab 5: Estadísticas -->
                <div v-if="activeTab === 'estadisticas'">
                    <div v-if="historyLoading" class="loading-state loading-state-sm">
                        <div class="spinner-border spinner-border-sm text-primary"></div>
                        <p class="text-muted mt-2 mb-0">Cargando estadísticas...</p>
                    </div>

                    <template v-else>
                        <p class="text-muted mb-3" style="font-size: 0.85rem;">
                            <i class="fa fa-info-circle me-1"></i>
                            Datos acumulados de todo el historial del equipo ({{ statsCount.total }} reportes registrados).
                        </p>

                        <div class="stats-grid">
                            <div class="stat-card stat-rstp">
                                <div class="stat-icon"><i class="fa fa-wrench"></i></div>
                                <div class="stat-info">
                                    <span class="stat-number">{{ statsCount.RSTP }}</span>
                                    <span class="stat-label">Preventivos (RSTP)</span>
                                </div>
                            </div>
                            <div class="stat-card stat-rstc">
                                <div class="stat-icon"><i class="fa fa-tools"></i></div>
                                <div class="stat-info">
                                    <span class="stat-number">{{ statsCount.RSTC }}</span>
                                    <span class="stat-label">Correctivos (RSTC)</span>
                                </div>
                            </div>
                            <div class="stat-card stat-rste">
                                <div class="stat-icon"><i class="fa fa-exclamation-triangle"></i></div>
                                <div class="stat-info">
                                    <span class="stat-number">{{ statsCount.RSTE }}</span>
                                    <span class="stat-label">Especiales (RSTE)</span>
                                </div>
                            </div>
                            <div class="stat-card stat-total">
                                <div class="stat-icon"><i class="fa fa-chart-bar"></i></div>
                                <div class="stat-info">
                                    <span class="stat-number">{{ statsCount.total }}</span>
                                    <span class="stat-label">Total General</span>
                                </div>
                            </div>
                        </div>

                        <div class="info-card mt-3">
                            <div class="info-header">
                                <i class="fa fa-clock"></i>
                                <span>Últimos Reportes por Tipo</span>
                            </div>
                            <div class="info-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0 data-table">
                                        <thead>
                                            <tr>
                                                <th>Tipo</th>
                                                <th>Reporte</th>
                                                <th>Fecha</th>
                                                <th>Técnico</th>
                                                <th>Estado</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="type in ['RSTP', 'RSTC', 'RSTE']" :key="type">
                                                <td data-label="Tipo">
                                                    <span class="report-type-badge" :class="'badge-' + type.toLowerCase()">{{ type }}</span>
                                                    <span class="ms-1 text-muted" style="font-size:0.8rem;">{{ {RSTP:'Preventivo',RSTC:'Correctivo',RSTE:'Especial'}[type] }}</span>
                                                </td>
                                                <td v-if="statsLast[type]" data-label="Reporte">
                                                    <router-link :to="{ name: 'reports.show', params: { id: statsLast[type].id } }" class="report-link">
                                                        {{ statsLast[type].report_number }}
                                                    </router-link>
                                                </td>
                                                <td v-else class="text-muted" data-label="Reporte">Sin registro</td>
                                                <td data-label="Fecha">{{ statsLast[type] ? formatDateShort(statsLast[type].service_date) : '-' }}</td>
                                                <td data-label="Técnico">{{ statsLast[type]?.technician_name || '-' }}</td>
                                                <td data-label="Estado">
                                                    <span v-if="statsLast[type]?.status" class="report-status-badge" :class="'rs-' + statsLast[type].status">
                                                        {{ reportStatusLabels[statsLast[type].status] || statsLast[type].status }}
                                                    </span>
                                                    <span v-else>-</span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Tab 6: Código QR -->
                <div v-if="activeTab === 'qr'">
                    <div class="qr-layout">
                        <!-- Visor del QR -->
                        <div class="info-card qr-viewer-card">
                            <div class="info-header">
                                <i class="fa fa-qrcode"></i>
                                <span>Código QR del equipo</span>
                            </div>
                            <div class="info-body qr-viewer-body">
                                <div v-if="qrLoading" class="loading-state loading-state-sm">
                                    <div class="spinner-border spinner-border-sm text-primary"></div>
                                    <p class="text-muted mt-2 mb-0">Generando código QR...</p>
                                </div>

                                <div v-else-if="qrError" class="empty-state">
                                    <i class="fa fa-exclamation-triangle empty-icon text-danger"></i>
                                    <p>{{ qrError }}</p>
                                    <button class="btn-action btn-edit-action mt-2" @click="loadQr">
                                        <i class="fa fa-redo me-1"></i> Reintentar
                                    </button>
                                </div>

                                <div v-else-if="qrImage" class="qr-display">
                                    <img :src="qrImage" :alt="'QR ' + equipment.internal_code" class="qr-image" />
                                    <div class="qr-code-label">{{ equipment.internal_code }}</div>
                                    <p class="qr-url">{{ qrContent }}</p>
                                    <div class="qr-actions">
                                        <button class="btn-action btn-report" @click="printQr">
                                            <i class="fa fa-print me-1"></i> Imprimir
                                        </button>
                                        <a class="btn-action btn-edit-action" :href="qrImage" :download="'qr-' + equipment.internal_code + '.png'">
                                            <i class="fa fa-download me-1"></i> Descargar
                                        </a>
                                        <button class="btn-action btn-back" @click="loadQr">
                                            <i class="fa fa-redo me-1"></i> Regenerar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Instrucciones -->
                        <div class="info-card qr-help-card">
                            <div class="info-header">
                                <i class="fa fa-info-circle"></i>
                                <span>¿Cómo se usa?</span>
                            </div>
                            <div class="info-body">
                                <ol class="qr-steps">
                                    <li>Imprime el código y pégalo de forma visible en el ascensor o en su cuarto de máquinas.</li>
                                    <li>El técnico, al llegar, abre <strong>Registrar Llegada</strong> y escanea este QR con la cámara.</li>
                                    <li>El check-in queda registrado automáticamente con el equipo <strong>{{ equipment.internal_code }}</strong>.</li>
                                </ol>
                                <p class="qr-note">
                                    <i class="fa fa-lightbulb me-1"></i>
                                    El QR equivale a ingresar el código manual <strong>{{ equipment.internal_code }}</strong>.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </template>
    </div>
</template>

<script>
import equipmentService from '@/services/equipmentService.js';
import { useAuth } from '@/stores/auth';

export default {
    name: 'EquipmentDetail',

    data() {
        return {
            equipment: null,
            loading: true,
            activeTab: 'ficha',
            history: [],
            historyLoaded: false,
            historyLoading: false,
            historyFilters: {
                type: '',
                date_from: '',
                date_to: '',
            },
            uploadForm: {
                file: null,
                type: 'foto',
            },
            uploading: false,
            qrImage: null,
            qrContent: '',
            qrLoading: false,
            qrError: '',
            tabs: [
                { key: 'ficha', label: 'Ficha Tecnica', icon: 'fa fa-id-card' },
                { key: 'vida', label: 'Hoja de Vida', icon: 'fa fa-clipboard-list' },
                { key: 'mantenimientos', label: 'Mantenimientos', icon: 'fa fa-wrench' },
                { key: 'adjuntos', label: 'Adjuntos', icon: 'fa fa-paperclip' },
                { key: 'estadisticas', label: 'Estadisticas', icon: 'fa fa-chart-bar' },
                { key: 'qr', label: 'Código QR', icon: 'fa fa-qrcode', roles: ['master', 'coordinator'] },
            ],
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
            contractLabels: {
                mantenimiento: 'Mantenimiento',
                correctivo: 'Correctivo',
                integral: 'Integral',
            },
            reportStatusLabels: {
                borrador: 'Borrador',
                firmado_tecnico: 'Firmado Técnico',
                firmado_cliente: 'Firmado Cliente',
                cerrado: 'Cerrado',
                anulado: 'Anulado',
            },
            attTypeLabels: {
                plano: 'Plano',
                certificado: 'Certificado',
                manual: 'Manual',
                foto: 'Foto',
                otro: 'Otro',
            },
        };
    },

    computed: {
        auth() {
            return useAuth();
        },

        visibleTabs() {
            return this.tabs.filter(tab => !tab.roles || tab.roles.some(r => this.auth.hasRole(r)));
        },

        attachments() {
            return this.equipment?.attachments || [];
        },

        filteredHistory() {
            let items = [...this.history];
            if (this.historyFilters.type) {
                items = items.filter(h => h.report_type === this.historyFilters.type);
            }
            if (this.historyFilters.date_from) {
                items = items.filter(h => (h.service_date) >= this.historyFilters.date_from);
            }
            if (this.historyFilters.date_to) {
                items = items.filter(h => (h.service_date) <= this.historyFilters.date_to);
            }
            return items;
        },

        rstpReports() {
            return this.history.filter(h => h.report_type === 'RSTP');
        },

        lastRstpDate() {
            const reports = this.rstpReports;
            if (!reports.length) return null;
            const dates = reports.map(r => String(r.service_date || '').substring(0, 10)).filter(Boolean).sort().reverse();
            return dates[0] || null;
        },

        nextMaintenanceDate() {
            if (!this.lastRstpDate || !this.equipment?.maintenance_frequency_days) return null;
            try {
                const parts = this.lastRstpDate.split('-');
                const last = new Date(+parts[0], +parts[1] - 1, +parts[2]);
                last.setDate(last.getDate() + this.equipment.maintenance_frequency_days);
                return this.formatDateShort(last.getFullYear() + '-' + String(last.getMonth() + 1).padStart(2, '0') + '-' + String(last.getDate()).padStart(2, '0'));
            } catch { return null; }
        },

        isOverdue() {
            if (!this.lastRstpDate || !this.equipment?.maintenance_frequency_days) return false;
            try {
                const parts = this.lastRstpDate.split('-');
                const last = new Date(+parts[0], +parts[1] - 1, +parts[2]);
                last.setDate(last.getDate() + this.equipment.maintenance_frequency_days);
                return last < new Date();
            } catch { return false; }
        },

        rstpThisYear() {
            const year = String(new Date().getFullYear());
            return this.rstpReports.filter(r => {
                const d = String(r.service_date || '').substring(0, 10);
                return d.startsWith(year);
            }).length;
        },

        expectedThisYear() {
            if (!this.equipment?.maintenance_frequency_days) return 0;
            return Math.floor(365 / this.equipment.maintenance_frequency_days);
        },

        compliancePercent() {
            if (!this.expectedThisYear) return 0;
            return Math.round((this.rstpThisYear / this.expectedThisYear) * 100);
        },

        complianceClass() {
            if (this.compliancePercent >= 80) return 'compliance-good';
            if (this.compliancePercent >= 50) return 'compliance-warn';
            return 'compliance-bad';
        },

        statsCount() {
            const counts = { RSTP: 0, RSTC: 0, RSTE: 0, total: 0 };
            this.history.forEach(h => {
                const t = h.report_type;
                if (counts[t] !== undefined) counts[t]++;
                counts.total++;
            });
            return counts;
        },

        statsLast() {
            const last = { RSTP: null, RSTC: null, RSTE: null };
            this.history.forEach(h => {
                const t = h.report_type;
                const d = h.service_date;
                if (last[t] !== undefined && d) {
                    if (!last[t] || d > last[t].service_date) {
                        last[t] = {
                            id: h.id,
                            report_number: h.report_number,
                            service_date: d,
                            technician_name: h.technician?.name || '-',
                            status: h.status,
                        };
                    }
                }
            });
            return last;
        },
    },

    async created() {
        await this.load();
    },

    methods: {
        async load() {
            this.loading = true;
            try {
                const { data } = await equipmentService.get(this.$route.params.id);
                this.equipment = data;
            } finally {
                this.loading = false;
            }
        },

        switchTab(tab) {
            this.activeTab = tab;
            if ((tab === 'vida' || tab === 'mantenimientos' || tab === 'estadisticas') && !this.historyLoaded && !this.historyLoading) {
                this.loadHistory();
            }
            if (tab === 'qr' && !this.qrImage && !this.qrLoading) {
                this.loadQr();
            }
        },

        async loadQr() {
            if (this.qrLoading) return;
            this.qrLoading = true;
            this.qrError = '';
            try {
                const { data } = await equipmentService.generateQr(this.equipment.id);
                this.qrImage = data.qr_code_base64;
                this.qrContent = data.content;
            } catch (e) {
                console.error('Error generando QR:', e);
                this.qrError = 'No se pudo generar el código QR. Inténtalo de nuevo.';
            } finally {
                this.qrLoading = false;
            }
        },

        printQr() {
            if (!this.qrImage) return;
            const code = this.equipment.internal_code || '';
            const client = this.equipment.site?.client?.business_name || '';
            const site = this.equipment.site?.name || '';
            const win = window.open('', '_blank', 'width=600,height=700');
            if (!win) return;
            win.document.write(`
                <html>
                    <head>
                        <title>QR ${code}</title>
                        <style>
                            * { font-family: Arial, sans-serif; }
                            body { margin: 0; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
                            .label { text-align: center; padding: 24px; border: 2px solid #000; border-radius: 12px; }
                            .label img { width: 260px; height: 260px; }
                            .code { font-size: 22px; font-weight: 700; margin-top: 12px; }
                            .meta { font-size: 13px; color: #444; margin-top: 4px; }
                        </style>
                    </head>
                    <body>
                        <div class="label">
                            <img src="${this.qrImage}" alt="QR ${code}" />
                            <div class="code">${code}</div>
                            ${client ? `<div class="meta">${client}</div>` : ''}
                            ${site ? `<div class="meta">${site}</div>` : ''}
                        </div>
                        <script>window.onload = function () { window.print(); }<\/script>
                    </body>
                </html>
            `);
            win.document.close();
        },

        async loadHistory() {
            if (this.historyLoading) return;
            this.historyLoading = true;
            this.history = [];
            try {
                const { data } = await equipmentService.getHistory(this.equipment.id);
                this.history = Array.isArray(data) ? data : (data.data || []);
                this.historyLoaded = true;
            } catch (e) {
                console.error('Error cargando historial:', e);
                this.history = [];
                this.historyLoaded = true;
            } finally {
                this.historyLoading = false;
            }
        },

        applyHistoryFilters() {
            // filteredHistory is a computed, so this is just a trigger for reactivity if needed
        },

        onFileSelect(e) {
            this.uploadForm.file = e.target.files[0] || null;
        },

        async uploadAttachment() {
            if (!this.uploadForm.file) return;
            this.uploading = true;
            try {
                const formData = new FormData();
                formData.append('file', this.uploadForm.file);
                formData.append('type', this.uploadForm.type);
                const { data } = await equipmentService.uploadAttachment(this.equipment.id, formData);
                // Reload equipment to get updated attachments
                const resp = await equipmentService.get(this.equipment.id);
                this.equipment = resp.data;
                this.uploadForm.file = null;
                this.uploadForm.type = 'foto';
                if (this.$refs.fileInput) this.$refs.fileInput.value = '';
            } catch (e) {
                alert('Error al subir el archivo.');
            } finally {
                this.uploading = false;
            }
        },

        async confirmDeleteAttachment(att) {
            if (!confirm('¿Eliminar este archivo adjunto?')) return;
            att.deleting = true;
            try {
                await equipmentService.deleteAttachment(this.equipment.id, att.id);
                const resp = await equipmentService.get(this.equipment.id);
                this.equipment = resp.data;
            } catch (e) {
                alert('Error al eliminar el archivo.');
            } finally {
                att.deleting = false;
            }
        },

        formatDate(dateStr) {
            if (!dateStr) return '-';
            const date = new Date(dateStr + (dateStr.includes('T') ? '' : 'T00:00:00'));
            return date.toLocaleDateString('es-ES', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
            });
        },

        formatDateShort(dateStr) {
            if (!dateStr) return '-';
            const date = new Date(dateStr + (dateStr.includes('T') ? '' : 'T00:00:00'));
            return date.toLocaleDateString('es-ES', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
            });
        },

        truncate(text, len) {
            if (!text) return '';
            return text.length > len ? text.substring(0, len) + '...' : text;
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

.loading-state-sm {
    padding: 2rem 1rem;
    border-radius: 12px;
}

/* Breadcrumb */
.breadcrumb-bar {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.25rem;
    background: white;
    border-radius: 10px;
    margin-bottom: 1.5rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    border: 1px solid #e2e8f0;
    font-size: 0.9rem;
    flex-wrap: wrap;
}

.breadcrumb-item {
    color: #64748b;
    display: inline-flex;
    align-items: center;
}

.breadcrumb-active {
    color: #279208;
    font-weight: 600;
}

.breadcrumb-sep {
    color: #cbd5e1;
    font-size: 0.7rem;
}

/* Header card */
.detail-header-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    border: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    flex-wrap: wrap;
    gap: 1rem;
}

.header-main {
    display: flex;
    align-items: flex-start;
    gap: 1.25rem;
}

.header-icon {
    width: 56px;
    height: 56px;
    background: #e8f5e4;
    color: #279208;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    flex-shrink: 0;
}

.header-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1e293b;
    margin: 0 0 0.35rem 0;
}

.header-badges {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.35rem;
    flex-wrap: wrap;
}

.header-subtitle {
    font-size: 0.9rem;
    color: #64748b;
    margin: 0;
}

.header-actions {
    display: flex;
    gap: 0.5rem;
    align-items: center;
    flex-wrap: wrap;
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
    border: none;
    cursor: pointer;
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

.btn-edit-action {
    background: #279208;
    color: white;
}

.btn-edit-action:hover {
    background: #1f7506;
    color: white;
}

.btn-report {
    background: #30ab0a;
    color: white;
}

.btn-report:hover {
    background: #279208;
    color: white;
}

.btn-upload {
    background: #30ab0a;
    color: white;
    justify-content: center;
}

.btn-upload:hover {
    background: #279208;
    color: white;
}

.btn-upload:disabled {
    background: #94a3b8;
    cursor: not-allowed;
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

.contract-badge {
    display: inline-block;
    font-size: 0.75rem;
    font-weight: 500;
    padding: 0.25rem 0.6rem;
    border-radius: 6px;
    background: #eff6ff;
    color: #2563eb;
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

/* Tab navigation */
.tab-nav {
    display: flex;
    background: white;
    border-radius: 12px 12px 0 0;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    border: 1px solid #e2e8f0;
    border-bottom: 2px solid #e2e8f0;
    overflow-x: auto;
}

.tab-btn {
    flex: 1;
    min-width: max-content;
    padding: 0.875rem 1.25rem;
    background: none;
    border: none;
    border-bottom: 3px solid transparent;
    margin-bottom: -2px;
    color: #64748b;
    font-size: 0.9rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    white-space: nowrap;
}

.tab-btn:hover {
    color: #279208;
    background: #f8faf8;
}

.tab-btn.active {
    color: #279208;
    border-bottom-color: #30ab0a;
    font-weight: 600;
}

/* Tab content */
.tab-content-area {
    background: white;
    border-radius: 0 0 12px 12px;
    border: 1px solid #e2e8f0;
    border-top: none;
    padding: 1.5rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    margin-bottom: 1.5rem;
}

/* Info Grid */
.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}

.info-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    border: 1px solid #e2e8f0;
    overflow: hidden;
    margin-bottom: 0;
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

/* Detail items */
.detail-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.625rem 0;
    border-bottom: 1px solid #f1f5f9;
}

.detail-item:last-child {
    border-bottom: none;
}

.detail-label {
    font-size: 0.85rem;
    color: #64748b;
}

.detail-value {
    font-size: 0.9rem;
    font-weight: 500;
    color: #1e293b;
    text-align: right;
}

/* Notes */
.notes-text {
    color: #475569;
    line-height: 1.7;
    margin: 0;
    white-space: pre-wrap;
}

/* Filter bar */
.filter-bar {
    display: flex;
    gap: 1rem;
    margin-bottom: 1.5rem;
    padding: 1rem 1.25rem;
    background: #f8fafc;
    border-radius: 10px;
    border: 1px solid #e2e8f0;
    flex-wrap: wrap;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    min-width: 150px;
}

.filter-label {
    font-size: 0.8rem;
    font-weight: 600;
    color: #64748b;
}

/* Timeline */
.timeline {
    position: relative;
    padding-left: 2rem;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 8px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e2e8f0;
}

.timeline-item {
    position: relative;
    margin-bottom: 1.5rem;
}

.timeline-dot {
    position: absolute;
    left: -2rem;
    top: 0.5rem;
    width: 14px;
    height: 14px;
    border-radius: 50%;
    border: 3px solid white;
    box-shadow: 0 0 0 2px #e2e8f0;
    z-index: 1;
}

.timeline-dot.dot-rstp {
    background: #30ab0a;
    box-shadow: 0 0 0 2px #30ab0a;
}

.timeline-dot.dot-rstc {
    background: #ba2831;
    box-shadow: 0 0 0 2px #ba2831;
}

.timeline-dot.dot-rste {
    background: #d97706;
    box-shadow: 0 0 0 2px #d97706;
}

.timeline-content {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 1rem 1.25rem;
}

.timeline-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 0.5rem;
    flex-wrap: wrap;
}

.timeline-date {
    font-size: 0.85rem;
    font-weight: 600;
    color: #475569;
}

.report-type-badge {
    font-size: 0.7rem;
    font-weight: 600;
    padding: 0.2rem 0.5rem;
    border-radius: 4px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.badge-rstp {
    background: #d1fae5;
    color: #059669;
}

.badge-rstc {
    background: #fef2f2;
    color: #ba2831;
}

.badge-rste {
    background: #fff7ed;
    color: #d97706;
}

.timeline-report-link {
    font-size: 0.85rem;
    color: #279208;
    font-weight: 500;
    text-decoration: none;
}

.timeline-report-link:hover {
    text-decoration: underline;
}

.timeline-body {
    margin-bottom: 0.5rem;
}

.timeline-tech {
    font-size: 0.85rem;
    color: #64748b;
}

.timeline-conclusion {
    font-size: 0.85rem;
    color: #475569;
    margin: 0.35rem 0 0 0;
    line-height: 1.5;
}

.timeline-footer {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.functional-badge {
    font-size: 0.75rem;
    font-weight: 500;
    padding: 0.2rem 0.5rem;
    border-radius: 20px;
}

.functional-yes {
    background: #d1fae5;
    color: #059669;
}

.functional-no {
    background: #fef2f2;
    color: #ba2831;
}

/* Report type dots in dropdown */
.report-type-dot {
    display: inline-block;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    margin-right: 0.5rem;
}

.report-type-dot.dot-rstp {
    background: #30ab0a;
}

.report-type-dot.dot-rstc {
    background: #ba2831;
}

.report-type-dot.dot-rste {
    background: #d97706;
}

/* Maintenance summary */
.maint-summary {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.maint-summary-item {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 1rem 1.25rem;
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.maint-summary-label {
    font-size: 0.8rem;
    font-weight: 600;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.maint-summary-value {
    font-size: 1rem;
    font-weight: 600;
    color: #1e293b;
}

.compliance-badge {
    font-size: 0.85rem;
    font-weight: 600;
    padding: 0.2rem 0.6rem;
    border-radius: 6px;
}

.compliance-good {
    background: #d1fae5;
    color: #059669;
}

.compliance-warn {
    background: #fff7ed;
    color: #d97706;
}

.compliance-bad {
    background: #fef2f2;
    color: #ba2831;
}

/* Attachment type badges */
.att-type-badge {
    display: inline-block;
    font-size: 0.75rem;
    font-weight: 500;
    padding: 0.2rem 0.5rem;
    border-radius: 6px;
}

.att-plano {
    background: #dbeafe;
    color: #2563eb;
}

.att-certificado {
    background: #d1fae5;
    color: #059669;
}

.att-manual {
    background: #ede9fe;
    color: #7c3aed;
}

.att-foto {
    background: #fff7ed;
    color: #d97706;
}

.att-otro {
    background: #f1f5f9;
    color: #606060;
}

.attachment-link {
    color: #279208;
    text-decoration: none;
    font-weight: 500;
}

.attachment-link:hover {
    text-decoration: underline;
}

.report-link {
    color: #279208;
    text-decoration: none;
    font-weight: 600;
}

.report-link:hover {
    text-decoration: underline;
    color: #1f7506;
}

/* Report status badges (sm) */
.report-status-badge {
    display: inline-block;
    font-size: 0.7rem;
    font-weight: 600;
    padding: 0.2rem 0.5rem;
    border-radius: 12px;
    white-space: nowrap;
}

.rs-borrador {
    background: #f1f5f9;
    color: #606060;
}

.rs-firmado_tecnico {
    background: #fff7ed;
    color: #d97706;
}

.rs-firmado_cliente {
    background: #eff6ff;
    color: #2563eb;
}

.rs-cerrado {
    background: #d1fae5;
    color: #059669;
}

.rs-anulado {
    background: #fef2f2;
    color: #ba2831;
}

/* Report type badges */
.report-type-badge {
    display: inline-block;
    font-size: 0.7rem;
    font-weight: 700;
    padding: 0.15rem 0.45rem;
    border-radius: 4px;
    letter-spacing: 0.03em;
}

.badge-rstp {
    background: #d1fae5;
    color: #059669;
}

.badge-rstc {
    background: #fef2f2;
    color: #ba2831;
}

.badge-rste {
    background: #fff7ed;
    color: #d97706;
}

/* Adjuntos form controls */
.info-body .form-label {
    font-family: 'Poppins', sans-serif;
    font-size: 0.85rem;
    font-weight: 500;
    color: #374151;
    margin-bottom: 0.25rem;
}

.info-body .form-control,
.info-body .form-select {
    font-family: 'Poppins', sans-serif;
    font-size: 0.875rem;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
    padding: 0.5rem 0.75rem;
}

.info-body .form-control:focus,
.info-body .form-select:focus {
    border-color: #30ab0a;
    box-shadow: 0 0 0 3px rgba(48, 171, 10, 0.1);
}

/* Stats grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.stat-card {
    background: white;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    padding: 1.25rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    flex-shrink: 0;
}

.stat-rstp .stat-icon {
    background: #e8f5e4;
    color: #30ab0a;
}

.stat-rstc .stat-icon {
    background: #fef2f2;
    color: #ba2831;
}

.stat-rste .stat-icon {
    background: #fff7ed;
    color: #d97706;
}

.stat-total .stat-icon {
    background: #f1f5f9;
    color: #606060;
}

.stat-info {
    display: flex;
    flex-direction: column;
}

.stat-number {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1e293b;
    line-height: 1.2;
}

.stat-label {
    font-size: 0.8rem;
    color: #64748b;
    font-weight: 500;
}

/* QR tab */
.qr-layout {
    display: grid;
    grid-template-columns: minmax(320px, 1fr) minmax(280px, 1fr);
    gap: 1.5rem;
    align-items: start;
}

.qr-viewer-body {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 320px;
}

.qr-display {
    text-align: center;
    width: 100%;
}

.qr-image {
    width: 240px;
    height: 240px;
    max-width: 100%;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 8px;
    background: white;
}

.qr-code-label {
    font-size: 1.15rem;
    font-weight: 700;
    color: #1e293b;
    margin-top: 0.75rem;
}

.qr-url {
    font-size: 0.75rem;
    color: #94a3b8;
    word-break: break-all;
    margin: 0.35rem auto 1rem;
    max-width: 320px;
}

.qr-actions {
    display: flex;
    gap: 0.5rem;
    justify-content: center;
    flex-wrap: wrap;
}

.qr-steps {
    margin: 0;
    padding-left: 1.25rem;
    color: #475569;
    line-height: 1.7;
    font-size: 0.9rem;
}

.qr-steps li {
    margin-bottom: 0.5rem;
}

.qr-note {
    margin: 1rem 0 0;
    padding: 0.75rem 1rem;
    background: #f8faf8;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-size: 0.85rem;
    color: #475569;
}

.qr-note i {
    color: #d97706;
}

@media (max-width: 768px) {
    .qr-layout {
        grid-template-columns: 1fr;
    }
}

/* Empty state */
.empty-state {
    text-align: center;
    padding: 3rem 2rem;
    color: #94a3b8;
}

.empty-icon {
    font-size: 2.5rem;
    margin-bottom: 0.75rem;
    display: block;
}

.empty-state p {
    margin: 0;
    font-size: 0.95rem;
}

/* Tables */
.table th {
    font-size: 0.8rem;
    font-weight: 600;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    background: #f8fafc;
    border-bottom: 2px solid #e2e8f0;
    padding: 0.75rem 1rem;
}

.table td {
    font-size: 0.9rem;
    color: #475569;
    padding: 0.75rem 1rem;
    vertical-align: middle;
}

/* Responsive */
@media (max-width: 768px) {
    .info-grid {
        grid-template-columns: 1fr;
    }

    .detail-header-card {
        flex-direction: column;
    }

    .header-actions {
        width: 100%;
    }

    .detail-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.25rem;
    }

    .detail-value {
        text-align: left;
    }

    .tab-btn {
        padding: 0.75rem 0.75rem;
        font-size: 0.8rem;
    }

    .filter-bar {
        flex-direction: column;
    }

    .maint-summary {
        grid-template-columns: 1fr;
    }

    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 480px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
}
</style>
