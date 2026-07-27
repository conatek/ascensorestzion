<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreScheduledVisitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage_schedule') ?? false;
    }

    public function rules(): array
    {
        return [
            'equipment_id' => ['required', 'exists:equipment,id'],
            'technician_id' => ['required', 'exists:users,id'],
            'scheduled_start' => ['required', 'date'],
            'scheduled_end' => ['required', 'date', 'after:scheduled_start'],
            'visit_type' => ['nullable', Rule::in(['preventivo', 'correctivo', 'especial'])],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'equipment_id.required' => 'El equipo es obligatorio.',
            'equipment_id.exists' => 'El equipo seleccionado no existe.',
            'technician_id.required' => 'El técnico es obligatorio.',
            'technician_id.exists' => 'El técnico seleccionado no existe.',
            'scheduled_start.required' => 'La fecha y hora de inicio son obligatorias.',
            'scheduled_end.after' => 'La hora de fin debe ser posterior a la de inicio.',
        ];
    }
}
