<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function __construct()
    {
        // Permission middleware
    }

    /**
     * Display list of roles for current company.
     */
    public function index()
    {
        if (!auth()->user()->hasPermissionTo('role.manage')) {
            abort(403);
        }

        $companyId = auth()->user()->company_id;

        // Get company-specific roles + system roles
        $roles = Role::withoutGlobalScopes()
            ->where(function ($query) use ($companyId) {
                $query->where('company_id', $companyId)
                      ->orWhere('is_system', true);
            })
            ->withCount('users')
            ->orderBy('is_system', 'desc')
            ->orderBy('name')
            ->get();

        return view('roles.index', compact('roles'));
    }

    /**
     * Show create role form.
     */
    public function create()
    {
        if (!auth()->user()->hasPermissionTo('role.manage')) {
            abort(403);
        }

        $permissions = $this->getGroupedPermissions();
        
        return view('roles.create', compact('permissions'));
    }

    /**
     * Store new role.
     */
    public function store(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('role.manage')) {
            abort(403);
        }

        $companyId = auth()->user()->company_id;

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:50',
                'regex:/^[a-z_]+$/',
                Rule::unique('roles')->where(fn ($query) => $query->where('company_id', $companyId)),
            ],
            'display_name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'permissions' => 'required|array|min:1',
            'permissions.*' => 'exists:permissions,name',
        ], [
            'name.regex' => 'Role name must be lowercase letters and underscores only.',
        ]);

        $role = Role::create([
            'company_id' => $companyId,
            'name' => $validated['name'],
            'display_name' => $validated['display_name'],
            'description' => $validated['description'] ?? null,
            'is_system' => false,
        ]);

        $role->syncPermissions($validated['permissions']);

        return redirect()
            ->route('roles.index')
            ->with('success', 'Role created successfully.');
    }

    /**
     * Show edit role form.
     */
    public function edit(Role $role)
    {
        if (!auth()->user()->hasPermissionTo('role.manage')) {
            abort(403);
        }

        // Verify ownership (system roles or own company)
        if (!$role->is_system && $role->company_id !== auth()->user()->company_id) {
            abort(404);
        }

        // System roles cannot be edited
        if ($role->is_system) {
            return redirect()
                ->route('roles.index')
                ->with('error', 'System roles cannot be edited.');
        }

        $permissions = $this->getGroupedPermissions();
        $rolePermissions = $role->permissions()->pluck('name')->toArray();

        return view('roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    /**
     * Update role.
     */
    public function update(Request $request, Role $role)
    {
        if (!auth()->user()->hasPermissionTo('role.manage')) {
            abort(403);
        }

        // Verify ownership
        if ($role->company_id !== auth()->user()->company_id) {
            abort(404);
        }

        // System roles cannot be updated
        if ($role->is_system) {
            return back()->with('error', 'System roles cannot be modified.');
        }

        $validated = $request->validate([
            'display_name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'permissions' => 'required|array|min:1',
            'permissions.*' => 'exists:permissions,name',
        ]);

        // Self-lockout protection: prevent removing role.manage from own role
        if ($this->wouldCauseSelfLockout($role, $validated['permissions'])) {
            return back()
                ->withInput()
                ->with('error', 'Cannot remove role.manage permission from a role you belong to. This would lock you out.');
        }

        $role->update([
            'display_name' => $validated['display_name'],
            'description' => $validated['description'] ?? null,
        ]);

        $role->syncPermissions($validated['permissions']);

        return redirect()
            ->route('roles.index')
            ->with('success', 'Role updated successfully.');
    }

    /**
     * Delete role.
     */
    public function destroy(Role $role)
    {
        if (!auth()->user()->hasPermissionTo('role.manage')) {
            abort(403);
        }

        // System roles cannot be deleted (check FIRST before ownership)
        if ($role->is_system) {
            return back()->with('error', 'System roles cannot be deleted.');
        }

        // Verify ownership (only for custom roles)
        if ($role->company_id !== auth()->user()->company_id) {
            abort(404);
        }

        // Prevent deleting role if users are assigned
        if ($role->users()->count() > 0) {
            return back()->with('error', 'Cannot delete role with assigned users. Reassign users first.');
        }

        $role->delete();

        return redirect()
            ->route('roles.index')
            ->with('success', 'Role deleted successfully.');
    }

    /**
     * Get permissions grouped by category.
     */
    protected function getGroupedPermissions(): array
    {
        $permissions = Permission::orderBy('group')->orderBy('name')->get();
        
        $grouped = [];
        $groupLabels = [
            'inventory' => ['label' => 'Inventory', 'icon' => '📦', 'description' => 'View and manage inventory data'],
            'stock' => ['label' => 'Stock Operations', 'icon' => '📥', 'description' => 'Create stock movements'],
            'transaction' => ['label' => 'Transaction Workflow', 'icon' => '✅', 'description' => 'Approve or reject pending transactions'],
            'batch' => ['label' => 'Batch Management', 'icon' => '📋', 'description' => 'Manage batch/lot tracking'],
            'finance' => ['label' => 'Finance', 'icon' => '💰', 'description' => 'View financial data and reports'],
            'currency' => ['label' => 'Currency', 'icon' => '💱', 'description' => 'Manage currency settings'],
            'trade' => ['label' => 'Trade & Logistics', 'icon' => '🚢', 'description' => 'Manage international trade metadata'],
            'user' => ['label' => 'User Management', 'icon' => '👤', 'description' => 'Manage users and access'],
            'audit' => ['label' => 'Audit & Logs', 'icon' => '📊', 'description' => 'View system audit logs'],
            'warehouse' => ['label' => 'Warehouses', 'icon' => '🏭', 'description' => 'Manage warehouse locations'],
            'product' => ['label' => 'Products', 'icon' => '📦', 'description' => 'Manage product catalog'],
            'supplier' => ['label' => 'Suppliers', 'icon' => '🤝', 'description' => 'Manage supplier records'],
            'customer' => ['label' => 'Customers', 'icon' => '👥', 'description' => 'Manage customer records'],
            'document' => ['label' => 'Documents', 'icon' => '📄', 'description' => 'Upload and manage documents'],
            'report' => ['label' => 'Reports', 'icon' => '📈', 'description' => 'View and export reports'],
            'role' => ['label' => 'Role Management', 'icon' => '🔐', 'description' => 'Create and manage roles'],
        ];

        foreach ($permissions as $permission) {
            $group = $permission->group ?? 'other';
            if (!isset($grouped[$group])) {
                $meta = $groupLabels[$group] ?? ['label' => ucfirst($group), 'icon' => '⚙️', 'description' => ''];
                $grouped[$group] = [
                    'label' => $meta['label'],
                    'icon' => $meta['icon'],
                    'description' => $meta['description'],
                    'permissions' => [],
                ];
            }
            $grouped[$group]['permissions'][] = $permission;
        }

        return $grouped;
    }

    /**
     * Check if updating permissions would lock out current user.
     */
    protected function wouldCauseSelfLockout(Role $role, array $newPermissions): bool
    {
        $user = auth()->user();
        
        // Check if user belongs to this role
        if (!$user->roles()->where('id', $role->id)->exists()) {
            return false;
        }

        // Check if role.manage is being removed
        if (!in_array('role.manage', $newPermissions)) {
            // Check if user has role.manage from other roles or direct permissions
            $otherRolesHavePermission = $user->roles()
                ->where('id', '!=', $role->id)
                ->whereHas('permissions', fn($q) => $q->where('name', 'role.manage'))
                ->exists();

            $hasDirectPermission = $user->permissions()
                ->where('name', 'role.manage')
                ->exists();

            return !$otherRolesHavePermission && !$hasDirectPermission;
        }

        return false;
    }
}
