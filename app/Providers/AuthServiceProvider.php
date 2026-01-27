<?php

namespace App\Providers;

use App\Models\Batch;
use App\Models\Claim;
use App\Models\Document;
use App\Models\ImportJob;
use App\Models\Payment;
use App\Models\Permission;
use App\Models\SalesOrder;
use App\Models\SalesReturn;
use App\Models\StockTake;
use App\Models\StockTransfer;
use App\Models\UomConversion;
use App\Models\Webhook;
use App\Policies\BatchPolicy;
use App\Policies\ClaimPolicy;
use App\Policies\DocumentPolicy;
use App\Policies\ImportJobPolicy;
use App\Policies\PaymentPolicy;
use App\Policies\SalesOrderPolicy;
use App\Policies\SalesReturnPolicy;
use App\Policies\StockTakePolicy;
use App\Policies\StockTransferPolicy;
use App\Policies\UomConversionPolicy;
use App\Policies\WebhookPolicy;
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
        Claim::class => ClaimPolicy::class,
        Document::class => DocumentPolicy::class,
        ImportJob::class => ImportJobPolicy::class,
        Payment::class => PaymentPolicy::class,
        SalesOrder::class => SalesOrderPolicy::class,
        SalesReturn::class => SalesReturnPolicy::class,
        StockTake::class => StockTakePolicy::class,
        StockTransfer::class => StockTransferPolicy::class,
        UomConversion::class => UomConversionPolicy::class,
        Webhook::class => WebhookPolicy::class,
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
            
            // Sales & Invoice (Segregation of Duties)
            'sales.view',        // View sales orders (warehouse staff, sales team)
            'sales.create',      // Create sales orders
            'sales.update',      // Update sales orders
            'sales.delete',      // Delete sales orders (admin/manager)
            'invoice.view',      // View invoices with prices (finance only)
            
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
