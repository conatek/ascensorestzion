<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Sirve para la edicion del modal y para el drag & drop del calendario, que solo
 * manda las fechas. De ahi que todo sea `sometimes`.
 */
class UpdateScheduledVisitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage_schedule') ?? false;
    }

    public function rules(): array
    {
        return [
            'technician_id' => ['sometimes', 'exists:users,id'],
            'scheduled_start' => ['sometimes', 'date', 'required_with:scheduled_end'],
            'scheduled_end' => ['sometimes', 'date', 'after:scheduled_start', 'required_with:scheduled_start'],
            'visit_type' => ['sometimes', Rule::in(['preventivo', 'correctivo', 'especial'])],
            'notes' => ['sometimes', 'nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'technician_id.exists' => 'El técnico seleccionado no existe.',
            'scheduled_end.after' => 'La hora de fin debe ser posterior a la de inicio.',
        ];
    }
}
