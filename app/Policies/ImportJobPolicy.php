<?php

namespace App\Policies;

use App\Models\ImportJob;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * ImportJobPolicy
 * 
 * Authorization policy for Import Jobs (bulk data import).
 */
class ImportJobPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isManager();
    }

    public function view(User $user, ImportJob $importJob): bool
    {
        return $this->belongsToSameCompany($user, $importJob) &&
               ($user->isAdmin() || $user->isManager() || $importJob->user_id === $user->id);
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isManager();
    }

    public function update(User $user, ImportJob $importJob): bool
    {
        return $this->belongsToSameCompany($user, $importJob) &&
               ($user->isAdmin() || $user->isManager());
    }

    public function delete(User $user, ImportJob $importJob): bool
    {
        return $this->belongsToSameCompany($user, $importJob) &&
               $user->isAdmin();
    }

    protected function belongsToSameCompany(User $user, ImportJob $importJob): bool
    {
        return $user->company_id === $importJob->company_id;
    }
}
