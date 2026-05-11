<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $companyId = $this->route('company');

        return [
            'name' => ['sometimes', 'required', 'string', 'max:150'],
            'slug' => [
                'sometimes',
                'required',
                'string',
                'max:80',
                'alpha_dash',
                Rule::unique('companies', 'slug')->ignore($companyId),
            ],
            'logo' => ['nullable', 'image', 'max:5120'],
            'address' => ['nullable', 'string', 'max:500'],
            'web' => ['nullable', 'url', 'max:255'],
            'my_business' => ['nullable', 'url', 'max:255'],
            'facebook' => ['nullable', 'url', 'max:255'],
            'instagram' => ['nullable', 'url', 'max:255'],
            'twitter' => ['nullable', 'url', 'max:255'],
            'youtube' => ['nullable', 'url', 'max:255'],
            'tiktok' => ['nullable', 'url', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'slug.alpha_dash' => 'El slug solo puede contener letras, números, guiones (-) y underscores (_).',
            'slug.unique'     => 'Este slug ya está en uso. Elige otro.',
        ];
    }
}
