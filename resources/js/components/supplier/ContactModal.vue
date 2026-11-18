<template>
    <DmModal :title="contact ? 'Editar asesor' : 'Nuevo asesor'" :saving="saving" :error="error"
             @close="$emit('close')" @submit="submit">
        <div class="sp-row">
            <div class="sp-group">
                <label class="sp-label">Nombre <span class="sp-req">*</span></label>
                <input v-model="form.name" type="text" class="sp-input" :class="{ 'sp-has-error': fieldError('name') }"
                       placeholder="Ej: Laura Rincón" />
                <span v-if="fieldError('name')" class="sp-field-error">{{ fieldError('name') }}</span>
            </div>
            <div class="sp-group">
                <label class="sp-label">Cargo</label>
                <input v-model="form.position" type="text" class="sp-input" placeholder="Ej: Asesora comercial" />
            </div>
        </div>
        <div class="sp-row">
            <div class="sp-group">
                <label class="sp-label">Celular</label>
                <input v-model="form.mobile" type="text" class="sp-input" placeholder="Ej: 310 123 4567" />
            </div>
            <div class="sp-group">
                <label class="sp-label">WhatsApp</label>
                <input v-model="form.whatsapp" type="text" class="sp-input" placeholder="Si es distinto del celular" />
                <p class="sp-hint">Vacío = se usa el celular.</p>
            </div>
        </div>
        <div class="sp-row">
            <div class="sp-group">
                <label class="sp-label">Teléfono fijo</label>
                <input v-model="form.phone" type="text" class="sp-input" placeholder="Ej: 601 123 4567" />
            </div>
            <div class="sp-group">
                <label class="sp-label">Extensión</label>
                <input v-model="form.extension" type="text" class="sp-input" placeholder="Ej: 102" />
            </div>
        </div>
        <div class="sp-row">
            <div class="sp-group">
                <label class="sp-label">Correo electrónico</label>
                <input v-model="form.email" type="email" class="sp-input" :class="{ 'sp-has-error': fieldError('email') }"
                       placeholder="asesor@proveedor.com" />
                <span v-if="fieldError('email')" class="sp-field-error">{{ fieldError('email') }}</span>
            </div>
            <div class="sp-group">
                <label class="sp-label">Sede</label>
                <select v-model="form.supplier_branch_id" class="sp-input">
                    <option :value="null">Todas / sin sede fija</option>
                    <option v-for="b in branches" :key="b.id" :value="b.id">
                        {{ b.name }}{{ b.city ? ' · ' + b.city : '' }}
                    </option>
                </select>
            </div>
        </div>
        <div class="sp-group">
            <label class="sp-label">Notas</label>
            <textarea v-model="form.notes" class="sp-input" rows="2" placeholder="Qué líneas maneja, horario en que responde..."></textarea>
        </div>
        <label class="sp-check">
            <input v-model="form.is_primary" type="checkbox" />
            Es el asesor principal (el de los botones rápidos del directorio)
        </label>
    </DmModal>
</template>

<script>
import DmModal from './DmModal.vue';
import modalForm from './modalForm.js';
import supplierService from '@/services/supplierService.js';

export default {
    name: 'ContactModal',
    components: { DmModal },
    mixins: [modalForm],

    props: {
        supplierId: { type: [Number, String], required: true },
        contact: { type: Object, default: null },
        branches: { type: Array, default: () => [] },
    },

    data() {
        const c = this.contact || {};
        return {
            form: {
                name: c.name || '',
                position: c.position || '',
                mobile: c.mobile || '',
                // Si el WhatsApp es el mismo celular, el campo se deja vacío.
                whatsapp: c.whatsapp && c.whatsapp !== c.mobile ? c.whatsapp : '',
                phone: c.phone || '',
                extension: c.extension || '',
                email: c.email || '',
                supplier_branch_id: c.supplier_branch_id ?? null,
                notes: c.notes || '',
                is_primary: !!c.is_primary,
            },
        };
    },

    methods: {
        submit() {
            const payload = { ...this.form, whatsapp: this.form.whatsapp || this.form.mobile || null };

            this.run(() => this.contact
                ? supplierService.updateContact(this.supplierId, this.contact.id, {
                    ...payload, expected_updated_at: this.contact.updated_at,
                })
                : supplierService.storeContact(this.supplierId, payload));
        },
    },
};
</script>
