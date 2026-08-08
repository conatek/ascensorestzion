<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRescheduleRequestRequest extends FormRequest
{
    /**
     * Mismo criterio que el resto del portal: manda el client_id del usuario, no un
     * permiso propio. El scoping fino (que la visita sea suya) va en el controller.
     */
    public function authorize(): bool
    {
        return (bool) $this->user()?->client_id;
    }

    public function rules(): array
    {
        return [
            'proposed_start' => ['required', 'date'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ];
        // proposed_end no se acepta: el cliente propone cuando, no cuanto. La
        // duracion se conserva de la visita original.
    }

    public function messages(): array
    {
        return [
            'proposed_start.required' => 'Elige un horario.',
            'proposed_start.date' => 'El horario propuesto no es valido.',
            'reason.max' => 'El motivo no puede pasar de 1000 caracteres.',
        ];
    }
}
