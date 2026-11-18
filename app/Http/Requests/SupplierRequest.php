<?php

namespace App\Http\Requests;

use App\Models\Supplier;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SupplierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // Un NIT en blanco es "sin NIT", no un NIT vacio repetido.
        if ($this->has('nit') && trim((string) $this->input('nit')) === '') {
            $this->merge(['nit' => null]);
        }
    }

    public function rules(): array
    {
        $supplier = $this->route('supplier');

        $rules = [
            'business_name' => ['required', 'string', 'max:255'],
            'trade_name' => ['nullable', 'string', 'max:255'],
            'nit' => ['nullable', 'string', 'max:30', Rule::unique('suppliers', 'nit')->ignore($supplier?->id)->whereNull('deleted_at')],
            'supplier_type' => ['required', Rule::in(Supplier::TYPES)],
            'categories' => ['nullable', 'array'],
            'categories.*' => ['string', 'max:60'],
            'brands' => ['nullable', 'array'],
            'brands.*' => ['string', 'max:60'],
            'website' => ['nullable', 'string', 'max:255'],
            'payment_terms' => ['nullable', 'string', 'max:255'],
            'credit_days' => ['nullable', 'integer', 'min:0', 'max:365'],
            'default_currency' => ['nullable', Rule::in(Supplier::CURRENCIES)],
            'default_lead_time_days' => ['nullable', 'integer', 'min:0', 'max:365'],
            'rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'notes' => ['nullable', 'string'],
            'active' => ['nullable', 'boolean'],
        ];

        // Solo al crear: la sede principal y el asesor principal se pueden dar
        // en el mismo formulario para no obligar a tres pasos.
        if (! $supplier) {
            $rules += [
                'main_branch' => ['nullable', 'array'],
                'main_branch.name' => ['nullable', 'string', 'max:255'],
                'main_branch.address' => ['nullable', 'string', 'max:255'],
                'main_branch.city' => ['nullable', 'string', 'max:100'],
                'main_branch.department' => ['nullable', 'string', 'max:100'],
                'main_branch.phone' => ['nullable', 'string', 'max:30'],
                'main_branch.mobile' => ['nullable', 'string', 'max:30'],
                'main_branch.email' => ['nullable', 'email', 'max:255'],
                'primary_contact' => ['nullable', 'array'],
                'primary_contact.name' => ['nullable', 'string', 'max:255'],
                'primary_contact.position' => ['nullable', 'string', 'max:255'],
                'primary_contact.mobile' => ['nullable', 'string', 'max:30'],
                'primary_contact.email' => ['nullable', 'email', 'max:255'],
            ];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'business_name.required' => 'La razón social es obligatoria.',
            'nit.unique' => 'Ya hay un proveedor con este NIT.',
            'supplier_type.required' => 'Elige el tipo de proveedor.',
            'supplier_type.in' => 'El tipo de proveedor no es válido.',
            'main_branch.email.email' => 'El correo de la sede no tiene un formato válido.',
            'primary_contact.email.email' => 'El correo del asesor no tiene un formato válido.',
        ];
    }
}
