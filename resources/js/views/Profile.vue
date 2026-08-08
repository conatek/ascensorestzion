<template>
    <div class="profile-page">
        <div class="profile-head">
            <div class="profile-avatar-wrap">
                <div class="profile-avatar">
                    <img v-if="avatarUrl" :src="avatarUrl" alt="Avatar" />
                    <span v-else>{{ initials }}</span>
                </div>
                <button type="button" class="avatar-edit" :disabled="uploadingAvatar" title="Cambiar foto" @click="$refs.avatarInput.click()">
                    <i class="fa" :class="uploadingAvatar ? 'fa-spinner fa-spin' : 'fa-camera'"></i>
                </button>
                <button v-if="avatarUrl && !uploadingAvatar" type="button" class="avatar-remove" title="Quitar foto" @click="removeAvatar">
                    <i class="fa fa-times"></i>
                </button>
                <input ref="avatarInput" type="file" accept="image/*" class="avatar-input" @change="onAvatarSelected" />
            </div>
            <div class="profile-head__info">
                <h1>{{ form.name || 'Mi Perfil' }}</h1>
                <span class="role-chip">{{ roleLabel }}</span>
            </div>
        </div>

        <!-- Modal de recorte 1:1 -->
        <Teleport to="body">
            <div v-if="showCropper" class="dm-cropper" @click.self="cancelCrop">
                <div class="dm-cropper__card">
                    <div class="dm-cropper__header">
                        <span>Recortar foto (1:1)</span>
                        <button type="button" @click="cancelCrop"><i class="fa fa-times"></i></button>
                    </div>
                    <div class="dm-cropper__body">
                        <Cropper
                            ref="cropper"
                            :src="cropperSrc"
                            :stencil-props="{ aspectRatio: 1 }"
                            class="cropper-component"
                        />
                    </div>
                    <div class="dm-cropper__footer">
                        <button type="button" class="crop-btn crop-btn--secondary" @click="cancelCrop">Cancelar</button>
                        <button type="button" class="crop-btn crop-btn--primary" :disabled="uploadingAvatar" @click="confirmCrop">
                            <i v-if="uploadingAvatar" class="fa fa-spinner fa-spin"></i>
                            <template v-else><i class="fa fa-check"></i> Aplicar</template>
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- Datos de la cuenta -->
        <section class="card">
            <h2 class="card__title"><i class="fa fa-user"></i> Datos de la cuenta</h2>
            <form @submit.prevent="saveProfile">
                <div class="form-grid">
                    <div class="field">
                        <label>Nombre completo</label>
                        <input v-model="form.name" type="text" :class="{ 'is-invalid': errors.name }" />
                        <span v-if="errors.name" class="err">{{ errors.name[0] }}</span>
                    </div>
                    <div class="field">
                        <label>Correo electrónico</label>
                        <input v-model="form.email" type="email" :class="{ 'is-invalid': errors.email }" />
                        <span v-if="errors.email" class="err">{{ errors.email[0] }}</span>
                    </div>
                    <div class="field">
                        <label>Teléfono</label>
                        <input v-model="form.phone" type="text" :class="{ 'is-invalid': errors.phone }" />
                        <span v-if="errors.phone" class="err">{{ errors.phone[0] }}</span>
                    </div>
                    <div class="field field--split">
                        <div>
                            <label>Tipo de documento</label>
                            <select v-model="form.document_type" :class="{ 'is-invalid': errors.document_type }">
                                <option value="">—</option>
                                <option v-for="d in docTypes" :key="d.value" :value="d.value">{{ d.label }}</option>
                            </select>
                        </div>
                        <div>
                            <label>Número</label>
                            <input v-model="form.document_number" type="text" :class="{ 'is-invalid': errors.document_number }" />
                        </div>
                    </div>
                </div>
                <div class="actions">
                    <button type="submit" class="btn-primary" :disabled="savingProfile">
                        <i v-if="savingProfile" class="fa fa-spinner fa-spin"></i>
                        {{ savingProfile ? 'Guardando…' : 'Guardar cambios' }}
                    </button>
                </div>
            </form>
        </section>

        <!-- Cambio de contraseña -->
        <section class="card">
            <h2 class="card__title"><i class="fa fa-lock"></i> Cambiar contraseña</h2>
            <form @submit.prevent="changePassword">
                <div class="form-grid">
                    <div class="field field--full">
                        <label>Contraseña actual</label>
                        <input v-model="pwd.current_password" type="password" autocomplete="current-password" :class="{ 'is-invalid': pwdErrors.current_password }" />
                        <span v-if="pwdErrors.current_password" class="err">{{ pwdErrors.current_password[0] }}</span>
                    </div>
                    <div class="field">
                        <label>Nueva contraseña</label>
                        <input v-model="pwd.password" type="password" autocomplete="new-password" :class="{ 'is-invalid': pwdErrors.password }" />
                        <span v-if="pwdErrors.password" class="err">{{ pwdErrors.password[0] }}</span>
                    </div>
                    <div class="field">
                        <label>Confirmar nueva contraseña</label>
                        <input v-model="pwd.password_confirmation" type="password" autocomplete="new-password" />
                    </div>
                </div>
                <p class="hint">Mínimo 8 caracteres.</p>
                <div class="actions">
                    <button type="submit" class="btn-primary" :disabled="savingPwd">
                        <i v-if="savingPwd" class="fa fa-spinner fa-spin"></i>
                        {{ savingPwd ? 'Actualizando…' : 'Actualizar contraseña' }}
                    </button>
                </div>
            </form>
        </section>

        <!-- Solo a quien le llegan visitas: el cliente y el técnico. -->
        <ReminderSettingsCard v-if="receivesVisitReminders" />
    </div>
</template>

<script>
import { useAuth } from '@/stores/auth';
import profileService from '@/services/profileService.js';
import { Cropper } from 'vue-advanced-cropper';
import 'vue-advanced-cropper/dist/style.css';
import ReminderSettingsCard from '@/components/schedule/ReminderSettingsCard.vue';

const ROLE_LABELS = {
    master: 'Master', super: 'Super', coordinator: 'Coordinador',
    technician: 'Técnico', admin: 'Admin Cliente',
};

export default {
    name: 'Profile',

    components: { Cropper, ReminderSettingsCard },

    data() {
        return {
            form: { name: '', email: '', phone: '', document_type: '', document_number: '' },
            pwd: { current_password: '', password: '', password_confirmation: '' },
            errors: {},
            pwdErrors: {},
            savingProfile: false,
            savingPwd: false,
            uploadingAvatar: false,
            showCropper: false,
            cropperSrc: null,
            docTypes: [
                { value: 'CC', label: 'Cédula de ciudadanía' },
                { value: 'CE', label: 'Cédula de extranjería' },
                { value: 'NIT', label: 'NIT' },
                { value: 'PP', label: 'Pasaporte' },
            ],
        };
    },

    computed: {
        auth() {
            return useAuth();
        },
        roleLabel() {
            const role = this.auth.state.user?.roles?.[0]?.name || '';
            return ROLE_LABELS[role] || role;
        },
        avatarUrl() {
            return this.auth.state.user?.image_url || null;
        },
        initials() {
            const name = this.form.name || this.auth.state.user?.name || '';
            return name.trim().split(/\s+/).slice(0, 2).map(p => p[0]?.toUpperCase() || '').join('') || '?';
        },
        /** Coordinación no recibe recordatorios: vive en el tablero. */
        receivesVisitReminders() {
            return this.auth.isTechnician() || this.auth.isAdmin();
        },
    },

    mounted() {
        this.loadFromUser();
    },

    methods: {
        loadFromUser() {
            const u = this.auth.state.user || {};
            this.form = {
                name: u.name || '',
                email: u.email || '',
                phone: u.phone || '',
                document_type: u.document_type || '',
                document_number: u.document_number || '',
            };
        },

        async saveProfile() {
            this.savingProfile = true;
            this.errors = {};
            try {
                const { data } = await profileService.update(this.form);
                this.auth.setUser(data.user, data.permissions);
                this.loadFromUser();
                this.toast('Perfil actualizado');
            } catch (e) {
                if (e.response?.status === 422) {
                    this.errors = e.response.data.errors || {};
                } else {
                    this.$swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo guardar. Intente de nuevo.' });
                }
            } finally {
                this.savingProfile = false;
            }
        },

        async changePassword() {
            this.savingPwd = true;
            this.pwdErrors = {};
            try {
                await profileService.updatePassword(this.pwd);
                this.pwd = { current_password: '', password: '', password_confirmation: '' };
                this.toast('Contraseña actualizada');
            } catch (e) {
                if (e.response?.status === 422) {
                    this.pwdErrors = e.response.data.errors || {};
                } else {
                    this.$swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo actualizar la contraseña.' });
                }
            } finally {
                this.savingPwd = false;
            }
        },

        toast(title) {
            this.$swal.fire({
                toast: true, position: 'top-end', icon: 'success', title,
                showConfirmButton: false, timer: 2500, timerProgressBar: true,
            });
        },

        // ── Avatar ──
        onAvatarSelected(event) {
            const file = event.target.files[0];
            event.target.value = '';
            if (!file) return;
            const reader = new FileReader();
            reader.onload = (e) => {
                this.cropperSrc = e.target.result;
                this.showCropper = true;
            };
            reader.readAsDataURL(file);
        },

        cancelCrop() {
            this.showCropper = false;
            this.cropperSrc = null;
        },

        async confirmCrop() {
            const result = this.$refs.cropper?.getResult();
            const canvas = result?.canvas;
            if (!canvas) return;

            // Normalizar a 512x512 (1:1) para mantener avatares ligeros y uniformes.
            const size = 512;
            const out = document.createElement('canvas');
            out.width = size;
            out.height = size;
            out.getContext('2d').drawImage(canvas, 0, 0, size, size);

            const blob = await new Promise(resolve => out.toBlob(resolve, 'image/jpeg', 0.9));
            if (!blob) return;

            this.uploadingAvatar = true;
            try {
                const fd = new FormData();
                fd.append('file', blob, 'avatar.jpg');
                const { data } = await profileService.updateAvatar(fd);
                this.auth.setUser(data.user, data.permissions);
                this.cancelCrop();
                this.toast('Foto de perfil actualizada');
            } catch (e) {
                const msg = e.response?.data?.errors?.file?.[0] || 'No se pudo subir la foto. Intente de nuevo.';
                this.$swal.fire({ icon: 'error', title: 'Error', text: msg });
            } finally {
                this.uploadingAvatar = false;
            }
        },

        async removeAvatar() {
            const res = await this.$swal.fire({
                icon: 'warning',
                title: '¿Quitar foto de perfil?',
                showCancelButton: true,
                confirmButtonText: 'Quitar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#ba2831',
            });
            if (!res.isConfirmed) return;

            this.uploadingAvatar = true;
            try {
                const { data } = await profileService.deleteAvatar();
                this.auth.setUser(data.user, data.permissions);
                this.toast('Foto eliminada');
            } catch {
                this.$swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo quitar la foto.' });
            } finally {
                this.uploadingAvatar = false;
            }
        },
    },
};
</script>

<style scoped>
.profile-page {
    max-width: 720px;
    margin: 0 auto;
    padding: 1.5rem 1rem;
}

.profile-head {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1.25rem;
}

.profile-avatar-wrap {
    position: relative;
    flex-shrink: 0;
    width: 72px;
    height: 72px;
}

.profile-avatar {
    width: 72px;
    height: 72px;
    border-radius: 18px;
    background: linear-gradient(135deg, #30ab0a, #279208);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.6rem;
    font-weight: 700;
    overflow: hidden;
}

.profile-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.avatar-input {
    display: none;
}

.avatar-edit {
    position: absolute;
    right: -6px;
    bottom: -6px;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: #30ab0a;
    color: #fff;
    border: 2px solid #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    cursor: pointer;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.25);
}
.avatar-edit:disabled { opacity: 0.7; cursor: not-allowed; }

.avatar-remove {
    position: absolute;
    right: -6px;
    top: -6px;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: #ba2831;
    color: #fff;
    border: 2px solid #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.65rem;
    cursor: pointer;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.25);
}

/* Modal cropper (diálogo centrado) */
.dm-cropper {
    position: fixed;
    inset: 0;
    background: rgba(2, 6, 16, 0.72);
    backdrop-filter: blur(2px);
    z-index: 4000;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
}
.dm-cropper__card {
    width: 100%;
    max-width: 440px;
    max-height: 90vh;
    background: #0f1729;
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 24px 60px rgba(0, 0, 0, 0.55);
    display: flex;
    flex-direction: column;
}
.dm-cropper__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.85rem 1.1rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    font-weight: 600;
    color: #f1f5f9;
    flex-shrink: 0;
}
.dm-cropper__header button {
    background: none;
    border: none;
    color: #93a3bb;
    font-size: 1.1rem;
    cursor: pointer;
}
.dm-cropper__body {
    height: 340px;
    overflow: hidden;
    background: #000;
    flex-shrink: 0;
}
.cropper-component {
    height: 100%;
    width: 100%;
}
.dm-cropper__footer {
    display: flex;
    gap: 0.75rem;
    padding: 0.85rem 1.1rem;
    border-top: 1px solid rgba(255, 255, 255, 0.08);
    flex-shrink: 0;
}
.crop-btn {
    flex: 1;
    padding: 0.75rem;
    border: none;
    border-radius: 10px;
    font-size: 0.95rem;
    font-weight: 600;
    cursor: pointer;
    min-height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
}
.crop-btn--secondary { background: #1e293b; color: #cbd5e1; }
.crop-btn--primary { background: linear-gradient(135deg, #30ab0a, #279208); color: #fff; }
.crop-btn--primary:disabled { opacity: 0.7; cursor: not-allowed; }

.profile-head__info h1 {
    font-size: 1.3rem;
    font-weight: 700;
    color: #1e293b;
    margin: 0 0 0.25rem;
}

.role-chip {
    display: inline-block;
    font-size: 0.72rem;
    font-weight: 600;
    padding: 0.15rem 0.6rem;
    border-radius: 8px;
    background: #e8f5e4;
    color: #279208;
}

.card {
    background: white;
    border-radius: 14px;
    padding: 1.25rem;
    margin-bottom: 1.25rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
}

.card__title {
    font-size: 1rem;
    font-weight: 700;
    color: #1e293b;
    margin: 0 0 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.card__title i {
    color: #30ab0a;
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.field {
    display: flex;
    flex-direction: column;
}

.field--full {
    grid-column: 1 / -1;
}

.field--split {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.75rem;
}

.field--split > div {
    display: flex;
    flex-direction: column;
}

.field label,
.field--split label {
    font-size: 0.82rem;
    font-weight: 600;
    color: #475569;
    margin-bottom: 0.35rem;
}

.field input,
.field select,
.field--split input,
.field--split select {
    padding: 0.6rem 0.75rem;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    font-size: 0.95rem;
    transition: border-color 0.2s;
}

.field input:focus,
.field select:focus,
.field--split input:focus,
.field--split select:focus {
    outline: none;
    border-color: #30ab0a;
}

.is-invalid {
    border-color: #ba2831 !important;
}

.err {
    color: #ba2831;
    font-size: 0.75rem;
    margin-top: 0.25rem;
}

.hint {
    font-size: 0.78rem;
    color: #94a3b8;
    margin: 0.75rem 0 0;
}

.actions {
    margin-top: 1.25rem;
    display: flex;
    justify-content: flex-end;
}

.btn-primary {
    padding: 0.65rem 1.4rem;
    border: none;
    border-radius: 10px;
    background: linear-gradient(135deg, #30ab0a, #279208);
    color: white;
    font-size: 0.92rem;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-primary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

@media (max-width: 576px) {
    .form-grid {
        grid-template-columns: 1fr;
    }
}
</style>
