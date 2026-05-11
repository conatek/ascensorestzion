<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanySetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'template_name',
        'customization',
    ];

    protected $casts = [
        'customization' => 'array',
    ];

    /**
     * Obtener la empresa a la que pertenece esta configuración
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Obtener la configuración con valores por defecto aplicados
     * Solo incluye campos que existen en el schema actual
     */
    public function getFullCustomizationAttribute(): array
    {
        $schema = config("templates.schemas.{$this->template_name}", []);
        $custom = $this->customization ?? [];
        $result = [];

        foreach ($schema as $sectionKey => $section) {
            if (!is_array($section)) {
                continue;
            }

            $result[$sectionKey] = [];

            foreach ($section as $fieldKey => $fieldConfig) {
                // Copiar metadatos como _label
                if (str_starts_with($fieldKey, '_')) {
                    $result[$sectionKey][$fieldKey] = $fieldConfig;
                    continue;
                }

                // Usar valor guardado si existe, sino usar default del schema
                if (isset($custom[$sectionKey][$fieldKey])) {
                    $result[$sectionKey][$fieldKey] = array_merge(
                        $fieldConfig,
                        ['value' => $custom[$sectionKey][$fieldKey]]
                    );
                } else {
                    $result[$sectionKey][$fieldKey] = $fieldConfig;
                }
            }
        }

        return $result;
    }

    /**
     * Obtener un valor específico de la customización
     */
    public function getValue(string $key, mixed $default = null): mixed
    {
        return data_get($this->full_customization, $key, $default);
    }

    /**
     * Obtener solo los valores de customización (sin metadatos del schema)
     * Formato: { section: { field: value, ... }, ... }
     */
    public function getValuesOnlyAttribute(): array
    {
        $schema = config("templates.schemas.{$this->template_name}", []);
        $custom = $this->customization ?? [];
        $result = [];

        foreach ($schema as $sectionKey => $section) {
            if (!is_array($section)) {
                continue;
            }

            $result[$sectionKey] = [];

            foreach ($section as $fieldKey => $fieldConfig) {
                // Ignorar metadatos como _label
                if (str_starts_with($fieldKey, '_')) {
                    continue;
                }

                // Usar valor guardado si existe, sino usar default del schema
                if (isset($custom[$sectionKey][$fieldKey])) {
                    $result[$sectionKey][$fieldKey] = $custom[$sectionKey][$fieldKey];
                } elseif (is_array($fieldConfig) && isset($fieldConfig['value'])) {
                    $result[$sectionKey][$fieldKey] = $fieldConfig['value'];
                }
            }
        }

        return $result;
    }
}
