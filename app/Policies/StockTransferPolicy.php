<?php

namespace App\Policies;

use App\Models\StockTransfer;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * StockTransferPolicy
 * 
 * Authorization policy for Stock Transfers between warehouses.
 */
class StockTransferPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission(['inventory.view', 'stock.adjustment']);
    }

    public function view(User $user, StockTransfer $stockTransfer): bool
    {
        return $this->belongsToSameCompany($user, $stockTransfer) &&
               $user->hasAnyPermission(['inventory.view', 'stock.adjustment']);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('stock.adjustment');
    }

    public function update(User $user, StockTransfer $stockTransfer): bool
    {
        return $this->belongsToSameCompany($user, $stockTransfer) &&
               $user->hasPermissionTo('stock.adjustment');
    }

    public function delete(User $user, StockTransfer $stockTransfer): bool
    {
        return $this->belongsToSameCompany($user, $stockTransfer) &&
               $user->hasPermissionTo('stock.adjustment') &&
               ($user->isAdmin() || $user->isManager());
    }

    protected function belongsToSameCompany(User $user, StockTransfer $stockTransfer): bool
    {
        return $user->company_id === $stockTransfer->company_id;
    }
}
