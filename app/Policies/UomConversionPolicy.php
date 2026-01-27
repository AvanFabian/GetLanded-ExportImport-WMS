<?php

namespace App\Policies;

use App\Models\UomConversion;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * UomConversionPolicy
 * 
 * Authorization policy for Unit of Measure Conversions.
 */
class UomConversionPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return true; // All authenticated users can view UoM
    }

    public function view(User $user, UomConversion $conversion): bool
    {
        return $this->belongsToSameCompany($user, $conversion);
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isManager();
    }

    public function update(User $user, UomConversion $conversion): bool
    {
        return $this->belongsToSameCompany($user, $conversion) &&
               ($user->isAdmin() || $user->isManager());
    }

    public function delete(User $user, UomConversion $conversion): bool
    {
        return $this->belongsToSameCompany($user, $conversion) &&
               $user->isAdmin();
    }

    protected function belongsToSameCompany(User $user, UomConversion $conversion): bool
    {
        return $user->company_id === $conversion->company_id;
    }
}
