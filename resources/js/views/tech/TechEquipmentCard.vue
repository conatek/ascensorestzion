<template>
    <div class="tech-equipment">
        <!-- Loading -->
        <div v-if="loading" class="tech-loading">
            <i class="fa fa-spinner fa-spin"></i> Cargando equipo...
        </div>

        <template v-else-if="equipment">
            <!-- Header del equipo -->
            <div class="equip-header">
                <div class="equip-header__type" :class="'type-' + equipment.equipment_type">
                    {{ typeLabel }}
                </div>
                <h1 class="equip-header__code">{{ equipment.internal_code }}</h1>
                <div class="equip-header__status" :class="'status-' + equipment.status">
                    {{ statusLabel }}
                </div>
            </div>

            <!-- Cards de info -->
            <div class="equip-sections">
                <!-- Identificacion -->
                <div class="equip-card" :class="{ 'is-collapsed': collapsed.ident }" @click="collapsed.ident = !collapsed.ident">
                    <div class="equip-card__header">
                        <h3><i class="fa fa-info-circle"></i> Identificación</h3>
                        <i class="fa" :class="collapsed.ident ? 'fa-chevron-down' : 'fa-chevron-up'"></i>
                    </div>
                    <div v-show="!collapsed.ident" class="equip-card__body">
                        <div class="info-row">
                            <span class="info-label">Marca</span>
                            <span class="info-value">{{ equipment.brand }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Modelo</span>
                            <span class="info-value">{{ equipment.model }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Serial</span>
                            <span class="info-value">{{ equipment.serial_number }}</span>
                        </div>
                        <div v-if="equipment.customer_code" class="info-row">
                            <span class="info-label">Código cliente</span>
                            <span class="info-value">{{ equipment.customer_code }}</span>
                        </div>
                    </div>
                </div>

                <!-- Ubicacion -->
                <div class="equip-card" :class="{ 'is-collapsed': collapsed.location }" @click="collapsed.location = !collapsed.location">
                    <div class="equip-card__header">
                        <h3><i class="fa fa-map-marker-alt"></i> Ubicación</h3>
                        <i class="fa" :class="collapsed.location ? 'fa-chevron-down' : 'fa-chevron-up'"></i>
                    </div>
                    <div v-show="!collapsed.location" class="equip-card__body">
                        <div class="info-row">
                            <span class="info-label">Cliente</span>
                            <span class="info-value">{{ equipment.site?.client?.business_name }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Sede</span>
                            <span class="info-value">{{ equipment.site?.name }}</span>
                        </div>
                        <div v-if="equipment.site?.address" class="info-row">
                            <span class="info-label">Dirección</span>
                            <span class="info-value">{{ equipment.site?.address }}<template v-if="equipment.site?.city">, {{ equipment.site.city }}</template></span>
                        </div>
                        <div v-if="equipment.site?.contact_name_onsite" class="info-row">
                            <span class="info-label">Contacto sitio</span>
                            <span class="info-value">{{ equipment.site.contact_name_onsite }} <a v-if="equipment.site.contact_phone_onsite" :href="'tel:' + equipment.site.contact_phone_onsite" class="info-phone"><i class="fa fa-phone"></i></a></span>
                        </div>
                    </div>
                </div>

                <!-- Capacidad -->
                <div class="equip-card" :class="{ 'is-collapsed': collapsed.capacity }" @click="collapsed.capacity = !collapsed.capacity">
                    <div class="equip-card__header">
                        <h3><i class="fa fa-tachometer-alt"></i> Capacidad</h3>
                        <i class="fa" :class="collapsed.capacity ? 'fa-chevron-down' : 'fa-chevron-up'"></i>
                    </div>
                    <div v-show="!collapsed.capacity" class="equip-card__body">
                        <div class="capacity-grid">
                            <div class="capacity-item">
                                <span class="capacity-value">{{ equipment.capacity_kg || '-' }}</span>
                                <span class="capacity-label">kg</span>
                            </div>
                            <div class="capacity-item">
                                <span class="capacity-value">{{ equipment.capacity_persons || '-' }}</span>
                                <span class="capacity-label">personas</span>
                            </div>
                            <div class="capacity-item">
                                <span class="capacity-value">{{ equipment.stops || '-' }}</span>
                                <span class="capacity-label">paradas</span>
                            </div>
                            <div class="capacity-item">
                                <span class="capacity-value">{{ equipment.speed_mps || '-' }}</span>
                                <span class="capacity-label">m/s</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contrato -->
                <div class="equip-card" :class="{ 'is-collapsed': collapsed.contract }" @click="collapsed.contract = !collapsed.contract">
                    <div class="equip-card__header">
                        <h3><i class="fa fa-file-contract"></i> Contrato</h3>
                        <i class="fa" :class="collapsed.contract ? 'fa-chevron-down' : 'fa-chevron-up'"></i>
                    </div>
                    <div v-show="!collapsed.contract" class="equip-card__body">
                        <div class="info-row">
                            <span class="info-label">Tipo</span>
                            <span class="info-value">{{ contractLabel }}</span>
                        </div>
                        <div v-if="equipment.contract_start" class="info-row">
                            <span class="info-label">Vigencia</span>
                            <span class="info-value">{{ formatDate(equipment.contract_start) }} — {{ formatDate(equipment.contract_end) }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Frecuencia</span>
                            <span class="info-value">Cada {{ equipment.maintenance_frequency_days || 30 }} días</span>
                        </div>
                    </div>
                </div>

                <!-- Ultimo reporte -->
                <div v-if="lastReport" class="equip-card last-report-card">
                    <div class="equip-card__header" @click="collapsed.report = !collapsed.report">
                        <h3><i class="fa fa-clipboard-list"></i> Último reporte</h3>
                        <i class="fa" :class="collapsed.report ? 'fa-chevron-down' : 'fa-chevron-up'"></i>
                    </div>
                    <div v-show="!collapsed.report" class="equip-card__body">
                        <div class="info-row">
                            <span class="info-label">Número</span>
                            <span class="info-value report-number" :class="'type-' + lastReport.report_type">{{ lastReport.report_number }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Tipo</span>
                            <span class="info-value">{{ lastReport.report_type }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Fecha</span>
                            <span class="info-value">{{ formatDate(lastReport.service_date) }}</span>
                        </div>
                        <div v-if="lastReport.technician" class="info-row">
                            <span class="info-label">Técnico</span>
                            <span class="info-value">{{ lastReport.technician.name }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <!-- Boton sticky inferior -->
        <div v-if="equipment" class="sticky-bottom">
            <router-link
                :to="{ name: 'tech.report.type', query: { equipment_id: equipment.id } }"
                class="start-report-btn"
            >
                Iniciar Reporte <i class="fa fa-arrow-right"></i>
            </router-link>
        </div>
    </div>
</template>

<script>
import equipmentService from '@/services/equipmentService.js';
import offlineManager from '@/utils/offlineManager.js';

const TYPE_LABELS = {
    ascensor: 'Ascensor',
    escalera_electrica: 'Escalera Eléctrica',
    montacargas: 'Montacargas',
    otro: 'Otro',
};

const STATUS_LABELS = {
    activo: 'Activo',
    fuera_servicio: 'Fuera de Servicio',
    modernizacion: 'En Modernización',
    retirado: 'Retirado',
};

const CONTRACT_LABELS = {
    mantenimiento: 'Mantenimiento',
    garantia: 'Garantía',
    por_llamada: 'Por Llamada',
    integral: 'Integral',
};

export default {
    name: 'TechEquipmentCard',

    data() {
        return {
            equipment: null,
            lastReport: null,
            loading: true,
            collapsed: {
                ident: false,
                location: false,
                capacity: true,
                contract: true,
                report: false,
            },
        };
    },

    computed: {
        typeLabel() {
            return TYPE_LABELS[this.equipment?.equipment_type] || this.equipment?.equipment_type;
        },
        statusLabel() {
            return STATUS_LABELS[this.equipment?.status] || this.equipment?.status;
        },
        contractLabel() {
            return CONTRACT_LABELS[this.equipment?.contract_type] || this.equipment?.contract_type;
        },
    },

    async created() {
        await this.loadEquipment();
    },

    methods: {
        async loadEquipment() {
            this.loading = true;
            const id = this.$route.params.id;

            try {
                const { data } = await equipmentService.get(id);
                this.equipment = data;

                // Cachear para offline
                await offlineManager.cacheEquipment(data);

                // Cargar ultimo reporte
                try {
                    const historyRes = await equipmentService.getHistory(id, { per_page: 1 });
                    const reports = historyRes.data?.data || historyRes.data || [];
                    this.lastReport = reports.length > 0 ? reports[0] : null;
                } catch {}
            } catch {
                // Intentar cache offline
                const cached = await offlineManager.getCachedEquipment(parseInt(id));
                if (cached) {
                    this.equipment = cached;
                }
            } finally {
                this.loading = false;
            }
        },

        formatDate(dateStr) {
            if (!dateStr) return '-';
            return new Date(dateStr).toLocaleDateString('es-CO', {
                day: 'numeric',
                month: 'short',
                year: 'numeric',
            });
        },
    },
};
</script>

<style scoped>
.tech-equipment {
    padding: 1rem;
    padding-bottom: 80px;
}

.tech-loading {
    text-align: center;
    padding: 3rem 1rem;
    color: #64748b;
    font-size: 0.95rem;
}

/* Header */
.equip-header {
    text-align: center;
    margin-bottom: 1.25rem;
}

.equip-header__type {
    display: inline-block;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    padding: 0.2rem 0.6rem;
    border-radius: 6px;
    margin-bottom: 0.25rem;
    background: #f1f5f9;
    color: #64748b;
}

.equip-header__code {
    font-size: 1.5rem;
    font-weight: 800;
    color: #1e293b;
    margin: 0.25rem 0;
}

.equip-header__status {
    display: inline-block;
    font-size: 0.75rem;
    font-weight: 600;
    padding: 0.2rem 0.6rem;
    border-radius: 6px;
}

.status-activo { background: #e8f5e4; color: #279208; }
.status-fuera_servicio { background: #fef2f2; color: #ba2831; }
.status-modernizacion { background: #fffbeb; color: #d97706; }
.status-retirado { background: #f1f5f9; color: #64748b; }

/* Cards */
.equip-sections {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.equip-card {
    background: white;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    overflow: hidden;
    cursor: pointer;
}

.equip-card__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.85rem 1rem;
}

.equip-card__header h3 {
    margin: 0;
    font-size: 0.9rem;
    font-weight: 600;
    color: #1e293b;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.equip-card__header h3 i {
    color: #30ab0a;
    font-size: 0.85rem;
}

.equip-card__header > i {
    color: #94a3b8;
    font-size: 0.75rem;
}

.equip-card__body {
    padding: 0 1rem 0.85rem;
    border-top: 1px solid #f1f5f9;
}

.info-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.4rem 0;
    border-bottom: 1px solid #f8fafc;
}

.info-row:last-child {
    border-bottom: none;
}

.info-label {
    font-size: 0.8rem;
    color: #64748b;
}

.info-value {
    font-size: 0.85rem;
    font-weight: 500;
    color: #1e293b;
    text-align: right;
}

.info-phone {
    color: #30ab0a;
    margin-left: 0.4rem;
}

/* Capacity grid */
.capacity-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 0.5rem;
    padding-top: 0.5rem;
}

.capacity-item {
    text-align: center;
    padding: 0.5rem;
    background: #f8fafc;
    border-radius: 8px;
}

.capacity-value {
    display: block;
    font-size: 1.1rem;
    font-weight: 700;
    color: #1e293b;
}

.capacity-label {
    font-size: 0.7rem;
    color: #94a3b8;
}

/* Report type colors */
.type-RSTP { color: #30ab0a; }
.type-RSTC { color: #ba2831; }
.type-RSTE { color: #d97706; }

.last-report-card {
    border-color: #30ab0a;
    border-width: 2px;
}

/* Sticky bottom */
.sticky-bottom {
    position: fixed;
    bottom: 64px; /* above bottom nav */
    left: 0;
    right: 0;
    padding: 0.75rem 1rem;
    background: linear-gradient(transparent, #f1f4f6 30%);
    z-index: 50;
}

.start-report-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    width: 100%;
    max-width: 640px;
    margin: 0 auto;
    padding: 0.85rem;
    background: linear-gradient(135deg, #30ab0a, #279208);
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 1rem;
    font-weight: 700;
    text-decoration: none;
    min-height: 52px;
    box-shadow: 0 4px 16px rgba(48, 171, 10, 0.35);
}

@media (min-width: 769px) {
    .sticky-bottom {
        bottom: 0;
    }
}
</style>
