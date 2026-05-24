<template>
    <div class="confirmation-page">
        <div class="confirmation-card">
            <!-- Logo -->
            <div class="confirmation-logo">
                <img :src="'/images/logo/logo-atzion.svg'" alt="Ascensores Tzion" />
            </div>

            <!-- Loading -->
            <div v-if="loading" class="confirmation-loading">
                <i class="fa fa-spinner fa-spin"></i>
                <p>Cargando información del reporte...</p>
            </div>

            <!-- Error -->
            <div v-else-if="error" class="confirmation-error">
                <div class="confirmation-icon confirmation-icon--error">
                    <i class="fa fa-exclamation-circle"></i>
                </div>
                <h2>Error</h2>
                <p>{{ error }}</p>
            </div>

            <!-- Ya confirmado -->
            <div v-else-if="status === 'already_confirmed'" class="confirmation-confirmed">
                <div class="confirmation-icon confirmation-icon--info">
                    <i class="fa fa-info-circle"></i>
                </div>
                <h2>Ya fue confirmado</h2>
                <p>Este informe ya fue confirmado anteriormente.</p>
                <p v-if="confirmedAt" class="confirmed-date">
                    Confirmado el {{ formatDate(confirmedAt) }}
                </p>
            </div>

            <!-- Pendiente de confirmar -->
            <div v-else-if="status === 'pending'" class="confirmation-pending">
                <div class="confirmation-icon confirmation-icon--pending">
                    <i class="fa fa-clipboard-check"></i>
                </div>
                <h2>Confirmar Recepción</h2>
                <p class="confirmation-subtitle">Informe de servicio de Ascensores Tzion</p>

                <!-- Resumen del reporte -->
                <div class="report-summary">
                    <div class="summary-item">
                        <span class="summary-label">Informe</span>
                        <span class="summary-value">
                            <span class="type-badge" :class="'type-' + report.report_type">{{ report.report_type }}</span>
                            {{ report.report_number }}
                        </span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Tipo</span>
                        <span class="summary-value">{{ report.report_type_label }}</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Fecha</span>
                        <span class="summary-value">{{ report.service_date }}</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Equipo</span>
                        <span class="summary-value">{{ report.equipment_code }} — {{ report.equipment_brand }}</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Sede</span>
                        <span class="summary-value">{{ report.site_name }}</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Técnico</span>
                        <span class="summary-value">{{ report.technician_name }}</span>
                    </div>
                </div>

                <button
                    class="confirm-btn"
                    :disabled="confirming"
                    @click="confirmReception"
                >
                    <i v-if="confirming" class="fa fa-spinner fa-spin"></i>
                    <template v-else>
                        <i class="fa fa-check-circle"></i> Confirmo la recepción de este reporte
                    </template>
                </button>
            </div>

            <!-- Confirmacion exitosa -->
            <div v-else-if="status === 'confirmed'" class="confirmation-success">
                <div class="confirmation-icon confirmation-icon--success">
                    <i class="fa fa-check-circle"></i>
                </div>
                <h2>Recepción confirmada</h2>
                <p>Gracias por confirmar la recepción del informe.</p>
                <p class="success-detail">El informe ha sido cerrado exitosamente.</p>
            </div>
        </div>

        <!-- Footer -->
        <div class="confirmation-footer">
            <p>&copy; {{ new Date().getFullYear() }} Ascensores Tzion</p>
        </div>
    </div>
</template>

<script>
import api from '@/services/api.js';

export default {
    name: 'ReportConfirmation',

    data() {
        return {
            loading: true,
            status: null,
            report: {},
            confirmedAt: null,
            error: null,
            confirming: false,
        };
    },

    created() {
        this.loadReport();
    },

    methods: {
        async loadReport() {
            const token = this.$route.params.token;
            this.loading = true;

            try {
                const { data } = await api.get(`/report-confirmation/${token}`);
                this.status = data.status;
                this.report = data.report || {};
                this.confirmedAt = data.confirmed_at || null;
            } catch (err) {
                if (err.response?.status === 404) {
                    this.error = 'El enlace de confirmación no es válido o ha expirado.';
                } else {
                    this.error = 'Error al cargar la información. Intente nuevamente.';
                }
            } finally {
                this.loading = false;
            }
        },

        async confirmReception() {
            const token = this.$route.params.token;
            this.confirming = true;

            try {
                await api.post(`/report-confirmation/${token}`);
                this.status = 'confirmed';
            } catch (err) {
                if (err.response?.status === 422) {
                    this.status = 'already_confirmed';
                } else {
                    this.error = 'Error al confirmar. Intente nuevamente.';
                }
            } finally {
                this.confirming = false;
            }
        },

        formatDate(dateStr) {
            if (!dateStr) return '';
            return new Date(dateStr).toLocaleDateString('es-CO', {
                day: 'numeric',
                month: 'long',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
            });
        },
    },
};
</script>

<style scoped>
.confirmation-page {
    min-height: 100vh;
    background: linear-gradient(135deg, #f0fdf0 0%, #f8fafc 50%, #f0fdf0 100%);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 1.5rem;
}

.confirmation-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
    border: 1px solid #e2e8f0;
    padding: 2rem 1.5rem;
    max-width: 480px;
    width: 100%;
}

.confirmation-logo {
    text-align: center;
    margin-bottom: 1.5rem;
}

.confirmation-logo img {
    height: 50px;
    width: auto;
}

.confirmation-loading {
    text-align: center;
    padding: 2rem 0;
    color: #64748b;
}

.confirmation-loading i {
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
    display: block;
}

/* Icons */
.confirmation-icon {
    text-align: center;
    margin-bottom: 0.75rem;
}

.confirmation-icon i {
    font-size: 3rem;
}

.confirmation-icon--pending i { color: #30ab0a; }
.confirmation-icon--success i { color: #30ab0a; }
.confirmation-icon--error i { color: #ba2831; }
.confirmation-icon--info i { color: #2563eb; }

/* Headings */
.confirmation-pending h2,
.confirmation-success h2,
.confirmation-confirmed h2,
.confirmation-error h2 {
    text-align: center;
    font-size: 1.25rem;
    font-weight: 700;
    color: #1e293b;
    margin: 0 0 0.25rem;
}

.confirmation-subtitle {
    text-align: center;
    color: #64748b;
    font-size: 0.85rem;
    margin: 0 0 1.25rem;
}

/* Summary */
.report-summary {
    background: #f8fafc;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    padding: 0.85rem;
    margin-bottom: 1.25rem;
}

.summary-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.4rem 0;
    border-bottom: 1px solid #f1f5f9;
}

.summary-item:last-child {
    border-bottom: none;
}

.summary-label {
    font-size: 0.8rem;
    color: #64748b;
}

.summary-value {
    font-size: 0.85rem;
    font-weight: 500;
    color: #1e293b;
    text-align: right;
}

.type-badge {
    font-size: 0.65rem;
    font-weight: 700;
    padding: 0.1rem 0.35rem;
    border-radius: 4px;
}

.type-RSTP { background: #e8f5e4; color: #30ab0a; }
.type-RSTC { background: #fef2f2; color: #ba2831; }
.type-RSTE { background: #fffbeb; color: #d97706; }

/* Confirm button */
.confirm-btn {
    width: 100%;
    padding: 0.85rem;
    background: linear-gradient(135deg, #30ab0a, #279208);
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    min-height: 52px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    box-shadow: 0 4px 16px rgba(48, 171, 10, 0.3);
    transition: opacity 0.2s;
}

.confirm-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* Success */
.confirmation-success p,
.confirmation-confirmed p,
.confirmation-error p {
    text-align: center;
    color: #64748b;
    font-size: 0.9rem;
    margin: 0 0 0.25rem;
}

.success-detail {
    font-size: 0.8rem !important;
    color: #94a3b8 !important;
}

.confirmed-date {
    font-size: 0.8rem !important;
    color: #94a3b8 !important;
    font-style: italic;
}

/* Footer */
.confirmation-footer {
    margin-top: 1.5rem;
    text-align: center;
    color: #94a3b8;
    font-size: 0.75rem;
}

.confirmation-footer p {
    margin: 0;
}
</style>
