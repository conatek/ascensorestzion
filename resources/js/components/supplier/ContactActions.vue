<template>
    <div class="contact-actions" :class="{ 'contact-actions--lg': large }">
        <a v-if="tel" :href="tel" class="ca-btn ca-call" title="Llamar" @click.stop>
            <i class="fa fa-phone"></i>
        </a>
        <a v-if="whatsapp" :href="whatsapp" class="ca-btn ca-whatsapp" title="WhatsApp" target="_blank" rel="noopener" @click.stop>
            <i class="fab fa-whatsapp"></i>
        </a>
        <a v-if="email" :href="`mailto:${email}`" class="ca-btn ca-mail" title="Correo" @click.stop>
            <i class="fa fa-envelope"></i>
        </a>
    </div>
</template>

<script>
import { telLink, whatsappLink } from '@/services/supplierOptions.js';

// Botones directos de llamar, WhatsApp y correo para un asesor o una sede.
export default {
    name: 'ContactActions',

    props: {
        phone: { type: String, default: null },
        mobile: { type: String, default: null },
        whatsappNumber: { type: String, default: null },
        email: { type: String, default: null },
        large: { type: Boolean, default: false },
    },

    computed: {
        tel() {
            return telLink(this.mobile || this.phone);
        },
        whatsapp() {
            return whatsappLink(this.whatsappNumber || this.mobile);
        },
    },
};
</script>

<style scoped>
.contact-actions {
    display: inline-flex;
    gap: 0.4rem;
    flex-shrink: 0;
}

.ca-btn {
    width: 32px;
    height: 32px;
    min-height: 0;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.85rem;
    text-decoration: none;
    transition: all 0.2s;
}

.contact-actions--lg .ca-btn {
    width: 40px;
    height: 40px;
    font-size: 1rem;
}

.ca-call { background: #e8f5e4; color: #279208; }
.ca-call:hover { background: #279208; color: #fff; }

.ca-whatsapp { background: #dcfce7; color: #16a34a; }
.ca-whatsapp:hover { background: #16a34a; color: #fff; }

.ca-mail { background: #f1f5f9; color: #475569; }
.ca-mail:hover { background: #475569; color: #fff; }
</style>
