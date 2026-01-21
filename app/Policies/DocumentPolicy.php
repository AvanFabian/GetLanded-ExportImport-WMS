<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * DocumentPolicy
 * 
 * Ensures users can only access documents belonging to their company.
 */
class DocumentPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Document $document): bool
    {
        return $user->company_id === $document->company_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Document $document): bool
    {
        return $user->company_id === $document->company_id;
    }

    public function delete(User $user, Document $document): bool
    {
        return $user->company_id === $document->company_id &&
               ($user->isAdmin() || $user->isManager() || $document->uploaded_by === $user->id);
    }
}
