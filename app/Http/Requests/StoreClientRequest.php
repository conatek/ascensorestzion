<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'business_name' => ['required', 'string', 'max:255'],
            'nit'           => ['required', 'string', 'max:30', 'unique:clients,nit'],
            'contact_name'  => ['nullable', 'string', 'max:255'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:20'],
            'address'       => ['nullable', 'string', 'max:500'],
            'city'          => ['nullable', 'string', 'max:100'],
            'department'    => ['nullable', 'string', 'max:100'],
            'notes'         => ['nullable', 'string'],
            'active'        => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'business_name.required' => 'La razón social es obligatoria.',
            'nit.required'           => 'El NIT es obligatorio.',
            'nit.unique'             => 'Este NIT ya está registrado.',
        ];
    }
}
