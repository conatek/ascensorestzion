<template>
    <div>
        <!-- Cabecera -->
        <div class="app-page-title">
            <div class="page-title-wrapper">
                <div class="page-title-heading">
                    <div class="page-title-icon">
                        <i class="fa fa-clipboard-list icon-gradient bg-mean-fruit"></i>
                    </div>
                    <div>
                        {{ isEditMode ? 'Editar Informe de Servicio' : 'Nuevo Informe de Servicio' }}
                        <div class="page-title-subheading text-muted">
                            {{ isEditMode ? 'Modifica los datos del informe' : 'Completa el formulario paso a paso' }}
                        </div>
                    </div>
                </div>
                <div class="page-title-actions">
                    <router-link :to="{ name: 'reports.index' }" class="btn-action btn-back">
                        <i class="fa fa-arrow-left me-1"></i> Volver
                    </router-link>
                </div>
            </div>
        </div>

        <!-- Step Indicator -->
        <div class="autosave-bar" v-if="lastSaved">
            <span class="autosave-indicator">
                <i class="fa fa-save"></i> Borrador guardado: {{ lastSaved }}
            </span>
        </div>
        <div class="wizard-steps">
            <div
                v-for="(step, index) in steps"
                :key="index"
                class="wizard-step"
                :class="{
                    'is-active': currentStep === index + 1,
                    'is-completed': currentStep > index + 1,
                }"
                @click="goToStep(index + 1)"
            >
                <div class="step-circle">
                    <i v-if="currentStep > index + 1" class="fa fa-check"></i>
                    <span v-else>{{ index + 1 }}</span>
                </div>
                <span class="step-label">{{ step }}</span>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="loadingReport" class="text-center py-5">
            <span class="spinner spinner-lg"></span>
            <p class="mt-3 text-muted">Cargando informe...</p>
        </div>

        <template v-else>
            <!-- Step 1: Informacion Basica -->
            <div v-show="currentStep === 1" class="section-card">
                <div class="section-header">
                    <i class="fa fa-info-circle section-icon"></i>
                    <span>Informacion Basica</span>
                </div>
                <div class="section-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Cliente <span class="required">*</span></label>
                            <select v-model="selectedClientId" class="form-input"
                                    :class="{ 'has-error': stepErrors.client_id }"
                                    @change="onClientChange">
                                <option value="">Seleccionar cliente</option>
                                <option v-for="client in clients" :key="client.id" :value="client.id">
                                    {{ client.name }}
                                </option>
                            </select>
                            <span v-if="stepErrors.client_id" class="error-text">{{ stepErrors.client_id }}</span>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Sede <span class="required">*</span></label>
                            <select v-model="selectedSiteId" class="form-input"
                                    :class="{ 'has-error': stepErrors.site_id }"
                                    :disabled="!selectedClientId || loadingSites"
                                    @change="onSiteChange">
                                <option value="">{{ loadingSites ? 'Cargando...' : 'Seleccionar sede' }}</option>
                                <option v-for="site in sites" :key="site.id" :value="site.id">
                                    {{ site.name }}
                                </option>
                            </select>
                            <span v-if="stepErrors.site_id" class="error-text">{{ stepErrors.site_id }}</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Equipo <span class="required">*</span></label>
                        <select v-model="form.equipment_id" class="form-input"
                                :class="{ 'has-error': stepErrors.equipment_id }"
                                :disabled="!selectedSiteId || loadingEquipments"
                                @change="onEquipmentChange">
                            <option value="">{{ loadingEquipments ? 'Cargando...' : 'Seleccionar equipo' }}</option>
                            <option v-for="eq in equipments" :key="eq.id" :value="eq.id">
                                {{ eq.internal_code }} - {{ eq.brand }} {{ eq.model }}
                            </option>
                        </select>
                        <span v-if="stepErrors.equipment_id" class="error-text">{{ stepErrors.equipment_id }}</span>
                    </div>

                    <!-- Equipment Info -->
                    <div v-if="selectedEquipment" class="equipment-info">
                        <div class="info-row">
                            <span class="info-label">Marca:</span>
                            <span class="info-value">{{ selectedEquipment.brand || '-' }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Modelo:</span>
                            <span class="info-value">{{ selectedEquipment.model || '-' }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Tipo:</span>
                            <span class="info-value">{{ selectedEquipment.equipment_type || '-' }}</span>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Fecha del servicio <span class="required">*</span></label>
                            <input v-model="form.service_date" type="date" class="form-input"
                                   :class="{ 'has-error': stepErrors.service_date }" />
                            <span v-if="stepErrors.service_date" class="error-text">{{ stepErrors.service_date }}</span>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Ped. del cliente</label>
                            <input v-model="form.customer_request" type="text" class="form-input"
                                   placeholder="Pedido o solicitud del cliente" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 2: Condicion Inicial del Equipo -->
            <div v-show="currentStep === 2" class="section-card">
                <div class="section-header">
                    <i class="fa fa-clipboard-check section-icon"></i>
                    <span>Condicion Inicial del Equipo</span>
                </div>
                <div class="section-body">
                    <div v-if="loadingConditions" class="text-center py-4">
                        <span class="spinner"></span>
                        <p class="mt-2 text-muted">Cargando condiciones...</p>
                    </div>
                    <div v-else>
                        <!-- RSTP: Single column conditions -->
                        <div v-if="reportType === 'RSTP'">
                            <div class="condition-table">
                                <div class="condition-header">
                                    <span class="condition-col-label">Condicion</span>
                                    <span class="condition-col-radio">Si</span>
                                    <span class="condition-col-radio">No</span>
                                    <span class="condition-col-obs">Observacion</span>
                                </div>
                                <div v-for="(cond, index) in initialConditions" :key="cond.condition_key"
                                     class="condition-row">
                                    <span class="condition-col-label">{{ cond.label }}</span>
                                    <span class="condition-col-radio">
                                        <input type="radio" :name="'cond_' + index" value="si"
                                               v-model="cond.value" class="form-check-input" />
                                    </span>
                                    <span class="condition-col-radio">
                                        <input type="radio" :name="'cond_' + index" value="no"
                                               v-model="cond.value" class="form-check-input" />
                                    </span>
                                    <span class="condition-col-obs">
                                        <input v-model="cond.observation" type="text" class="form-input form-input-sm"
                                               placeholder="Observacion..." />
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- RSTC/RSTE: Two column conditions -->
                        <div v-if="reportType === 'RSTC' || reportType === 'RSTE'">
                            <div class="conditions-two-col">
                                <div class="condition-table">
                                    <div class="condition-header">
                                        <span class="condition-col-label">Condicion</span>
                                        <span class="condition-col-radio">Si</span>
                                        <span class="condition-col-radio">No</span>
                                        <span class="condition-col-obs">Observacion</span>
                                    </div>
                                    <div v-for="(cond, index) in conditionsLeft" :key="cond.condition_key"
                                         class="condition-row">
                                        <span class="condition-col-label">{{ cond.label }}</span>
                                        <span class="condition-col-radio">
                                            <input type="radio" :name="'cond_l_' + index" value="si"
                                                   v-model="cond.value" class="form-check-input" />
                                        </span>
                                        <span class="condition-col-radio">
                                            <input type="radio" :name="'cond_l_' + index" value="no"
                                                   v-model="cond.value" class="form-check-input" />
                                        </span>
                                        <span class="condition-col-obs">
                                            <input v-model="cond.observation" type="text" class="form-input form-input-sm"
                                                   placeholder="Observacion..." />
                                        </span>
                                    </div>
                                </div>
                                <div class="condition-table">
                                    <div class="condition-header">
                                        <span class="condition-col-label">Condicion</span>
                                        <span class="condition-col-radio">Si</span>
                                        <span class="condition-col-radio">No</span>
                                        <span class="condition-col-obs">Observacion</span>
                                    </div>
                                    <div v-for="(cond, index) in conditionsRight" :key="cond.condition_key"
                                         class="condition-row">
                                        <span class="condition-col-label">{{ cond.label }}</span>
                                        <span class="condition-col-radio">
                                            <input type="radio" :name="'cond_r_' + index" value="si"
                                                   v-model="cond.value" class="form-check-input" />
                                        </span>
                                        <span class="condition-col-radio">
                                            <input type="radio" :name="'cond_r_' + index" value="no"
                                                   v-model="cond.value" class="form-check-input" />
                                        </span>
                                        <span class="condition-col-obs">
                                            <input v-model="cond.observation" type="text" class="form-input form-input-sm"
                                                   placeholder="Observacion..." />
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Fault Codes -->
                            <div class="fault-codes-section">
                                <h6 class="subsection-title">Codigos de Falla</h6>
                                <div class="fault-codes-grid">
                                    <div v-for="fc in faultCodes" :key="fc.code" class="fault-code-item">
                                        <label class="activity-check">
                                            <input type="checkbox" v-model="fc.active" class="form-check-input" />
                                            <span class="fault-code-label">{{ fc.code }}</span>
                                        </label>
                                        <input v-model="fc.description" type="text" class="form-input form-input-sm"
                                               placeholder="Descripcion..." :disabled="!fc.active" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <p v-if="initialConditions.length === 0" class="text-muted text-center py-3">
                            No se encontraron condiciones para este tipo de informe.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Step 3: Type-specific section -->
            <div v-show="currentStep === 3">

                <!-- RSTP: Actividades Relevantes + Mes -->
                <template v-if="reportType === 'RSTP'">
                    <div class="section-card">
                        <div class="section-header">
                            <i class="fa fa-tasks section-icon"></i>
                            <span>Actividades Relevantes</span>
                        </div>
                        <div class="section-body">
                            <div v-if="loadingActivities" class="text-center py-4">
                                <span class="spinner"></span>
                                <p class="mt-2 text-muted">Cargando actividades...</p>
                            </div>
                            <div v-else>
                                <div v-for="(activities, groupKey) in groupedActivities" :key="groupKey"
                                     class="activity-group">
                                    <div class="activity-group-header" @click="toggleGroup(groupKey)">
                                        <i class="fa" :class="expandedGroups[groupKey] ? 'fa-chevron-down' : 'fa-chevron-right'"></i>
                                        <span>{{ groupLabels[groupKey] || groupKey }}</span>
                                        <span class="activity-count">{{ activities.length }} actividades</span>
                                    </div>
                                    <div v-show="expandedGroups[groupKey]" class="activity-group-body">
                                        <div v-for="act in activities" :key="act.activity_key"
                                             class="activity-row">
                                            <label class="activity-check">
                                                <input type="checkbox" v-model="act.is_ok" class="form-check-input" />
                                                <span>{{ act.label }}</span>
                                            </label>
                                            <input v-model="act.observation" type="text" class="form-input form-input-sm"
                                                   placeholder="Observacion..." />
                                        </div>
                                    </div>
                                </div>
                                <p v-if="rstpActivities.length === 0" class="text-muted text-center py-3">
                                    No se encontraron actividades para este tipo de informe.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Month Selector -->
                    <div class="section-card">
                        <div class="section-header">
                            <i class="fa fa-calendar-alt section-icon"></i>
                            <span>Mes del Servicio</span>
                        </div>
                        <div class="section-body">
                            <div class="form-group">
                                <label class="form-label">Ano</label>
                                <input v-model.number="rstpMonth.year" type="number" class="form-input"
                                       style="max-width: 140px;" :min="2020" :max="2050" />
                            </div>
                            <div class="month-grid">
                                <button v-for="(label, index) in monthLabels" :key="index"
                                        type="button"
                                        class="month-btn"
                                        :class="{ 'is-selected': rstpMonth.month === index + 1 }"
                                        @click="rstpMonth.month = index + 1">
                                    {{ label }}
                                </button>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- RSTC: Revision, Diagnostico y Solucion de Fallas -->
                <template v-if="reportType === 'RSTC'">
                    <div class="section-card">
                        <div class="section-header">
                            <i class="fa fa-search section-icon"></i>
                            <span>Revision, Diagnostico y Solucion de Fallas</span>
                        </div>
                        <div class="section-body">
                            <!-- Ubicacion de La Falla -->
                            <div class="form-group">
                                <label class="form-label fw-bold">Ubicacion de La Falla</label>
                                <div class="radio-group-vertical">
                                    <label class="radio-option" v-for="opt in faultLocationOptions" :key="opt.value">
                                        <input type="radio" v-model="rstcDetails.fault_location" :value="opt.value"
                                               class="form-check-input" />
                                        <span>{{ opt.label }}</span>
                                    </label>
                                    <div v-if="rstcDetails.fault_location === 'otra'" class="ms-radio-indent">
                                        <input v-model="rstcDetails.fault_location_other" type="text"
                                               class="form-input form-input-sm" placeholder="Especifique..." />
                                    </div>
                                </div>
                                <div class="form-group mt-2">
                                    <label class="form-label">Notas de analisis</label>
                                    <textarea v-model="rstcDetails.analysis_notes" class="form-input form-textarea"
                                              placeholder="Notas sobre el analisis de la falla..." rows="3"></textarea>
                                </div>
                            </div>

                            <!-- Causas de La Falla -->
                            <div class="form-group">
                                <label class="form-label fw-bold">Causas de La Falla</label>
                                <div class="radio-group-vertical">
                                    <label class="radio-option" v-for="opt in faultCauseOptions" :key="opt.value">
                                        <input type="radio" v-model="rstcDetails.fault_cause" :value="opt.value"
                                               class="form-check-input" />
                                        <span>{{ opt.label }}</span>
                                    </label>
                                    <div v-if="rstcDetails.fault_cause === 'otra'" class="ms-radio-indent">
                                        <input v-model="rstcDetails.fault_cause_other" type="text"
                                               class="form-input form-input-sm" placeholder="Especifique..." />
                                    </div>
                                </div>
                                <div class="form-group mt-2">
                                    <label class="form-label">Notas de causa</label>
                                    <textarea v-model="rstcDetails.cause_notes" class="form-input form-textarea"
                                              placeholder="Notas sobre la causa de la falla..." rows="3"></textarea>
                                </div>
                            </div>

                            <!-- Solucion de La Falla -->
                            <div class="form-group">
                                <label class="form-label fw-bold">Solucion de La Falla</label>
                                <div class="radio-group-vertical">
                                    <label class="radio-option" v-for="opt in faultSolutionOptions" :key="opt.value">
                                        <input type="radio" v-model="rstcDetails.fault_solution_area" :value="opt.value"
                                               class="form-check-input" />
                                        <span>{{ opt.label }}</span>
                                    </label>
                                    <div v-if="rstcDetails.fault_solution_area === 'otra'" class="ms-radio-indent">
                                        <input v-model="rstcDetails.fault_solution_other" type="text"
                                               class="form-input form-input-sm" placeholder="Especifique..." />
                                    </div>
                                </div>
                                <div class="form-group mt-2">
                                    <label class="form-label">Notas de solucion</label>
                                    <textarea v-model="rstcDetails.solution_notes" class="form-input form-textarea"
                                              placeholder="Notas sobre la solucion aplicada..." rows="3"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- RSTE: Trabajos y/o Reparaciones a Efectuar -->
                <template v-if="reportType === 'RSTE'">
                    <div class="section-card">
                        <div class="section-header">
                            <i class="fa fa-wrench section-icon"></i>
                            <span>Trabajos y/o Reparaciones a Efectuar</span>
                        </div>
                        <div class="section-body">
                            <div v-if="loadingRsteWorks" class="text-center py-4">
                                <span class="spinner"></span>
                                <p class="mt-2 text-muted">Cargando trabajos...</p>
                            </div>
                            <div v-else>
                                <div v-for="(works, groupKey) in groupedRsteWorks" :key="groupKey"
                                     class="activity-group">
                                    <div class="activity-group-header" @click="toggleGroup(groupKey)">
                                        <i class="fa" :class="expandedGroups[groupKey] ? 'fa-chevron-down' : 'fa-chevron-right'"></i>
                                        <span>{{ groupLabels[groupKey] || groupKey }}</span>
                                        <span class="activity-count">{{ works.length }} trabajos</span>
                                    </div>
                                    <div v-show="expandedGroups[groupKey]" class="activity-group-body">
                                        <div v-for="w in works" :key="w.work_key"
                                             class="activity-row">
                                            <label class="activity-check">
                                                <input type="checkbox" v-model="w.is_ok" class="form-check-input" />
                                                <span>{{ w.label }}</span>
                                            </label>
                                            <input v-model="w.observation" type="text" class="form-input form-input-sm"
                                                   placeholder="Observacion..." />
                                        </div>
                                    </div>
                                </div>
                                <p v-if="rsteWorks.length === 0" class="text-muted text-center py-3">
                                    No se encontraron trabajos para este tipo de informe.
                                </p>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Step 4: Conclusion -->
            <div v-show="currentStep === 4" class="section-card">
                <div class="section-header">
                    <i class="fa fa-flag-checkered section-icon"></i>
                    <span>Conclusion</span>
                </div>
                <div class="section-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">El equipo queda funcionando?</label>
                            <div class="radio-group">
                                <label class="radio-option">
                                    <input type="radio" v-model="form.equipment_operational" :value="true"
                                           class="form-check-input" />
                                    <span>Si</span>
                                </label>
                                <label class="radio-option">
                                    <input type="radio" v-model="form.equipment_operational" :value="false"
                                           class="form-check-input" />
                                    <span>No</span>
                                </label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Genera alguna cotizacion?</label>
                            <div class="radio-group">
                                <label class="radio-option">
                                    <input type="radio" v-model="form.generates_quote" :value="true"
                                           class="form-check-input" />
                                    <span>Si</span>
                                </label>
                                <label class="radio-option">
                                    <input type="radio" v-model="form.generates_quote" :value="false"
                                           class="form-check-input" />
                                    <span>No</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Requiere cambio de repuestos?</label>
                        <div class="radio-group">
                            <label class="radio-option">
                                <input type="radio" v-model="form.requires_parts" :value="true"
                                       class="form-check-input" />
                                <span>Si</span>
                            </label>
                            <label class="radio-option">
                                <input type="radio" v-model="form.requires_parts" :value="false"
                                       class="form-check-input" />
                                <span>No</span>
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Notas de conclusion</label>
                        <textarea v-model="form.conclusion_notes" class="form-input form-textarea"
                                  placeholder="Observaciones finales del servicio..." rows="4"></textarea>
                    </div>

                    <!-- RSTC: Call/Entry/Exit times + Response time -->
                    <div v-if="reportType === 'RSTC'" class="rstc-times-section">
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Hora de Llamada</label>
                                <input v-model="rstcDetails.call_time" type="datetime-local" class="form-input" />
                            </div>
                            <div class="form-group">
                                <label class="form-label">Hora de Entrada</label>
                                <input v-model="rstcDetails.entry_time" type="datetime-local" class="form-input" />
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Hora de Salida</label>
                                <input v-model="rstcDetails.exit_time" type="datetime-local" class="form-input" />
                            </div>
                            <div class="form-group">
                                <label class="form-label">Tiempo de Respuesta</label>
                                <div class="response-time-display">
                                    {{ rstcResponseTime || 'Ingrese hora de llamada y entrada' }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Hora entrada</label>
                            <input v-model="form.entry_time" type="time" class="form-input" />
                        </div>
                        <div class="form-group">
                            <label class="form-label">Hora salida</label>
                            <input v-model="form.exit_time" type="time" class="form-input" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 5: Firmas -->
            <div v-show="currentStep === 5">
                <div class="section-card">
                    <div class="section-header">
                        <i class="fa fa-pen-nib section-icon"></i>
                        <span>Firma del Tecnico</span>
                    </div>
                    <div class="section-body">
                        <SignaturePad ref="techSignature" :width="400" :height="150" />
                        <div class="form-row mt-3">
                            <div class="form-group">
                                <label class="form-label">Nombre</label>
                                <input :value="technicianName" type="text" class="form-input" disabled />
                            </div>
                            <div class="form-group">
                                <label class="form-label">CC</label>
                                <input :value="technicianCC" type="text" class="form-input" disabled />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="section-card">
                    <div class="section-header">
                        <i class="fa fa-signature section-icon"></i>
                        <span>Firma del Cliente</span>
                    </div>
                    <div class="section-body">
                        <SignaturePad ref="customerSignature" :width="400" :height="150" />
                        <div class="form-row mt-3">
                            <div class="form-group">
                                <label class="form-label">Nombre</label>
                                <input v-model="customerSigner.name" type="text" class="form-input"
                                       placeholder="Nombre de quien firma" />
                            </div>
                            <div class="form-group">
                                <label class="form-label">CC</label>
                                <input v-model="customerSigner.cc" type="text" class="form-input"
                                       placeholder="Cedula" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Actions -->
                <div v-if="generalError" class="error-alert">
                    <i class="fa fa-exclamation-circle"></i>
                    {{ generalError }}
                </div>

                <div class="form-actions signature-actions-row">
                    <button type="button" class="btn-draft" :disabled="saving" @click="saveDraft">
                        <span v-if="saving && savingType === 'draft'" class="spinner"></span>
                        <i v-else class="fa fa-save"></i>
                        {{ saving && savingType === 'draft' ? 'Guardando...' : 'Guardar Borrador' }}
                    </button>
                    <button type="button" class="btn-submit" :disabled="saving" @click="finalizeAndSign">
                        <span v-if="saving && savingType === 'finalize'" class="spinner"></span>
                        <i v-else class="fa fa-check-double"></i>
                        {{ saving && savingType === 'finalize' ? 'Finalizando...' : 'Finalizar y Firmar' }}
                    </button>
                </div>
            </div>

            <!-- Navigation Buttons -->
            <div class="wizard-nav">
                <button v-if="currentStep > 1" type="button" class="btn-cancel" @click="prevStep">
                    <i class="fa fa-arrow-left me-1"></i> Anterior
                </button>
                <span v-else></span>
                <button v-if="currentStep < 5" type="button" class="btn-submit" @click="nextStep">
                    Siguiente <i class="fa fa-arrow-right ms-1"></i>
                </button>
            </div>
        </template>
    </div>
</template>

<script>
import reportService from '@/services/reportService.js';
import clientService from '@/services/clientService.js';
import siteService from '@/services/siteService.js';
import equipmentService from '@/services/equipmentService.js';
import SignaturePad from '@/components/SignaturePad.vue';
import { useAuth } from '@/stores/auth';

export default {
    name: 'ServiceReportForm',

    components: {
        SignaturePad,
    },

    data() {
        const now = new Date();
        return {
            currentStep: 1,
            steps: [
                'Informacion Basica',
                'Condicion Inicial',
                'Actividades',
                'Conclusion',
                'Firmas',
            ],
            stepErrors: {},
            generalError: null,
            saving: false,
            savingType: null,
            loadingReport: false,
            autosaveTimer: null,
            lastSaved: null,

            // Step 1
            clients: [],
            sites: [],
            equipments: [],
            selectedClientId: '',
            selectedSiteId: '',
            selectedEquipment: null,
            loadingSites: false,
            loadingEquipments: false,
            form: {
                type: this.$route.query.type || 'RSTP',
                equipment_id: this.$route.query.equipment_id || '',
                service_date: now.toISOString().split('T')[0],
                customer_request: '',
                equipment_operational: true,
                generates_quote: false,
                requires_parts: false,
                conclusion_notes: '',
                entry_time: '',
                exit_time: '',
            },

            // Step 2
            initialConditions: [],
            loadingConditions: false,
            faultCodes: Array.from({length: 20}, (_, i) => ({code: `C${i+1}`, active: false, description: ''})),

            // Step 3 - RSTP
            rstpActivities: [],
            loadingActivities: false,
            expandedGroups: {},
            groupLabels: {
                cuarto_maquinas: 'Cuarto de Maquinas',
                cabina: 'Cabina',
                pozo_foso: 'Pozo-Foso',
            },
            monthLabels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
            rstpMonth: {
                year: now.getFullYear(),
                month: now.getMonth() + 1,
            },

            // Step 3 - RSTC
            rstcDetails: {
                call_time: '',
                entry_time: '',
                exit_time: '',
                response_time_hh: null,
                response_time_mm: null,
                fault_location: '',
                fault_location_other: '',
                fault_cause: '',
                fault_cause_other: '',
                fault_solution_area: '',
                fault_solution_other: '',
                analysis_notes: '',
                cause_notes: '',
                solution_notes: '',
            },

            // Step 3 - RSTE
            rsteWorks: [],
            loadingRsteWorks: false,

            // Step 5
            customerSigner: {
                name: '',
                cc: '',
            },
        };
    },

    watch: {
        form: {
            handler() {
                clearTimeout(this.autosaveTimer);
                this.autosaveTimer = setTimeout(() => this.autosaveDraft(), 30000);
            },
            deep: true,
        },
    },

    computed: {
        reportType() {
            return this.form.type || 'RSTP';
        },
        isEditMode() {
            return !!this.$route.params.id;
        },
        technicianName() {
            const { state } = useAuth();
            const user = state.user;
            if (!user) return '';
            return [user.first_name, user.last_name].filter(Boolean).join(' ') || user.name || '';
        },
        technicianCC() {
            const { state } = useAuth();
            return state.user?.document_number || state.user?.cc || '';
        },
        groupedActivities() {
            const groups = {};
            for (const act of this.rstpActivities) {
                const key = act.group_key || 'otros';
                if (!groups[key]) groups[key] = [];
                groups[key].push(act);
            }
            return groups;
        },
        conditionsLeft() {
            return this.initialConditions.filter(c => c.group_key === 'left');
        },
        conditionsRight() {
            return this.initialConditions.filter(c => c.group_key === 'right');
        },
        groupedRsteWorks() {
            const groups = {};
            for (const w of this.rsteWorks) {
                const key = w.group_key || 'otros';
                if (!groups[key]) groups[key] = [];
                groups[key].push(w);
            }
            return groups;
        },
        rstcResponseTime() {
            if (!this.rstcDetails.call_time || !this.rstcDetails.entry_time) return '';
            const call = new Date(this.rstcDetails.call_time);
            const entry = new Date(this.rstcDetails.entry_time);
            if (isNaN(call) || isNaN(entry)) return '';
            const diffMs = entry - call;
            if (diffMs < 0) return 'N/A';
            const totalMin = Math.floor(diffMs / 60000);
            const hh = Math.floor(totalMin / 60);
            const mm = totalMin % 60;
            return `${hh}h ${mm}m`;
        },
        faultLocationOptions() {
            return [
                { value: 'sistema_puertas', label: 'Sistema De Puertas' },
                { value: 'control_maniobras', label: 'Control De Maniobras' },
                { value: 'maquinaria', label: 'Maquinaria' },
                { value: 'recorrido', label: 'Recorrido' },
                { value: 'otra', label: 'Otra' },
            ];
        },
        faultCauseOptions() {
            return [
                { value: 'energia_externa_tormenta', label: 'Energia Externa/Tormenta Electrica' },
                { value: 'inundacion_humedad', label: 'Inundacion/Humedad' },
                { value: 'provocada_tercero', label: 'Provocada Por Tercero' },
                { value: 'tecnica_equipo', label: 'Tecnica Por El Equipo' },
                { value: 'otra', label: 'Otra' },
            ];
        },
        faultSolutionOptions() {
            return [
                { value: 'control_maniobras', label: 'Control De Maniobras' },
                { value: 'perifericos', label: 'Perifericos' },
                { value: 'puertas_piso', label: 'Puertas De Piso' },
                { value: 'operador_puertas_cabina', label: 'Operador/Puertas De Cabina' },
                { value: 'activacion_interruptores', label: 'Activacion Interruptores/Totalizador' },
                { value: 'otra', label: 'Otra' },
            ];
        },
    },

    async created() {
        await this.loadClients();
        await this.loadCatalogs();

        if (this.isEditMode) {
            await this.loadReport();
        } else if (this.$route.query.equipment_id) {
            await this.preloadEquipment(this.$route.query.equipment_id);
        }

        if (!this.isEditMode) {
            this.checkDraft();
        }
    },

    beforeUnmount() {
        clearTimeout(this.autosaveTimer);
    },

    methods: {
        // --- Autosave ---

        getDraftKey() {
            const { state } = useAuth();
            const userId = state.user?.id || 'anon';
            return `draft_report_${this.reportType}_${this.form.equipment_id || 'new'}_${userId}`;
        },

        autosaveDraft() {
            try {
                const draft = {
                    form: { ...this.form },
                    initialConditions: this.initialConditions,
                    rstpActivities: this.rstpActivities,
                    rstcDetails: { ...this.rstcDetails },
                    rsteWorks: this.rsteWorks,
                    faultCodes: this.faultCodes,
                    rstpMonth: { ...this.rstpMonth },
                };
                localStorage.setItem(this.getDraftKey(), JSON.stringify(draft));
                this.lastSaved = new Date().toLocaleTimeString();
            } catch {
                // localStorage may be full or unavailable
            }
        },

        checkDraft() {
            try {
                const key = this.getDraftKey();
                const raw = localStorage.getItem(key);
                if (!raw) return;

                if (!confirm('Se encontro un borrador guardado. ¿Desea recuperarlo?')) {
                    this.clearDraft();
                    return;
                }

                const draft = JSON.parse(raw);
                if (draft.form) Object.assign(this.form, draft.form);
                if (draft.initialConditions) this.initialConditions = draft.initialConditions;
                if (draft.rstpActivities) this.rstpActivities = draft.rstpActivities;
                if (draft.rstcDetails) Object.assign(this.rstcDetails, draft.rstcDetails);
                if (draft.rsteWorks) this.rsteWorks = draft.rsteWorks;
                if (draft.faultCodes) this.faultCodes = draft.faultCodes;
                if (draft.rstpMonth) Object.assign(this.rstpMonth, draft.rstpMonth);
            } catch {
                // Corrupted draft, ignore
            }
        },

        clearDraft() {
            try {
                localStorage.removeItem(this.getDraftKey());
            } catch {
                // Ignore
            }
        },

        // --- Data Loading ---

        async loadClients() {
            try {
                const { data } = await clientService.all();
                this.clients = data;
            } catch {
                this.clients = [];
            }
        },

        async onClientChange() {
            this.selectedSiteId = '';
            this.form.equipment_id = '';
            this.selectedEquipment = null;
            this.sites = [];
            this.equipments = [];
            if (!this.selectedClientId) return;

            this.loadingSites = true;
            try {
                const { data } = await siteService.all(this.selectedClientId);
                this.sites = data;
            } catch {
                this.sites = [];
            } finally {
                this.loadingSites = false;
            }
        },

        async onSiteChange() {
            this.form.equipment_id = '';
            this.selectedEquipment = null;
            this.equipments = [];
            if (!this.selectedSiteId) return;

            this.loadingEquipments = true;
            try {
                const { data } = await equipmentService.all({ site_id: this.selectedSiteId });
                this.equipments = data;
            } catch {
                this.equipments = [];
            } finally {
                this.loadingEquipments = false;
            }
        },

        onEquipmentChange() {
            const eq = this.equipments.find(e => e.id == this.form.equipment_id);
            this.selectedEquipment = eq || null;
        },

        async loadCatalogs() {
            this.loadingConditions = true;
            this.loadingActivities = true;

            // Load conditions based on report type
            const condScope = (this.reportType === 'RSTC' || this.reportType === 'RSTE') ? 'RSTC' : 'RSTP';
            try {
                const { data } = await reportService.getCatalogs(condScope, 'initial_condition');
                this.initialConditions = (data || []).map(c => ({
                    condition_key: c.key || c.id,
                    label: c.label || c.name,
                    group_key: c.group_key || '',
                    value: 'na',
                    observation: '',
                }));
            } catch {
                this.initialConditions = [];
            } finally {
                this.loadingConditions = false;
            }

            // Load RSTP activities
            if (this.reportType === 'RSTP') {
                try {
                    const { data } = await reportService.getCatalogs('RSTP', 'rstp_activity');
                    this.rstpActivities = (data || []).map(a => ({
                        activity_key: a.key || a.id,
                        label: a.label || a.name,
                        group_key: a.group_key || 'otros',
                        is_ok: false,
                        observation: '',
                    }));
                    for (const act of this.rstpActivities) {
                        this.expandedGroups[act.group_key || 'otros'] = true;
                    }
                } catch {
                    this.rstpActivities = [];
                } finally {
                    this.loadingActivities = false;
                }
            } else if (this.reportType === 'RSTE') {
                this.loadingRsteWorks = true;
                try {
                    const { data } = await reportService.getCatalogs('RSTE', 'rste_work');
                    this.rsteWorks = (data || []).map(w => ({
                        work_key: w.key || w.id,
                        label: w.label || w.name,
                        group_key: w.group_key || 'otros',
                        is_ok: false,
                        observation: '',
                    }));
                    for (const w of this.rsteWorks) {
                        this.expandedGroups[w.group_key || 'otros'] = true;
                    }
                } catch {
                    this.rsteWorks = [];
                } finally {
                    this.loadingRsteWorks = false;
                    this.loadingActivities = false;
                }
            } else {
                // RSTC - no activities/works catalog to load
                this.loadingActivities = false;
            }
        },

        async preloadEquipment(equipmentId) {
            try {
                const { data } = await equipmentService.get(equipmentId);
                const eq = data;
                if (eq && eq.site) {
                    const site = eq.site;
                    if (site.client_id) {
                        this.selectedClientId = site.client_id;
                        await this.onClientChange();
                        this.selectedSiteId = site.id;
                        await this.onSiteChange();
                        this.form.equipment_id = eq.id;
                        this.onEquipmentChange();
                    }
                }
            } catch {
                // Silently fail preload
            }
        },

        async loadReport() {
            this.loadingReport = true;
            try {
                const { data } = await reportService.get(this.$route.params.id);
                const report = data;

                // Basic info
                this.form.type = report.type || 'RSTP';
                this.form.equipment_id = report.equipment_id || '';
                this.form.service_date = report.service_date || '';
                this.form.customer_request = report.customer_request || '';
                this.form.equipment_operational = report.equipment_operational ?? true;
                this.form.generates_quote = report.generates_quote ?? false;
                this.form.requires_parts = report.requires_parts ?? false;
                this.form.conclusion_notes = report.conclusion_notes || '';
                this.form.entry_time = report.entry_time || '';
                this.form.exit_time = report.exit_time || '';

                // Cascading selects
                if (report.equipment && report.equipment.site) {
                    const site = report.equipment.site;
                    if (site.client_id) {
                        this.selectedClientId = site.client_id;
                        await this.onClientChange();
                        this.selectedSiteId = site.id;
                        await this.onSiteChange();
                        this.form.equipment_id = report.equipment_id;
                        this.onEquipmentChange();
                    }
                }

                // Initial conditions
                if (report.initial_conditions && report.initial_conditions.length) {
                    for (const saved of report.initial_conditions) {
                        const match = this.initialConditions.find(c => c.condition_key === saved.condition_key);
                        if (match) {
                            match.value = saved.value || 'na';
                            match.observation = saved.observation || '';
                        }
                    }
                }

                // RSTP Activities
                if (report.rstp_activities && report.rstp_activities.length) {
                    for (const saved of report.rstp_activities) {
                        const match = this.rstpActivities.find(a => a.activity_key === saved.activity_key);
                        if (match) {
                            match.is_ok = saved.is_ok ?? false;
                            match.observation = saved.observation || '';
                        }
                    }
                }

                // Month
                if (report.rstp_month) {
                    this.rstpMonth.year = report.rstp_month.year || new Date().getFullYear();
                    this.rstpMonth.month = report.rstp_month.month || (new Date().getMonth() + 1);
                }

                // RSTC details
                if (report.rstc_details) {
                    Object.assign(this.rstcDetails, report.rstc_details);
                }

                // Fault codes (for RSTC and RSTE)
                if (report.fault_codes && report.fault_codes.length) {
                    for (const saved of report.fault_codes) {
                        const match = this.faultCodes.find(fc => fc.code === saved.code);
                        if (match) {
                            match.active = true;
                            match.description = saved.description || '';
                        }
                    }
                }

                // RSTE works
                if (report.rste_works && report.rste_works.length) {
                    for (const saved of report.rste_works) {
                        const match = this.rsteWorks.find(w => w.work_key === saved.work_key);
                        if (match) {
                            match.is_ok = saved.is_ok ?? false;
                            match.observation = saved.observation || '';
                        }
                    }
                }

                // Customer signer
                if (report.customer_signer_name) {
                    this.customerSigner.name = report.customer_signer_name;
                }
                if (report.customer_signer_cc) {
                    this.customerSigner.cc = report.customer_signer_cc;
                }
            } catch (err) {
                this.generalError = 'Error al cargar el informe.';
            } finally {
                this.loadingReport = false;
            }
        },

        // --- Wizard Navigation ---

        goToStep(step) {
            if (step < this.currentStep) {
                this.currentStep = step;
            }
        },

        nextStep() {
            this.stepErrors = {};
            if (!this.validateCurrentStep()) return;
            if (this.currentStep < 5) {
                this.currentStep++;
            }
        },

        prevStep() {
            if (this.currentStep > 1) {
                this.currentStep--;
            }
        },

        validateCurrentStep() {
            if (this.currentStep === 1) {
                if (!this.form.equipment_id) {
                    this.stepErrors.equipment_id = 'Selecciona un equipo.';
                }
                if (!this.form.service_date) {
                    this.stepErrors.service_date = 'La fecha del servicio es requerida.';
                }
                return Object.keys(this.stepErrors).length === 0;
            }
            return true;
        },

        toggleGroup(groupKey) {
            this.expandedGroups[groupKey] = !this.expandedGroups[groupKey];
        },

        // --- Submit ---

        buildPayload() {
            const payload = {
                type: this.form.type,
                equipment_id: this.form.equipment_id,
                service_date: this.form.service_date,
                customer_request: this.form.customer_request,
                equipment_operational: this.form.equipment_operational,
                generates_quote: this.form.generates_quote,
                requires_parts: this.form.requires_parts,
                conclusion_notes: this.form.conclusion_notes,
                entry_time: this.form.entry_time,
                exit_time: this.form.exit_time,
                initial_conditions: this.initialConditions.map(c => ({
                    condition_key: c.condition_key,
                    value: c.value,
                    observation: c.observation,
                })),
            };

            if (this.reportType === 'RSTP') {
                payload.rstp_activities = this.rstpActivities.map(a => ({
                    activity_key: a.activity_key,
                    is_ok: a.is_ok,
                    observation: a.observation,
                }));
                payload.rstp_month = {
                    year: this.rstpMonth.year,
                    month: this.rstpMonth.month,
                };
            }

            if (this.reportType === 'RSTC') {
                payload.rstc_details = { ...this.rstcDetails };
                payload.fault_codes = this.faultCodes
                    .filter(fc => fc.active)
                    .map(fc => ({ code: fc.code, description: fc.description }));
            }

            if (this.reportType === 'RSTE') {
                payload.rste_works = this.rsteWorks.map(w => ({
                    work_key: w.work_key,
                    is_ok: w.is_ok,
                    observation: w.observation,
                }));
                payload.fault_codes = this.faultCodes
                    .filter(fc => fc.active)
                    .map(fc => ({ code: fc.code, description: fc.description }));
            }

            return payload;
        },

        async saveDraft() {
            this.saving = true;
            this.savingType = 'draft';
            this.generalError = null;

            try {
                const payload = this.buildPayload();
                let response;
                if (this.isEditMode) {
                    response = await reportService.update(this.$route.params.id, payload);
                } else {
                    response = await reportService.store(payload);
                }
                const reportId = response.data.id || this.$route.params.id;
                this.clearDraft();
                this.$router.push({ name: 'reports.show', params: { id: reportId } });
            } catch (err) {
                if (err.response?.status === 422) {
                    this.generalError = Object.values(err.response.data.errors || {}).flat().join('. ');
                } else {
                    this.generalError = err.response?.data?.message || 'Error al guardar el borrador.';
                }
            } finally {
                this.saving = false;
                this.savingType = null;
            }
        },

        async finalizeAndSign() {
            this.saving = true;
            this.savingType = 'finalize';
            this.generalError = null;

            try {
                const payload = this.buildPayload();
                let reportId;

                // Save first
                if (this.isEditMode) {
                    const response = await reportService.update(this.$route.params.id, payload);
                    reportId = response.data.id || this.$route.params.id;
                } else {
                    const response = await reportService.store(payload);
                    reportId = response.data.id;
                }

                // Sign technician
                const techSig = this.$refs.techSignature?.toDataURL();
                if (techSig) {
                    await reportService.signTechnician(reportId, {
                        signature: techSig,
                    });
                }

                // Sign customer
                const custSig = this.$refs.customerSignature?.toDataURL();
                if (custSig) {
                    await reportService.signCustomer(reportId, {
                        signature: custSig,
                        name: this.customerSigner.name,
                        cc: this.customerSigner.cc,
                    });
                }

                this.clearDraft();
                this.$router.push({ name: 'reports.show', params: { id: reportId } });
            } catch (err) {
                if (err.response?.status === 422) {
                    this.generalError = Object.values(err.response.data.errors || {}).flat().join('. ');
                } else {
                    this.generalError = err.response?.data?.message || 'Error al finalizar el informe.';
                }
            } finally {
                this.saving = false;
                this.savingType = null;
            }
        },
    },
};
</script>

<style scoped>
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

/* Autosave Indicator */
.autosave-bar {
    display: flex;
    justify-content: flex-end;
    margin-bottom: 0.5rem;
}

.autosave-indicator {
    font-size: 0.78rem;
    color: #94a3b8;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
}

/* Wizard Steps */
.wizard-steps {
    display: flex;
    justify-content: center;
    align-items: flex-start;
    gap: 0.5rem;
    margin-bottom: 2rem;
    padding: 1.25rem;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    border: 1px solid #e2e8f0;
}

.wizard-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    flex: 1;
    cursor: pointer;
    position: relative;
}

.wizard-step:not(:last-child)::after {
    content: '';
    position: absolute;
    top: 16px;
    left: calc(50% + 20px);
    width: calc(100% - 40px);
    height: 2px;
    background: #e2e8f0;
}

.wizard-step.is-completed:not(:last-child)::after {
    background: #30ab0a;
}

.step-circle {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.85rem;
    font-weight: 600;
    background: #f1f5f9;
    color: #606060;
    border: 2px solid #e2e8f0;
    transition: all 0.2s;
    position: relative;
    z-index: 1;
}

.wizard-step.is-active .step-circle {
    background: #30ab0a;
    color: white;
    border-color: #30ab0a;
}

.wizard-step.is-completed .step-circle {
    background: #279208;
    color: white;
    border-color: #279208;
}

.step-label {
    font-size: 0.75rem;
    font-weight: 500;
    color: #606060;
    text-align: center;
    line-height: 1.2;
}

.wizard-step.is-active .step-label {
    color: #30ab0a;
    font-weight: 600;
}

.wizard-step.is-completed .step-label {
    color: #279208;
}

/* Section Card */
.section-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    border: 1px solid #e2e8f0;
    overflow: hidden;
    margin-bottom: 1.5rem;
}

.section-header {
    padding: 1rem 1.25rem;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-weight: 600;
    color: #1e293b;
}

.section-icon {
    width: 32px;
    height: 32px;
    background: #e8f5e4;
    color: #279208;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.9rem;
}

.section-body {
    padding: 1.5rem;
}

/* Form Elements */
.form-group {
    margin-bottom: 1.25rem;
}

.form-group:last-child {
    margin-bottom: 0;
}

.form-label {
    display: block;
    font-size: 0.875rem;
    font-weight: 500;
    color: #374151;
    margin-bottom: 0.5rem;
}

.required {
    color: #ba2831;
}

.form-input {
    width: 100%;
    padding: 0.625rem 0.875rem;
    font-size: 0.95rem;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    background: white;
    transition: all 0.2s;
}

.form-input:focus {
    outline: none;
    border-color: #279208;
    box-shadow: 0 0 0 3px rgba(39, 146, 8, 0.1);
}

.form-input.has-error {
    border-color: #ba2831;
}

.form-input::placeholder {
    color: #94a3b8;
}

.form-input:disabled {
    background: #f8fafc;
    color: #94a3b8;
    cursor: not-allowed;
}

.form-input-sm {
    padding: 0.375rem 0.625rem;
    font-size: 0.85rem;
}

.form-textarea {
    resize: vertical;
    min-height: 100px;
}

.error-text {
    display: block;
    font-size: 0.8rem;
    color: #ba2831;
    margin-top: 0.375rem;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

@media (max-width: 576px) {
    .form-row {
        grid-template-columns: 1fr;
    }
}

/* Equipment Info Box */
.equipment-info {
    background: #f0fdf0;
    border: 1px solid #bbf7d0;
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 1.25rem;
    display: flex;
    gap: 2rem;
    flex-wrap: wrap;
}

.info-row {
    display: flex;
    gap: 0.5rem;
    align-items: center;
}

.info-label {
    font-size: 0.85rem;
    font-weight: 600;
    color: #374151;
}

.info-value {
    font-size: 0.85rem;
    color: #606060;
}

/* Condition Table */
.condition-table {
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    overflow: hidden;
}

.condition-header {
    display: grid;
    grid-template-columns: 1fr 50px 50px 1fr;
    gap: 0.5rem;
    padding: 0.75rem 1rem;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    font-size: 0.8rem;
    font-weight: 600;
    color: #374151;
    text-align: center;
}

.condition-header .condition-col-label {
    text-align: left;
}

.condition-row {
    display: grid;
    grid-template-columns: 1fr 50px 50px 1fr;
    gap: 0.5rem;
    padding: 0.625rem 1rem;
    align-items: center;
    border-bottom: 1px solid #f1f5f9;
}

.condition-row:last-child {
    border-bottom: none;
}

.condition-col-label {
    font-size: 0.875rem;
    color: #374151;
}

.condition-col-radio {
    text-align: center;
}

.condition-col-obs {
    min-width: 0;
}

/* Activity Groups */
.activity-group {
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    overflow: hidden;
    margin-bottom: 1rem;
}

.activity-group:last-child {
    margin-bottom: 0;
}

.activity-group-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    background: #f8fafc;
    cursor: pointer;
    font-weight: 600;
    font-size: 0.9rem;
    color: #1e293b;
    user-select: none;
    transition: background 0.2s;
}

.activity-group-header:hover {
    background: #f1f5f9;
}

.activity-group-header i {
    font-size: 0.75rem;
    color: #606060;
    width: 12px;
}

.activity-count {
    margin-left: auto;
    font-size: 0.75rem;
    font-weight: 400;
    color: #94a3b8;
}

.activity-group-body {
    border-top: 1px solid #e2e8f0;
}

.activity-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.75rem;
    padding: 0.625rem 1rem;
    align-items: center;
    border-bottom: 1px solid #f1f5f9;
}

.activity-row:last-child {
    border-bottom: none;
}

.activity-check {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    color: #374151;
    cursor: pointer;
    margin: 0;
}

/* Radio Group */
.radio-group {
    display: flex;
    gap: 1.5rem;
}

.radio-option {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.9rem;
    color: #374151;
    cursor: pointer;
    margin: 0;
}

/* Month Grid */
.month-grid {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 0.5rem;
    margin-top: 0.75rem;
}

@media (max-width: 576px) {
    .month-grid {
        grid-template-columns: repeat(4, 1fr);
    }
}

.month-btn {
    padding: 0.5rem 0.25rem;
    font-size: 0.85rem;
    font-weight: 500;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    background: white;
    color: #374151;
    cursor: pointer;
    transition: all 0.2s;
    text-align: center;
}

.month-btn:hover {
    background: #f0fdf0;
    border-color: #30ab0a;
}

.month-btn.is-selected {
    background: #30ab0a;
    color: white;
    border-color: #30ab0a;
}

/* Error Alert */
.error-alert {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem;
    background: #fef2f2;
    border: 1px solid #fecaca;
    border-radius: 8px;
    color: #dc2626;
    font-size: 0.9rem;
    margin-bottom: 1.5rem;
}

/* Wizard Navigation */
.wizard-nav {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 0.5rem;
}

/* Form Actions */
.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
}

.signature-actions-row {
    margin-bottom: 1rem;
}

.btn-cancel {
    padding: 0.625rem 1.25rem;
    font-size: 0.9rem;
    font-weight: 500;
    background: #f1f5f9;
    color: #475569;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.2s;
}

.btn-cancel:hover {
    background: #e2e8f0;
    color: #334155;
}

.btn-submit {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.625rem 1.25rem;
    font-size: 0.9rem;
    font-weight: 500;
    background: #279208;
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-submit:hover:not(:disabled) {
    background: #1f7506;
}

.btn-submit:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

.btn-draft {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.625rem 1.25rem;
    font-size: 0.9rem;
    font-weight: 500;
    background: #606060;
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-draft:hover:not(:disabled) {
    background: #4a4a4a;
}

.btn-draft:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

.spinner {
    width: 16px;
    height: 16px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-top-color: white;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

.spinner-lg {
    width: 32px;
    height: 32px;
    border: 3px solid #e2e8f0;
    border-top-color: #30ab0a;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
    display: inline-block;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Two column conditions (RSTC/RSTE) */
.conditions-two-col {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

@media (max-width: 992px) {
    .conditions-two-col {
        grid-template-columns: 1fr;
    }
}

/* Fault Codes */
.fault-codes-section {
    margin-top: 1.5rem;
}

.subsection-title {
    font-size: 0.95rem;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #e2e8f0;
}

.fault-codes-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 0.5rem;
}

@media (max-width: 768px) {
    .fault-codes-grid {
        grid-template-columns: 1fr;
    }
}

.fault-code-item {
    display: grid;
    grid-template-columns: auto 1fr;
    gap: 0.5rem;
    align-items: center;
    padding: 0.375rem 0.5rem;
    border: 1px solid #f1f5f9;
    border-radius: 6px;
}

.fault-code-label {
    font-weight: 600;
    font-size: 0.85rem;
    min-width: 32px;
}

/* Radio group vertical (RSTC) */
.radio-group-vertical {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.fw-bold {
    font-weight: 600;
}

.ms-radio-indent {
    margin-left: 1.75rem;
    margin-top: 0.25rem;
}

/* RSTC Times */
.rstc-times-section {
    margin-bottom: 1.25rem;
    padding-bottom: 1.25rem;
    border-bottom: 1px solid #e2e8f0;
}

.response-time-display {
    padding: 0.625rem 0.875rem;
    font-size: 0.95rem;
    background: #f0fdf0;
    border: 1px solid #bbf7d0;
    border-radius: 8px;
    color: #374151;
    font-weight: 500;
}

/* Utilities */
.text-center { text-align: center; }
.text-muted { color: #94a3b8; }
.py-3 { padding-top: 1rem; padding-bottom: 1rem; }
.py-4 { padding-top: 1.5rem; padding-bottom: 1.5rem; }
.py-5 { padding-top: 3rem; padding-bottom: 3rem; }
.mt-2 { margin-top: 0.5rem; }
.mt-3 { margin-top: 1rem; }
.me-1 { margin-right: 0.25rem; }
.ms-1 { margin-left: 0.25rem; }
</style>
