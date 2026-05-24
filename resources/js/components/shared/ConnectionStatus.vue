<template>
    <div v-if="!offlineState.isOnline || offlineState.pendingCount > 0" class="connection-status" :class="statusClass">
        <i :class="iconClass"></i>
        <span class="connection-status__text">{{ statusText }}</span>
        <button
            v-if="offlineState.isOnline && offlineState.pendingCount > 0"
            class="connection-status__sync-btn"
            @click="syncNow"
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

    data() {
        return {
            syncing: false,
        };
    },

    computed: {
        offlineState() {
            return offlineManager.state;
        },

        statusClass() {
            if (!this.offlineState.isOnline) return 'connection-status--offline';
            if (this.offlineState.pendingCount > 0) return 'connection-status--pending';
            return '';
        },

        iconClass() {
            if (!this.offlineState.isOnline) return 'fa fa-wifi-slash';
            return 'fa fa-cloud-upload-alt';
        },

        statusText() {
            if (!this.offlineState.isOnline) {
                const pending = this.offlineState.pendingCount;
                if (pending > 0) {
                    return `Sin conexión — ${pending} pendiente${pending > 1 ? 's' : ''}`;
                }
                return 'Sin conexión';
            }
            if (this.offlineState.pendingCount > 0) {
                return `${this.offlineState.pendingCount} pendiente${this.offlineState.pendingCount > 1 ? 's' : ''} de sincronizar`;
            }
            return '';
        },
    },

    methods: {
        async syncNow() {
            this.syncing = true;
            try {
                await offlineManager.syncPending();
            } finally {
                this.syncing = false;
            }
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
