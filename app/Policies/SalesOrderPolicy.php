<?php

namespace App\Policies;

use App\Models\SalesOrder;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * SalesOrderPolicy
 * 
 * Authorization policy for Sales Orders.
 * Combines company_id verification with Spatie permission checks
 * for segregation of duties (Warehouse vs Finance).
 */
class SalesOrderPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the user can view any sales orders.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission(['sales.view', 'invoice.view', 'stock.out.create']);
    }

    /**
     * Determine if the user can view the sales order.
     * Warehouse staff can view for packing, but invoice requires invoice.view
     */
    public function view(User $user, SalesOrder $salesOrder): bool
    {
        if (!$this->belongsToSameCompany($user, $salesOrder)) {
            return false;
        }

        // Any of these permissions allows viewing the SO
        return $user->hasAnyPermission(['sales.view', 'stock.out.create']);
    }

    /**
     * Determine if the user can view invoice (financial data).
     * Stricter than view - only finance/admin roles.
     */
    public function viewInvoice(User $user, SalesOrder $salesOrder): bool
    {
        if (!$this->belongsToSameCompany($user, $salesOrder)) {
            return false;
        }

        return $user->hasPermissionTo('invoice.view');
    }

    /**
     * Determine if the user can create sales orders.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('sales.create');
    }

    /**
     * Determine if the user can update the sales order.
     */
    public function update(User $user, SalesOrder $salesOrder): bool
    {
        if (!$this->belongsToSameCompany($user, $salesOrder)) {
            return false;
        }

        return $user->hasPermissionTo('sales.update');
    }

    /**
     * Determine if the user can delete the sales order.
     */
    public function delete(User $user, SalesOrder $salesOrder): bool
    {
        if (!$this->belongsToSameCompany($user, $salesOrder)) {
            return false;
        }

        return $user->hasPermissionTo('sales.delete') && 
               ($user->isAdmin() || $user->isManager());
    }

    /**
     * Check if user belongs to the same company as the sales order.
     * Double-check in addition to TenantScope.
     */
    protected function belongsToSameCompany(User $user, SalesOrder $salesOrder): bool
    {
        return $user->company_id === $salesOrder->company_id;
    }
}
