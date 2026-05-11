<template>
    <div>
        <div class="app-page-title">
            <div class="page-title-wrapper">
                <div class="page-title-heading">
                    <div class="page-title-icon">
                        <i class="fa fa-tachometer-alt icon-gradient bg-mean-fruit"></i>
                    </div>
                    <div>
                        Panel de Administracion
                        <div class="page-title-subheading text-muted">
                            Metricas globales de NexosCard
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="loading-state">
            <div class="spinner-border text-primary"></div>
            <p class="text-muted mt-3">Cargando metricas...</p>
        </div>

        <template v-else>
            <!-- Stat Cards -->
            <div class="stats-grid">
                <div class="stat-card stat-purple">
                    <div class="stat-icon"><i class="fa fa-building"></i></div>
                    <div class="stat-content">
                        <div class="stat-number">{{ metrics.total_companies }}</div>
                        <div class="stat-label">Empresas</div>
                    </div>
                </div>

                <div class="stat-card stat-blue">
                    <div class="stat-icon"><i class="fa fa-users"></i></div>
                    <div class="stat-content">
                        <div class="stat-number">{{ metrics.total_users }}</div>
                        <div class="stat-label">Usuarios</div>
                    </div>
                </div>

                <div class="stat-card stat-green">
                    <div class="stat-icon"><i class="fa fa-check-circle"></i></div>
                    <div class="stat-content">
                        <div class="stat-number">{{ metrics.active_subscriptions }}</div>
                        <div class="stat-label">Suscripciones activas</div>
                    </div>
                </div>

                <div class="stat-card stat-amber">
                    <div class="stat-icon"><i class="fa fa-flask"></i></div>
                    <div class="stat-content">
                        <div class="stat-number">{{ metrics.trial_subscriptions }}</div>
                        <div class="stat-label">En prueba</div>
                    </div>
                </div>

                <div class="stat-card stat-emerald">
                    <div class="stat-icon"><i class="fa fa-dollar-sign"></i></div>
                    <div class="stat-content">
                        <div class="stat-number">${{ formatCurrency(metrics.monthly_revenue) }}</div>
                        <div class="stat-label">Ingresos del mes</div>
                    </div>
                </div>

                <div class="stat-card stat-red">
                    <div class="stat-icon"><i class="fa fa-exclamation-triangle"></i></div>
                    <div class="stat-content">
                        <div class="stat-number">{{ metrics.expiring_soon }}</div>
                        <div class="stat-label">Por vencer (&lt;5 dias)</div>
                    </div>
                </div>
            </div>

            <!-- Distribución por plan -->
            <div class="section-card" v-if="metrics.companies_by_plan?.length">
                <div class="section-header">
                    <i class="fa fa-chart-pie section-icon icon-purple"></i>
                    <span>Suscripciones por plan</span>
                </div>
                <div class="section-body">
                    <div class="plan-distribution">
                        <div v-for="plan in metrics.companies_by_plan" :key="plan.id" class="dist-item">
                            <div class="dist-info">
                                <span class="dist-name">{{ plan.display_name }}</span>
                                <span class="dist-count">{{ plan.subscriptions_count }}</span>
                            </div>
                            <div class="dist-bar">
                                <div class="dist-fill" :style="{ width: distPercent(plan.subscriptions_count) + '%' }"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</template>

<script>
import adminService from '@/services/adminService.js';

export default {
    name: 'AdminDashboard',

    data() {
        return {
            metrics: {},
            loading: true,
        };
    },

    async created() {
        await this.load();
    },

    methods: {
        async load() {
            this.loading = true;
            try {
                const { data } = await adminService.dashboard();
                this.metrics = data;
            } finally {
                this.loading = false;
            }
        },

        formatCurrency(value) {
            if (!value) return '0';
            return Number(value).toLocaleString('es-CO', { maximumFractionDigits: 0 });
        },

        distPercent(count) {
            const total = (this.metrics.companies_by_plan || [])
                .reduce((sum, p) => sum + (p.subscriptions_count || 0), 0);
            if (!total) return 0;
            return Math.round((count / total) * 100);
        },
    },
};
</script>

<style scoped>
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.25rem;
    margin-bottom: 1.5rem;
}

.stat-card {
    background: white;
    border-radius: 12px;
    padding: 1.25rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    border: 1px solid #e2e8f0;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    flex-shrink: 0;
}

.stat-purple .stat-icon  { background: #f3e8ff; color: #7c3aed; }
.stat-blue .stat-icon    { background: #dbeafe; color: #2563eb; }
.stat-green .stat-icon   { background: #d1fae5; color: #059669; }
.stat-amber .stat-icon   { background: #fef3c7; color: #d97706; }
.stat-emerald .stat-icon { background: #d1fae5; color: #047857; }
.stat-red .stat-icon     { background: #fee2e2; color: #dc2626; }

.stat-number {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1e293b;
    line-height: 1.2;
}

.stat-label {
    font-size: 0.8rem;
    color: #64748b;
    margin-top: 0.1rem;
}

/* Section card */
.section-card {
    background: white;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
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
    border-radius: 12px 12px 0 0;
}

.section-icon {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
}

.icon-purple { background: #f3e8ff; color: #7c3aed; }

.section-body { padding: 1.25rem; }

/* Distribution */
.plan-distribution {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.dist-item { }

.dist-info {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.4rem;
}

.dist-name {
    font-size: 0.9rem;
    font-weight: 500;
    color: #334155;
}

.dist-count {
    font-size: 0.9rem;
    font-weight: 700;
    color: #1e293b;
}

.dist-bar {
    height: 8px;
    background: #e2e8f0;
    border-radius: 4px;
    overflow: hidden;
}

.dist-fill {
    height: 100%;
    background: linear-gradient(135deg, #8b5cf6, #a78bfa);
    border-radius: 4px;
    transition: width 0.5s ease;
    min-width: 4px;
}

.loading-state {
    text-align: center;
    padding: 4rem 0;
}
</style>
