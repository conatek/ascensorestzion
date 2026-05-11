<template>
    <div>
        <div class="app-page-title">
            <div class="page-title-wrapper">
                <div class="page-title-heading">
                    <div class="page-title-icon">
                        <i class="fa fa-crown icon-gradient bg-mean-fruit"></i>
                    </div>
                    <div>
                        Planes
                        <div class="page-title-subheading text-muted">
                            Elige el plan ideal para tu empresa
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Toggle mensual/anual -->
        <div class="billing-toggle">
            <span :class="{ 'active': billingPeriod === 'monthly' }">Mensual</span>
            <button class="toggle-switch" :class="{ 'yearly': billingPeriod === 'yearly' }" @click="toggleBilling">
                <span class="toggle-knob"></span>
            </button>
            <span :class="{ 'active': billingPeriod === 'yearly' }">
                Anual
                <span class="save-badge">Ahorra ~17%</span>
            </span>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="loading-state">
            <div class="spinner-border text-primary"></div>
            <p class="text-muted mt-3">Cargando planes...</p>
        </div>

        <!-- Pricing cards -->
        <div v-else class="plans-grid">
            <div v-for="plan in plans" :key="plan.id"
                 class="plan-card"
                 :class="[planClass(plan), { 'current': isCurrentPlan(plan) }]">

                <!-- Badge plan actual -->
                <div v-if="isCurrentPlan(plan)" class="current-badge">Plan actual</div>

                <!-- Header -->
                <div class="plan-header">
                    <div class="plan-icon" :class="'icon-' + plan.name">
                        <i :class="planIcon(plan)"></i>
                    </div>
                    <h3 class="plan-name">{{ plan.display_name }}</h3>
                    <div class="plan-price">
                        <span class="price-amount">${{ formatPrice(plan) }}</span>
                        <span class="price-period" v-if="planPrice(plan) > 0">/ {{ billingPeriod === 'monthly' ? 'mes' : 'año' }}</span>
                        <span class="price-period" v-else>Gratis</span>
                    </div>
                </div>

                <!-- Features -->
                <ul class="plan-features">
                    <li>
                        <i class="fa fa-check"></i>
                        <span>{{ plan.max_cards }} {{ plan.max_cards === 1 ? 'tarjeta' : 'tarjetas' }}</span>
                    </li>
                    <li>
                        <i class="fa fa-check"></i>
                        <span>{{ plan.max_products ? plan.max_products + ' productos' : 'Productos ilimitados' }}</span>
                    </li>
                    <li>
                        <i class="fa fa-check"></i>
                        <span>{{ plan.max_services ? plan.max_services + ' servicios' : 'Servicios ilimitados' }}</span>
                    </li>
                    <li>
                        <i class="fa fa-check"></i>
                        <span>{{ plan.available_templates ? plan.available_templates.length + ' plantillas' : 'Todas las plantillas' }}</span>
                    </li>
                    <li v-if="plan.show_watermark">
                        <i class="fa fa-info-circle text-muted"></i>
                        <span class="text-muted">Con marca NexosCard</span>
                    </li>
                    <li v-else>
                        <i class="fa fa-check"></i>
                        <span>Sin marca de agua</span>
                    </li>
                </ul>

                <!-- Action -->
                <div class="plan-action">
                    <button v-if="isCurrentPlan(plan)" class="btn-plan btn-current" disabled>
                        Plan actual
                    </button>
                    <button v-else-if="plan.name === 'guest'" class="btn-plan btn-guest" disabled>
                        Incluido
                    </button>
                    <router-link v-else
                        :to="{ name: 'subscription.checkout', params: { planId: plan.id }, query: { period: billingPeriod } }"
                        class="btn-plan"
                        :class="'btn-' + plan.name">
                        {{ isUpgrade(plan) ? 'Mejorar plan' : 'Contratar' }}
                    </router-link>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import planService from '@/services/planService.js';
import subscriptionService from '@/services/subscriptionService.js';

export default {
    name: 'Plans',

    data() {
        return {
            plans: [],
            currentPlan: null,
            loading: true,
            billingPeriod: 'monthly',
        };
    },

    async created() {
        await this.load();
    },

    methods: {
        async load() {
            this.loading = true;
            try {
                const [plansRes, subRes] = await Promise.all([
                    planService.all(),
                    subscriptionService.current(),
                ]);
                this.plans = plansRes.data;
                this.currentPlan = subRes.data.plan || null;
            } finally {
                this.loading = false;
            }
        },

        toggleBilling() {
            this.billingPeriod = this.billingPeriod === 'monthly' ? 'yearly' : 'monthly';
        },

        planPrice(plan) {
            return this.billingPeriod === 'yearly' ? plan.price_yearly : plan.price_monthly;
        },

        formatPrice(plan) {
            const price = this.planPrice(plan);
            if (price === 0 || price === '0.00') return '0';
            return Number(price).toLocaleString('es-CO', { maximumFractionDigits: 0 });
        },

        isCurrentPlan(plan) {
            return this.currentPlan && this.currentPlan.id === plan.id;
        },

        isUpgrade(plan) {
            if (!this.currentPlan) return true;
            return plan.sort_order > this.currentPlan.sort_order;
        },

        planClass(plan) {
            return 'plan-' + plan.name;
        },

        planIcon(plan) {
            const icons = {
                guest: 'fa fa-user',
                basico: 'fa fa-star',
                pro: 'fa fa-crown',
            };
            return icons[plan.name] || 'fa fa-cube';
        },
    },
};
</script>

<style scoped>
/* Billing Toggle */
.billing-toggle {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    margin-bottom: 2rem;
    font-size: 0.95rem;
    color: #64748b;
}

.billing-toggle span.active {
    color: #1e293b;
    font-weight: 600;
}

.toggle-switch {
    width: 48px;
    height: 26px;
    background: #cbd5e1;
    border-radius: 13px;
    border: none;
    cursor: pointer;
    position: relative;
    transition: background 0.3s;
    padding: 0;
}

.toggle-switch.yearly {
    background: linear-gradient(135deg, #8b5cf6, #ec4899);
}

.toggle-knob {
    width: 20px;
    height: 20px;
    background: white;
    border-radius: 50%;
    position: absolute;
    top: 3px;
    left: 3px;
    transition: transform 0.3s;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
}

.toggle-switch.yearly .toggle-knob {
    transform: translateX(22px);
}

.save-badge {
    font-size: 0.7rem;
    font-weight: 700;
    background: linear-gradient(135deg, #10b981, #34d399);
    color: white;
    padding: 0.15rem 0.5rem;
    border-radius: 10px;
    margin-left: 0.25rem;
}

/* Plans Grid */
.plans-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1.5rem;
    max-width: 960px;
    margin: 0 auto;
}

/* Plan Card */
.plan-card {
    background: white;
    border-radius: 16px;
    padding: 2rem;
    border: 2px solid #e2e8f0;
    position: relative;
    transition: all 0.3s ease;
    display: flex;
    flex-direction: column;
}

.plan-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.1);
}

.plan-card.current {
    border-color: #8b5cf6;
    box-shadow: 0 0 0 1px #8b5cf6;
}

.current-badge {
    position: absolute;
    top: -12px;
    left: 50%;
    transform: translateX(-50%);
    background: linear-gradient(135deg, #8b5cf6, #ec4899);
    color: white;
    padding: 0.25rem 1rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 700;
    white-space: nowrap;
}

/* Plan Header */
.plan-header {
    text-align: center;
    margin-bottom: 1.5rem;
}

.plan-icon {
    width: 56px;
    height: 56px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
    font-size: 1.25rem;
}

.icon-guest {
    background: #f1f5f9;
    color: #64748b;
}

.icon-basico {
    background: linear-gradient(135deg, #dbeafe, #e0f2fe);
    color: #2563eb;
}

.icon-pro {
    background: linear-gradient(135deg, #f3e8ff, #fce7f3);
    color: #8b5cf6;
}

.plan-name {
    font-size: 1.25rem;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 0.5rem;
}

.plan-price {
    display: flex;
    align-items: baseline;
    justify-content: center;
    gap: 0.25rem;
}

.price-amount {
    font-size: 2rem;
    font-weight: 800;
    color: #1e293b;
}

.price-period {
    font-size: 0.9rem;
    color: #64748b;
}

/* Features */
.plan-features {
    list-style: none;
    padding: 0;
    margin: 0 0 1.5rem;
    flex: 1;
}

.plan-features li {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.5rem 0;
    font-size: 0.9rem;
    color: #334155;
}

.plan-features li i.fa-check {
    color: #10b981;
    font-size: 0.8rem;
    width: 16px;
    text-align: center;
}

.plan-features li i.fa-info-circle {
    font-size: 0.8rem;
    width: 16px;
    text-align: center;
}

/* Action Buttons */
.plan-action {
    margin-top: auto;
}

.btn-plan {
    display: block;
    width: 100%;
    padding: 0.75rem;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.95rem;
    text-align: center;
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-current {
    background: #f1f5f9;
    color: #64748b;
    cursor: default;
}

.btn-guest {
    background: #f1f5f9;
    color: #94a3b8;
    cursor: default;
}

.btn-basico {
    background: linear-gradient(135deg, #3b82f6, #2563eb);
    color: white;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
}

.btn-basico:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 16px rgba(37, 99, 235, 0.4);
    color: white;
}

.btn-pro {
    background: linear-gradient(135deg, #8b5cf6, #ec4899);
    color: white;
    box-shadow: 0 4px 12px rgba(139, 92, 246, 0.3);
}

.btn-pro:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 16px rgba(139, 92, 246, 0.4);
    color: white;
}

/* Loading */
.loading-state {
    text-align: center;
    padding: 4rem 0;
}

/* Responsive */
@media (max-width: 768px) {
    .plans-grid {
        grid-template-columns: 1fr;
        max-width: 400px;
    }

    .price-amount {
        font-size: 1.75rem;
    }
}
</style>
