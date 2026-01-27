<?php

namespace App\Policies;

use App\Models\Claim;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * ClaimPolicy
 * 
 * Authorization policy for Claims (quality issues, returns).
 */
class ClaimPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission(['sales.view', 'inventory.view']);
    }

    public function view(User $user, Claim $claim): bool
    {
        return $this->belongsToSameCompany($user, $claim) &&
               $user->hasAnyPermission(['sales.view', 'inventory.view']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyPermission(['sales.view', 'inventory.view']);
    }

    public function update(User $user, Claim $claim): bool
    {
        return $this->belongsToSameCompany($user, $claim) &&
               $user->hasAnyPermission(['sales.view', 'inventory.view']);
    }

    public function delete(User $user, Claim $claim): bool
    {
        return $this->belongsToSameCompany($user, $claim) &&
               ($user->isAdmin() || $user->isManager());
    }

    protected function belongsToSameCompany(User $user, Claim $claim): bool
    {
        return $user->company_id === $claim->company_id;
    }
}
