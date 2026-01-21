<?php

namespace App\Providers;

use App\Models\Batch;
use App\Models\Document;
use App\Models\Permission;
use App\Policies\BatchPolicy;
use App\Policies\DocumentPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Batch::class => BatchPolicy::class,
        Document::class => DocumentPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Define gates based on permissions
        $this->definePermissionGates();
    }

    /**
     * Define gates for all permissions.
     * 
     * Usage: Gate::allows('stock.in.create')
     */
    protected function definePermissionGates(): void
    {
        // Define a gate for each permission
        Gate::before(function ($user, $ability) {
            // Super admin bypass (optional - remove if you want strict permission checks)
            // if ($user->hasRole('admin')) {
            //     return true;
            // }
            
            // Check if user has the permission
            if ($user->hasPermissionTo($ability)) {
                return true;
            }
            
            return null; // Continue to other gates/policies
        });

        // Explicitly define gates for documentation and IDE support
        $permissionGates = [
            // Inventory
            'inventory.view',
            
            // Stock Operations
            'stock.in.create',
            'stock.out.create',
            'stock.adjustment',
            
            // Transaction Workflow
            'transaction.approve',
            'transaction.reject',
            
            // Batch Management
            'batch.manage',
            
            // Finance
            'finance.view',
            
            // Currency
            'currency.manage',
            
            // Trade Metadata
            'trade.metadata.manage',
            
            // User Management
            'user.manage',
            
            // Audit Logs
            'audit.view',
            
            // Additional
            'warehouse.manage',
            'product.manage',
            'supplier.manage',
            'customer.manage',
            'document.upload',
            'document.delete',
            'report.view',
            'report.export',
            'role.manage',
        ];

        foreach ($permissionGates as $permission) {
            Gate::define($permission, function ($user) use ($permission) {
                return $user->hasPermissionTo($permission);
            });
        }
    }
}
