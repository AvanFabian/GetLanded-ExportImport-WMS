<?php

namespace App\Models\Traits;

use App\Models\Permission;
use App\Models\Role;

/**
 * HasPermissions Trait
 * 
 * Adds permission and role management to the User model.
 * All authorization checks should use hasPermissionTo() instead of role checks.
 */
trait HasPermissions
{
    // =====================================
    // RELATIONSHIPS
    // =====================================

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'permission_user');
    }

    // =====================================
    // PERMISSION CHECKS
    // =====================================

    /**
     * Check if user has a specific permission.
     * Checks both direct permissions and role-based permissions.
     * 
     * @param string $permissionName e.g., 'stock.in.create'
     * @return bool
     */
    public function hasPermissionTo(string $permissionName): bool
    {
        // Check direct permissions first
        if ($this->permissions()->where('name', $permissionName)->exists()) {
            return true;
        }

        // Check role-based permissions
        return $this->hasPermissionViaRole($permissionName);
    }

    /**
     * Check if user has permission through any of their roles.
     */
    public function hasPermissionViaRole(string $permissionName): bool
    {
        return $this->roles()
            ->whereHas('permissions', function ($query) use ($permissionName) {
                $query->where('name', $permissionName);
            })
            ->exists();
    }

    /**
     * Check if user has any of the given permissions.
     */
    public function hasAnyPermission(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermissionTo($permission)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if user has all of the given permissions.
     */
    public function hasAllPermissions(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (!$this->hasPermissionTo($permission)) {
                return false;
            }
        }
        return true;
    }

    // =====================================
    // ROLE CHECKS
    // =====================================

    /**
     * Check if user has a specific role.
     */
    public function hasRole(string $roleName): bool
    {
        return $this->roles()->where('name', $roleName)->exists();
    }

    /**
     * Check if user has any of the given roles.
     */
    public function hasAnyRole(array $roles): bool
    {
        return $this->roles()->whereIn('name', $roles)->exists();
    }

    // =====================================
    // ROLE ASSIGNMENT
    // =====================================

    /**
     * Assign a role to the user.
     */
    public function assignRole(string|Role $role): void
    {
        if (is_string($role)) {
            $role = Role::findByName($role, $this->company_id);
        }

        if ($role) {
            $this->roles()->syncWithoutDetaching([$role->id]);
        }
    }

    /**
     * Remove a role from the user.
     */
    public function removeRole(string|Role $role): void
    {
        if (is_string($role)) {
            $role = Role::findByName($role, $this->company_id);
        }

        if ($role) {
            $this->roles()->detach($role->id);
        }
    }

    /**
     * Sync roles (replace all).
     */
    public function syncRoles(array $roleNames): void
    {
        $roleIds = Role::whereIn('name', $roleNames)
            ->where(function ($query) {
                $query->where('company_id', $this->company_id)
                      ->orWhere('is_system', true);
            })
            ->pluck('id');
            
        $this->roles()->sync($roleIds);
    }

    // =====================================
    // DIRECT PERMISSION ASSIGNMENT
    // =====================================

    /**
     * Give direct permission to user.
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
     * Revoke direct permission from user.
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

    // =====================================
    // HELPERS
    // =====================================

    /**
     * Get all permissions (direct + via roles).
     */
    public function getAllPermissions(): \Illuminate\Support\Collection
    {
        $directPermissions = $this->permissions()->pluck('name');
        
        $rolePermissions = Permission::whereHas('roles', function ($query) {
            $query->whereIn('id', $this->roles()->pluck('id'));
        })->pluck('name');

        return $directPermissions->merge($rolePermissions)->unique();
    }
}
