<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * PaymentPolicy
 * 
 * Authorization policy for Payments.
 * Finance-only access for payment management.
 */
class PaymentPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('finance.view');
    }

    public function view(User $user, Payment $payment): bool
    {
        return $this->belongsToSameCompany($user, $payment) &&
               $user->hasPermissionTo('finance.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('finance.view');
    }

    public function update(User $user, Payment $payment): bool
    {
        return $this->belongsToSameCompany($user, $payment) &&
               $user->hasPermissionTo('finance.view');
    }

    public function delete(User $user, Payment $payment): bool
    {
        return $this->belongsToSameCompany($user, $payment) &&
               $user->hasPermissionTo('finance.view') &&
               ($user->isAdmin() || $user->isManager());
    }

    protected function belongsToSameCompany(User $user, Payment $payment): bool
    {
        return $user->company_id === $payment->company_id;
    }
}
