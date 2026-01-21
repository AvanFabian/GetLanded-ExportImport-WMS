<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Permission Model
 * 
 * Represents a granular permission in the system.
 * Permissions are global and attached to roles.
 */
class Permission extends Model
{
    protected $fillable = [
        'name',
        'display_name',
        'group',
        'description',
    ];

    // =====================================
    // RELATIONSHIPS
    // =====================================

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_permission');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'permission_user');
    }

    // =====================================
    // SCOPES
    // =====================================

    public function scopeInGroup($query, string $group)
    {
        return $query->where('group', $group);
    }

    // =====================================
    // STATIC HELPERS
    // =====================================

    /**
     * Find permission by name.
     */
    public static function findByName(string $name): ?Permission
    {
        return static::where('name', $name)->first();
    }
}
