<?php

namespace App\Http\Controllers\Concerns;

use App\Events\SupplyDataChanged;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
 * Lo que necesitan los controladores de datos que editan varios master a la
 * vez: no pisar el cambio de otro y avisar a las demas pantallas.
 */
trait SharedEditing
{
    /**
     * El front manda el `updated_at` con el que cargo el registro. Si en el
     * servidor ya es otro, alguien lo cambio entretanto: 409 con quien fue.
     */
    protected function abortIfStale(Request $request, Model $model): void
    {
        $expected = $request->input('expected_updated_at');

        if (! $expected || ! $model->updated_at) {
            return;
        }

        try {
            $expectedTs = Carbon::parse($expected)->getTimestamp();
        } catch (\Throwable) {
            return;
        }

        if ($expectedTs === $model->updated_at->getTimestamp()) {
            return;
        }

        $editor = $model->updated_by ? User::find($model->updated_by) : null;

        abort(response()->json([
            'message' => ($editor?->name ?? 'Otra persona').' modificó este registro mientras lo editabas. '
                .'Recarga para ver sus cambios antes de guardar.',
            'updated_by' => $editor?->name,
            'updated_at' => $model->updated_at->toIso8601String(),
        ], 409));
    }

    protected function announce(Request $request, string $entity, string $action, ?int $supplierId): void
    {
        $user = $request->user();

        // Si el websocket o la cola fallan, el guardado no debe fallar con ellos.
        rescue(
            fn () => SupplyDataChanged::dispatch($entity, $action, $supplierId, $user?->id, $user?->name),
            report: false,
        );
    }
}
