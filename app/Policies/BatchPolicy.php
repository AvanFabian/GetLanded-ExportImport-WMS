<?php

namespace App\Policies;

use App\Models\Batch;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * BatchPolicy
 * 
 * Ensures users can only access batches belonging to their company.
 * Prevents IDOR (Insecure Direct Object Reference) attacks.
 */
class BatchPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the user can view any batches.
     */
    public function viewAny(User $user): bool
    {
        return true; // TenantScope handles filtering
    }

    /**
     * Determine if the user can view the batch.
     */
    public function view(User $user, Batch $batch): bool
    {
        return $this->belongsToSameCompany($user, $batch);
    }

    /**
     * Determine if the user can create batches.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine if the user can update the batch.
     */
    public function update(User $user, Batch $batch): bool
    {
        return $this->belongsToSameCompany($user, $batch);
    }

    /**
     * Determine if the user can delete the batch.
     */
    public function delete(User $user, Batch $batch): bool
    {
        return $this->belongsToSameCompany($user, $batch) && 
               ($user->isAdmin() || $user->isManager());
    }

    /**
     * Check if user belongs to the same company as the batch.
     */
    protected function belongsToSameCompany(User $user, Batch $batch): bool
    {
        return $user->company_id === $batch->company_id;
    }
}
