<template>
    <div class="tech-report-form">
        <!-- Sticky: Header + Steps + Titulo/Progreso del paso actual -->
        <div class="report-sticky-top">
            <!-- Header con tipo de reporte -->
            <div class="report-header" :class="'report-header--' + reportType">
                <span class="report-header__type">{{ reportType }}</span>
                <span class="report-header__label">{{ reportTypeLabel }}</span>
                <span v-if="reportNumber" class="report-header__number">{{ reportNumber }}</span>
            </div>

            <!-- Step indicator -->
            <div class="step-bar">
                <div
                    v-for="(step, i) in steps"
                    :key="i"
                    class="step-bar__item"
                    :class="{
                        'is-active': currentStep === i,
                        'is-completed': currentStep > i,
                    }"
                    @click="goToStep(i)"
                >
                    <div class="step-bar__circle">
                        <i v-if="currentStep > i" class="fa fa-check"></i>
                        <span v-else>{{ i + 1 }}</span>
                    </div>
                    <span class="step-bar__label">{{ step }}</span>
                </div>
            </div>

            <!-- Titulo + progreso del paso actual -->
            <div class="step-sticky-info">
                <!-- Paso 1 -->
                <template v-if="currentStep === 0">
                    <div class="step-title-row">
                        <h2 class="step-title">Condición Inicial</h2>
                        <span class="step-progress">{{ conditionsAnswered }}/{{ conditions.length }}</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-bar__fill" :style="{ width: conditionsProgress + '%' }"></div>
                    </div>
                </template>

                <!-- Paso 2: RSTP -->
                <template v-else-if="currentStep === 1 && reportType === 'RSTP'">
                    <div class="step-title-row">
                        <h2 class="step-title">Actividades</h2>
                        <span class="step-progress">{{ activitiesAnswered }}/{{ allActivities.length }}</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-bar__fill" :style="{ width: activitiesProgress + '%' }"></div>
                    </div>
                </template>

                <!-- Paso 2: RSTC -->
                <template v-else-if="currentStep === 1 && reportType === 'RSTC'">
                    <div class="step-title-row">
                        <h2 class="step-title">Detalles de la Falla</h2>
                    </div>
                </template>

                <!-- Paso 2: RSTE -->
                <template v-else-if="currentStep === 1 && reportType === 'RSTE'">
                    <div class="step-title-row">
                        <h2 class="step-title">Trabajos Especiales</h2>
                        <span class="step-progress">{{ rsteWorksAnswered }}/{{ allRsteWorks.length }}</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-bar__fill" :style="{ width: rsteWorksProgress + '%' }"></div>
                    </div>
                </template>

                <!-- Paso 3 -->
                <template v-else-if="currentStep === 2">
                    <div class="step-title-row">
                        <h2 class="step-title">Conclusión</h2>
                    </div>
                </template>

                <!-- Paso 4 -->
                <template v-else-if="currentStep === 3">
                    <div class="step-title-row">
                        <h2 class="step-title">Firma y Finalización</h2>
                    </div>
                </template>
            </div>
        </div>

        <!-- Spacer para compensar el bloque fixed -->
        <div ref="stickySpacerRef" class="sticky-spacer"></div>

        <!-- Loading -->
        <div v-if="loading" class="report-loading">
            <i class="fa fa-spinner fa-spin"></i> Cargando...
        </div>

        <template v-else>
            <!-- ===== PASO 1: CONDICION INICIAL ===== -->
            <div v-show="currentStep === 0" class="step-content">
                <div class="items-list">
                    <ConditionItem
                        v-for="(cond, idx) in conditions"
                        :key="cond.key"
                        :condition="cond"
                        :value="cond.value"
                        :observation="cond.observation"
                        :photo="cond.photo"
                        @update:value="cond.value = $event"
                        @update:observation="cond.observation = $event"
                        @update:photo="cond.photo = $event"
                    />
                </div>
            </div>

            <!-- ===== PASO 2: ACTIVIDADES ===== -->
            <div v-show="currentStep === 1" class="step-content">
                <!-- RSTP: Actividades agrupadas -->
                <template v-if="reportType === 'RSTP'">
                    <!-- Selector de mes -->
                    <div class="month-selector">
                        <label>Mes de mantenimiento:</label>
                        <div class="month-row">
                            <select v-model="rstpMonth.month" class="month-select">
                                <option v-for="(m, i) in monthLabels" :key="i" :value="i + 1">{{ m }}</option>
                            </select>
                            <input v-model.number="rstpMonth.year" type="number" min="2020" max="2099" class="year-input" />
                        </div>
                    </div>

                    <div v-for="group in activityGroups" :key="group.key" class="activity-group">
                        <button type="button" class="group-header" @click="group.expanded = !group.expanded">
                            <span class="group-header__label">{{ group.label }}</span>
                            <span class="group-header__count">{{ groupAnswered(group) }}/{{ group.items.length }}</span>
                            <i class="fa" :class="group.expanded ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                        </button>
                        <div v-show="group.expanded" class="group-items">
                            <ActivityItem
                                v-for="item in group.items"
                                :key="item.key"
                                :activity="item"
                                :value="item.value"
                                :observation="item.observation"
                                :photo="item.photo"
                                @update:value="item.value = $event"
                                @update:observation="item.observation = $event"
                                @update:photo="item.photo = $event"
                            />
                        </div>
                    </div>
                </template>

                <!-- RSTC: Detalles de falla -->
                <template v-else-if="reportType === 'RSTC'">
                    <div class="rstc-section">
                        <label class="field-label">Ubicación de la falla</label>
                        <div class="radio-group">
                            <label v-for="opt in faultLocationOptions" :key="opt.value" class="radio-option" :class="{ 'is-selected': rstcDetails.fault_location === opt.value }">
                                <input type="radio" v-model="rstcDetails.fault_location" :value="opt.value" />
                                <span>{{ opt.label }}</span>
                            </label>
                        </div>
                        <input v-if="rstcDetails.fault_location === 'otra'" v-model="rstcDetails.fault_location_other" class="field-input" placeholder="Especifique..." spellcheck="true" lang="es" />
                    </div>

                    <div class="rstc-section">
                        <label class="field-label">Causa de la falla</label>
                        <div class="radio-group">
                            <label v-for="opt in faultCauseOptions" :key="opt.value" class="radio-option" :class="{ 'is-selected': rstcDetails.fault_cause === opt.value }">
                                <input type="radio" v-model="rstcDetails.fault_cause" :value="opt.value" />
                                <span>{{ opt.label }}</span>
                            </label>
                        </div>
                        <input v-if="rstcDetails.fault_cause === 'otra'" v-model="rstcDetails.fault_cause_other" class="field-input" placeholder="Especifique..." spellcheck="true" lang="es" />
                    </div>

                    <div class="rstc-section">
                        <label class="field-label">Área de solución</label>
                        <div class="radio-group">
                            <label v-for="opt in faultSolutionOptions" :key="opt.value" class="radio-option" :class="{ 'is-selected': rstcDetails.fault_solution_area === opt.value }">
                                <input type="radio" v-model="rstcDetails.fault_solution_area" :value="opt.value" />
                                <span>{{ opt.label }}</span>
                            </label>
                        </div>
                        <input v-if="rstcDetails.fault_solution_area === 'otra'" v-model="rstcDetails.fault_solution_other" class="field-input" placeholder="Especifique..." spellcheck="true" lang="es" />
                    </div>

                    <div class="rstc-section">
                        <label class="field-label">Análisis</label>
                        <textarea v-model="rstcDetails.analysis_notes" class="field-textarea" rows="3" spellcheck="true" lang="es" placeholder="Describa el análisis..."></textarea>
                    </div>
                    <div class="rstc-section">
                        <label class="field-label">Causa</label>
                        <textarea v-model="rstcDetails.cause_notes" class="field-textarea" rows="3" spellcheck="true" lang="es" placeholder="Describa la causa..."></textarea>
                    </div>
                    <div class="rstc-section">
                        <label class="field-label">Solución</label>
                        <textarea v-model="rstcDetails.solution_notes" class="field-textarea" rows="3" spellcheck="true" lang="es" placeholder="Describa la solución aplicada..."></textarea>
                    </div>
                </template>

                <!-- RSTE: Trabajos especiales -->
                <template v-else-if="reportType === 'RSTE'">
                    <div v-for="group in rsteWorkGroups" :key="group.key" class="activity-group">
                        <button type="button" class="group-header" @click="group.expanded = !group.expanded">
                            <span class="group-header__label">{{ group.label }}</span>
                            <span class="group-header__count">{{ groupAnswered(group) }}/{{ group.items.length }}</span>
                            <i class="fa" :class="group.expanded ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                        </button>
                        <div v-show="group.expanded" class="group-items">
                            <ActivityItem
                                v-for="item in group.items"
                                :key="item.key"
                                :activity="item"
                                :value="item.value"
                                :observation="item.observation"
                                :photo="item.photo"
                                @update:value="item.value = $event"
                                @update:observation="item.observation = $event"
                                @update:photo="item.photo = $event"
                            />
                        </div>
                    </div>
                </template>
            </div>

            <!-- ===== PASO 3: CONCLUSION ===== -->
            <div v-show="currentStep === 2" class="step-content">
                <div class="conclusion-toggles">
                    <div class="conclusion-toggle-item">
                        <span class="conclusion-toggle-label">Equipo queda funcional</span>
                        <div class="mini-toggle" :class="conclusion.equipment_functional === null ? 'is-unset' : conclusion.equipment_functional ? 'is-on' : 'is-off'" @click="conclusion.equipment_functional = conclusion.equipment_functional === null ? true : !conclusion.equipment_functional">
                            <span class="mini-toggle__text">{{ conclusion.equipment_functional === null ? '—' : conclusion.equipment_functional ? 'Sí' : 'No' }}</span>
                        </div>
                    </div>
                    <div class="conclusion-toggle-item">
                        <span class="conclusion-toggle-label">Genera cotización</span>
                        <div class="mini-toggle" :class="conclusion.generates_quotation === null ? 'is-unset' : conclusion.generates_quotation ? 'is-on' : 'is-off'" @click="conclusion.generates_quotation = conclusion.generates_quotation === null ? true : !conclusion.generates_quotation">
                            <span class="mini-toggle__text">{{ conclusion.generates_quotation === null ? '—' : conclusion.generates_quotation ? 'Sí' : 'No' }}</span>
                        </div>
                    </div>
                    <div class="conclusion-toggle-item">
                        <span class="conclusion-toggle-label">Requiere cambio de repuestos</span>
                        <div class="mini-toggle" :class="conclusion.requires_parts === null ? 'is-unset' : conclusion.requires_parts ? 'is-on' : 'is-off'" @click="conclusion.requires_parts = conclusion.requires_parts === null ? true : !conclusion.requires_parts">
                            <span class="mini-toggle__text">{{ conclusion.requires_parts === null ? '—' : conclusion.requires_parts ? 'Sí' : 'No' }}</span>
                        </div>
                    </div>
                </div>

                <div class="conclusion-notes">
                    <label class="field-label">Notas de conclusión</label>
                    <textarea v-model="conclusion.notes" class="field-textarea" rows="4" spellcheck="true" lang="es" placeholder="Escriba las notas de conclusión..."></textarea>
                </div>

                <div class="conclusion-video">
                    <label class="field-label">Video (opcional)</label>
                    <VideoCapture v-model="conclusion.video" />
                </div>
            </div>

            <!-- ===== PASO 4: FIRMA ===== -->
            <div v-show="currentStep === 3" class="step-content">
                <!-- Resumen -->
                <div class="sign-summary">
                    <div class="summary-row">
                        <span>Hora de entrada</span>
                        <strong>{{ timeIn || '—' }}</strong>
                    </div>
                    <div class="summary-row">
                        <span>Hora de salida</span>
                        <strong>{{ timeOut }}</strong>
                    </div>
                    <div class="summary-row">
                        <span>Condiciones respondidas</span>
                        <strong>{{ conditionsAnswered }}/{{ conditions.length }}</strong>
                    </div>
                    <div v-if="reportType === 'RSTP'" class="summary-row">
                        <span>Actividades</span>
                        <strong>{{ activitiesAnswered }}/{{ allActivities.length }}</strong>
                    </div>
                    <div class="summary-row">
                        <span>Equipo funcional</span>
                        <strong>{{ conclusion.equipment_functional === null ? '—' : conclusion.equipment_functional ? 'Sí' : 'No' }}</strong>
                    </div>
                </div>

                <!-- Firma tecnico -->
                <div class="sign-section">
                    <label class="field-label">Firma del técnico</label>
                    <div class="signature-canvas-wrap">
                        <canvas ref="techSignature" class="signature-canvas" @mousedown="startSign('tech', $event)" @touchstart.prevent="startSign('tech', $event)"></canvas>
                        <button type="button" class="clear-sign-btn" @click="clearSign('tech')">
                            <i class="fa fa-eraser"></i> Borrar
                        </button>
                    </div>
                </div>

                <!-- Datos firmante cliente -->
                <div class="sign-section">
                    <label class="field-label">Nombre del firmante (cliente)</label>
                    <input v-model="customerSigner.name" class="field-input" placeholder="Nombre completo" spellcheck="true" lang="es" />
                    <label class="field-label" style="margin-top: 0.5rem;">Número de documento</label>
                    <input v-model="customerSigner.document" class="field-input" placeholder="CC / NIT" />
                </div>

                <!-- Firma cliente -->
                <div class="sign-section">
                    <label class="field-label">Firma del cliente</label>
                    <div class="signature-canvas-wrap">
                        <canvas ref="customerSignature" class="signature-canvas" @mousedown="startSign('customer', $event)" @touchstart.prevent="startSign('customer', $event)"></canvas>
                        <button type="button" class="clear-sign-btn" @click="clearSign('customer')">
                            <i class="fa fa-eraser"></i> Borrar
                        </button>
                    </div>
                </div>
            </div>
        </template>

        <!-- Navegacion inferior -->
        <div class="nav-bottom">
            <button v-if="currentStep > 0" type="button" class="nav-btn nav-btn--back" @click="prevStep">
                <i class="fa fa-arrow-left"></i> Anterior
            </button>
            <div v-else></div>

            <button v-if="currentStep < steps.length - 1" type="button" class="nav-btn nav-btn--next" :disabled="!canAdvance" @click="nextStep">
                Siguiente <i class="fa fa-arrow-right"></i>
            </button>
            <button v-else type="button" class="nav-btn nav-btn--finalize" :disabled="saving" @click="finalizeAndSign">
                <i v-if="saving" class="fa fa-spinner fa-spin"></i>
                <template v-else><i class="fa fa-check-circle"></i> Finalizar y Firmar</template>
            </button>
        </div>

        <!-- Autosave indicator -->
        <div v-if="lastSaved" class="autosave-indicator">
            <i class="fa fa-save"></i> Guardado: {{ lastSaved }}
        </div>
    </div>
</template>

<script>
import ConditionItem from '@/components/tech/ConditionItem.vue';
import ActivityItem from '@/components/tech/ActivityItem.vue';
import VideoCapture from '@/components/tech/VideoCapture.vue';
import reportService from '@/services/reportService.js';
import offlineManager from '@/utils/offlineManager.js';

const GROUP_LABELS = {
    cuarto_maquinas: 'Cuarto de Máquinas',
    cabina: 'Cabina',
    pozo_foso: 'Pozo / Foso',
};

const MONTH_LABELS = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

export default {
    name: 'TechReportForm',
    components: { ConditionItem, ActivityItem, VideoCapture },

    data() {
        const now = new Date();
        return {
            loading: true,
            saving: false,
            currentStep: 0,
            steps: ['Condición Inicial', 'Actividades', 'Conclusión', 'Firma'],
            reportId: null,
            isOffline: false,
            reportNumber: null,
            reportType: this.$route.query.type || 'RSTP',
            equipmentId: null,
            serviceDate: null,
            timeIn: '',

            // Paso 1
            conditions: [],

            // Paso 2 - RSTP
            activityGroups: [],
            rstpMonth: { year: now.getFullYear(), month: now.getMonth() + 1 },
            monthLabels: MONTH_LABELS,

            // Paso 2 - RSTC
            rstcDetails: {
                fault_location: '', fault_location_other: '',
                fault_cause: '', fault_cause_other: '',
                fault_solution_area: '', fault_solution_other: '',
                analysis_notes: '', cause_notes: '', solution_notes: '',
            },
            faultLocationOptions: [
                { value: 'sistema_puertas', label: 'Sistema de puertas' },
                { value: 'control_maniobras', label: 'Control y maniobras' },
                { value: 'maquinaria', label: 'Maquinaria' },
                { value: 'recorrido', label: 'Recorrido' },
                { value: 'otra', label: 'Otra' },
            ],
            faultCauseOptions: [
                { value: 'energia_externa', label: 'Energía externa' },
                { value: 'inundacion_humedad', label: 'Inundación / humedad' },
                { value: 'tercero', label: 'Tercero' },
                { value: 'tecnica_equipo', label: 'Técnica del equipo' },
                { value: 'otra', label: 'Otra' },
            ],
            faultSolutionOptions: [
                { value: 'control_maniobras', label: 'Control y maniobras' },
                { value: 'perifericos', label: 'Periféricos' },
                { value: 'puertas_piso', label: 'Puertas de piso' },
                { value: 'operador_puertas_cabina', label: 'Operador puertas cabina' },
                { value: 'activacion_interruptores', label: 'Activación interruptores' },
                { value: 'otra', label: 'Otra' },
            ],

            // Paso 2 - RSTE
            rsteWorkGroups: [],

            // Paso 3
            conclusion: {
                equipment_functional: null,
                generates_quotation: null,
                requires_parts: null,
                notes: '',
                video: null,
            },

            // Paso 4
            customerSigner: { name: '', document: '' },
            techSignCtx: null,
            customerSignCtx: null,
            isDrawing: false,
            currentSignTarget: null,

            // Autosave
            lastSaved: null,
            autosaveTimer: null,
        };
    },

    computed: {
        timeOut() {
            return new Date().toLocaleTimeString('es-CO', { hour: '2-digit', minute: '2-digit' });
        },
        conditionsAnswered() {
            return this.conditions.filter(c => c.value !== null).length;
        },
        conditionsProgress() {
            return this.conditions.length ? Math.round((this.conditionsAnswered / this.conditions.length) * 100) : 0;
        },
        allActivities() {
            return this.activityGroups.flatMap(g => g.items);
        },
        activitiesAnswered() {
            return this.allActivities.filter(a => a.value !== null).length;
        },
        activitiesProgress() {
            return this.allActivities.length ? Math.round((this.activitiesAnswered / this.allActivities.length) * 100) : 0;
        },
        allRsteWorks() {
            return this.rsteWorkGroups.flatMap(g => g.items);
        },
        rsteWorksAnswered() {
            return this.allRsteWorks.filter(w => w.value !== null).length;
        },
        rsteWorksProgress() {
            return this.allRsteWorks.length ? Math.round((this.rsteWorksAnswered / this.allRsteWorks.length) * 100) : 0;
        },
        reportTypeLabel() {
            const labels = { RSTP: 'Preventivo', RSTC: 'Correctivo', RSTE: 'Especial' };
            return labels[this.reportType] || '';
        },
        canAdvance() {
            if (this.currentStep === 0) {
                return this.conditionsAnswered === this.conditions.length;
            }
            if (this.currentStep === 1) {
                if (this.reportType === 'RSTP') return this.activitiesAnswered === this.allActivities.length;
                if (this.reportType === 'RSTE') return this.rsteWorksAnswered === this.allRsteWorks.length;
                return true; // RSTC no requiere validacion de toggles
            }
            if (this.currentStep === 2) {
                return this.conclusion.equipment_functional !== null
                    && this.conclusion.generates_quotation !== null
                    && this.conclusion.requires_parts !== null;
            }
            return true;
        },
    },

    async created() {
        this.reportId = this.$route.query.report_id;
        // Modo offline: el reporte aún no existe en el servidor (id local)
        this.isOffline = this.$route.query.offline === '1'
            || String(this.reportId || '').startsWith('local-');
        if (this.isOffline) {
            this.equipmentId = this.$route.query.equipment_id || this.equipmentId;
        }
        await this.loadCatalogs();

        // Leer time_in del checkin
        try {
            const checkin = JSON.parse(sessionStorage.getItem('current_checkin') || '{}');
            if (checkin.checked_in_at) {
                this.timeIn = new Date(checkin.checked_in_at).toLocaleTimeString('es-CO', { hour: '2-digit', minute: '2-digit' });
            }
        } catch {}

        // Autosave cada 30s
        this.autosaveTimer = setInterval(() => this.autosave(), 30000);
    },

    mounted() {
        this.$nextTick(() => {
            this.initSignatureCanvases();
            this.updateStickySpacerHeight();
        });
    },

    watch: {
        currentStep() {
            this.$nextTick(() => this.updateStickySpacerHeight());
        },
        loading() {
            this.$nextTick(() => this.updateStickySpacerHeight());
        },
    },

    beforeUnmount() {
        if (this.autosaveTimer) clearInterval(this.autosaveTimer);
        this.removeSignListeners();
    },

    methods: {
        updateStickySpacerHeight() {
            const stickyEl = this.$el?.querySelector('.report-sticky-top');
            const spacerEl = this.$refs.stickySpacerRef;
            if (stickyEl && spacerEl) {
                spacerEl.style.height = stickyEl.offsetHeight + 'px';
            }
        },

        async loadCatalogs() {
            this.loading = true;
            try {
                // Cargar condiciones iniciales
                const condScope = this.reportType === 'RSTP' ? 'RSTP' : 'RSTC';
                const condRes = await reportService.getCatalogs(condScope, 'initial_condition');
                this.conditions = (condRes.data || []).map(c => ({
                    ...c, value: null, observation: '', photo: null,
                }));

                // Cargar actividades/trabajos segun tipo
                if (this.reportType === 'RSTP') {
                    const actRes = await reportService.getCatalogs('RSTP', 'rstp_activity');
                    const grouped = {};
                    (actRes.data || []).forEach(a => {
                        if (!grouped[a.group_key]) {
                            grouped[a.group_key] = {
                                key: a.group_key,
                                label: GROUP_LABELS[a.group_key] || a.group_key,
                                expanded: true,
                                items: [],
                            };
                        }
                        grouped[a.group_key].items.push({
                            ...a, value: null, observation: '', photo: null,
                        });
                    });
                    this.activityGroups = Object.values(grouped);
                } else if (this.reportType === 'RSTE') {
                    const workRes = await reportService.getCatalogs('RSTE', 'rste_work');
                    const grouped = {};
                    (workRes.data || []).forEach(w => {
                        if (!grouped[w.group_key]) {
                            grouped[w.group_key] = {
                                key: w.group_key,
                                label: GROUP_LABELS[w.group_key] || w.group_key,
                                expanded: true,
                                items: [],
                            };
                        }
                        grouped[w.group_key].items.push({
                            ...w, value: null, observation: '', photo: null,
                        });
                    });
                    this.rsteWorkGroups = Object.values(grouped);
                }

                // Si hay reporte existente en el servidor, cargar datos
                // (en modo offline el reporte aún no existe; se omite el fetch)
                if (this.reportId && !this.isOffline) {
                    try {
                        const rpt = await reportService.get(this.reportId);
                        const data = rpt.data;
                        this.reportNumber = data.report_number;
                        this.equipmentId = data.equipment_id;
                        this.serviceDate = data.service_date;
                        if (data.time_in) this.timeIn = data.time_in;
                    } catch {}
                }
            } catch (err) {
                console.error('Error loading catalogs:', err);
            } finally {
                this.loading = false;
            }
        },

        groupAnswered(group) {
            return group.items.filter(i => i.value !== null).length;
        },

        nextStep() {
            if (this.canAdvance && this.currentStep < this.steps.length - 1) {
                this.currentStep++;
                this.autosave();
                this.scrollStepTop();
                if (this.currentStep === 3) {
                    this.$nextTick(() => this.initSignatureCanvases());
                }
            }
        },

        prevStep() {
            if (this.currentStep > 0) {
                this.currentStep--;
                this.scrollStepTop();
            }
        },

        goToStep(step) {
            // Solo permitir ir a pasos completados o el actual
            if (step <= this.currentStep) {
                this.currentStep = step;
                this.scrollStepTop();
            }
        },

        // Al cambiar de paso, volver al inicio. El contenedor scrolleable es
        // .tech-content (overflow-y:auto del TechLayout), así que window.scrollTo
        // no basta: hay que resetear su scrollTop.
        scrollStepTop() {
            this.$nextTick(() => {
                const container = this.$el?.closest('.tech-content');
                if (container) container.scrollTop = 0;
                window.scrollTo(0, 0);
            });
        },

        buildPayload() {
            const payload = {
                report_type: this.reportType,
                equipment_id: this.equipmentId,
                service_date: this.serviceDate || new Date().toISOString().split('T')[0],
                initial_conditions: this.conditions.map(c => ({
                    condition_key: c.key,
                    value: c.value || 'na',
                    observation: c.observation || null,
                })),
                equipment_functional: this.conclusion.equipment_functional,
                generates_quotation: this.conclusion.generates_quotation,
                requires_parts_change: this.conclusion.requires_parts,
                conclusion_notes: this.conclusion.notes || null,
                exit_time: new Date().toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' }),
            };

            if (this.reportType === 'RSTP') {
                payload.rstp_activities = this.allActivities.map(a => ({
                    group_key: a.group_key,
                    activity_key: a.key,
                    is_ok: a.value ?? false,
                    observation: a.observation || null,
                }));
                payload.rstp_month = this.rstpMonth;
            }

            if (this.reportType === 'RSTC') {
                payload.rstc_details = { ...this.rstcDetails };
            }

            if (this.reportType === 'RSTE') {
                payload.rste_works = this.allRsteWorks.map(w => ({
                    group_key: w.group_key,
                    work_key: w.key,
                    is_ok: w.value ?? false,
                    observation: w.observation || null,
                }));
            }

            return payload;
        },

        // Recolecta las fotos capturadas (Blobs) con su contexto. Las fotos de
        // condiciones aplican a todos los tipos; las de actividades/trabajos según
        // el tipo de reporte. `_item` permite limpiar el Blob al subirlo online.
        collectAttachments() {
            const list = [];
            const push = (item, ctx) => {
                if (item.photo instanceof Blob) {
                    list.push({ blob: item.photo, media_type: 'foto', _item: item, ...ctx });
                }
            };
            this.conditions.forEach(c => push(c, { condition_key: c.key }));
            if (this.reportType === 'RSTP') {
                this.allActivities.forEach(a => push(a, { group_key: a.group_key, activity_key: a.key }));
            }
            if (this.reportType === 'RSTE') {
                this.allRsteWorks.forEach(w => push(w, { group_key: w.group_key, activity_key: w.key }));
            }
            // Video del servicio (paso Conclusión). El backend detecta el tipo real
            // en Cloudinary; aquí marcamos media_type para nombrar el archivo.
            if (this.conclusion.video instanceof Blob) {
                list.push({ blob: this.conclusion.video, media_type: 'video', _video: true });
            }
            return list;
        },

        attachmentFilename(att) {
            if (att.media_type === 'video') {
                return `video-${Date.now()}.mp4`;
            }
            const ctx = att.condition_key || att.activity_key || 'foto';
            return `${ctx}-${Date.now()}.jpg`;
        },

        // Sube las fotos al servidor (flujo online). Cada foto lleva su propio
        // client_uuid para que un reintento no la duplique; al subir con éxito se
        // libera el Blob del item para no re-subirla si una firma posterior falla.
        async uploadAttachmentsOnline(reportId, attachments) {
            for (const att of attachments) {
                const fd = new FormData();
                fd.append('file', att.blob, this.attachmentFilename(att));
                fd.append('type', 'otro');
                fd.append('media_type', att.media_type || 'foto');
                fd.append('client_uuid', (crypto.randomUUID && crypto.randomUUID()) || `${Date.now()}-${Math.round(Math.random() * 1e9)}`);
                if (att.condition_key) fd.append('condition_key', att.condition_key);
                if (att.activity_key) fd.append('activity_key', att.activity_key);
                if (att.group_key) fd.append('group_key', att.group_key);

                await reportService.uploadAttachment(reportId, fd);
                // Liberar el Blob al subir con éxito (evita re-subir si una firma falla).
                if (att._video) this.conclusion.video = null;
                else if (att._item) att._item.photo = null;
            }
        },

        async autosave() {
            if (!this.reportId || this.saving) return;
            try {
                if (this.isOffline) {
                    // Sin servidor: guardar el borrador en el dispositivo
                    await offlineManager.saveDraftReport(this.reportId, this.buildPayload());
                } else {
                    await reportService.update(this.reportId, this.buildPayload());
                }
                this.lastSaved = new Date().toLocaleTimeString('es-CO', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            } catch {}
        },

        async finalizeAndSign() {
            // Validar firmas
            if (!this.hasSignature('tech')) {
                this.$swal.fire({ icon: 'warning', title: 'Firma requerida', text: 'Debe firmar como técnico.', confirmButtonText: 'Entendido' });
                return;
            }
            if (!this.customerSigner.name.trim()) {
                this.$swal.fire({ icon: 'warning', title: 'Datos incompletos', text: 'Ingrese el nombre del firmante.', confirmButtonText: 'Entendido' });
                return;
            }
            if (!this.hasSignature('customer')) {
                this.$swal.fire({ icon: 'warning', title: 'Firma requerida', text: 'El cliente debe firmar.', confirmButtonText: 'Entendido' });
                return;
            }

            this.saving = true;
            try {
                const techSig = this.$refs.techSignature.toDataURL('image/png');
                const custSig = this.$refs.customerSignature.toDataURL('image/png');
                const attachments = this.collectAttachments();

                // OFFLINE: encolar el reporte completo (datos + firmas) para subir al
                // reconectar. El syncEngine hace create → firmar técnico → firmar cliente.
                if (this.isOffline) {
                    const reportClientUuid = await offlineManager.queueReportForSync({
                        payload: this.buildPayload(),
                        techSignature: techSig,
                        custSignature: custSig,
                        custName: this.customerSigner.name,
                        custDoc: this.customerSigner.document,
                    });
                    // Encolar las fotos enlazadas al reporte; el syncEngine las sube
                    // una vez que el reporte se crea y obtiene su id real.
                    for (const att of attachments) {
                        await offlineManager.queueAttachment({
                            report_client_uuid: reportClientUuid,
                            blob: att.blob,
                            type: 'otro',
                            media_type: att.media_type || 'foto',
                            condition_key: att.condition_key || null,
                            activity_key: att.activity_key || null,
                            group_key: att.group_key || null,
                            filename: this.attachmentFilename(att),
                        });
                    }
                    await offlineManager.removeDraftReport(this.reportId);
                    sessionStorage.removeItem('current_checkin');
                    this.$router.push({ name: 'tech.dashboard' });
                    this.$swal.fire({
                        icon: 'success',
                        title: 'Reporte guardado sin conexión',
                        text: 'Se enviará automáticamente cuando vuelva la conexión.',
                        confirmButtonText: 'Aceptar',
                    });
                    return;
                }

                // 1. Guardar datos del reporte
                await reportService.update(this.reportId, this.buildPayload());

                // 2. Subir fotos (antes de firmar; si falla, el usuario reintenta sin
                //    re-firmar y sin re-subir las ya cargadas).
                await this.uploadAttachmentsOnline(this.reportId, attachments);

                // 3. Firmar como tecnico
                await reportService.signTechnician(this.reportId, { signature: techSig });

                // 4. Firmar como cliente
                await reportService.signCustomer(this.reportId, {
                    signature: custSig,
                    signer_name: this.customerSigner.name,
                    signer_document: this.customerSigner.document,
                });

                // Limpiar session
                sessionStorage.removeItem('current_checkin');

                // Navegar a confirmacion
                this.$router.push({ name: 'tech.dashboard' });

                // Notificar exito
                this.$swal.fire({
                    icon: 'success',
                    title: 'Reporte finalizado',
                    text: 'El informe ha sido firmado y enviado correctamente.',
                    confirmButtonText: 'Aceptar',
                });
            } catch (err) {
                this.$swal.fire({
                    icon: 'error',
                    title: 'Error al finalizar',
                    text: err.response?.data?.message || err.message,
                    confirmButtonText: 'Aceptar',
                });
            } finally {
                this.saving = false;
            }
        },

        // ── Signature canvas ──
        initSignatureCanvases() {
            ['techSignature', 'customerSignature'].forEach(ref => {
                const canvas = this.$refs[ref];
                if (!canvas) return;
                const rect = canvas.parentElement.getBoundingClientRect();
                canvas.width = rect.width;
                canvas.height = 150;
                const ctx = canvas.getContext('2d');
                ctx.strokeStyle = '#1e293b';
                ctx.lineWidth = 2;
                ctx.lineCap = 'round';
                ctx.lineJoin = 'round';
                if (ref === 'techSignature') this.techSignCtx = ctx;
                else this.customerSignCtx = ctx;
            });

            // Global listeners for drawing
            this._onMove = this.drawSign.bind(this);
            this._onEnd = this.endSign.bind(this);
            document.addEventListener('mousemove', this._onMove);
            document.addEventListener('mouseup', this._onEnd);
            document.addEventListener('touchmove', this._onMove, { passive: false });
            document.addEventListener('touchend', this._onEnd);
        },

        removeSignListeners() {
            if (this._onMove) {
                document.removeEventListener('mousemove', this._onMove);
                document.removeEventListener('touchmove', this._onMove);
            }
            if (this._onEnd) {
                document.removeEventListener('mouseup', this._onEnd);
                document.removeEventListener('touchend', this._onEnd);
            }
        },

        startSign(target, e) {
            this.isDrawing = true;
            this.currentSignTarget = target;
            const ctx = target === 'tech' ? this.techSignCtx : this.customerSignCtx;
            if (!ctx) return;
            const pos = this.getCanvasPos(e, target);
            ctx.beginPath();
            ctx.moveTo(pos.x, pos.y);
        },

        drawSign(e) {
            if (!this.isDrawing || !this.currentSignTarget) return;
            e.preventDefault();
            const ctx = this.currentSignTarget === 'tech' ? this.techSignCtx : this.customerSignCtx;
            if (!ctx) return;
            const pos = this.getCanvasPos(e, this.currentSignTarget);
            ctx.lineTo(pos.x, pos.y);
            ctx.stroke();
        },

        endSign() {
            this.isDrawing = false;
            this.currentSignTarget = null;
        },

        getCanvasPos(e, target) {
            const ref = target === 'tech' ? 'techSignature' : 'customerSignature';
            const canvas = this.$refs[ref];
            const rect = canvas.getBoundingClientRect();
            const touch = e.touches ? e.touches[0] : e;
            return {
                x: touch.clientX - rect.left,
                y: touch.clientY - rect.top,
            };
        },

        clearSign(target) {
            const ref = target === 'tech' ? 'techSignature' : 'customerSignature';
            const canvas = this.$refs[ref];
            const ctx = target === 'tech' ? this.techSignCtx : this.customerSignCtx;
            if (ctx && canvas) {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
            }
        },

        hasSignature(target) {
            const ref = target === 'tech' ? 'techSignature' : 'customerSignature';
            const canvas = this.$refs[ref];
            if (!canvas) return false;
            const ctx = canvas.getContext('2d');
            const pixels = ctx.getImageData(0, 0, canvas.width, canvas.height).data;
            for (let i = 3; i < pixels.length; i += 4) {
                if (pixels[i] > 0) return true;
            }
            return false;
        },
    },
};
</script>

<style scoped>
.tech-report-form {
    padding: 0 0 100px;
}

/* Fixed top block */
.report-sticky-top {
    position: fixed;
    top: 56px; /* altura del tech-header */
    left: 0;
    right: 0;
    z-index: 40;
    background: white;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}

@media (min-width: 769px) {
    .report-sticky-top {
        max-width: 640px;
        margin: 0 auto;
    }
}

.step-sticky-info {
    padding: 0.6rem 1rem 0.5rem;
    background: white;
    border-bottom: 1px solid #e2e8f0;
}

.step-sticky-info .step-title-row {
    margin-bottom: 0.35rem;
}

.step-sticky-info .step-title {
    margin: 0;
    font-size: 1rem;
}

.step-sticky-info .progress-bar {
    margin-bottom: 0;
}

/* Report header */
.report-header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    flex-wrap: wrap;
}

.report-header--RSTP { background: #e8f5e4; }
.report-header--RSTC { background: #fef2f2; }
.report-header--RSTE { background: #fffbeb; }

.report-header__type {
    font-size: 0.75rem;
    font-weight: 700;
    padding: 0.15rem 0.5rem;
    border-radius: 6px;
}
.report-header--RSTP .report-header__type { background: #30ab0a; color: white; }
.report-header--RSTC .report-header__type { background: #ba2831; color: white; }
.report-header--RSTE .report-header__type { background: #d97706; color: white; }

.report-header__label {
    font-size: 0.85rem;
    font-weight: 600;
    color: #1e293b;
}

.report-header__number {
    font-size: 0.75rem;
    color: #64748b;
    margin-left: auto;
}

/* Step bar */
.step-bar {
    display: flex;
    padding: 0.5rem 1rem;
    gap: 0.25rem;
    background: white;
    border-bottom: 1px solid #f1f5f9;
    overflow-x: auto;
}

.step-bar__item {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.2rem;
    cursor: pointer;
    min-width: 60px;
}

.step-bar__circle {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    font-weight: 700;
    background: #f1f5f9;
    color: #94a3b8;
    border: 2px solid #e2e8f0;
    transition: all 0.2s;
}

.step-bar__item.is-active .step-bar__circle {
    background: #30ab0a;
    color: white;
    border-color: #30ab0a;
}

.step-bar__item.is-completed .step-bar__circle {
    background: #e8f5e4;
    color: #30ab0a;
    border-color: #30ab0a;
}

.step-bar__label {
    font-size: 0.6rem;
    font-weight: 500;
    color: #94a3b8;
    text-align: center;
    white-space: nowrap;
}

.step-bar__item.is-active .step-bar__label { color: #30ab0a; font-weight: 600; }

/* Step content */
.step-content {
    padding: 1rem;
}

.step-title-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 0.5rem;
}

.step-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: #1e293b;
    margin: 0 0 0.75rem;
}

.step-title-row .step-title {
    margin: 0;
}

.step-progress {
    font-size: 0.8rem;
    font-weight: 600;
    color: #30ab0a;
    background: #e8f5e4;
    padding: 0.2rem 0.6rem;
    border-radius: 12px;
}

/* Progress bar */
.progress-bar {
    height: 6px;
    background: #e2e8f0;
    border-radius: 3px;
    margin-bottom: 1rem;
    overflow: hidden;
}

.progress-bar__fill {
    height: 100%;
    background: linear-gradient(90deg, #30ab0a, #279208);
    border-radius: 3px;
    transition: width 0.3s ease;
}

/* Items list */
.items-list {
    display: flex;
    flex-direction: column;
    gap: 0.6rem;
}

/* Activity groups */
.activity-group {
    margin-bottom: 0.75rem;
}

.group-header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    width: 100%;
    padding: 0.7rem 1rem;
    background: linear-gradient(135deg, #f8fafc, #f1f5f9);
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    font-size: 0.85rem;
    font-weight: 600;
    color: #1e293b;
    cursor: pointer;
    min-height: 44px;
}

.group-header__label { flex: 1; text-align: left; }
.group-header__count { font-size: 0.75rem; color: #30ab0a; }
.group-header i { color: #94a3b8; font-size: 0.7rem; }

.group-items {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    margin-top: 0.5rem;
    padding-left: 0.25rem;
}

/* Month selector */
.month-selector {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 0.75rem;
    margin-bottom: 1rem;
}

.month-selector label {
    font-size: 0.8rem;
    font-weight: 600;
    color: #475569;
    margin-bottom: 0.4rem;
    display: block;
}

.month-row {
    display: flex;
    gap: 0.5rem;
}

.month-select, .year-input {
    padding: 0.5rem;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-size: 0.9rem;
    min-height: 44px;
}

.month-select { flex: 1; }
.year-input { width: 90px; }

/* RSTC sections */
.rstc-section {
    margin-bottom: 1rem;
}

.field-label {
    display: block;
    font-size: 0.85rem;
    font-weight: 600;
    color: #475569;
    margin-bottom: 0.4rem;
}

.field-input {
    width: 100%;
    padding: 0.65rem 0.75rem;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    font-size: 0.9rem;
    min-height: 44px;
    transition: border-color 0.2s;
}
.field-input:focus { outline: none; border-color: #30ab0a; }

.field-textarea {
    width: 100%;
    padding: 0.65rem 0.75rem;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    font-size: 0.9rem;
    font-family: inherit;
    resize: vertical;
    min-height: 80px;
    transition: border-color 0.2s;
}
.field-textarea:focus { outline: none; border-color: #30ab0a; }

.radio-group {
    display: flex;
    flex-wrap: wrap;
    gap: 0.4rem;
}

.radio-option {
    display: flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.5rem 0.75rem;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-size: 0.8rem;
    color: #475569;
    cursor: pointer;
    transition: all 0.15s;
    min-height: 40px;
}

.radio-option input { display: none; }

.radio-option.is-selected {
    background: #e8f5e4;
    border-color: #30ab0a;
    color: #279208;
    font-weight: 600;
}

/* Conclusion toggles */
.conclusion-toggles {
    display: flex;
    flex-direction: column;
    gap: 0.6rem;
    margin-bottom: 1.25rem;
}

.conclusion-toggle-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.75rem 1rem;
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    min-height: 52px;
}

.conclusion-toggle-label {
    font-size: 0.85rem;
    font-weight: 600;
    color: #1e293b;
}

.mini-toggle {
    width: 52px;
    height: 32px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
    flex-shrink: 0;
}

.mini-toggle__text {
    font-size: 0.75rem;
    font-weight: 700;
    color: white;
}

.mini-toggle.is-unset { background: #e2e8f0; }
.mini-toggle.is-unset .mini-toggle__text { color: #94a3b8; }
.mini-toggle.is-on { background: #30ab0a; }
.mini-toggle.is-off { background: #ba2831; }

.conclusion-notes, .conclusion-video {
    margin-bottom: 1rem;
}

/* Signature */
.sign-summary {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 0.85rem;
    margin-bottom: 1rem;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    padding: 0.35rem 0;
    border-bottom: 1px solid #f8fafc;
    font-size: 0.85rem;
}

.summary-row:last-child { border-bottom: none; }
.summary-row span { color: #64748b; }
.summary-row strong { color: #1e293b; }

.sign-section {
    margin-bottom: 1rem;
}

.signature-canvas-wrap {
    position: relative;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    overflow: hidden;
    background: white;
}

.signature-canvas {
    display: block;
    width: 100%;
    touch-action: none;
}

.clear-sign-btn {
    position: absolute;
    top: 0.5rem;
    right: 0.5rem;
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 0.3rem 0.6rem;
    font-size: 0.75rem;
    color: #64748b;
    cursor: pointer;
}

/* Navigation bottom */
.nav-bottom {
    position: fixed;
    bottom: 64px;
    left: 0;
    right: 0;
    display: flex;
    justify-content: space-between;
    padding: 0.75rem 1rem;
    background: white;
    border-top: 1px solid #e2e8f0;
    z-index: 50;
}

.nav-btn {
    padding: 0.7rem 1.25rem;
    border: none;
    border-radius: 10px;
    font-size: 0.9rem;
    font-weight: 600;
    cursor: pointer;
    min-height: 48px;
    display: flex;
    align-items: center;
    gap: 0.4rem;
}

.nav-btn--back {
    background: #f1f5f9;
    color: #475569;
}

.nav-btn--next {
    background: linear-gradient(135deg, #30ab0a, #279208);
    color: white;
}

.nav-btn--next:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

.nav-btn--finalize {
    background: linear-gradient(135deg, #ba2831, #9e2229);
    color: white;
}

.nav-btn--finalize:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* Autosave indicator */
.autosave-indicator {
    position: fixed;
    bottom: 128px;
    right: 1rem;
    background: #e8f5e4;
    color: #279208;
    padding: 0.3rem 0.6rem;
    border-radius: 6px;
    font-size: 0.7rem;
    font-weight: 500;
    z-index: 51;
}

/* Loading */
.report-loading {
    text-align: center;
    padding: 3rem;
    color: #64748b;
}

@media (min-width: 769px) {
    .nav-bottom {
        bottom: 0;
        max-width: 640px;
        margin: 0 auto;
    }

    .autosave-indicator {
        bottom: 64px;
    }
}
</style>
