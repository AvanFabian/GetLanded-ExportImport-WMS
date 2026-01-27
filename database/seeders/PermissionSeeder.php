<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Seed granular permissions and system roles.
     */
    public function run(): void
    {
        // =========================================
        // PERMISSIONS
        // =========================================
        $permissions = [
            // Inventory
            ['name' => 'inventory.view', 'display_name' => 'View Inventory', 'group' => 'inventory'],
            
            // Stock Operations
            ['name' => 'stock.in.create', 'display_name' => 'Create Stock In', 'group' => 'stock'],
            ['name' => 'stock.out.create', 'display_name' => 'Create Stock Out', 'group' => 'stock'],
            ['name' => 'stock.adjustment', 'display_name' => 'Stock Adjustment (Opname)', 'group' => 'stock'],
            
            // Transaction Workflow
            ['name' => 'transaction.approve', 'display_name' => 'Approve Transactions', 'group' => 'transaction'],
            ['name' => 'transaction.reject', 'display_name' => 'Reject Transactions', 'group' => 'transaction'],
            
            // Batch Management
            ['name' => 'batch.manage', 'display_name' => 'Manage Batches', 'group' => 'batch'],
            
            // Finance
            ['name' => 'finance.view', 'display_name' => 'View Financial Data', 'group' => 'finance'],
            
            // Sales & Invoice (Segregation of Duties)
            ['name' => 'sales.view', 'display_name' => 'View Sales Orders', 'group' => 'sales'],
            ['name' => 'sales.create', 'display_name' => 'Create Sales Orders', 'group' => 'sales'],
            ['name' => 'sales.update', 'display_name' => 'Update Sales Orders', 'group' => 'sales'],
            ['name' => 'sales.delete', 'display_name' => 'Delete Sales Orders', 'group' => 'sales'],
            ['name' => 'invoice.view', 'display_name' => 'View Invoices (Financial)', 'group' => 'finance'],
            
            // Currency
            ['name' => 'currency.manage', 'display_name' => 'Manage Currency Settings', 'group' => 'currency'],
            
            // Trade Metadata
            ['name' => 'trade.metadata.manage', 'display_name' => 'Manage Trade Metadata', 'group' => 'trade'],
            
            // User Management
            ['name' => 'user.manage', 'display_name' => 'Manage Users', 'group' => 'user'],
            
            // Audit Logs
            ['name' => 'audit.view', 'display_name' => 'View Audit Logs', 'group' => 'audit'],
            
            // Additional Permissions
            ['name' => 'warehouse.manage', 'display_name' => 'Manage Warehouses', 'group' => 'warehouse'],
            ['name' => 'product.manage', 'display_name' => 'Manage Products', 'group' => 'product'],
            ['name' => 'supplier.manage', 'display_name' => 'Manage Suppliers', 'group' => 'supplier'],
            ['name' => 'customer.manage', 'display_name' => 'Manage Customers', 'group' => 'customer'],
            ['name' => 'document.upload', 'display_name' => 'Upload Documents', 'group' => 'document'],
            ['name' => 'document.delete', 'display_name' => 'Delete Documents', 'group' => 'document'],
            ['name' => 'report.view', 'display_name' => 'View Reports', 'group' => 'report'],
            ['name' => 'report.export', 'display_name' => 'Export Reports', 'group' => 'report'],
            ['name' => 'role.manage', 'display_name' => 'Manage Roles', 'group' => 'role'],
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(
                ['name' => $perm['name']],
                $perm
            );
        }

        $this->command->info('Created ' . count($permissions) . ' permissions.');

        // =========================================
        // SYSTEM ROLES (Global, not company-specific)
        // =========================================

        // Admin Role - Full access
        $adminRole = Role::firstOrCreate(
            ['name' => 'admin', 'company_id' => null],
            [
                'display_name' => 'Administrator',
                'is_system' => true,
                'description' => 'Full system access',
            ]
        );
        $adminRole->syncPermissions(Permission::pluck('name')->toArray());

        // Manager Role - Operational access
        $managerRole = Role::firstOrCreate(
            ['name' => 'manager', 'company_id' => null],
            [
                'display_name' => 'Manager',
                'is_system' => true,
                'description' => 'Warehouse and transaction management',
            ]
        );
        $managerRole->syncPermissions([
            'inventory.view',
            'stock.in.create',
            'stock.out.create',
            'stock.adjustment',
            'transaction.approve',
            'transaction.reject',
            'batch.manage',
            'finance.view',
            'sales.view',
            'sales.create',
            'sales.update',
            'invoice.view',
            'trade.metadata.manage',
            'warehouse.manage',
            'product.manage',
            'supplier.manage',
            'customer.manage',
            'document.upload',
            'report.view',
            'report.export',
            'audit.view',
        ]);

        // Staff Role - Limited access
        $staffRole = Role::firstOrCreate(
            ['name' => 'staff', 'company_id' => null],
            [
                'display_name' => 'Staff',
                'is_system' => true,
                'description' => 'Basic warehouse operations',
            ]
        );
        $staffRole->syncPermissions([
            'inventory.view',
            'stock.in.create',
            'stock.out.create',
            'batch.manage',
            'document.upload',
            'report.view',
            'sales.view',  // Can view SO for packing, but NOT invoice.view
        ]);

        // Viewer Role - Read-only
        $viewerRole = Role::firstOrCreate(
            ['name' => 'viewer', 'company_id' => null],
            [
                'display_name' => 'Viewer',
                'is_system' => true,
                'description' => 'Read-only access',
            ]
        );
        $viewerRole->syncPermissions([
            'inventory.view',
            'report.view',
        ]);

        $this->command->info('Created 4 system roles: admin, manager, staff, viewer');
    }
}
