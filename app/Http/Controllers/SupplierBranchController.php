<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\SharedEditing;
use App\Models\Supplier;
use App\Models\SupplierBranch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupplierBranchController extends Controller
{
    use SharedEditing;

    public function store(Request $request, Supplier $supplier): JsonResponse
    {
        $data = $this->validated($request);

        // La primera sede es la principal aunque no se marque.
        if (! $supplier->branches()->exists()) {
            $data['is_main'] = true;
        }

        $branch = $supplier->branches()->create($data);
        $this->keepSingleMain($branch);

        $this->announce($request, 'branch', 'created', $supplier->id);

        return response()->json($branch->fresh(), 201);
    }

    public function update(Request $request, Supplier $supplier, SupplierBranch $branch): JsonResponse
    {
        $this->ensureBelongs($supplier, $branch);
        $this->abortIfStale($request, $branch);

        $branch->update($this->validated($request));
        $this->keepSingleMain($branch);

        $this->announce($request, 'branch', 'updated', $supplier->id);

        return response()->json($branch->fresh());
    }

    public function destroy(Request $request, Supplier $supplier, SupplierBranch $branch): JsonResponse
    {
        $this->ensureBelongs($supplier, $branch);

        $wasMain = $branch->is_main;
        $branch->delete();

        // Si se va la principal, la siguiente hereda el puesto.
        if ($wasMain && ($next = $supplier->branches()->first())) {
            $next->update(['is_main' => true]);
        }

        $this->announce($request, 'branch', 'deleted', $supplier->id);

        return response()->json(null, 204);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'is_main' => ['nullable', 'boolean'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'department' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:30'],
            'mobile' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'business_hours' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'active' => ['nullable', 'boolean'],
        ], [
            'name.required' => 'El nombre de la sede es obligatorio.',
            'email.email' => 'El correo no tiene un formato válido.',
        ]);
    }

    private function keepSingleMain(SupplierBranch $branch): void
    {
        if ($branch->is_main) {
            SupplierBranch::where('supplier_id', $branch->supplier_id)
                ->where('id', '!=', $branch->id)
                ->update(['is_main' => false]);
        }
    }

    private function ensureBelongs(Supplier $supplier, SupplierBranch $branch): void
    {
        abort_if((int) $branch->supplier_id !== (int) $supplier->id, 404);
    }
}
