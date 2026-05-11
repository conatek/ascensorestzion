<?php

namespace App\Http\Middleware;

use App\Models\Company;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCompanyAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user->hasRole('Master')) {
            return $next($request);
        }

        $company = $request->route('company');

        if ($company instanceof Company) {
            $isOwner  = (int) $company->user_id === (int) $user->id;
            $isMember = (int) $user->company_id === (int) $company->id;

            if (!$isOwner && !$isMember) {
                abort(403, 'No tienes acceso a esta empresa.');
            }
        }

        return $next($request);
    }
}
