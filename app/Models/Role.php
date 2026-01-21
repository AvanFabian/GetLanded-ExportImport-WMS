<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Role Model
 * 
 * Represents a role scoped to a company.
 * System roles (is_system=true) are shared across all companies.
 * Custom roles are company-specific.
 * 
 * NOTE: This model does NOT use BelongsToTenant because system roles
 * have company_id = null and must be accessible to all companies.
 */
class Role extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'display_name',
        'is_system',
        'description',
    ];

    protected $casts = [
        'is_system' => 'boolean',
    ];

    // =====================================
    // RELATIONSHIPS
    // =====================================

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permission');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'role_user');
    }

    // =====================================
    // PERMISSION HELPERS
    // =====================================

    /**
     * Grant a permission to this role.
     */
    public function givePermissionTo(string|Permission $permission): void
    {
        if (is_string($permission)) {
            $permission = Permission::findByName($permission);
        }

        if ($permission) {
            $this->permissions()->syncWithoutDetaching([$permission->id]);
        }
    }

    /**
     * Revoke a permission from this role.
     */
    public function revokePermission(string|Permission $permission): void
    {
        if (is_string($permission)) {
            $permission = Permission::findByName($permission);
        }

        if ($permission) {
            $this->permissions()->detach($permission->id);
        }
    }

    /**
     * Check if role has a specific permission.
     */
    public function hasPermission(string $permissionName): bool
    {
        return $this->permissions()->where('name', $permissionName)->exists();
    }

    /**
     * Sync permissions (replace all).
     */
    public function syncPermissions(array $permissionNames): void
    {
        $permissionIds = Permission::whereIn('name', $permissionNames)->pluck('id');
        $this->permissions()->sync($permissionIds);
    }

    // =====================================
    // STATIC HELPERS
    // =====================================

    /**
     * Find role by name within a company.
     */
    public static function findByName(string $name, ?int $companyId = null): ?Role
    {
        $query = static::where('name', $name);
        
        if ($companyId) {
            $query->where(function ($q) use ($companyId) {
                $q->where('company_id', $companyId)
                  ->orWhere('is_system', true);
            });
        }
        
        return $query->first();
    }
}
