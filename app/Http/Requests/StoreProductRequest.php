<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
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
            'price'       => ['required', 'numeric', 'min:0'],
            'discount'    => ['nullable', 'numeric', 'min:0', 'max:100'],
            'comment'     => ['nullable', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:5000'],
            'order'       => ['nullable', 'integer', 'min:0'],
            'is_active'   => ['nullable', 'in:0,1,true,false'],
        ];
    }
}
