<?php

namespace App\Traits;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

trait HasCompanyAccess
{
    protected function canAccessCompany(User $user, Company $company): bool
    {
        if ($user->hasRole('Master')) {
            return true;
        }

        return (int) $user->company_id === (int) $company->id
            || (int) $company->user_id === (int) $user->id;
    }

    protected function getAccessibleCompanies(User $user): Builder
    {
        if ($user->hasRole('Master')) {
            return Company::query();
        }

        return Company::where('id', $user->company_id)
            ->orWhere('user_id', $user->id);
    }

    protected function enforceCompanyAccess(User $user, Company $company): void
    {
        abort_if(!$this->canAccessCompany($user, $company), 403, 'No tienes acceso a esta empresa.');
    }
}
