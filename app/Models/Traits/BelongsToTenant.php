<?php

namespace App\Models\Traits;

use App\Models\Company;
use App\Models\Scopes\TenantScope;

/**
 * BelongsToTenant Trait
 * 
 * Apply this trait to any model that should be scoped by company_id.
 * It automatically:
 * 1. Adds TenantScope for query filtering
 * 2. Sets company_id on model creation
 * 3. Provides company relationship
 */
trait BelongsToTenant
{
    /**
     * Boot the trait.
     */
    public static function bootBelongsToTenant(): void
    {
        // Apply global scope for automatic filtering
        static::addGlobalScope(new TenantScope());

        // Auto-set company_id on creation
        static::creating(function ($model) {
            if (auth()->check() && auth()->user()->company_id) {
                $model->company_id = $model->company_id ?? auth()->user()->company_id;
            }
        });
    }

    /**
     * Get the company that owns this model.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Scope to include all tenants (bypass tenant scope).
     * Use with caution - for admin/super-admin operations only.
     */
    public function scopeWithoutTenantScope($query)
    {
        return $query->withoutGlobalScope(TenantScope::class);
    }

    /**
     * Scope to a specific company.
     */
    public function scopeForCompany($query, $companyId)
    {
        return $query->withoutGlobalScope(TenantScope::class)
                     ->where('company_id', $companyId);
    }
}
