<?php

namespace App\Policies;

use App\Models\Webhook;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * WebhookPolicy
 * 
 * Authorization policy for Webhooks (admin-only).
 */
class WebhookPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, Webhook $webhook): bool
    {
        return $this->belongsToSameCompany($user, $webhook) &&
               $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Webhook $webhook): bool
    {
        return $this->belongsToSameCompany($user, $webhook) &&
               $user->isAdmin();
    }

    public function delete(User $user, Webhook $webhook): bool
    {
        return $this->belongsToSameCompany($user, $webhook) &&
               $user->isAdmin();
    }

    protected function belongsToSameCompany(User $user, Webhook $webhook): bool
    {
        return $user->company_id === $webhook->company_id;
    }
}
