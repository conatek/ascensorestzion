<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\SharedEditing;
use App\Models\Supplier;
use App\Models\SupplierContact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SupplierContactController extends Controller
{
    use SharedEditing;

    public function store(Request $request, Supplier $supplier): JsonResponse
    {
        $data = $this->validated($request, $supplier);

        // El primer asesor es el principal aunque no se marque.
        if (! $supplier->contacts()->exists()) {
            $data['is_primary'] = true;
        }

        $contact = $supplier->contacts()->create($data);
        $this->keepSinglePrimary($contact);

        $this->announce($request, 'contact', 'created', $supplier->id);

        return response()->json($contact->fresh()->load('branch:id,name,city'), 201);
    }

    public function update(Request $request, Supplier $supplier, SupplierContact $contact): JsonResponse
    {
        $this->ensureBelongs($supplier, $contact);
        $this->abortIfStale($request, $contact);

        $contact->update($this->validated($request, $supplier));
        $this->keepSinglePrimary($contact);

        $this->announce($request, 'contact', 'updated', $supplier->id);

        return response()->json($contact->fresh()->load('branch:id,name,city'));
    }

    public function destroy(Request $request, Supplier $supplier, SupplierContact $contact): JsonResponse
    {
        $this->ensureBelongs($supplier, $contact);

        $wasPrimary = $contact->is_primary;
        $contact->delete();

        if ($wasPrimary && ($next = $supplier->contacts()->first())) {
            $next->update(['is_primary' => true]);
        }

        $this->announce($request, 'contact', 'deleted', $supplier->id);

        return response()->json(null, 204);
    }

    private function validated(Request $request, Supplier $supplier): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'supplier_branch_id' => [
                'nullable',
                Rule::exists('supplier_branches', 'id')->where('supplier_id', $supplier->id),
            ],
            'position' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'extension' => ['nullable', 'string', 'max:10'],
            'mobile' => ['nullable', 'string', 'max:30'],
            'whatsapp' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'is_primary' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
            'active' => ['nullable', 'boolean'],
        ], [
            'name.required' => 'El nombre del asesor es obligatorio.',
            'email.email' => 'El correo no tiene un formato válido.',
            'supplier_branch_id.exists' => 'La sede elegida no pertenece a este proveedor.',
        ]);
    }

    private function keepSinglePrimary(SupplierContact $contact): void
    {
        if ($contact->is_primary) {
            SupplierContact::where('supplier_id', $contact->supplier_id)
                ->where('id', '!=', $contact->id)
                ->update(['is_primary' => false]);
        }
    }

    private function ensureBelongs(Supplier $supplier, SupplierContact $contact): void
    {
        abort_if((int) $contact->supplier_id !== (int) $supplier->id, 404);
    }
}
