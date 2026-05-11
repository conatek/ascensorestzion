import api from './api'

export default {
    /**
     * Obtener lista de plantillas disponibles
     */
    getTemplates() {
        return api.get('/templates')
    },

    /**
     * Obtener el schema de una plantilla específica
     */
    getSchema(templateName) {
        return api.get(`/templates/${templateName}/schema`)
    },

    /**
     * Obtener la configuración de una empresa
     */
    getSettings(companyId) {
        return api.get(`/companies/${companyId}/settings`)
    },

    /**
     * Actualizar la configuración de una empresa
     */
    updateSettings(companyId, data) {
        return api.put(`/companies/${companyId}/settings`, data)
    },

    /**
     * Restablecer la configuración a valores por defecto
     */
    resetSettings(companyId) {
        return api.post(`/companies/${companyId}/settings/reset`)
    },

    /**
     * Subir imagen para configuración de plantilla
     */
    uploadImage(companyId, file) {
        const formData = new FormData()
        formData.append('image', file, file.name)
        return api.post(`/companies/${companyId}/settings/upload-image`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        })
    },
}
