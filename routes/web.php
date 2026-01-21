<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Language Switcher
Route::get('/locale/{locale}', [App\Http\Controllers\LocaleController::class, 'switch'])->name('locale.switch');

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'throttle:web'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy')->middleware('throttle:sensitive');

    // API endpoints for AJAX
    Route::get('/api/products', [App\Http\Controllers\ProductController::class, 'getAll'])->name('api.products');

    // Master data
    Route::resource('categories', App\Http\Controllers\CategoryController::class);
    Route::resource('suppliers', App\Http\Controllers\SupplierController::class);
    Route::resource('products', App\Http\Controllers\ProductController::class);
    Route::post('products/import', [App\Http\Controllers\ProductController::class, 'import'])->name('products.import');
    Route::get('products-export', [App\Http\Controllers\ProductController::class, 'export'])->name('products.export');

    // Product Variants (nested routes)
    Route::resource('products.variants', App\Http\Controllers\ProductVariantController::class)
        ->except(['show'])
        ->shallow();

    // Warehouses
    Route::resource('warehouses', App\Http\Controllers\WarehouseController::class);

    // Transactions
    Route::resource('stock-ins', App\Http\Controllers\StockInController::class)->except(['edit', 'update']);
    Route::resource('stock-outs', App\Http\Controllers\StockOutController::class)->except(['edit', 'update']);
    Route::get('products/{productId}/stock', [App\Http\Controllers\StockOutController::class, 'getProductStock'])->name('products.stock');
    Route::get('warehouses/{warehouseId}/products', [App\Http\Controllers\StockOutController::class, 'getWarehouseProducts'])->name('warehouses.products');

    // Barcode & QR Code
    Route::get('products/{product}/barcode', [App\Http\Controllers\BarcodeController::class, 'generateBarcode'])->name('products.barcode');
    Route::get('products/{product}/qrcode', [App\Http\Controllers\BarcodeController::class, 'generateQrCode'])->name('products.qrcode');
    Route::get('products/{product}/label', [App\Http\Controllers\BarcodeController::class, 'showLabel'])->name('products.label');
    Route::post('products/print-labels', [App\Http\Controllers\BarcodeController::class, 'printLabels'])->name('products.print-labels');
    Route::post('barcode/scan', [App\Http\Controllers\BarcodeController::class, 'scan'])->name('barcode.scan');

    // Inter-Warehouse Transfers
    Route::resource('transfers', App\Http\Controllers\InterWarehouseTransferController::class)->except(['edit', 'update']);
    Route::post('transfers/{transfer}/approve', [App\Http\Controllers\InterWarehouseTransferController::class, 'approve'])->name('transfers.approve');
    Route::post('transfers/{transfer}/reject', [App\Http\Controllers\InterWarehouseTransferController::class, 'reject'])->name('transfers.reject');
    Route::post('transfers/{transfer}/start-transit', [App\Http\Controllers\InterWarehouseTransferController::class, 'startTransit'])->name('transfers.start-transit');
    Route::post('transfers/{transfer}/complete', [App\Http\Controllers\InterWarehouseTransferController::class, 'complete'])->name('transfers.complete');

    // Stock Opname (Owner only)
    Route::middleware('owner')->group(function () {
        Route::resource('stock-opnames', App\Http\Controllers\StockOpnameController::class)->except(['edit', 'update', 'show']);
    });

    // Purchase Orders
    Route::resource('purchase-orders', App\Http\Controllers\PurchaseOrderController::class);
    Route::post('purchase-orders/{purchaseOrder}/submit', [App\Http\Controllers\PurchaseOrderController::class, 'submit'])->name('purchase-orders.submit');
    Route::post('purchase-orders/{purchaseOrder}/approve', [App\Http\Controllers\PurchaseOrderController::class, 'approve'])->name('purchase-orders.approve');
    Route::post('purchase-orders/{purchaseOrder}/reject', [App\Http\Controllers\PurchaseOrderController::class, 'reject'])->name('purchase-orders.reject');
    Route::get('purchase-orders/{purchaseOrder}/receive', [App\Http\Controllers\PurchaseOrderController::class, 'receive'])->name('purchase-orders.receive');
    Route::post('purchase-orders/{purchaseOrder}/receive', [App\Http\Controllers\PurchaseOrderController::class, 'processReceive'])->name('purchase-orders.process-receive');

    // Sales Management
    Route::resource('customers', App\Http\Controllers\CustomerController::class);
    Route::resource('sales-orders', App\Http\Controllers\SalesOrderController::class);
    Route::post('sales-orders/{salesOrder}/confirm', [App\Http\Controllers\SalesOrderController::class, 'confirm'])->name('sales-orders.confirm');
    Route::post('sales-orders/{salesOrder}/ship', [App\Http\Controllers\SalesOrderController::class, 'ship'])->name('sales-orders.ship');
    Route::post('sales-orders/{salesOrder}/deliver', [App\Http\Controllers\SalesOrderController::class, 'deliver'])->name('sales-orders.deliver');
    Route::post('sales-orders/{salesOrder}/cancel', [App\Http\Controllers\SalesOrderController::class, 'cancel'])->name('sales-orders.cancel');
    Route::get('sales-orders/{salesOrder}/generate-stock-out', [App\Http\Controllers\SalesOrderController::class, 'generateStockOut'])->name('sales-orders.generate-stock-out');
    Route::get('sales-orders/{salesOrder}/delivery-order', [App\Http\Controllers\SalesOrderController::class, 'deliveryOrder'])->name('sales-orders.delivery-order');

    Route::resource('invoices', App\Http\Controllers\InvoiceController::class);
    Route::post('invoices/{invoice}/record-payment', [App\Http\Controllers\InvoiceController::class, 'recordPayment'])->name('invoices.record-payment');
    Route::get('invoices/{invoice}/pdf', [App\Http\Controllers\InvoiceController::class, 'viewPdf'])->name('invoices.pdf');
    Route::get('invoices/{invoice}/download', [App\Http\Controllers\InvoiceController::class, 'downloadPdf'])->name('invoices.download');

    // Location Management (Zones, Racks, Bins)
    Route::resource('zones', App\Http\Controllers\WarehouseZoneController::class);
    Route::resource('racks', App\Http\Controllers\WarehouseRackController::class);
    Route::resource('bins', App\Http\Controllers\WarehouseBinController::class);
    Route::get('bins/{bin}/qrcode', [App\Http\Controllers\WarehouseBinController::class, 'generateQrCode'])->name('bins.qrcode');

    // Reports
    Route::get('reports', [App\Http\Controllers\ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/stock', [App\Http\Controllers\ReportController::class, 'stock'])->name('reports.stock');
    Route::get('reports/transactions', [App\Http\Controllers\ReportController::class, 'transactions'])->name('reports.transactions');
    Route::get('reports/inventory-value', [App\Http\Controllers\ReportController::class, 'inventoryValue'])->name('reports.inventory-value');
    Route::get('reports/stock-card', [App\Http\Controllers\ReportController::class, 'stockCard'])->name('reports.stock-card');

    // Batch Management (Phase B UI)
    Route::get('batches', [App\Http\Controllers\BatchController::class, 'index'])->name('batches.index');
    Route::get('batches/{batch}', [App\Http\Controllers\BatchController::class, 'show'])->name('batches.show');

    // Notifications
    Route::get('notifications', [App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications/mark-all-read', [App\Http\Controllers\NotificationController::class, 'markAllRead'])->name('notifications.mark-all-read');
    Route::post('notifications/{id}/mark-read', [App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.mark-as-read');

    // Company Settings (Owner/Admin only)
    Route::get('company/settings', [App\Http\Controllers\CompanySettingsController::class, 'index'])->name('company.settings');
    Route::put('company/settings', [App\Http\Controllers\CompanySettingsController::class, 'update'])->name('company.settings.update');
    Route::post('company/settings/remove-logo', [App\Http\Controllers\CompanySettingsController::class, 'removeLogo'])->name('company.settings.remove-logo');

    // Approval Center
    Route::middleware('permission:transaction.approve')->group(function () {
        Route::get('approvals', [App\Http\Controllers\ApprovalCenterController::class, 'index'])->name('approvals.index');
        Route::post('approvals/{type}/{id}/approve', [App\Http\Controllers\ApprovalCenterController::class, 'approve'])->name('approvals.approve');
        Route::post('approvals/{type}/{id}/reject', [App\Http\Controllers\ApprovalCenterController::class, 'reject'])->name('approvals.reject');
    });
    Route::get('api/approvals/counts', [App\Http\Controllers\ApprovalCenterController::class, 'counts'])->name('api.approvals.counts');

    // User Management (Admin only)
    Route::middleware('admin')->group(function () {
        Route::resource('users', App\Http\Controllers\UserController::class);

        // Settings (Admin only)
        Route::get('settings', [App\Http\Controllers\SettingsController::class, 'index'])->name('settings.index');
        Route::put('settings', [App\Http\Controllers\SettingsController::class, 'update'])->name('settings.update');

        // Currency Management (Admin only)
        Route::get('currencies', [App\Http\Controllers\CurrencyController::class, 'index'])->name('currencies.index');
        Route::post('currencies', [App\Http\Controllers\CurrencyController::class, 'store'])->name('currencies.store');
        Route::put('currencies/{currency}', [App\Http\Controllers\CurrencyController::class, 'update'])->name('currencies.update');
        Route::post('currencies/sync', [App\Http\Controllers\CurrencyController::class, 'syncRates'])->name('currencies.sync');
    });

    // Role Management (Permission-based)
    Route::middleware('permission:role.manage')->group(function () {
        Route::resource('roles', App\Http\Controllers\RoleController::class);
    });
});

// Super-Admin Platform Management (outside tenant scope)
Route::prefix('admin/platform')->middleware(['auth', 'super-admin'])->name('platform.')->group(function () {
    Route::get('companies', [App\Http\Controllers\SuperAdmin\CompanyController::class, 'index'])->name('companies.index');
    Route::get('companies/create', [App\Http\Controllers\SuperAdmin\CompanyController::class, 'create'])->name('companies.create');
    Route::post('companies', [App\Http\Controllers\SuperAdmin\CompanyController::class, 'store'])->name('companies.store');
    Route::get('companies/{company}', [App\Http\Controllers\SuperAdmin\CompanyController::class, 'show'])->name('companies.show');
    Route::post('companies/{company}/toggle-active', [App\Http\Controllers\SuperAdmin\CompanyController::class, 'toggleActive'])->name('companies.toggle-active');
});

// Subscription Suspended Page (accessible without subscription check)
Route::get('/subscription/suspended', function () {
    return view('subscription.suspended');
})->name('subscription.suspended')->middleware('auth');

require __DIR__ . '/auth.php';

// Fallback route for 404 errors
Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});
