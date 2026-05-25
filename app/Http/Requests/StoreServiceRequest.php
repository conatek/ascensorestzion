<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:120'],
            'image'       => ['nullable', 'image', 'max:5120'],
            'description' => ['nullable', 'string', 'max:5000'],
            'order'       => ['nullable', 'integer', 'min:0'],
            'is_active'   => ['nullable', 'in:0,1,true,false'],
        ];
    }
}
