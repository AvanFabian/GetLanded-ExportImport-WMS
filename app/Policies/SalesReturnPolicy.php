<?php

namespace App\Policies;

use App\Models\SalesReturn;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * SalesReturnPolicy
 * 
 * Authorization policy for Sales Returns.
 */
class SalesReturnPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission(['sales.view', 'stock.in.create']);
    }

    public function view(User $user, SalesReturn $salesReturn): bool
    {
        return $this->belongsToSameCompany($user, $salesReturn) &&
               $user->hasAnyPermission(['sales.view', 'stock.in.create']);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('sales.view');
    }

    public function update(User $user, SalesReturn $salesReturn): bool
    {
        return $this->belongsToSameCompany($user, $salesReturn) &&
               $user->hasPermissionTo('sales.view');
    }

    public function delete(User $user, SalesReturn $salesReturn): bool
    {
        return $this->belongsToSameCompany($user, $salesReturn) &&
               $user->hasPermissionTo('sales.view') &&
               ($user->isAdmin() || $user->isManager());
    }

    protected function belongsToSameCompany(User $user, SalesReturn $salesReturn): bool
    {
        return $user->company_id === $salesReturn->company_id;
    }
}
