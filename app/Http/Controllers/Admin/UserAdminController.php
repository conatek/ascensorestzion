<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ScheduledVisit;
use App\Models\ServiceReport;
use App\Models\TechnicianCheckin;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserAdminController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = User::with(['company:id,name', 'client:id,business_name', 'roles:id,name']);

        if ($request->filled('role')) {
            $query->role($request->role);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->get();

        return response()->json($users);
    }

    public function show(User $user): JsonResponse
    {
        $user->load(['company:id,name,slug', 'client:id,business_name', 'roles:id,name']);

        return response()->json($user);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|in:master,super,coordinator,technician,admin',
            'phone' => 'nullable|string|max:20',
            'document_type' => 'nullable|in:CC,CE,NIT,PP',
            'document_number' => 'nullable|string|max:30',
            'company_id' => 'nullable|exists:companies,id',
            'client_id' => 'nullable|exists:clients,id',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'phone' => $data['phone'] ?? null,
            'document_type' => $data['document_type'] ?? null,
            'document_number' => $data['document_number'] ?? null,
            'company_id' => $data['company_id'] ?? null,
            'client_id' => $data['client_id'] ?? null,
            'active' => true,
        ]);

        $user->assignRole($data['role']);

        return response()->json($user->load(['roles:id,name', 'client:id,business_name']), 201);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,'.$user->id,
            'role' => 'sometimes|string|in:master,super,coordinator,technician,admin',
            'phone' => 'nullable|string|max:20',
            'document_type' => 'nullable|in:CC,CE,NIT,PP',
            'document_number' => 'nullable|string|max:30',
            'company_id' => 'sometimes|nullable|exists:companies,id',
            'client_id' => 'sometimes|nullable|exists:clients,id',
            'active' => 'sometimes|boolean',
        ]);

        if (isset($data['role'])) {
            $user->syncRoles([$data['role']]);
            unset($data['role']);
        }

        $user->update($data);

        return response()->json($user->fresh()->load(['company:id,name', 'client:id,business_name', 'roles:id,name']));
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        // No permitir auto-eliminación
        if ((int) $user->id === (int) $request->user()->id) {
            return response()->json(['message' => 'No puedes eliminar tu propio usuario.'], 422);
        }

        // No dejar el sistema sin ningún master
        if ($user->hasRole('master') && User::role('master')->count() <= 1) {
            return response()->json(['message' => 'No se puede eliminar el último usuario master.'], 422);
        }

        // Informes, visitas y check-ins usan restrictOnDelete para conservar la
        // traza (la firma del técnico en un informe no puede quedar huérfana). Si
        // el usuario tiene alguno, se bloquea con un mensaje claro y se sugiere
        // desactivarlo. Es el caso de los técnicos antiguos sin rol.
        $blockers = $this->deletionBlockers($user);
        if ($blockers !== []) {
            return response()->json([
                'message' => 'No se puede eliminar: el usuario tiene '.$this->humanList($blockers)
                    .' asociados. Desactívalo en su lugar para conservar el historial.',
                'blockers' => $blockers,
            ], 422);
        }

        try {
            $user->delete();
        } catch (QueryException $e) {
            // Red de seguridad ante cualquier otra relación con restricción
            // (auditoría, recordatorios) que no se cuenta arriba.
            return response()->json([
                'message' => 'No se puede eliminar: el usuario tiene registros asociados. Desactívalo en su lugar para conservar el historial.',
            ], 422);
        }

        return response()->json(null, 204);
    }

    /**
     * Cuenta los registros que impiden borrar al usuario por su FK restrictiva.
     *
     * @return array<int, array{label: string, count: int}>
     */
    private function deletionBlockers(User $user): array
    {
        $id = $user->id;

        $counts = [
            'informe' => ServiceReport::where('technician_id', $id)->orWhere('created_by', $id)->count(),
            'visita' => ScheduledVisit::where('technician_id', $id)->orWhere('created_by', $id)->count(),
            'check-in' => TechnicianCheckin::where('technician_id', $id)->count(),
        ];

        $blockers = [];
        foreach ($counts as $label => $n) {
            if ($n > 0) {
                $blockers[] = ['label' => $label, 'count' => $n];
            }
        }

        return $blockers;
    }

    /**
     * Convierte los bloqueadores en un texto legible: "3 informes y 2 visitas".
     *
     * @param  array<int, array{label: string, count: int}>  $blockers
     */
    private function humanList(array $blockers): string
    {
        $plurals = [
            'informe' => 'informes',
            'visita' => 'visitas',
            'check-in' => 'check-ins',
        ];

        $parts = array_map(function ($b) use ($plurals) {
            $n = $b['count'];
            $word = $n === 1 ? $b['label'] : ($plurals[$b['label']] ?? $b['label'].'s');

            return $n.' '.$word;
        }, $blockers);

        if (count($parts) === 1) {
            return $parts[0];
        }

        $last = array_pop($parts);

        return implode(', ', $parts).' y '.$last;
    }
}
