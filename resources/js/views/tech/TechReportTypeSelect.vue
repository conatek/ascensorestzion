<template>
    <div class="tech-report-type">
        <h1 class="type-title">Tipo de Reporte</h1>
        <p class="type-subtitle">Seleccione el tipo de servicio realizado</p>

        <div class="type-cards">
            <button
                class="type-card type-card--rstp"
                :class="{ 'is-selected': selectedType === 'RSTP' }"
                @click="selectType('RSTP')"
            >
                <div class="type-card__icon">
                    <i class="fa fa-tools"></i>
                </div>
                <div class="type-card__info">
                    <h3>RSTP</h3>
                    <p>Preventivo</p>
                </div>
                <i class="fa fa-chevron-right type-card__arrow"></i>
            </button>

            <button
                class="type-card type-card--rstc"
                :class="{ 'is-selected': selectedType === 'RSTC', 'is-emergency': isEmergency }"
                @click="selectType('RSTC')"
            >
                <div class="type-card__icon">
                    <i class="fa fa-exclamation-triangle"></i>
                </div>
                <div class="type-card__info">
                    <h3>RSTC</h3>
                    <p>Correctivo</p>
                </div>
                <span v-if="isEmergency" class="emergency-tag">
                    <i class="fa fa-bolt"></i> Emergencia
                </span>
                <i class="fa fa-chevron-right type-card__arrow"></i>
            </button>

            <button
                class="type-card type-card--rste"
                :class="{ 'is-selected': selectedType === 'RSTE' }"
                @click="selectType('RSTE')"
            >
                <div class="type-card__icon">
                    <i class="fa fa-star"></i>
                </div>
                <div class="type-card__info">
                    <h3>RSTE</h3>
                    <p>Especial</p>
                </div>
                <i class="fa fa-chevron-right type-card__arrow"></i>
            </button>
        </div>

        <!-- Loading -->
        <div v-if="creating" class="creating-overlay">
            <i class="fa fa-spinner fa-spin"></i>
            <span>Creando reporte...</span>
        </div>
    </div>
</template>

<script>
import reportService from '@/services/reportService.js';
import offlineManager from '@/utils/offlineManager.js';

export default {
    name: 'TechReportTypeSelect',

    data() {
        return {
            selectedType: null,
            creating: false,
            equipmentId: null,
            isEmergency: false,
            checkinData: null,
        };
    },

    created() {
        this.equipmentId = this.$route.query.equipment_id;

        // Leer datos del check-in
        try {
            const raw = sessionStorage.getItem('current_checkin');
            if (raw) {
                this.checkinData = JSON.parse(raw);
                this.isEmergency = this.checkinData.is_emergency;

                // Pre-seleccionar RSTC si es emergencia
                if (this.isEmergency) {
                    this.selectedType = 'RSTC';
                }
            }
        } catch {}

        if (!this.equipmentId) {
            this.$router.replace({ name: 'tech.checkin' });
        }
    },

    methods: {
        async selectType(type) {
            this.selectedType = type;
            this.creating = true;

            // OFFLINE: no se puede crear en el servidor; abrir el formulario en modo
            // local (se encolará al finalizar y subirá al reconectar).
            if (!offlineManager.state.isOnline) {
                const localId = 'local-' + ((crypto.randomUUID && crypto.randomUUID()) || Date.now());
                this.$router.push({
                    name: 'tech.report.create',
                    query: { report_id: localId, type, equipment_id: this.equipmentId, offline: '1' },
                });
                return;
            }

            try {
                // Crear reporte borrador con datos del check-in
                const payload = {
                    report_type: type,
                    equipment_id: this.equipmentId,
                    service_date: new Date().toISOString().split('T')[0],
                    entry_time: this.checkinData?.checked_in_at
                        ? new Date(this.checkinData.checked_in_at).toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' })
                        : new Date().toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' }),
                    status: 'borrador',
                };

                // Para RSTC con emergencia, pasar datos de tiempo de respuesta
                if (type === 'RSTC' && this.checkinData?.is_emergency) {
                    payload.rstc_details = {
                        call_time: this.checkinData.call_received_at || null,
                        entry_time: this.checkinData.checked_in_at || null,
                    };
                }

                const { data } = await reportService.store(payload);

                // Vincular check-in con reporte (si tenemos checkin_id)
                if (this.checkinData?.checkin_id) {
                    sessionStorage.setItem('current_checkin', JSON.stringify({
                        ...this.checkinData,
                        service_report_id: data.id,
                    }));
                }

                this.$router.push({
                    name: 'tech.report.create',
                    query: {
                        report_id: data.id,
                        type,
                    },
                });
            } catch (err) {
                this.creating = false;
                this.$swal?.fire?.({
                    icon: 'error',
                    title: 'Error',
                    text: err.response?.data?.message || 'No se pudo crear el reporte.',
                }) || alert('Error: ' + (err.response?.data?.message || 'No se pudo crear el reporte.'));
            }
        },
    },
};
</script>

<style scoped>
.tech-report-type {
    padding: 1.5rem 1rem;
}

.type-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: #1e293b;
    margin: 0 0 0.25rem;
    text-align: center;
}

.type-subtitle {
    font-size: 0.85rem;
    color: #64748b;
    margin: 0 0 1.5rem;
    text-align: center;
}

/* Cards */
.type-cards {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.type-card {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.25rem;
    background: white;
    border: 2px solid #e2e8f0;
    border-radius: 16px;
    cursor: pointer;
    transition: all 0.2s;
    text-align: left;
    min-height: 80px;
    width: 100%;
}

.type-card:active {
    transform: scale(0.98);
}

/* RSTP verde */
.type-card--rstp {
    border-left: 5px solid #30ab0a;
}
.type-card--rstp .type-card__icon {
    background: #e8f5e4;
    color: #30ab0a;
}
.type-card--rstp.is-selected {
    border-color: #30ab0a;
    background: #f0fdf0;
}

/* RSTC rojo */
.type-card--rstc {
    border-left: 5px solid #ba2831;
}
.type-card--rstc .type-card__icon {
    background: #fef2f2;
    color: #ba2831;
}
.type-card--rstc.is-selected {
    border-color: #ba2831;
    background: #fef7f7;
}
.type-card--rstc.is-emergency {
    border-color: #ba2831;
    background: #fef2f2;
    animation: pulse-emergency 2s infinite;
}

/* RSTE naranja */
.type-card--rste {
    border-left: 5px solid #d97706;
}
.type-card--rste .type-card__icon {
    background: #fffbeb;
    color: #d97706;
}
.type-card--rste.is-selected {
    border-color: #d97706;
    background: #fffef5;
}

.type-card__icon {
    width: 52px;
    height: 52px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
    flex-shrink: 0;
}

.type-card__info {
    flex: 1;
}

.type-card__info h3 {
    font-size: 1.1rem;
    font-weight: 700;
    color: #1e293b;
    margin: 0;
}

.type-card__info p {
    font-size: 0.85rem;
    color: #64748b;
    margin: 0;
}

.type-card__arrow {
    color: #cbd5e1;
    font-size: 0.85rem;
}

.emergency-tag {
    background: #ba2831;
    color: white;
    font-size: 0.7rem;
    font-weight: 600;
    padding: 0.2rem 0.5rem;
    border-radius: 6px;
    white-space: nowrap;
}

@keyframes pulse-emergency {
    0%, 100% { box-shadow: 0 0 0 0 rgba(186, 40, 49, 0.2); }
    50% { box-shadow: 0 0 0 8px rgba(186, 40, 49, 0); }
}

/* Creating overlay */
.creating-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.9);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    z-index: 200;
    font-size: 1rem;
    font-weight: 600;
    color: #30ab0a;
}

.creating-overlay i {
    font-size: 2rem;
}
</style>
