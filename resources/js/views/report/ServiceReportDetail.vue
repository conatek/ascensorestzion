<template>
    <div>
        <!-- Estado de carga -->
        <div v-if="loading" class="loading-state">
            <div class="spinner-border text-primary"></div>
            <p class="text-muted mt-3">Cargando reporte...</p>
        </div>

        <template v-else-if="report.id">
            <!-- Cabecera -->
            <div class="app-page-title">
                <div class="page-title-wrapper">
                    <div class="page-title-heading">
                        <div class="page-title-icon">
                            <i class="fa fa-file-alt icon-gradient bg-mean-fruit"></i>
                        </div>
                        <div>
                            {{ report.report_number }}
                            <div class="page-title-subheading">
                                <span class="type-badge" :class="'type-' + report.report_type">
                                    {{ report.report_type }}
                                </span>
                                <span class="status-badge" :class="'status-' + report.status">
                                    {{ statusLabels[report.status] || report.status }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="page-title-actions d-flex gap-2">
                        <router-link :to="{ path: '/reportes' }" class="btn-action btn-back">
                            <i class="fa fa-arrow-left me-1"></i> Volver
                        </router-link>
                        <router-link
                            v-if="report.status === 'borrador'"
                            :to="{ path: '/reportes/' + report.id + '/editar' }"
                            class="btn-action btn-edit-action"
                        >
                            <i class="fa fa-edit me-1"></i> Editar
                        </router-link>
                        <button class="btn-action btn-pdf" @click="downloadPdf">
                            <i class="fa fa-file-pdf me-1"></i> Descargar PDF
                        </button>
                        <button class="btn-action btn-email" @click="showEmailModal = true">
                            <i class="fa fa-envelope me-1"></i> Enviar por Email
                        </button>
                    </div>
                </div>
            </div>

            <!-- Breadcrumb -->
            <nav class="breadcrumb-bar" v-if="report.equipment">
                <span class="breadcrumb-item">
                    <i class="fa fa-user-tie me-1"></i>
                    {{ report.equipment.site?.client?.business_name || '-' }}
                </span>
                <i class="fa fa-chevron-right breadcrumb-sep"></i>
                <span class="breadcrumb-item">
                    <i class="fa fa-building me-1"></i>
                    {{ report.equipment.site?.name || '-' }}
                </span>
                <i class="fa fa-chevron-right breadcrumb-sep"></i>
                <span class="breadcrumb-item">
                    <i class="fa fa-arrows-alt-v me-1"></i>
                    {{ report.equipment.internal_code }}
                </span>
                <i class="fa fa-chevron-right breadcrumb-sep"></i>
                <span class="breadcrumb-item breadcrumb-active">
                    <i class="fa fa-file-alt me-1"></i>
                    {{ report.report_number }}
                </span>
            </nav>

            <!-- Header card -->
            <div class="detail-header-card">
                <div class="header-main">
                    <div class="header-icon">
                        <i class="fa fa-file-alt"></i>
                    </div>
                    <div class="header-info">
                        <h2 class="header-title">{{ report.report_number }}</h2>
                        <p class="header-subtitle">
                            {{ report.report_type }} &mdash; {{ formatDate(report.service_date) }}
                        </p>
                        <p class="header-subtitle" v-if="report.technician">
                            Tecnico: {{ report.technician.name }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Seccion 1: Informacion Basica -->
            <div class="info-card">
                <div class="info-header">
                    <i class="fa fa-info-circle"></i>
                    <span>Informacion Basica</span>
                </div>
                <div class="info-body">
                    <div class="detail-grid">
                        <div class="detail-item">
                            <span class="detail-label">Cliente</span>
                            <span class="detail-value">{{ report.equipment?.site?.client?.business_name || '-' }}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Sede/Obra</span>
                            <span class="detail-value">{{ report.equipment?.site?.name || '-' }}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Equipo (codigo interno)</span>
                            <span class="detail-value">{{ report.equipment?.internal_code || '-' }}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Marca</span>
                            <span class="detail-value">{{ report.equipment?.brand || '-' }}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Modelo</span>
                            <span class="detail-value">{{ report.equipment?.model || '-' }}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Fecha</span>
                            <span class="detail-value">{{ formatDate(report.service_date) }}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Ped. Cliente</span>
                            <span class="detail-value">{{ report.customer_order_ref || '-' }}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Tecnico</span>
                            <span class="detail-value">{{ report.technician?.name || '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Seccion 2: Condicion Inicial -->
            <div class="info-card" v-if="report.initial_conditions && report.initial_conditions.length">
                <div class="info-header">
                    <i class="fa fa-clipboard-check"></i>
                    <span>Condicion Inicial</span>
                </div>
                <!-- RSTP: single-column table -->
                <div class="info-body p-0" v-if="report.report_type === 'RSTP'">
                    <table class="condition-table">
                        <thead>
                            <tr>
                                <th>Descripcion</th>
                                <th class="text-center" style="width: 80px;">Si/No</th>
                                <th>Observacion</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="cond in report.initial_conditions" :key="cond.id">
                                <td>{{ conditionLabels[cond.condition_key] || cond.condition_key }}</td>
                                <td class="text-center">
                                    <span v-if="cond.value === 'si'" class="indicator indicator-yes">
                                        <i class="fa fa-check"></i>
                                    </span>
                                    <span v-else-if="cond.value === 'no'" class="indicator indicator-no">
                                        <i class="fa fa-times"></i>
                                    </span>
                                    <span v-else class="text-muted">-</span>
                                </td>
                                <td class="text-muted">{{ cond.observation || '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <!-- RSTC/RSTE: 2-column layout -->
                <div class="info-body" v-else>
                    <div class="conditions-two-col">
                        <div class="conditions-col">
                            <table class="condition-table">
                                <thead>
                                    <tr>
                                        <th>Descripcion</th>
                                        <th class="text-center" style="width: 80px;">Si/No</th>
                                        <th>Observacion</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="cond in leftConditions" :key="cond.id">
                                        <td>{{ conditionLabelsRstcRste[cond.condition_key] || cond.condition_key }}</td>
                                        <td class="text-center">
                                            <span v-if="cond.value === 'si'" class="indicator indicator-yes">
                                                <i class="fa fa-check"></i>
                                            </span>
                                            <span v-else-if="cond.value === 'no'" class="indicator indicator-no">
                                                <i class="fa fa-times"></i>
                                            </span>
                                            <span v-else class="text-muted">-</span>
                                        </td>
                                        <td class="text-muted">{{ cond.observation || '-' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="conditions-col">
                            <table class="condition-table">
                                <thead>
                                    <tr>
                                        <th>Descripcion</th>
                                        <th class="text-center" style="width: 80px;">Si/No</th>
                                        <th>Observacion</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="cond in rightConditions" :key="cond.id">
                                        <td>{{ conditionLabelsRstcRste[cond.condition_key] || cond.condition_key }}</td>
                                        <td class="text-center">
                                            <span v-if="cond.value === 'si'" class="indicator indicator-yes">
                                                <i class="fa fa-check"></i>
                                            </span>
                                            <span v-else-if="cond.value === 'no'" class="indicator indicator-no">
                                                <i class="fa fa-times"></i>
                                            </span>
                                            <span v-else class="text-muted">-</span>
                                        </td>
                                        <td class="text-muted">{{ cond.observation || '-' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Seccion 2b: Codigos de Falla (RSTC/RSTE) -->
            <div class="info-card" v-if="(report.report_type === 'RSTC' || report.report_type === 'RSTE') && report.fault_codes && report.fault_codes.length > 0">
                <div class="info-header">
                    <i class="fa fa-exclamation-triangle"></i>
                    <span>Codigos de Falla</span>
                </div>
                <div class="info-body">
                    <div class="fault-codes-grid">
                        <div class="fault-codes-col">
                            <div v-for="fc in faultCodesLeft" :key="fc.id" class="fault-code-item">
                                <div class="fault-code-header">
                                    <span class="fault-code-label">{{ fc.code }}</span>
                                    <span class="severity-badge" :class="'severity-' + fc.severity">
                                        {{ fc.severity }}
                                    </span>
                                </div>
                                <div class="fault-code-desc" v-if="fc.description">{{ fc.description }}</div>
                            </div>
                        </div>
                        <div class="fault-codes-col">
                            <div v-for="fc in faultCodesRight" :key="fc.id" class="fault-code-item">
                                <div class="fault-code-header">
                                    <span class="fault-code-label">{{ fc.code }}</span>
                                    <span class="severity-badge" :class="'severity-' + fc.severity">
                                        {{ fc.severity }}
                                    </span>
                                </div>
                                <div class="fault-code-desc" v-if="fc.description">{{ fc.description }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Seccion 3: Actividades Relevantes (RSTP only) -->
            <div class="info-card" v-if="report.report_type === 'RSTP' && report.rstp_activities && report.rstp_activities.length">
                <div class="info-header">
                    <i class="fa fa-tasks"></i>
                    <span>Actividades Relevantes</span>
                </div>
                <div class="info-body">
                    <!-- Mes del mantenimiento -->
                    <div v-if="report.rstp_month" class="maintenance-month mb-3">
                        <i class="fa fa-calendar-alt me-1"></i>
                        <strong>Mes del mantenimiento:</strong> {{ monthNames[report.rstp_month.month - 1] }} {{ report.rstp_month.year }}
                    </div>

                    <div v-for="(groupLabel, groupKey) in activityGroups" :key="groupKey" class="activity-group">
                        <h6 class="group-title">{{ groupLabel }}</h6>
                        <table class="condition-table">
                            <thead>
                                <tr>
                                    <th>Actividad</th>
                                    <th class="text-center" style="width: 80px;">Ok</th>
                                    <th>Observacion</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="act in getGroupActivities(groupKey)" :key="act.id">
                                    <td>{{ activityLabels[act.activity_key] || act.activity_key }}</td>
                                    <td class="text-center">
                                        <span v-if="act.is_ok" class="indicator indicator-yes">
                                            <i class="fa fa-check"></i>
                                        </span>
                                        <span v-else class="indicator indicator-no">
                                            <i class="fa fa-times"></i>
                                        </span>
                                    </td>
                                    <td class="text-muted">{{ act.observation || '-' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Seccion 3b: Revision, Diagnostico y Solucion (RSTC only) -->
            <div class="info-card" v-if="report.report_type === 'RSTC' && report.rstc_details">
                <div class="info-header">
                    <i class="fa fa-search"></i>
                    <span>Revision, Diagnostico y Solucion</span>
                </div>
                <div class="info-body">
                    <!-- Ubicacion de La Falla -->
                    <div class="analysis-subsection">
                        <h6 class="subsection-title">Ubicacion de La Falla</h6>
                        <p class="subsection-value">
                            {{ faultLocationLabels[report.rstc_details.fault_location] || report.rstc_details.fault_location || '-' }}
                            <span v-if="report.rstc_details.fault_location === 'otra' && report.rstc_details.fault_location_other" class="text-muted">
                                &mdash; {{ report.rstc_details.fault_location_other }}
                            </span>
                        </p>
                        <p v-if="report.rstc_details.analysis_notes" class="notes-text mt-1">{{ report.rstc_details.analysis_notes }}</p>
                    </div>

                    <!-- Causas de La Falla -->
                    <div class="analysis-subsection">
                        <h6 class="subsection-title">Causas de La Falla</h6>
                        <p class="subsection-value">
                            {{ faultCauseLabels[report.rstc_details.fault_cause] || report.rstc_details.fault_cause || '-' }}
                            <span v-if="report.rstc_details.fault_cause === 'otra' && report.rstc_details.fault_cause_other" class="text-muted">
                                &mdash; {{ report.rstc_details.fault_cause_other }}
                            </span>
                        </p>
                        <p v-if="report.rstc_details.cause_notes" class="notes-text mt-1">{{ report.rstc_details.cause_notes }}</p>
                    </div>

                    <!-- Solucion de La Falla -->
                    <div class="analysis-subsection">
                        <h6 class="subsection-title">Solucion de La Falla</h6>
                        <p class="subsection-value">
                            {{ faultSolutionLabels[report.rstc_details.fault_solution_area] || report.rstc_details.fault_solution_area || '-' }}
                            <span v-if="report.rstc_details.fault_solution_area === 'otra' && report.rstc_details.fault_solution_other" class="text-muted">
                                &mdash; {{ report.rstc_details.fault_solution_other }}
                            </span>
                        </p>
                        <p v-if="report.rstc_details.solution_notes" class="notes-text mt-1">{{ report.rstc_details.solution_notes }}</p>
                    </div>
                </div>
            </div>

            <!-- Seccion 3c: Trabajos y/o Reparaciones a Efectuar (RSTE only) -->
            <div class="info-card" v-if="report.report_type === 'RSTE' && report.rste_works && report.rste_works.length">
                <div class="info-header">
                    <i class="fa fa-wrench"></i>
                    <span>Trabajos y/o Reparaciones a Efectuar</span>
                </div>
                <div class="info-body">
                    <div v-for="(groupLabel, groupKey) in workGroups" :key="groupKey" class="activity-group">
                        <template v-if="getGroupWorks(groupKey).length">
                            <h6 class="group-title">{{ groupLabel }}</h6>
                            <table class="condition-table">
                                <thead>
                                    <tr>
                                        <th>Trabajo</th>
                                        <th class="text-center" style="width: 80px;">Ok</th>
                                        <th>Observacion</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="work in getGroupWorks(groupKey)" :key="work.id">
                                        <td>{{ workLabels[work.work_key] || work.work_key }}</td>
                                        <td class="text-center">
                                            <span v-if="work.is_ok" class="indicator indicator-yes">
                                                <i class="fa fa-check"></i>
                                            </span>
                                            <span v-else class="indicator indicator-no">
                                                <i class="fa fa-times"></i>
                                            </span>
                                        </td>
                                        <td class="text-muted">{{ work.observation || '-' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Seccion 4: Conclusion -->
            <div class="info-card">
                <div class="info-header">
                    <i class="fa fa-flag-checkered"></i>
                    <span>Conclusion</span>
                </div>
                <div class="info-body">
                    <div class="detail-grid">
                        <div class="detail-item">
                            <span class="detail-label">El equipo queda funcionando?</span>
                            <span class="detail-value">
                                <span v-if="report.equipment_functional === true" class="conclusion-badge conclusion-yes">Si</span>
                                <span v-else-if="report.equipment_functional === false" class="conclusion-badge conclusion-no">No</span>
                                <span v-else class="text-muted">-</span>
                            </span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Genera cotizacion?</span>
                            <span class="detail-value">
                                <span v-if="report.generates_quotation" class="conclusion-badge conclusion-yes">Si</span>
                                <span v-else class="conclusion-badge conclusion-no">No</span>
                            </span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Requiere repuestos?</span>
                            <span class="detail-value">
                                <span v-if="report.requires_parts_change" class="conclusion-badge conclusion-yes">Si</span>
                                <span v-else class="conclusion-badge conclusion-no">No</span>
                            </span>
                        </div>
                    </div>
                    <div v-if="report.conclusion_notes" class="mt-3">
                        <span class="detail-label d-block mb-1">Notas de conclusion</span>
                        <p class="notes-text">{{ report.conclusion_notes }}</p>
                    </div>
                    <div class="detail-grid mt-3" v-if="report.time_in || report.time_out">
                        <div class="detail-item" v-if="report.time_in">
                            <span class="detail-label">Hora entrada</span>
                            <span class="detail-value">{{ report.time_in }}</span>
                        </div>
                        <div class="detail-item" v-if="report.time_out">
                            <span class="detail-label">Hora salida</span>
                            <span class="detail-value">{{ report.time_out }}</span>
                        </div>
                    </div>

                    <!-- RSTC Times -->
                    <div v-if="report.report_type === 'RSTC' && report.rstc_details" class="mt-3">
                        <h6 class="subsection-title mb-2">Tiempos RSTC</h6>
                        <div class="detail-grid">
                            <div class="detail-item" v-if="report.rstc_details.call_time">
                                <span class="detail-label">Hora Llamada</span>
                                <span class="detail-value">{{ formatDateTime(report.rstc_details.call_time) }}</span>
                            </div>
                            <div class="detail-item" v-if="report.rstc_details.entry_time">
                                <span class="detail-label">Hora Entrada</span>
                                <span class="detail-value">{{ formatDateTime(report.rstc_details.entry_time) }}</span>
                            </div>
                            <div class="detail-item" v-if="report.rstc_details.exit_time">
                                <span class="detail-label">Hora Salida</span>
                                <span class="detail-value">{{ formatDateTime(report.rstc_details.exit_time) }}</span>
                            </div>
                            <div class="detail-item" v-if="report.rstc_details.response_time_hh != null || report.rstc_details.response_time_mm != null">
                                <span class="detail-label">Tiempo de Respuesta</span>
                                <span class="detail-value">
                                    {{ report.rstc_details.response_time_hh || 0 }}h {{ report.rstc_details.response_time_mm || 0 }}m
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Seccion: Anexos (fotos y video) -->
            <div class="info-card" v-if="report.attachments && report.attachments.length">
                <div class="info-header">
                    <i class="fa fa-images"></i>
                    <span>Anexos — {{ anexosCountLabel }}</span>
                </div>
                <div class="info-body">
                    <!-- Fotos agrupadas por punto -->
                    <div v-for="group in photoGroups" :key="group.label" class="anexo-group">
                        <h6 class="anexo-group-label">{{ group.label }}</h6>
                        <div class="anexo-grid">
                            <button
                                v-for="photo in group.photos"
                                :key="photo.id"
                                type="button"
                                class="anexo-thumb"
                                @click="openLightbox(photo)"
                            >
                                <img :src="thumbUrl(photo.url)" :alt="group.label" loading="lazy">
                            </button>
                        </div>
                    </div>

                    <!-- Video(s) -->
                    <div v-if="videoAttachments.length" class="anexo-group">
                        <h6 class="anexo-group-label"><i class="fa fa-video me-1"></i>Video del servicio</h6>
                        <div class="anexo-videos">
                            <video
                                v-for="v in videoAttachments"
                                :key="v.id"
                                :src="v.url"
                                controls
                                preload="metadata"
                                class="anexo-video"
                            ></video>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Seccion 5: Firmas -->
            <div class="info-card">
                <div class="info-header">
                    <i class="fa fa-signature"></i>
                    <span>Firmas</span>
                </div>
                <div class="info-body">
                    <div class="signatures-grid">
                        <!-- Firma tecnico -->
                        <div class="signature-box">
                            <h6 class="signature-title">Firma Tecnico</h6>
                            <div v-if="report.technician_signed_at" class="signature-signed">
                                <i class="fa fa-check-circle text-success me-1"></i>
                                Firmado el {{ formatDateTime(report.technician_signed_at) }}
                                <div class="signature-name">{{ report.technician?.name || '-' }}</div>
                            </div>
                            <div v-else class="signature-pending">
                                <i class="fa fa-clock text-muted me-1"></i>
                                Pendiente de firma
                            </div>
                        </div>

                        <!-- Firma cliente -->
                        <div class="signature-box">
                            <h6 class="signature-title">Firma Cliente</h6>
                            <div v-if="report.customer_signed_at" class="signature-signed">
                                <i class="fa fa-check-circle text-success me-1"></i>
                                Firmado el {{ formatDateTime(report.customer_signed_at) }}
                                <div class="signature-name" v-if="report.customer_signer_name">
                                    {{ report.customer_signer_name }}
                                    <span v-if="report.customer_signer_document" class="text-muted">
                                        ({{ report.customer_signer_document }})
                                    </span>
                                </div>
                            </div>
                            <div v-else class="signature-pending">
                                <i class="fa fa-clock text-muted me-1"></i>
                                Pendiente de firma
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Seccion 6: Auditoria -->
            <div class="info-card" v-if="report.audit_log && report.audit_log.length">
                <div class="info-header">
                    <i class="fa fa-history"></i>
                    <span>Auditoria</span>
                </div>
                <div class="info-body">
                    <div class="audit-timeline">
                        <div class="audit-entry" v-for="entry in report.audit_log" :key="entry.id">
                            <div class="audit-dot"></div>
                            <div class="audit-content">
                                <div class="audit-action">
                                    <strong>{{ auditActionLabels[entry.action] || entry.action }}</strong>
                                </div>
                                <div class="audit-meta">
                                    <span class="audit-user">
                                        <i class="fa fa-user me-1"></i>{{ entry.user?.name || 'Sistema' }}
                                    </span>
                                    <span class="audit-date">
                                        <i class="fa fa-clock me-1"></i>{{ formatDateTime(entry.created_at) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <!-- Modal Enviar por Email -->
        <div v-if="showEmailModal" class="modal-overlay" @click.self="closeEmailModal">
            <div class="modal-container">
                <div class="modal-icon-wrapper">
                    <div class="modal-icon modal-icon-email">
                        <i class="fa fa-envelope"></i>
                    </div>
                </div>
                <h4 class="modal-title">Enviar Reporte por Email</h4>
                <p class="modal-message">
                    Ingresa el correo electronico del destinatario para enviar el reporte.
                </p>
                <div class="email-input-wrapper">
                    <input
                        v-model="emailTo"
                        type="email"
                        class="email-input"
                        placeholder="correo@ejemplo.com"
                        :disabled="sendingEmail"
                    />
                </div>
                <div v-if="emailSent" class="email-success-msg">
                    <i class="fa fa-check-circle me-1"></i> Reporte enviado exitosamente
                </div>
                <div class="modal-actions">
                    <button class="modal-btn modal-btn-cancel" @click="closeEmailModal" :disabled="sendingEmail">
                        Cancelar
                    </button>
                    <button class="modal-btn modal-btn-send" @click="sendEmail" :disabled="sendingEmail || !emailTo">
                        <span v-if="sendingEmail" class="spinner-border spinner-border-sm me-2"></span>
                        {{ sendingEmail ? 'Enviando...' : 'Enviar' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Lightbox de fotos -->
        <Teleport to="body">
            <div v-if="lightboxPhoto" class="lightbox-overlay" @click.self="closeLightbox">
                <button class="lightbox-close" @click="closeLightbox" title="Cerrar"><i class="fa fa-times"></i></button>
                <button v-if="allPhotos.length > 1" class="lightbox-nav lightbox-prev" @click.stop="lightboxPrev"><i class="fa fa-chevron-left"></i></button>
                <img :src="transformUrl(lightboxPhoto.url, 'q_auto,f_auto')" class="lightbox-img" alt="">
                <button v-if="allPhotos.length > 1" class="lightbox-nav lightbox-next" @click.stop="lightboxNext"><i class="fa fa-chevron-right"></i></button>
                <div class="lightbox-counter">{{ lightboxIndex + 1 }} / {{ allPhotos.length }}</div>
            </div>
        </Teleport>
    </div>
</template>

<script>
import reportService from '@/services/reportService.js';

export default {
    name: 'ServiceReportDetail',

    data() {
        return {
            report: {},
            loading: true,
            showEmailModal: false,
            emailTo: '',
            sendingEmail: false,
            emailSent: false,
            lightboxIndex: null,

            statusLabels: {
                borrador: 'Borrador',
                firmado_tecnico: 'Firmado por Tecnico',
                firmado_cliente: 'Firmado por Cliente',
                cerrado: 'Cerrado',
                anulado: 'Anulado',
            },

            conditionLabels: {
                fuera_de_servicio:     'Fuera de Servicio',
                nivelado:              'Nivelado',
                sincronizado_piso:     'Sincronizado En El Piso',
                pasado_extremos:       'Pasado En Extremos',
                puertas_cerradas:      'Puertas Cerradas',
                puertas_descarriladas: 'Puertas Descarriladas',
                freno:                 'Freno',
                luces_interiores:      'Luces Interiores',
                botoneras_llamadas:    'Botoneras Llamadas',
                foso_inundado:         'Foso Inundado',
                ruidos_generales:      'Ruidos En General',
            },

            // RSTC/RSTE condition labels (20 items, left/right)
            conditionLabelsRstcRste: {
                fuera_de_servicio:       'Fuera de Servicio',
                nivelado:                'Nivelado',
                sincronizado_piso:       'Sincronizado En El Piso',
                puertas_cerradas:        'Puertas Cerradas',
                puertas_abiertas:        'Puertas Abiertas',
                puertas_descarriladas:   'Puertas Descarriladas',
                pasado_extremo_superior: 'Pasado Extremo Superior',
                pasado_extremo_inferior: 'Pasado Extremo Inferior',
                freno_mecanico:          'Freno Mecanico',
                control_principal:       'Control Principal',
                variador_principal:      'Variador Principal',
                acometida_electrica:     'Acometida Electrica',
                cable_viajero:           'Cable Viajero',
                cable_pozo:              'Cable De Pozo',
                limitador_velocidad:     'Limitador De Velocidad',
                luces_interiores:        'Luces Interiores',
                ventilacion:             'Ventilacion',
                fotocelula:              'Fotocelula/Cortina Infrarroja',
                foso_inundado:           'Foso Inundado',
                ruidos_generales:        'Ruidos En General',
            },

            // Keys that belong to "left" column
            leftConditionKeys: [
                'fuera_de_servicio', 'nivelado', 'sincronizado_piso',
                'puertas_cerradas', 'puertas_abiertas', 'puertas_descarriladas',
                'pasado_extremo_superior', 'pasado_extremo_inferior',
                'freno_mecanico', 'control_principal',
            ],

            // RSTC fault analysis labels
            faultLocationLabels: {
                sistema_puertas:    'Sistema De Puertas',
                control_maniobras:  'Control De Maniobras',
                maquinaria:         'Maquinaria',
                recorrido:          'Recorrido',
                otra:               'Otra',
            },

            faultCauseLabels: {
                energia_externa:    'Energia Externa/Tormenta Electrica',
                inundacion_humedad: 'Inundacion/Humedad',
                tercero:            'Provocada Por Tercero',
                tecnica_equipo:     'Tecnica Por El Equipo',
                otra:               'Otra',
            },

            faultSolutionLabels: {
                control_maniobras:          'Control De Maniobras',
                perifericos:                'Perifericos',
                puertas_piso:               'Puertas De Piso',
                operador_puertas_cabina:    'Operador/Puertas De Cabina',
                activacion_interruptores:   'Activacion Interruptores/Totalizador',
                otra:                       'Otra',
            },

            // RSTE work groups and labels
            workGroups: {
                cuarto_maquinas: 'Cuarto de Maquinas',
                cabina: 'Cabina',
                pozo_foso: 'Pozo-Foso',
            },

            workLabels: {
                acometidas_electricas:      'Acometidas Electricas',
                control_maniobras:          'Control De Maniobras',
                maquina_traccion:           'Maquina De Traccion',
                limitador_velocidad:        'Limitador De Velocidad',
                alambrado_pozo_foso:        'Alambrado De Pozo-Foso',
                operador_puertas:           'Operador De Puertas',
                caja_conexiones:            'Caja De Conexiones',
                aceiteras_cabina:           'Aceiteras Lubricantes/Guides',
                corral_techo:               'Corral Techo',
                microswitches_seguridad:    'Microswitches de Seguridad',
                fotocelula_cortina:         'Fotocelula/Cortina Infrarroja',
                puertas_piso:               'Puertas De Piso',
                instrumentacion_recorrido:  'Instrumentacion De Recorrido',
                aceiteras_pozo:             'Aceiteras Lubricantes/Guides',
                microswitches_seguridad_pf: 'Microswitches de Seguridad',
                rieles_guias:               'Rieles Guias/Contrapeso',
                amortiguadores:             'Amortiguadores',
            },

            activityGroups: {
                cuarto_maquinas: 'Cuarto de Maquinas',
                cabina: 'Cabina',
                pozo_foso: 'Pozo-Foso',
            },

            activityLabels: {
                acometidas_electricas:      'Acometidas Electricas',
                control_maniobras:          'Control De Maniobras',
                maquinaria:                 'Maquinaria',
                limitador_velocidad:        'Limitador De Velocidad',
                limpieza_general_cm:        'Limpieza Del Area En General',
                operador_puertas:           'Operador De Puertas',
                caja_conexiones:            'Caja De Conexiones',
                aceiteras_cabina:           'Aceiteras Lubricantes/Guides',
                corral_techo:               'Corral Techo',
                limpieza_general_cab:       'Limpieza En General',
                puertas_piso:               'Puertas De Piso',
                instrumentacion_recorrido:  'Instrumentacion De Recorrido',
                aceiteras_pozo:             'Aceiteras Lubricantes/Guides',
                rieles_guias:               'Rieles Guias/Contrapeso',
                limpieza_general_pf:        'Limpieza En General',
            },

            monthNames: [
                'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre',
            ],

            auditActionLabels: {
                created:         'Creado',
                edited:          'Editado',
                signed_tech:     'Firmado por tecnico',
                signed_customer: 'Firmado por cliente',
                reopened:        'Reabierto',
                cancelled:       'Anulado',
            },
        };
    },

    computed: {
        leftConditions() {
            if (!this.report.initial_conditions) return [];
            return this.report.initial_conditions.filter(
                c => this.leftConditionKeys.includes(c.condition_key)
            );
        },

        rightConditions() {
            if (!this.report.initial_conditions) return [];
            return this.report.initial_conditions.filter(
                c => !this.leftConditionKeys.includes(c.condition_key)
            );
        },

        faultCodesLeft() {
            if (!this.report.fault_codes) return [];
            return this.report.fault_codes.slice(0, 10);
        },

        faultCodesRight() {
            if (!this.report.fault_codes) return [];
            return this.report.fault_codes.slice(10, 20);
        },

        // ── Anexos (fotos/video) ──
        videoAttachments() {
            return (this.report.attachments || []).filter(a => a.media_type === 'video');
        },

        photoGroups() {
            const photos = (this.report.attachments || []).filter(a => a.media_type !== 'video');
            const map = new Map();
            photos.forEach(a => {
                const label = this.attachmentPointLabel(a);
                if (!map.has(label)) map.set(label, []);
                map.get(label).push(a);
            });
            return Array.from(map, ([label, items]) => ({ label, photos: items }));
        },

        allPhotos() {
            return this.photoGroups.flatMap(g => g.photos);
        },

        lightboxPhoto() {
            return this.lightboxIndex !== null ? this.allPhotos[this.lightboxIndex] : null;
        },

        anexosCountLabel() {
            const p = this.allPhotos.length;
            const v = this.videoAttachments.length;
            const parts = [];
            if (p) parts.push(`${p} foto${p > 1 ? 's' : ''}`);
            if (v) parts.push(`${v} video${v > 1 ? 's' : ''}`);
            return parts.join(' · ');
        },
    },

    async mounted() {
        await this.load();
    },

    methods: {
        async load() {
            this.loading = true;
            try {
                const { data } = await reportService.get(this.$route.params.id);
                this.report = data;
            } finally {
                this.loading = false;
            }
        },

        getGroupActivities(groupKey) {
            if (!this.report.rstp_activities) return [];
            return this.report.rstp_activities.filter(a => a.group_key === groupKey);
        },

        // ── Anexos: etiquetas, miniaturas y lightbox ──
        humanize(key) {
            return (key || '').replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
        },

        attachmentPointLabel(att) {
            if (att.condition_key) {
                return this.conditionLabels[att.condition_key]
                    || this.conditionLabelsRstcRste[att.condition_key]
                    || this.humanize(att.condition_key);
            }
            if (att.activity_key) {
                const group = this.activityGroups[att.group_key] || this.workGroups[att.group_key] || '';
                const act = this.activityLabels[att.activity_key]
                    || this.workLabels[att.activity_key]
                    || this.humanize(att.activity_key);
                return group ? `${group} — ${act}` : act;
            }
            return 'General';
        },

        transformUrl(url, t) {
            // Inserta transformaciones de Cloudinary tras /upload/
            if (!url || !url.includes('/upload/')) return url;
            return url.replace('/upload/', `/upload/${t}/`);
        },

        thumbUrl(url) {
            return this.transformUrl(url, 'w_320,h_320,c_fill,q_auto,f_auto');
        },

        openLightbox(att) {
            const idx = this.allPhotos.findIndex(p => p.id === att.id);
            this.lightboxIndex = idx >= 0 ? idx : 0;
        },

        closeLightbox() {
            this.lightboxIndex = null;
        },

        lightboxNext() {
            this.lightboxIndex = (this.lightboxIndex + 1) % this.allPhotos.length;
        },

        lightboxPrev() {
            this.lightboxIndex = (this.lightboxIndex - 1 + this.allPhotos.length) % this.allPhotos.length;
        },

        getGroupWorks(groupKey) {
            if (!this.report.rste_works) return [];
            return this.report.rste_works.filter(w => w.group_key === groupKey);
        },

        formatDate(dateStr) {
            if (!dateStr) return '-';
            const date = new Date(dateStr + 'T00:00:00');
            return date.toLocaleDateString('es-ES', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
            });
        },

        formatDateTime(dateStr) {
            if (!dateStr) return '-';
            const date = new Date(dateStr);
            return date.toLocaleDateString('es-ES', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
            }) + ' ' + date.toLocaleTimeString('es-ES', {
                hour: '2-digit',
                minute: '2-digit',
            });
        },

        async downloadPdf() {
            try {
                const response = await reportService.getPdf(this.report.id);
                const blob = new Blob([response.data], { type: 'application/pdf' });
                const url = window.URL.createObjectURL(blob);
                window.open(url, '_blank');
            } catch (error) {
                console.error('Error descargando PDF:', error);
            }
        },

        async sendEmail() {
            this.sendingEmail = true;
            this.emailSent = false;
            try {
                await reportService.sendEmail(this.$route.params.id, { email: this.emailTo });
                this.emailSent = true;
                setTimeout(() => {
                    this.closeEmailModal();
                }, 1500);
            } catch (error) {
                console.error('Error enviando email:', error);
                alert('Error al enviar el reporte por email.');
            } finally {
                this.sendingEmail = false;
            }
        },

        closeEmailModal() {
            this.showEmailModal = false;
            this.emailTo = '';
            this.emailSent = false;
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

.btn-pdf {
    background: #ba2831;
    color: white;
}

.btn-pdf:hover {
    background: #9e2029;
    color: white;
}

.btn-email {
    background: #2563eb;
    color: white;
}

.btn-email:hover {
    background: #1d4ed8;
    color: white;
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
}

.header-main {
    display: flex;
    align-items: center;
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
    margin: 0 0 0.25rem 0;
}

.header-subtitle {
    font-size: 0.9rem;
    color: #64748b;
    margin: 0;
}

/* Badges */
.type-badge {
    display: inline-block;
    font-size: 0.8rem;
    font-weight: 600;
    padding: 0.25rem 0.6rem;
    border-radius: 6px;
    letter-spacing: 0.5px;
}

.type-RSTP {
    background: #e8f5e4;
    color: #30ab0a;
}

.type-RSTC {
    background: #fef2f2;
    color: #ba2831;
}

.type-RSTE {
    background: #fff7ed;
    color: #d97706;
}

.status-badge {
    display: inline-block;
    font-size: 0.75rem;
    font-weight: 500;
    padding: 0.3rem 0.6rem;
    border-radius: 20px;
    white-space: nowrap;
    margin-left: 0.5rem;
}

.status-borrador {
    background: #f1f5f9;
    color: #606060;
}

.status-firmado_tecnico {
    background: #dbeafe;
    color: #2563eb;
}

.status-firmado_cliente {
    background: #d1fae5;
    color: #059669;
}

.status-cerrado {
    background: #d1fae5;
    color: #059669;
}

.status-anulado {
    background: #fef2f2;
    color: #ba2831;
}

/* Info Card */
.info-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    border: 1px solid #e2e8f0;
    overflow: hidden;
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

.info-body.p-0 {
    padding: 0;
}

/* Detail grid */
.detail-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 0;
}

.detail-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.625rem 0;
    border-bottom: 1px solid #f1f5f9;
}

.detail-grid .detail-item {
    padding: 0.625rem 0.5rem;
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

/* Condition table */
.condition-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.875rem;
}

.condition-table thead th {
    background: #f8fafc;
    padding: 0.625rem 1rem;
    text-align: left;
    font-weight: 600;
    color: #475569;
    border-bottom: 2px solid #e2e8f0;
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.condition-table tbody td {
    padding: 0.5rem 1rem;
    border-bottom: 1px solid #f1f5f9;
    color: #1e293b;
}

.condition-table tbody tr:last-child td {
    border-bottom: none;
}

/* Indicators */
.indicator {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    font-size: 0.7rem;
}

.indicator-yes {
    background: #d1fae5;
    color: #059669;
}

.indicator-no {
    background: #fef2f2;
    color: #ba2831;
}

/* Conclusion badges */
.conclusion-badge {
    display: inline-block;
    font-size: 0.8rem;
    font-weight: 600;
    padding: 0.2rem 0.75rem;
    border-radius: 20px;
}

.conclusion-yes {
    background: #d1fae5;
    color: #059669;
}

.conclusion-no {
    background: #fef2f2;
    color: #ba2831;
}

/* Activity groups */
.activity-group {
    margin-bottom: 1.25rem;
}

.activity-group:last-child {
    margin-bottom: 0;
}

.group-title {
    font-size: 0.9rem;
    font-weight: 600;
    color: #30ab0a;
    margin-bottom: 0.5rem;
    padding-bottom: 0.25rem;
    border-bottom: 2px solid #e8f5e4;
}

/* Maintenance month */
.maintenance-month {
    padding: 0.75rem 1rem;
    background: #e8f5e4;
    border-radius: 8px;
    color: #279208;
    font-size: 0.9rem;
}

/* Notes */
.notes-text {
    color: #475569;
    line-height: 1.7;
    margin: 0;
    white-space: pre-wrap;
}

/* Signatures */
.signatures-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
}

.signature-box {
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 1.25rem;
    text-align: center;
}

.signature-title {
    font-size: 0.85rem;
    font-weight: 600;
    color: #475569;
    margin-bottom: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.signature-signed {
    color: #059669;
    font-size: 0.85rem;
}

.signature-name {
    margin-top: 0.5rem;
    font-weight: 600;
    color: #1e293b;
    font-size: 0.9rem;
}

.signature-pending {
    color: #606060;
    font-size: 0.85rem;
    font-style: italic;
}

/* Audit timeline */
.audit-timeline {
    position: relative;
    padding-left: 1.5rem;
}

.audit-timeline::before {
    content: '';
    position: absolute;
    left: 6px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e2e8f0;
}

.audit-entry {
    position: relative;
    padding-bottom: 1rem;
}

.audit-entry:last-child {
    padding-bottom: 0;
}

.audit-dot {
    position: absolute;
    left: -1.5rem;
    top: 4px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: #30ab0a;
    border: 2px solid white;
    box-shadow: 0 0 0 2px #e2e8f0;
}

.audit-content {
    padding-left: 0.5rem;
}

.audit-action {
    font-size: 0.9rem;
    color: #1e293b;
    margin-bottom: 0.25rem;
}

.audit-meta {
    display: flex;
    gap: 1rem;
    font-size: 0.8rem;
    color: #64748b;
}

.audit-user,
.audit-date {
    display: inline-flex;
    align-items: center;
}

/* Two-column conditions layout */
.conditions-two-col {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
}

.conditions-col .condition-table {
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    overflow: hidden;
}

/* Fault codes grid */
.fault-codes-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
}

.fault-code-item {
    padding: 0.625rem 0;
    border-bottom: 1px solid #f1f5f9;
}

.fault-code-item:last-child {
    border-bottom: none;
}

.fault-code-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.5rem;
}

.fault-code-label {
    font-weight: 600;
    color: #1e293b;
    font-size: 0.9rem;
}

.fault-code-desc {
    font-size: 0.85rem;
    color: #64748b;
    margin-top: 0.25rem;
}

.severity-badge {
    display: inline-block;
    font-size: 0.7rem;
    font-weight: 600;
    padding: 0.15rem 0.5rem;
    border-radius: 12px;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.severity-baja {
    background: #d1fae5;
    color: #059669;
}

.severity-media {
    background: #fef9c3;
    color: #a16207;
}

.severity-alta {
    background: #fef2f2;
    color: #ba2831;
}

/* Analysis subsections */
.analysis-subsection {
    padding: 0.75rem 0;
    border-bottom: 1px solid #f1f5f9;
}

.analysis-subsection:last-child {
    border-bottom: none;
}

.subsection-title {
    font-size: 0.85rem;
    font-weight: 600;
    color: #30ab0a;
    margin-bottom: 0.35rem;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.subsection-value {
    font-size: 0.9rem;
    color: #1e293b;
    margin: 0;
}

/* Email Modal */
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
    max-width: 420px;
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
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    font-size: 1.5rem;
}

.modal-icon-email {
    background: linear-gradient(135deg, #eff6ff, #dbeafe);
    color: #2563eb;
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
    margin-bottom: 1.25rem;
}

.email-input-wrapper {
    margin-bottom: 1.25rem;
}

.email-input {
    width: 100%;
    padding: 0.75rem 1rem;
    font-size: 0.95rem;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    transition: all 0.2s;
    color: #1e293b;
}

.email-input:focus {
    outline: none;
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.email-success-msg {
    color: #059669;
    font-size: 0.9rem;
    font-weight: 500;
    margin-bottom: 1rem;
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

.modal-btn-send {
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    color: white;
}

.modal-btn-send:hover:not(:disabled) {
    background: linear-gradient(135deg, #1d4ed8, #1e40af);
}

.modal-btn-send:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

/* Responsive */
@media (max-width: 768px) {
    .detail-grid {
        grid-template-columns: 1fr;
    }

    .detail-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.25rem;
    }

    .detail-value {
        text-align: left;
    }

    .signatures-grid {
        grid-template-columns: 1fr;
    }

    .conditions-two-col {
        grid-template-columns: 1fr;
    }

    .fault-codes-grid {
        grid-template-columns: 1fr;
    }

    .audit-meta {
        flex-direction: column;
        gap: 0.25rem;
    }
}

/* ── Anexos (galería) ── */
.anexo-group {
    margin-bottom: 1.1rem;
}

.anexo-group:last-child {
    margin-bottom: 0;
}

.anexo-group-label {
    font-size: 0.82rem;
    font-weight: 600;
    color: #475569;
    margin: 0 0 0.5rem;
}

.anexo-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(96px, 1fr));
    gap: 0.5rem;
}

.anexo-thumb {
    position: relative;
    aspect-ratio: 1;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    overflow: hidden;
    padding: 0;
    cursor: pointer;
    background: #f8fafc;
    transition: transform 0.15s, box-shadow 0.15s;
}

.anexo-thumb:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
}

.anexo-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.anexo-videos {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
    gap: 0.75rem;
}

.anexo-video {
    width: 100%;
    border-radius: 10px;
    background: #000;
    max-height: 280px;
}

/* ── Lightbox ── */
.lightbox-overlay {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.92);
    z-index: 3000;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem;
}

.lightbox-img {
    max-width: 92vw;
    max-height: 88vh;
    object-fit: contain;
    border-radius: 6px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
}

.lightbox-close,
.lightbox-nav {
    position: absolute;
    background: rgba(255, 255, 255, 0.12);
    border: none;
    color: #fff;
    width: 44px;
    height: 44px;
    border-radius: 50%;
    cursor: pointer;
    font-size: 1.1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.15s;
}

.lightbox-close:hover,
.lightbox-nav:hover {
    background: rgba(255, 255, 255, 0.25);
}

.lightbox-close {
    top: 1.2rem;
    right: 1.2rem;
}

.lightbox-prev {
    left: 1.2rem;
    top: 50%;
    transform: translateY(-50%);
}

.lightbox-next {
    right: 1.2rem;
    top: 50%;
    transform: translateY(-50%);
}

.lightbox-counter {
    position: absolute;
    bottom: 1.2rem;
    left: 50%;
    transform: translateX(-50%);
    color: #fff;
    font-size: 0.85rem;
    background: rgba(0, 0, 0, 0.4);
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
}
</style>
