<template>
    <DmModal :title="branch ? 'Editar sede' : 'Nueva sede'" :saving="saving" :error="error"
             @close="$emit('close')" @submit="submit">
        <div class="sp-row">
            <div class="sp-group">
                <label class="sp-label">Nombre de la sede <span class="sp-req">*</span></label>
                <input v-model="form.name" type="text" class="sp-input" :class="{ 'sp-has-error': fieldError('name') }"
                       placeholder="Ej: Principal, Bodega Medellín" />
                <span v-if="fieldError('name')" class="sp-field-error">{{ fieldError('name') }}</span>
            </div>
            <div class="sp-group">
                <label class="sp-label">Dirección</label>
                <input v-model="form.address" type="text" class="sp-input" placeholder="Ej: Cra. 7 # 45-12" />
            </div>
        </div>
        <div class="sp-row">
            <div class="sp-group">
                <label class="sp-label">Ciudad</label>
                <input v-model="form.city" type="text" class="sp-input" placeholder="Ej: Bogotá" />
            </div>
            <div class="sp-group">
                <label class="sp-label">Departamento</label>
                <input v-model="form.department" type="text" class="sp-input" placeholder="Ej: Cundinamarca" />
            </div>
        </div>
        <div class="sp-row">
            <div class="sp-group">
                <label class="sp-label">Teléfono fijo</label>
                <input v-model="form.phone" type="text" class="sp-input" placeholder="Ej: 601 123 4567" />
            </div>
            <div class="sp-group">
                <label class="sp-label">Celular</label>
                <input v-model="form.mobile" type="text" class="sp-input" placeholder="Ej: 310 123 4567" />
            </div>
        </div>
        <div class="sp-row">
            <div class="sp-group">
                <label class="sp-label">Correo electrónico</label>
                <input v-model="form.email" type="email" class="sp-input" :class="{ 'sp-has-error': fieldError('email') }"
                       placeholder="ventas@proveedor.com" />
                <span v-if="fieldError('email')" class="sp-field-error">{{ fieldError('email') }}</span>
            </div>
            <div class="sp-group">
                <label class="sp-label">Horario de atención</label>
                <input v-model="form.business_hours" type="text" class="sp-input" placeholder="Ej: L-V 8 a. m. - 5 p. m." />
            </div>
        </div>
        <div class="sp-group">
            <label class="sp-label">Notas</label>
            <textarea v-model="form.notes" class="sp-input" rows="2" placeholder="Cómo llegar, parqueadero, a quién preguntar..."></textarea>
        </div>
        <label class="sp-check">
            <input v-model="form.is_main" type="checkbox" />
            Es la sede principal (la que se muestra en el directorio)
        </label>
    </DmModal>
</template>

<script>
import DmModal from './DmModal.vue';
import modalForm from './modalForm.js';
import supplierService from '@/services/supplierService.js';

export default {
    name: 'BranchModal',
    components: { DmModal },
    mixins: [modalForm],

    props: {
        supplierId: { type: [Number, String], required: true },
        branch: { type: Object, default: null },
    },

    data() {
        const b = this.branch || {};
        return {
            form: {
                name: b.name || '',
                address: b.address || '',
                city: b.city || '',
                department: b.department || '',
                phone: b.phone || '',
                mobile: b.mobile || '',
                email: b.email || '',
                business_hours: b.business_hours || '',
                notes: b.notes || '',
                is_main: !!b.is_main,
            },
        };
    },

    methods: {
        submit() {
            this.run(() => this.branch
                ? supplierService.updateBranch(this.supplierId, this.branch.id, {
                    ...this.form, expected_updated_at: this.branch.updated_at,
                })
                : supplierService.storeBranch(this.supplierId, this.form));
        },
    },
};
</script>
