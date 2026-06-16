<template>
    <div
        v-if="visible"
        class="connection-status"
        :class="[statusClass, { 'connection-status--clickable': !!to }]"
        @click="openDetail"
    >
        <i :class="iconClass"></i>
        <span class="connection-status__text">{{ statusText }}</span>
        <button
            v-if="offlineState.isOnline && (offlineState.pendingCount > 0 || offlineState.errorCount > 0)"
            class="connection-status__sync-btn"
            @click.stop="syncNow"
            :disabled="syncing"
        >
            <i class="fa fa-sync" :class="{ 'fa-spin': syncing }"></i>
        </button>
    </div>
</template>

<script>
import offlineManager from '@/utils/offlineManager.js';

export default {
    name: 'ConnectionStatus',

    props: {
        // Nombre de ruta a abrir al tocar el badge (detalle de pendientes).
        // Si no se pasa, el badge no es clickeable (p.ej. en el header admin).
        to: { type: String, default: '' },
    },

    data() {
        return {
            syncing: false,
        };
    },

    computed: {
        offlineState() {
            return offlineManager.state;
        },

        visible() {
            return !this.offlineState.isOnline
                || this.offlineState.pendingCount > 0
                || this.offlineState.errorCount > 0;
        },

        statusClass() {
            if (!this.offlineState.isOnline) return 'connection-status--offline';
            if (this.offlineState.errorCount > 0) return 'connection-status--error';
            if (this.offlineState.pendingCount > 0) return 'connection-status--pending';
            return '';
        },

        iconClass() {
            if (!this.offlineState.isOnline) return 'fa fa-wifi-slash';
            if (this.offlineState.errorCount > 0) return 'fa fa-exclamation-triangle';
            return 'fa fa-cloud-upload-alt';
        },

        statusText() {
            const pending = this.offlineState.pendingCount;
            const errors = this.offlineState.errorCount;

            if (!this.offlineState.isOnline) {
                return pending > 0
                    ? `Sin conexión — ${pending} pendiente${pending > 1 ? 's' : ''}`
                    : 'Sin conexión';
            }
            if (errors > 0) {
                const base = `${errors} con error`;
                return pending > 0 ? `${base}, ${pending} pendiente${pending > 1 ? 's' : ''}` : base;
            }
            if (pending > 0) {
                return `${pending} pendiente${pending > 1 ? 's' : ''} de sincronizar`;
            }
            return '';
        },
    },

    mounted() {
        window.addEventListener('tzion-sync-complete', this.onSyncComplete);
    },

    beforeUnmount() {
        window.removeEventListener('tzion-sync-complete', this.onSyncComplete);
    },

    methods: {
        async syncNow() {
            this.syncing = true;
            try {
                // Forzar reintento (incluye los items marcados como error)
                await offlineManager.syncPending(true);
            } finally {
                this.syncing = false;
            }
        },

        openDetail() {
            if (this.to) {
                this.$router.push({ name: this.to });
            }
        },

        onSyncComplete(e) {
            const uploaded = e.detail?.uploaded || 0;
            if (uploaded <= 0 || !this.$swal) return;
            this.$swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: `${uploaded} elemento${uploaded > 1 ? 's' : ''} sincronizado${uploaded > 1 ? 's' : ''}`,
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
            });
        },
    },
};
</script>

<style scoped>
.connection-status {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px;
    border-radius: 16px;
    font-size: 0.75rem;
    font-weight: 500;
    white-space: nowrap;
    line-height: 1;
}

.connection-status--offline {
    background-color: #fef2f2;
    color: #ba2831;
    border: 1px solid #fecaca;
}

.connection-status--pending {
    background-color: #fffbeb;
    color: #d97706;
    border: 1px solid #fde68a;
}

.connection-status--error {
    background-color: #fef2f2;
    color: #ba2831;
    border: 1px solid #fecaca;
}

.connection-status--clickable {
    cursor: pointer;
}

.connection-status__text {
    max-width: 200px;
    overflow: hidden;
    text-overflow: ellipsis;
}

.connection-status__sync-btn {
    background: none;
    border: none;
    color: inherit;
    cursor: pointer;
    padding: 2px;
    font-size: 0.75rem;
    line-height: 1;
}

.connection-status__sync-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

@media (max-width: 576px) {
    .connection-status__text {
        max-width: 140px;
    }
}
</style>
