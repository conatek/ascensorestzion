<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $companyId = $this->route('company');

        return [
            'first_name'   => ['required', 'string', 'max:80'],
            'last_name'    => ['required', 'string', 'max:80'],
            'slug'         => [
                'required',
                'string',
                'max:80',
                'alpha_dash',
                // Único SOLO dentro de la misma empresa
                Rule::unique('cards', 'slug')->where('company_id', $companyId),
            ],
            'job_title'    => ['nullable', 'string', 'max:120'],
            'photo'        => ['nullable', 'image', 'max:5120'],
            'mobile_phone' => ['nullable', 'string', 'max:30'],
            'whatsapp'     => ['nullable', 'string', 'max:30'],
            'email'        => ['nullable', 'email', 'max:150'],
            'linkedin'     => ['nullable', 'url', 'max:255'],
            'whatsapp_message' => ['nullable', 'string', 'max:500'],
            'description'  => ['nullable', 'string', 'max:1000'],
            'is_active'    => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'slug.alpha_dash' => 'El slug solo puede contener letras, números, guiones (-) y underscores (_).',
            'slug.unique'     => 'Ya existe una tarjeta con este slug en esta empresa.',
        ];
    }
}
