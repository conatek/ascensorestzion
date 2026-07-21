<template>
    <div class="tech-signatures">
        <div class="page-head">
            <button class="back-btn" @click="$router.back()"><i class="fa fa-arrow-left"></i></button>
            <h1>Firmas pendientes</h1>
        </div>

        <!-- Cargando -->
        <div v-if="loading" class="loading">
            <i class="fa fa-spinner fa-spin"></i> Cargando visitas…
        </div>

        <!-- Panel de firma de una visita -->
        <div v-else-if="selected" class="sign-panel">
            <button class="link-back" @click="selected = null">
                <i class="fa fa-arrow-left"></i> Volver a la lista
            </button>

            <div class="visit-head">
                <h2>{{ selected.client_name }}</h2>
                <p>{{ selected.site_name }} · {{ formatDate(selected.service_date) }}</p>
            </div>

            <div class="reports-box">
                <div class="reports-box__title">
                    Se firmarán {{ selected.reports_count }} informe{{ selected.reports_count > 1 ? 's' : '' }}
                </div>
                <div v-for="r in selected.reports" :key="r.id" class="report-row">
                    <span class="report-row__num">{{ r.report_number }}</span>
                    <span class="report-row__type">{{ typeLabel(r.report_type) }}</span>
                    <span class="report-row__equip">{{ r.equipment_code || '—' }}</span>
                </div>
            </div>

            <div class="field">
                <label class="field-label">Nombre del firmante (cliente)</label>
                <input v-model="signer.name" class="field-input" placeholder="Nombre completo" spellcheck="true" lang="es" />
            </div>
            <div class="field">
                <label class="field-label">Número de documento</label>
                <input v-model="signer.document" class="field-input" placeholder="CC / NIT" />
            </div>
            <div class="field">
                <label class="field-label">Firma del cliente</label>
                <SignaturePad ref="pad" />
            </div>

            <button class="sign-btn" :disabled="signing" @click="submit">
                <i v-if="signing" class="fa fa-spinner fa-spin"></i>
                <template v-else><i class="fa fa-check-circle"></i> Firmar {{ selected.reports_count }} informe{{ selected.reports_count > 1 ? 's' : '' }}</template>
            </button>
        </div>

        <!-- Lista de visitas -->
        <div v-else-if="visits.length || queuedSignatures.length" class="visits">
            <p v-if="!isOnline" class="offline-note">
                <i class="fa fa-wifi"></i> Sin conexión — la firma se guardará y se enviará al reconectar.
            </p>

            <!-- Visitas pendientes de firmar -->
            <div v-for="v in visits" :key="v.visit_uuid" class="visit-card" @click="select(v)">
                <div class="visit-card__icon"><i class="fa fa-file-signature"></i></div>
                <div class="visit-card__body">
                    <div class="visit-card__title">{{ v.client_name || 'Cliente' }}</div>
                    <div class="visit-card__subtitle">{{ v.site_name || '—' }}</div>
                    <div class="visit-card__meta">
                        <span class="badge">{{ v.reports_count }} informe{{ v.reports_count > 1 ? 's' : '' }}</span>
                        <span class="date">{{ formatDate(v.service_date) }}</span>
                        <span v-if="v.hasLocal" class="badge badge--local">sin subir</span>
                    </div>
                </div>
                <i class="fa fa-chevron-right visit-card__chevron"></i>
            </div>

            <!-- Firmas ya capturadas, esperando conexión -->
            <div v-for="s in queuedSignatures" :key="`q-${s.localId}`" class="visit-card visit-card--queued">
                <div class="visit-card__icon visit-card__icon--queued"><i class="fa fa-cloud-upload-alt"></i></div>
                <div class="visit-card__body">
                    <div class="visit-card__title">{{ s.client_name || 'Visita firmada' }}</div>
                    <div class="visit-card__subtitle">{{ s.site_name || '—' }} · Firmó {{ s.signer_name }}</div>
                    <div class="visit-card__meta">
                        <span class="badge badge--queued">Firma en cola</span>
                        <span v-if="s.status === 'error'" class="err">{{ s.lastError }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Vacío -->
        <div v-else class="empty">
            <i class="fa fa-check-circle"></i>
            <p>Sin firmas pendientes</p>
            <span>Todos los informes están firmados por el cliente.</span>
        </div>
    </div>
</template>

<script>
import SignaturePad from '@/components/SignaturePad.vue';
import visitService, { clearCurrentVisit, loadPendingVisits } from '@/services/visitService.js';
import offlineManager from '@/utils/offlineManager.js';

const TYPE_LABELS = { RSTP: 'Preventivo', RSTC: 'Correctivo', RSTE: 'Especial' };

export default {
    name: 'TechPendingSignatures',
    components: { SignaturePad },

    data() {
        return {
            loading: true,
            signing: false,
            visits: [],
            queuedSignatures: [],
            selected: null,
            signer: { name: '', document: '' },
        };
    },

    computed: {
        offlineState() {
            return offlineManager.state;
        },
        isOnline() {
            return offlineManager.state.isOnline;
        },
    },

    watch: {
        // Al terminar un ciclo de sync la cola cambia: refrescar la lista
        'offlineState.lastSyncAt': 'load',
    },

    mounted() {
        this.load();
    },

    methods: {
        async load() {
            this.loading = true;
            try {
                const { visits, queuedSignatures } = await loadPendingVisits();
                this.visits = visits;
                this.queuedSignatures = queuedSignatures;
                // El badge de la barra inferior se entera del cambio
                window.dispatchEvent(new CustomEvent('tzion-visits-changed', { detail: { count: visits.length } }));
            } finally {
                this.loading = false;
            }
        },

        select(visit) {
            this.selected = visit;
            this.signer = { name: '', document: '' };
        },

        typeLabel(type) {
            return TYPE_LABELS[type] || type;
        },

        formatDate(date) {
            if (!date) return '—';
            const [y, m, d] = date.split('-');
            return `${d}/${m}/${y}`;
        },

        async submit() {
            if (!this.signer.name.trim()) {
                this.$swal.fire({ icon: 'warning', title: 'Datos incompletos', text: 'Ingrese el nombre del firmante.', confirmButtonText: 'Entendido' });
                return;
            }
            if (!this.signer.document.trim()) {
                this.$swal.fire({ icon: 'warning', title: 'Datos incompletos', text: 'Ingrese el documento del firmante.', confirmButtonText: 'Entendido' });
                return;
            }
            const signature = this.$refs.pad?.toDataURL();
            if (!signature) {
                this.$swal.fire({ icon: 'warning', title: 'Firma requerida', text: 'El cliente debe firmar.', confirmButtonText: 'Entendido' });
                return;
            }

            const visit = this.selected;
            const payload = {
                visit_uuid: visit.visit_uuid,
                signature,
                signer_name: this.signer.name,
                signer_document: this.signer.document,
            };

            this.signing = true;
            try {
                // Sin conexión, o con reportes de la visita todavía en cola, la firma
                // se encola: el syncEngine la aplica cuando los reportes ya existan.
                if (!this.isOnline || visit.hasLocal) {
                    await this.queueSignature(visit, payload);
                    return;
                }

                const { data } = await visitService.signCustomer(payload);

                // La visita quedó cerrada: el próximo reporte abre una visita nueva.
                clearCurrentVisit(visit.visit_uuid);
                this.selected = null;
                await this.load();

                this.$swal.fire({
                    icon: 'success',
                    title: 'Visita firmada',
                    text: `Se firmaron ${data.signed} informe${data.signed === 1 ? '' : 's'}.`,
                    confirmButtonText: 'Aceptar',
                });
            } catch (err) {
                // Fallo de red al enviar: no perder la firma, encolarla.
                if (!err.response) {
                    await this.queueSignature(visit, payload);
                    return;
                }
                this.$swal.fire({
                    icon: 'error',
                    title: 'Error al firmar',
                    text: err.response?.data?.message || err.message,
                    confirmButtonText: 'Aceptar',
                });
            } finally {
                this.signing = false;
            }
        },

        async queueSignature(visit, payload) {
            await offlineManager.queueVisitSignature({
                ...payload,
                client_name: visit.client_name,
                site_name: visit.site_name,
                service_date: visit.service_date,
            });

            clearCurrentVisit(visit.visit_uuid);
            this.selected = null;
            await this.load();

            // Si hay conexión, intentar enviarla de inmediato
            if (this.isOnline) offlineManager.syncPending();

            this.$swal.fire({
                icon: 'success',
                title: 'Firma guardada',
                text: this.isOnline
                    ? 'Se enviará junto con los informes que faltan por subir.'
                    : 'Se enviará automáticamente cuando vuelva la conexión.',
                confirmButtonText: 'Aceptar',
            });
        },
    },
};
</script>

<style scoped>
.tech-signatures {
    padding: 1rem;
    padding-bottom: 2rem;
}

.page-head {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 1rem;
}

.page-head h1 {
    font-size: 1.15rem;
    font-weight: 700;
    color: #1e293b;
    margin: 0;
}

.back-btn {
    width: 36px;
    height: 36px;
    border: none;
    background: white;
    border-radius: 10px;
    color: #606060;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
    cursor: pointer;
}

.loading {
    text-align: center;
    padding: 3rem 1rem;
    color: #94a3b8;
}

/* Lista de visitas */
.visits {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.visit-card {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    background: white;
    border-radius: 12px;
    padding: 0.875rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
    cursor: pointer;
    transition: transform 0.15s, box-shadow 0.15s;
}

.visit-card:active {
    transform: scale(0.99);
}

.visit-card__icon {
    width: 40px;
    height: 40px;
    flex-shrink: 0;
    border-radius: 10px;
    background: #fef3c7;
    color: #b45309;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.05rem;
}

.visit-card__body {
    flex: 1;
    min-width: 0;
}

.visit-card__title {
    font-weight: 600;
    color: #1e293b;
    font-size: 0.95rem;
}

.visit-card__subtitle {
    font-size: 0.82rem;
    color: #606060;
    margin-top: 1px;
}

.visit-card__meta {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-top: 0.35rem;
}

.visit-card__chevron {
    color: #cbd5e1;
}

.badge {
    background: #ecfdf3;
    color: #279208;
    font-size: 0.72rem;
    font-weight: 600;
    padding: 2px 8px;
    border-radius: 999px;
}

.badge--local {
    background: #fff7ed;
    color: #c2410c;
}

.badge--queued {
    background: #eff6ff;
    color: #1d4ed8;
}

.visit-card--queued {
    cursor: default;
    opacity: 0.85;
}

.visit-card__icon--queued {
    background: #eff6ff;
    color: #1d4ed8;
}

.offline-note {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    background: #fffbeb;
    border: 1px solid #fde68a;
    border-radius: 10px;
    padding: 0.6rem 0.75rem;
    font-size: 0.8rem;
    color: #92400e;
    margin: 0;
}

.err {
    font-size: 0.72rem;
    color: #ba2831;
}

.date {
    font-size: 0.75rem;
    color: #94a3b8;
}

/* Panel de firma */
.sign-panel {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.link-back {
    align-self: flex-start;
    background: none;
    border: none;
    color: #30ab0a;
    font-size: 0.85rem;
    font-weight: 600;
    padding: 0;
    cursor: pointer;
}

.visit-head h2 {
    font-size: 1.05rem;
    font-weight: 700;
    color: #1e293b;
    margin: 0;
}

.visit-head p {
    font-size: 0.85rem;
    color: #606060;
    margin: 2px 0 0;
}

.reports-box {
    background: white;
    border-radius: 12px;
    padding: 0.875rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
}

.reports-box__title {
    font-size: 0.82rem;
    font-weight: 600;
    color: #606060;
    margin-bottom: 0.5rem;
}

.report-row {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.4rem 0;
    border-top: 1px solid #f1f5f9;
    font-size: 0.82rem;
}

.report-row__num {
    font-weight: 600;
    color: #1e293b;
}

.report-row__type {
    color: #606060;
}

.report-row__equip {
    margin-left: auto;
    color: #94a3b8;
}

.field-label {
    display: block;
    font-size: 0.82rem;
    font-weight: 600;
    color: #606060;
    margin-bottom: 0.35rem;
}

.field-input {
    width: 100%;
    padding: 0.65rem 0.75rem;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    font-size: 0.95rem;
    background: white;
}

.field-input:focus {
    outline: none;
    border-color: #30ab0a;
}

.sign-btn {
    width: 100%;
    padding: 0.9rem;
    border: none;
    border-radius: 12px;
    background: linear-gradient(135deg, #30ab0a, #279208);
    color: white;
    font-weight: 600;
    font-size: 0.95rem;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(48, 171, 10, 0.3);
}

.sign-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* Vacío */
.empty {
    text-align: center;
    padding: 3rem 1rem;
    color: #94a3b8;
}

.empty i {
    font-size: 2.5rem;
    color: #30ab0a;
    margin-bottom: 0.75rem;
}

.empty p {
    font-weight: 600;
    color: #606060;
    margin: 0;
}

.empty span {
    font-size: 0.85rem;
}
</style>
