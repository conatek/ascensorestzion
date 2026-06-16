<template>
    <div class="tech-pending">
        <div class="page-head">
            <button class="back-btn" @click="$router.back()"><i class="fa fa-arrow-left"></i></button>
            <h1>Pendientes</h1>
        </div>

        <!-- Resumen -->
        <div class="summary-card">
            <div class="summary-counts">
                <div class="count">
                    <span class="count__num">{{ pendingCount }}</span>
                    <span class="count__label">Pendientes</span>
                </div>
                <div class="count count--error" v-if="errorCount > 0">
                    <span class="count__num">{{ errorCount }}</span>
                    <span class="count__label">Con error</span>
                </div>
            </div>
            <div class="summary-meta">
                <span class="last-sync">
                    <i class="fa fa-clock"></i>
                    Última sincronización: {{ lastSyncText }}
                </span>
            </div>
            <button
                class="sync-btn"
                @click="syncNow"
                :disabled="offlineState.syncing || !offlineState.isOnline"
            >
                <i class="fa fa-sync" :class="{ 'fa-spin': offlineState.syncing }"></i>
                {{ offlineState.syncing ? 'Sincronizando…' : 'Sincronizar ahora' }}
            </button>
            <p v-if="!offlineState.isOnline" class="offline-note">
                <i class="fa fa-wifi-slash"></i> Sin conexión — se enviará al reconectar.
            </p>
        </div>

        <!-- Lista -->
        <div v-if="items.length" class="items">
            <div v-for="it in items" :key="it.uid" class="item" :class="`item--${it.status}`">
                <div class="item__icon"><i class="fa" :class="it.icon"></i></div>
                <div class="item__body">
                    <div class="item__title">{{ it.title }}</div>
                    <div class="item__subtitle">{{ it.subtitle }}</div>
                    <div class="item__meta">
                        <span class="badge" :class="`badge--${it.status}`">{{ statusLabel(it) }}</span>
                        <span v-if="it.attempts > 0" class="attempts">{{ it.attempts }} intento{{ it.attempts > 1 ? 's' : '' }}</span>
                        <span class="queued">{{ formatRelative(it.queuedAt) }}</span>
                    </div>
                    <div v-if="it.status === 'error' && it.lastError" class="item__error">{{ it.lastError }}</div>
                </div>
                <div class="item__actions">
                    <button v-if="it.status === 'error'" class="act act--retry" @click="retry(it)" title="Reintentar">
                        <i class="fa fa-redo"></i>
                    </button>
                    <button class="act act--discard" @click="discard(it)" title="Descartar">
                        <i class="fa fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Vacío -->
        <div v-else class="empty">
            <i class="fa fa-check-circle"></i>
            <p>Todo sincronizado</p>
            <span>No hay registros pendientes de enviar.</span>
        </div>
    </div>
</template>

<script>
import offlineManager from '@/utils/offlineManager.js';

export default {
    name: 'TechPending',

    data() {
        return {
            raw: { checkins: [], reports: [], attachments: [] },
        };
    },

    computed: {
        offlineState() {
            return offlineManager.state;
        },

        items() {
            const list = [];

            this.raw.checkins.forEach(c => list.push({
                uid: `checkin-${c.localId}`,
                type: 'checkin',
                localId: c.localId,
                icon: 'fa-qrcode',
                title: 'Check-in',
                subtitle: c.equipment_code ? `Equipo ${c.equipment_code}` : 'Registro de ingreso',
                status: c.status || 'pending',
                attempts: c.attempts || 0,
                lastError: c.lastError,
                queuedAt: c.queuedAt,
                _item: c,
            }));

            this.raw.reports.forEach(r => list.push({
                uid: `report-${r.localId}`,
                type: 'report',
                localId: r.localId,
                icon: 'fa-file-alt',
                title: `Reporte ${r.payload?.report_type || ''}`.trim(),
                subtitle: r.payload?.equipment_id ? `Equipo #${r.payload.equipment_id}` : 'Informe de servicio',
                status: r.status || 'pending',
                attempts: r.attempts || 0,
                lastError: r.lastError,
                queuedAt: r.queuedAt,
                _item: r,
            }));

            this.raw.attachments.forEach(a => list.push({
                uid: `attachment-${a.localId}`,
                type: 'attachment',
                localId: a.localId,
                icon: 'fa-camera',
                title: 'Foto',
                subtitle: a.report_id
                    ? (a.condition_key || a.activity_key || 'Adjunto del reporte')
                    : 'Esperando que suba el reporte',
                status: a.status || 'pending',
                attempts: a.attempts || 0,
                lastError: a.lastError,
                queuedAt: a.queuedAt,
                _item: a,
            }));

            // Errores primero, luego por antigüedad
            return list.sort((x, y) => {
                if (x.status === 'error' && y.status !== 'error') return -1;
                if (y.status === 'error' && x.status !== 'error') return 1;
                return (x.queuedAt || 0) - (y.queuedAt || 0);
            });
        },

        // Contadores derivados de la lista mostrada (auto-consistentes con la vista).
        pendingCount() {
            return this.items.filter(i => i.status !== 'error').length;
        },

        errorCount() {
            return this.items.filter(i => i.status === 'error').length;
        },

        lastSyncText() {
            if (!this.offlineState.lastSyncAt) return 'nunca';
            return this.formatRelative(this.offlineState.lastSyncAt);
        },
    },

    watch: {
        // Recargar la lista cuando cambian los contadores o termina un ciclo de sync
        'offlineState.pendingCount': 'load',
        'offlineState.errorCount': 'load',
        'offlineState.lastSyncAt': 'load',
    },

    mounted() {
        this.load();
    },

    methods: {
        async load() {
            this.raw = await offlineManager.getAllPendingItems();
        },

        statusLabel(it) {
            if (it.status === 'error') return 'Error';
            if (this.offlineState.syncing) return 'Subiendo…';
            return 'Pendiente';
        },

        async syncNow() {
            await offlineManager.syncPending(true);
            await this.load();
        },

        async retry(it) {
            await offlineManager.retryItem(it.type, it._item);
            await this.load();
        },

        async discard(it) {
            const res = await this.$swal.fire({
                icon: 'warning',
                title: '¿Descartar este registro?',
                text: 'Se eliminará del dispositivo y no se enviará al servidor. Esta acción no se puede deshacer.',
                showCancelButton: true,
                confirmButtonText: 'Descartar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#ba2831',
            });
            if (!res.isConfirmed) return;
            await offlineManager.discardPending(it.type, it.localId);
            await this.load();
        },

        formatRelative(ts) {
            if (!ts) return '';
            const diff = Date.now() - ts;
            const min = Math.floor(diff / 60000);
            if (min < 1) return 'hace un momento';
            if (min < 60) return `hace ${min} min`;
            const h = Math.floor(min / 60);
            if (h < 24) return `hace ${h} h`;
            const d = Math.floor(h / 24);
            return `hace ${d} día${d > 1 ? 's' : ''}`;
        },
    },
};
</script>

<style scoped>
.tech-pending {
    padding: 1rem;
    max-width: 640px;
    margin: 0 auto;
}

.page-head {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 1rem;
}

.back-btn {
    width: 36px;
    height: 36px;
    border: none;
    background: white;
    border-radius: 10px;
    color: #475569;
    font-size: 1rem;
    cursor: pointer;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
}

.page-head h1 {
    font-size: 1.15rem;
    font-weight: 700;
    color: #1e293b;
    margin: 0;
}

/* Resumen */
.summary-card {
    background: white;
    border-radius: 14px;
    padding: 1rem;
    margin-bottom: 1rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
}

.summary-counts {
    display: flex;
    gap: 1.5rem;
    margin-bottom: 0.75rem;
}

.count {
    display: flex;
    flex-direction: column;
}

.count__num {
    font-size: 1.75rem;
    font-weight: 800;
    color: #30ab0a;
    line-height: 1;
}

.count--error .count__num {
    color: #ba2831;
}

.count__label {
    font-size: 0.75rem;
    color: #64748b;
    margin-top: 2px;
}

.summary-meta {
    margin-bottom: 0.75rem;
}

.last-sync {
    font-size: 0.8rem;
    color: #64748b;
}

.last-sync i {
    margin-right: 4px;
}

.sync-btn {
    width: 100%;
    padding: 0.7rem;
    border: none;
    border-radius: 10px;
    background: linear-gradient(135deg, #30ab0a, #279208);
    color: white;
    font-size: 0.9rem;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.sync-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.offline-note {
    margin: 0.6rem 0 0;
    font-size: 0.8rem;
    color: #ba2831;
    text-align: center;
}

/* Items */
.items {
    display: flex;
    flex-direction: column;
    gap: 0.6rem;
}

.item {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    background: white;
    border-radius: 12px;
    padding: 0.85rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
    border-left: 3px solid #fde68a;
}

.item--error {
    border-left-color: #ba2831;
}

.item__icon {
    width: 38px;
    height: 38px;
    flex-shrink: 0;
    border-radius: 10px;
    background: #f1f5f9;
    color: #475569;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
}

.item__body {
    flex: 1;
    min-width: 0;
}

.item__title {
    font-size: 0.9rem;
    font-weight: 600;
    color: #1e293b;
}

.item__subtitle {
    font-size: 0.8rem;
    color: #64748b;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.item__meta {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-top: 0.35rem;
    flex-wrap: wrap;
}

.badge {
    font-size: 0.7rem;
    font-weight: 600;
    padding: 2px 8px;
    border-radius: 10px;
}

.badge--pending {
    background: #fffbeb;
    color: #d97706;
}

.badge--error {
    background: #fef2f2;
    color: #ba2831;
}

.attempts,
.queued {
    font-size: 0.7rem;
    color: #94a3b8;
}

.item__error {
    margin-top: 0.4rem;
    font-size: 0.75rem;
    color: #ba2831;
    background: #fef2f2;
    padding: 0.35rem 0.5rem;
    border-radius: 8px;
}

.item__actions {
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
}

.act {
    width: 34px;
    height: 34px;
    border: none;
    border-radius: 9px;
    cursor: pointer;
    font-size: 0.85rem;
    display: flex;
    align-items: center;
    justify-content: center;
}

.act--retry {
    background: #ecfdf5;
    color: #30ab0a;
}

.act--discard {
    background: #f1f5f9;
    color: #94a3b8;
}

.act--discard:active {
    background: #fef2f2;
    color: #ba2831;
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
    font-size: 1rem;
    font-weight: 600;
    color: #475569;
    margin: 0 0 0.25rem;
}

.empty span {
    font-size: 0.85rem;
}
</style>
