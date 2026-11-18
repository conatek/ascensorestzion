// Lo común de los modales de proveedores: guardar, errores de validación (422)
// y el aviso de edición simultánea (409).
export default {
    emits: ['close', 'saved', 'stale'],

    data() {
        return { saving: false, errors: {}, error: null };
    },

    methods: {
        fieldError(name) {
            return this.errors?.[name]?.[0] || null;
        },

        async run(request) {
            this.saving = true;
            this.errors = {};
            this.error = null;

            try {
                const { data } = await request();
                this.$emit('saved', data);
            } catch (err) {
                const status = err.response?.status;
                if (status === 422) {
                    this.errors = err.response.data.errors || {};
                    this.error = 'Revisa los campos marcados.';
                } else if (status === 409) {
                    // Otro master lo cambió mientras estaba abierto.
                    this.$emit('stale', err.response.data);
                } else {
                    this.error = err.response?.data?.message || 'No se pudo guardar. Inténtalo de nuevo.';
                }
            } finally {
                this.saving = false;
            }
        },
    },
};
