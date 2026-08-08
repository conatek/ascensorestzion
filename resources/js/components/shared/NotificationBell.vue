<template>
    <div class="notification-bell" ref="bellRef">
        <button class="bell-btn" @click="toggleDropdown">
            <i class="fa fa-bell"></i>
            <span v-if="unreadCount > 0" class="bell-badge">{{ unreadCount > 99 ? '99+' : unreadCount }}</span>
        </button>

        <Transition name="dropdown">
            <div v-if="showDropdown" class="bell-dropdown">
                <div class="bell-dropdown__header">
                    <span class="bell-dropdown__title">Notificaciones</span>
                    <button v-if="unreadCount > 0" class="bell-dropdown__mark-all" @click="markAllRead">
                        Marcar todas leídas
                    </button>
                </div>

                <div class="bell-dropdown__body">
                    <div v-if="loading" class="bell-dropdown__loading">
                        <i class="fa fa-spinner fa-spin"></i>
                    </div>

                    <div v-else-if="notifications.length === 0" class="bell-dropdown__empty">
                        <i class="fa fa-bell-slash"></i>
                        <p>Sin notificaciones</p>
                    </div>

                    <div v-else class="bell-dropdown__list">
                        <button
                            v-for="notif in notifications"
                            :key="notif.id"
                            class="notif-item"
                            :class="{ 'is-unread': !notif.read_at }"
                            @click="onClickNotification(notif)"
                        >
                            <div class="notif-item__icon" :class="iconClass(notif)">
                                <i :class="iconName(notif)"></i>
                            </div>
                            <div class="notif-item__content">
                                <p class="notif-item__message">{{ notif.data?.message || 'Notificación' }}</p>
                                <span class="notif-item__time">{{ timeAgo(notif.created_at) }}</span>
                            </div>
                            <span v-if="!notif.read_at" class="notif-item__dot"></span>
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </div>
</template>

<script>
import notificationService from '@/services/notificationService.js';
import { useAuth } from '@/stores/auth';

/** Notificaciones del cronograma: todas llevan a la misma pantalla por rol. */
const VISIT_TYPES = ['visit_scheduled', 'visit_reminder', 'visit_rescheduled', 'visit_cancelled'];

export default {
    name: 'NotificationBell',

    data() {
        return {
            showDropdown: false,
            unreadCount: 0,
            notifications: [],
            loading: false,
            pollTimer: null,
        };
    },

    mounted() {
        this.fetchUnreadCount();
        this.setupEchoListener();
        document.addEventListener('click', this.handleClickOutside);

        // Polling fallback cada 60s (por si WebSocket no conecta)
        this.pollTimer = setInterval(() => this.fetchUnreadCount(), 60000);
    },

    beforeUnmount() {
        document.removeEventListener('click', this.handleClickOutside);
        if (this.pollTimer) clearInterval(this.pollTimer);
        this.teardownEchoListener();
    },

    methods: {
        async fetchUnreadCount() {
            try {
                const { data } = await notificationService.unreadCount();
                this.unreadCount = data.count || 0;
            } catch {}
        },

        async fetchNotifications() {
            this.loading = true;
            try {
                const { data } = await notificationService.index({ per_page: 15 });
                this.notifications = data.data || data || [];
            } catch {
                this.notifications = [];
            } finally {
                this.loading = false;
            }
        },

        toggleDropdown() {
            this.showDropdown = !this.showDropdown;
            if (this.showDropdown) {
                this.fetchNotifications();
            }
        },

        handleClickOutside(event) {
            if (this.$refs.bellRef && !this.$refs.bellRef.contains(event.target)) {
                this.showDropdown = false;
            }
        },

        async onClickNotification(notif) {
            if (!notif.read_at) {
                try {
                    await notificationService.markAsRead(notif.id);
                    notif.read_at = new Date().toISOString();
                    this.unreadCount = Math.max(0, this.unreadCount - 1);
                } catch {}
            }

            this.showDropdown = false;

            // Navegar segun tipo de notificacion
            const type = notif.data?.type;
            if (type === 'report_completed' || type === 'report_reception_confirmed') {
                const reportId = notif.data?.report_id;
                if (reportId) {
                    this.$router.push({ name: 'reports.show', params: { id: reportId } });
                }
                return;
            }

            // Las del cronograma llevan a la pantalla del rol de quien mira: el
            // cliente al portal, el técnico a su agenda, coordinación al tablero.
            if (VISIT_TYPES.includes(type)) {
                const auth = useAuth();
                if (auth.isAdmin() && !auth.isMasterOrSuper()) this.$router.push('/portal/cronograma');
                else if (auth.isTechnician()) this.$router.push('/tech/agenda');
                else this.$router.push('/cronograma');
            }
        },

        async markAllRead() {
            try {
                await notificationService.markAllAsRead();
                this.unreadCount = 0;
                this.notifications.forEach(n => { n.read_at = new Date().toISOString(); });
            } catch {}
        },

        setupEchoListener() {
            const auth = useAuth();
            const userId = auth.state.user?.id;
            if (!userId || !window.Echo) return;

            window.Echo.private(`App.Models.User.${userId}`)
                .listen('.technician.checked-in', () => {
                    this.unreadCount++;
                    if (this.showDropdown) this.fetchNotifications();
                })
                .listen('.report.completed', () => {
                    this.unreadCount++;
                    if (this.showDropdown) this.fetchNotifications();
                });
        },

        teardownEchoListener() {
            const auth = useAuth();
            const userId = auth.state.user?.id;
            if (userId && window.Echo) {
                window.Echo.leave(`App.Models.User.${userId}`);
            }
        },

        iconClass(notif) {
            const type = notif.data?.type;
            if (type === 'technician_checked_in') {
                return notif.data?.is_emergency ? 'icon--emergency' : 'icon--checkin';
            }
            if (type === 'report_completed') return 'icon--report';
            if (type === 'report_reception_confirmed') return 'icon--confirmed';
            if (type === 'visit_cancelled') return 'icon--emergency';
            if (VISIT_TYPES.includes(type)) return 'icon--visit';
            return 'icon--default';
        },

        iconName(notif) {
            const type = notif.data?.type;
            if (type === 'technician_checked_in') return 'fa fa-map-marker-alt';
            if (type === 'report_completed') return 'fa fa-clipboard-check';
            if (type === 'report_reception_confirmed') return 'fa fa-check-double';
            if (type === 'visit_reminder') return 'fa fa-bell';
            if (type === 'visit_cancelled') return 'fa fa-calendar-times';
            if (type === 'visit_rescheduled') return 'fa fa-calendar-alt';
            if (type === 'visit_scheduled') return 'fa fa-calendar-plus';
            return 'fa fa-bell';
        },

        timeAgo(dateStr) {
            if (!dateStr) return '';
            // Si no tiene Z ni offset, asumir UTC (Laravel guarda en UTC en la BD)
            let normalized = dateStr;
            if (!dateStr.endsWith('Z') && !dateStr.includes('+') && !/\d{2}:\d{2}$/.test(dateStr.slice(-6))) {
                normalized = dateStr.replace(' ', 'T') + 'Z';
            }
            const diff = Date.now() - new Date(normalized).getTime();
            if (diff < 0) return 'ahora';
            const mins = Math.floor(diff / 60000);
            if (mins < 1) return 'ahora';
            if (mins < 60) return `hace ${mins} min`;
            const hours = Math.floor(mins / 60);
            if (hours < 24) return `hace ${hours}h`;
            const days = Math.floor(hours / 24);
            return `hace ${days}d`;
        },
    },
};
</script>

<style scoped>
.notification-bell {
    position: relative;
}

.bell-btn {
    position: relative;
    width: 40px;
    height: 40px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    color: #64748b;
    font-size: 1rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.bell-btn:hover {
    background: #f1f5f9;
    color: #1e293b;
}

.bell-badge {
    position: absolute;
    top: -4px;
    right: -4px;
    min-width: 18px;
    height: 18px;
    padding: 0 4px;
    background: #ba2831;
    color: white;
    font-size: 0.65rem;
    font-weight: 700;
    border-radius: 9px;
    display: flex;
    align-items: center;
    justify-content: center;
    line-height: 1;
}

/* Dropdown */
.bell-dropdown {
    position: absolute;
    top: calc(100% + 8px);
    right: 0;
    width: 340px;
    max-height: 420px;
    background: white;
    border-radius: 14px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.12), 0 2px 10px rgba(0, 0, 0, 0.08);
    border: 1px solid #e2e8f0;
    overflow: hidden;
    z-index: 1000;
    display: flex;
    flex-direction: column;
}

.bell-dropdown__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.85rem 1rem;
    border-bottom: 1px solid #f1f5f9;
    flex-shrink: 0;
}

.bell-dropdown__title {
    font-size: 0.95rem;
    font-weight: 700;
    color: #1e293b;
}

.bell-dropdown__mark-all {
    background: none;
    border: none;
    color: #30ab0a;
    font-size: 0.75rem;
    font-weight: 600;
    cursor: pointer;
}

.bell-dropdown__body {
    flex: 1;
    overflow-y: auto;
}

.bell-dropdown__loading,
.bell-dropdown__empty {
    text-align: center;
    padding: 2rem 1rem;
    color: #94a3b8;
}

.bell-dropdown__empty i {
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
    display: block;
}

.bell-dropdown__list {
    display: flex;
    flex-direction: column;
}

/* Notification item */
.notif-item {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    border-bottom: 1px solid #f8fafc;
    cursor: pointer;
    transition: background 0.15s;
    background: white;
    border: none;
    text-align: left;
    width: 100%;
}

.notif-item:hover {
    background: #f8fafc;
}

.notif-item.is-unread {
    background: #f0fdf0;
}

.notif-item__icon {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.85rem;
    flex-shrink: 0;
}

.icon--checkin { background: #e8f5e4; color: #30ab0a; }
.icon--emergency { background: #fef2f2; color: #ba2831; }
.icon--report { background: #dbeafe; color: #2563eb; }
.icon--confirmed { background: #e8f5e4; color: #279208; }
.icon--visit { background: #eefbe9; color: #227a0c; }
.icon--default { background: #f1f5f9; color: #64748b; }

.notif-item__content {
    flex: 1;
    min-width: 0;
}

.notif-item__message {
    font-size: 0.8rem;
    color: #1e293b;
    margin: 0;
    line-height: 1.3;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.notif-item__time {
    font-size: 0.7rem;
    color: #94a3b8;
}

.notif-item__dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #30ab0a;
    flex-shrink: 0;
    margin-top: 6px;
}

/* Animation */
.dropdown-enter-active,
.dropdown-leave-active {
    transition: all 0.2s ease;
}
.dropdown-enter-from,
.dropdown-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}

/* Mobile */
@media (max-width: 576px) {
    .bell-dropdown {
        position: fixed;
        top: 56px;
        left: 0;
        right: 0;
        width: 100%;
        max-height: calc(100vh - 120px);
        border-radius: 0;
    }
}
</style>
