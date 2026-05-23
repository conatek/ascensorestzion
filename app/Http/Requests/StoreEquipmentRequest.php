<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEquipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $equipmentId = $this->route('equipment')?->id;

        return [
            'site_id'                    => ['required', 'exists:sites,id'],
            'internal_code'              => ['required', 'string', 'max:50', Rule::unique('equipment', 'internal_code')->ignore($equipmentId)],
            'customer_code'              => ['nullable', 'string', 'max:50'],
            'equipment_type'             => ['required', Rule::in(['ascensor', 'escalera_electrica', 'montacargas', 'otro'])],
            'brand'                      => ['nullable', 'string', 'max:100'],
            'model'                      => ['nullable', 'string', 'max:100'],
            'serial_number'              => ['nullable', 'string', 'max:100'],
            'capacity_kg'                => ['nullable', 'integer', 'min:0'],
            'capacity_persons'           => ['nullable', 'integer', 'min:0'],
            'stops'                      => ['nullable', 'integer', 'min:0'],
            'speed_mps'                  => ['nullable', 'numeric', 'min:0'],
            'installation_date'          => ['nullable', 'date'],
            'commissioning_date'         => ['nullable', 'date'],
            'contract_type'              => ['nullable', Rule::in(['mantenimiento', 'garantia', 'por_llamada', 'integral'])],
            'contract_start'             => ['nullable', 'date'],
            'contract_end'               => ['nullable', 'date', 'after_or_equal:contract_start'],
            'maintenance_frequency_days' => ['nullable', 'integer', 'min:1'],
            'status'                     => ['nullable', Rule::in(['activo', 'fuera_servicio', 'modernizacion', 'retirado'])],
            'notes'                      => ['nullable', 'string'],
            'photo'                      => ['nullable', 'image', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'site_id.required'        => 'La sede es obligatoria.',
            'site_id.exists'          => 'La sede seleccionada no existe.',
            'internal_code.required'  => 'El código interno es obligatorio.',
            'internal_code.unique'    => 'Este código interno ya está en uso.',
            'equipment_type.required' => 'El tipo de equipo es obligatorio.',
        ];
    }
}
