<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * TenantScope - Global Scope for Multi-Tenancy
 * 
 * Automatically applies WHERE company_id = ? to all queries
 * on models that use the BelongsToTenant trait.
 * 
 * This ensures absolute data isolation between tenants.
 * 
 * Super-admins can bypass this scope via the SuperAdminMiddleware.
 */
class TenantScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // Skip if super-admin bypass is enabled
        if (app()->bound('disable_tenant_scope') && app('disable_tenant_scope') === true) {
            return;
        }

        // Only apply if we have an authenticated user with a company
        if (auth()->check() && auth()->user()->company_id) {
            $builder->where($model->getTable() . '.company_id', auth()->user()->company_id);
        }
    }
}

