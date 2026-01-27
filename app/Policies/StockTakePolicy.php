<?php

namespace App\Policies;

use App\Models\StockTake;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * StockTakePolicy
 * 
 * Authorization policy for Stock Takes (physical inventory counts).
 */
class StockTakePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission(['inventory.view', 'stock.adjustment']);
    }

    public function view(User $user, StockTake $stockTake): bool
    {
        return $this->belongsToSameCompany($user, $stockTake) &&
               $user->hasAnyPermission(['inventory.view', 'stock.adjustment']);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('stock.adjustment');
    }

    public function update(User $user, StockTake $stockTake): bool
    {
        return $this->belongsToSameCompany($user, $stockTake) &&
               $user->hasPermissionTo('stock.adjustment');
    }

    public function delete(User $user, StockTake $stockTake): bool
    {
        return $this->belongsToSameCompany($user, $stockTake) &&
               $user->hasPermissionTo('stock.adjustment') &&
               ($user->isAdmin() || $user->isManager());
    }

    protected function belongsToSameCompany(User $user, StockTake $stockTake): bool
    {
        return $user->company_id === $stockTake->company_id;
    }
}
