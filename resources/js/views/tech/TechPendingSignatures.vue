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
                <p>{{ selected.site_name }} · {{ selectedDatesLabel }}</p>
            </div>

            <div class="reports-box">
                <div class="reports-box__title">
                    {{ chosenCount ? `Se firmarán ${countLabel(chosenCount)}` : 'Elige al menos un día' }}
                    <span v-if="selected.visits.length > 1"> · una sola firma para todo</span>
                </div>

                <!-- Un bloque por día. Con varios días, cada uno se puede dejar fuera. -->
                <div v-for="v in selected.visits" :key="v.visit_uuid" class="day-block"
                     :class="{ 'day-block--off': !isChosen(v) }">
                    <label v-if="selected.visits.length > 1" class="day-check">
                        <input type="checkbox" :checked="isChosen(v)" @change="toggleDay(v)" />
                        <span class="day-check__date">{{ formatDate(v.service_date) }}</span>
                        <span class="day-check__count">{{ countLabel(v.reports_count) }}</span>
                    </label>
                    <div v-for="r in v.reports" :key="r.id" class="report-row">
                        <span class="report-row__num">{{ r.report_number }}</span>
                        <span class="report-row__type">{{ typeLabel(r.report_type) }}</span>
                        <span class="report-row__equip">{{ r.equipment_code || '—' }}</span>
                    </div>
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

            <button class="sign-btn" :disabled="signing || !chosenCount" @click="submit">
                <i v-if="signing" class="fa fa-spinner fa-spin"></i>
                <template v-else><i class="fa fa-check-circle"></i> Firmar {{ countLabel(chosenCount) }}</template>
            </button>
        </div>

        <!-- Lista de visitas -->
        <div v-else-if="visits.length || queuedSignatures.length" class="visits">
            <p v-if="!isOnline" class="offline-note">
                <i class="fa fa-wifi"></i> Sin conexión — la firma se guardará y se enviará al reconectar.
            </p>

            <!-- Pendientes de firmar, por sede -->
            <template v-for="g in groups" :key="g.key">
                <!-- Un solo día pendiente en la sede -->
                <div v-if="g.visits.length === 1" class="visit-card" @click="select(g.visits)">
                    <div class="visit-card__icon"><i class="fa fa-file-signature"></i></div>
                    <div class="visit-card__body">
                        <div class="visit-card__title">{{ g.client_name || 'Cliente' }}</div>
                        <div class="visit-card__subtitle">{{ g.site_name || '—' }}</div>
                        <div class="visit-card__meta">
                            <span class="badge">{{ countLabel(g.reports_count) }}</span>
                            <span class="date">{{ formatDate(g.visits[0].service_date) }}</span>
                            <span v-if="g.hasLocal" class="badge badge--local">sin subir</span>
                        </div>
                    </div>
                    <i class="fa fa-chevron-right visit-card__chevron"></i>
                </div>

                <!-- Varios días: se firman todos juntos o cada día por separado -->
                <div v-else class="site-group">
                    <div class="site-group__head">
                        <div class="visit-card__icon"><i class="fa fa-file-signature"></i></div>
                        <div class="visit-card__body">
                            <div class="visit-card__title">{{ g.client_name || 'Cliente' }}</div>
                            <div class="visit-card__subtitle">{{ g.site_name || '—' }}</div>
                            <div class="visit-card__meta">
                                <span class="badge">{{ countLabel(g.reports_count) }}</span>
                                <span class="date">{{ g.visits.length }} días</span>
                                <span v-if="g.hasLocal" class="badge badge--local">sin subir</span>
                            </div>
                        </div>
                    </div>

                    <button class="site-group__all" @click="select(g.visits)">
                        <i class="fa fa-layer-group"></i> Firmar los {{ g.visits.length }} días juntos
                    </button>

                    <div class="site-group__hint">O un solo día:</div>
                    <button v-for="v in g.visits" :key="v.visit_uuid" class="day-row" @click="select([v])">
                        <span class="day-row__date">{{ formatDate(v.service_date) }}</span>
                        <span class="day-row__count">{{ countLabel(v.reports_count) }}</span>
                        <span v-if="v.hasLocal" class="badge badge--local">sin subir</span>
                        <i class="fa fa-chevron-right visit-card__chevron"></i>
                    </button>
                </div>
            </template>

            <!-- Firmas ya capturadas, esperando conexión -->
            <div v-for="s in queuedSignatures" :key="`q-${s.localId}`" class="visit-card visit-card--queued">
                <div class="visit-card__icon visit-card__icon--queued"><i class="fa fa-cloud-upload-alt"></i></div>
                <div class="visit-card__body">
                    <div class="visit-card__title">{{ s.client_name || 'Visita firmada' }}</div>
                    <div class="visit-card__subtitle">{{ s.site_name || '—' }} · Firmó {{ s.signer_name }}</div>
                    <div class="visit-card__meta">
                        <span class="badge badge--queued">Firma en cola</span>
                        <span v-if="s.visit_uuids?.length > 1" class="date">{{ s.visit_uuids.length }} días</span>
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
            // { client_name, site_name, visits: [...] } — uno o varios días de una sede.
            selected: null,
            // visit_uuid de los días marcados para firmar.
            chosen: [],
            signer: { name: '', document: '' },
        };
    },

    computed: {
        /** Las visitas pendientes agrupadas por sede, con sus días en orden. */
        groups() {
            const bySite = new Map();
            this.visits.forEach(v => {
                const key = v.site_id ? `s${v.site_id}` : `n${v.client_name}|${v.site_name}`;
                if (!bySite.has(key)) {
                    bySite.set(key, { key, client_name: v.client_name, site_name: v.site_name, visits: [] });
                }
                bySite.get(key).visits.push(v);
            });

            return [...bySite.values()].map(g => {
                g.visits.sort((a, b) => String(a.service_date).localeCompare(String(b.service_date)));
                g.reports_count = g.visits.reduce((sum, v) => sum + v.reports_count, 0);
                g.hasLocal = g.visits.some(v => v.hasLocal);
                return g;
            });
        },
        chosenVisits() {
            return this.selected ? this.selected.visits.filter(v => this.chosen.includes(v.visit_uuid)) : [];
        },
        chosenCount() {
            return this.chosenVisits.reduce((sum, v) => sum + v.reports_count, 0);
        },
        selectedDatesLabel() {
            const visits = this.selected?.visits || [];
            if (visits.length <= 1) return this.formatDate(visits[0]?.service_date);
            return `${this.formatDate(visits[0].service_date)} al ${this.formatDate(visits[visits.length - 1].service_date)}`;
        },
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

        /** Abre el panel de firma para uno o varios días de la misma sede. */
        select(visits) {
            this.selected = {
                client_name: visits[0].client_name,
                site_name: visits[0].site_name,
                visits,
            };
            this.chosen = visits.map(v => v.visit_uuid);
            this.signer = { name: '', document: '' };
        },

        isChosen(visit) {
            return this.chosen.includes(visit.visit_uuid);
        },

        toggleDay(visit) {
            this.chosen = this.isChosen(visit)
                ? this.chosen.filter(uuid => uuid !== visit.visit_uuid)
                : [...this.chosen, visit.visit_uuid];
        },

        countLabel(count) {
            return `${count} informe${count === 1 ? '' : 's'}`;
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
            if (!this.chosenVisits.length) {
                this.$swal.fire({ icon: 'warning', title: 'Sin días elegidos', text: 'Marque al menos un día para firmar.', confirmButtonText: 'Entendido' });
                return;
            }
            const signature = this.$refs.pad?.toDataURL();
            if (!signature) {
                this.$swal.fire({ icon: 'warning', title: 'Firma requerida', text: 'El cliente debe firmar.', confirmButtonText: 'Entendido' });
                return;
            }

            const visits = this.chosenVisits;
            const visitUuids = visits.map(v => v.visit_uuid);
            const payload = {
                // Un día viaja como siempre; varios días, en una sola firma.
                ...(visitUuids.length > 1 ? { visit_uuids: visitUuids } : { visit_uuid: visitUuids[0] }),
                signature,
                signer_name: this.signer.name,
                signer_document: this.signer.document,
                // Hora real de la firma, aunque suba después. La salida de cada
                // equipo ya quedó en su reporte: esta solo cubre al que no la tenga.
                signed_at: new Date().toISOString(),
            };

            this.signing = true;
            try {
                // Sin conexión, o con reportes de la visita todavía en cola, la firma
                // se encola: el syncEngine la aplica cuando los reportes ya existan.
                if (!this.isOnline || visits.some(v => v.hasLocal)) {
                    await this.queueSignature(visits, payload);
                    return;
                }

                const { data } = await visitService.signCustomer(payload);

                // La visita quedó cerrada: el próximo reporte abre una visita nueva.
                visitUuids.forEach(uuid => clearCurrentVisit(uuid));
                this.selected = null;
                await this.load();

                this.$swal.fire({
                    icon: 'success',
                    title: visitUuids.length > 1 ? 'Visitas firmadas' : 'Visita firmada',
                    text: `Se firmaron ${this.countLabel(data.signed)}.`,
                    confirmButtonText: 'Aceptar',
                });
            } catch (err) {
                // Fallo de red al enviar: no perder la firma, encolarla.
                if (!err.response) {
                    await this.queueSignature(visits, payload);
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

        async queueSignature(visits, payload) {
            await offlineManager.queueVisitSignature({
                ...payload,
                visit_uuids: visits.map(v => v.visit_uuid),
                client_name: visits[0].client_name,
                site_name: visits[0].site_name,
                service_date: visits[0].service_date,
            });

            visits.forEach(v => clearCurrentVisit(v.visit_uuid));
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

/* Sede con varios días pendientes */
.site-group {
    background: white;
    border-radius: 12px;
    padding: 0.875rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.site-group__head {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.site-group__all {
    width: 100%;
    margin-top: 0.25rem;
    padding: 0.7rem;
    border: none;
    border-radius: 10px;
    background: linear-gradient(135deg, #30ab0a, #279208);
    color: white;
    font-weight: 600;
    font-size: 0.9rem;
    cursor: pointer;
}

.site-group__hint {
    font-size: 0.75rem;
    color: #94a3b8;
    margin-top: 0.25rem;
}

.day-row {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    width: 100%;
    padding: 0.6rem 0.25rem;
    background: none;
    border: none;
    border-top: 1px solid #f1f5f9;
    font-size: 0.85rem;
    text-align: left;
    cursor: pointer;
}

.day-row__date {
    font-weight: 600;
    color: #1e293b;
}

.day-row__count {
    color: #606060;
    margin-right: auto;
}

/* Días dentro del panel de firma */
.day-block + .day-block {
    margin-top: 0.5rem;
}

.day-block--off .report-row {
    opacity: 0.4;
}

.day-check {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 0;
    border-top: 1px solid #e2e8f0;
    font-size: 0.85rem;
    cursor: pointer;
}

.day-check input {
    width: 18px;
    height: 18px;
    accent-color: #30ab0a;
}

.day-check__date {
    font-weight: 700;
    color: #1e293b;
}

.day-check__count {
    margin-left: auto;
    color: #94a3b8;
    font-size: 0.78rem;
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
