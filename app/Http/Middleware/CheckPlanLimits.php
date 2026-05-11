<?php

namespace App\Http\Middleware;

use App\Models\Company;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPlanLimits
{
    /**
     * @param string $resource cards, products, services
     */
    public function handle(Request $request, Closure $next, string $resource): Response
    {
        $user = $request->user();

        // Master bypassa límites
        if ($user->hasRole('Master')) {
            return $next($request);
        }

        $company = $request->route('company');

        if (!$company instanceof Company) {
            return $next($request);
        }

        $plan = $company->currentPlan();

        // Sin plan activo → solo permitir si hay trial activo
        if (!$plan) {
            return response()->json([
                'message'          => 'No tienes un plan activo. Contrata un plan para continuar.',
                'upgrade_required' => true,
            ], 403);
        }

        $limit = $plan->getLimit($resource);

        // null = ilimitado
        if (is_null($limit)) {
            return $next($request);
        }

        $current = $company->{$resource}()->count();

        if ($current >= $limit) {
            $resourceNames = [
                'cards'    => 'tarjetas',
                'products' => 'productos',
                'services' => 'servicios',
            ];
            $name = $resourceNames[$resource] ?? $resource;

            return response()->json([
                'message'          => "Has alcanzado el límite de {$name} de tu plan ({$current}/{$limit}). Mejora tu plan para crear más.",
                'upgrade_required' => true,
                'current'          => $current,
                'limit'            => $limit,
            ], 403);
        }

        return $next($request);
    }
}
