<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $companyId = $this->route('company');
        $cardId    = $this->route('card');

        return [
            'first_name'   => ['sometimes', 'required', 'string', 'max:80'],
            'last_name'    => ['sometimes', 'required', 'string', 'max:80'],
            'slug'         => [
                'sometimes',
                'required',
                'string',
                'max:80',
                'alpha_dash',
                Rule::unique('cards', 'slug')
                    ->where('company_id', $companyId)
                    ->ignore($cardId),
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
}
