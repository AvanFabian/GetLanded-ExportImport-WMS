<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Language Switcher
Route::get('/lang/{locale}', [App\Http\Controllers\LocaleController::class, 'switch'])->name('lang.switch');

Route::get('/', function () {
    return view('welcome');
});

// Legal Pages
Route::get('/terms', fn() => view('legal.terms'))->name('terms');
Route::get('/privacy', fn() => view('legal.privacy'))->name('privacy');

Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

    // Knowledge Base & FAQ Routes
    Route::prefix('help')->name('help.')->group(function () {
        Route::get('/', [App\Http\Controllers\HelpController::class, 'index'])->name('index');
        Route::get('/faq', [App\Http\Controllers\HelpController::class, 'faq'])->name('faq');
        Route::get('/article/{slug}', [App\Http\Controllers\HelpController::class, 'article'])->name('article');
    });

Route::middleware(['auth', 'throttle:web'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy')->middleware('throttle:sensitive');

    // API endpoints for AJAX
    Route::get('/api/products', [App\Http\Controllers\ProductController::class, 'getAll'])->name('api.products');
    Route::get('/alerts', [App\Http\Controllers\Api\AlertController::class, 'index'])->name('alerts.index');

    // Master data
    Route::resource('categories', App\Http\Controllers\CategoryController::class);

    // Products
    Route::post('products/print-labels', [App\Http\Controllers\ProductController::class, 'printLabels'])->name('products.print-labels');
    Route::delete('products/bulk-destroy', [App\Http\Controllers\ProductController::class, 'bulkDestroy'])->name('products.bulk-destroy');
    Route::post('products/import', [App\Http\Controllers\ProductController::class, 'import'])->name('products.import');
    Route::get('products/export', [App\Http\Controllers\ProductController::class, 'export'])->name('products.export');
    Route::get('products/label/{product}', [App\Http\Controllers\ProductController::class, 'printLabel'])->name('products.label');
    Route::resource('products', App\Http\Controllers\ProductController::class);

    // Product Variants (nested routes)
    Route::resource('products.variants', App\Http\Controllers\ProductVariantController::class)
        ->except(['show'])
        ->shallow();

    // Suppliers
    Route::post('suppliers/import', [App\Http\Controllers\SupplierController::class, 'import'])->name('suppliers.import');
    Route::delete('suppliers/bulk-destroy', [App\Http\Controllers\SupplierController::class, 'bulkDestroy'])->name('suppliers.bulk-destroy');
    Route::resource('suppliers', App\Http\Controllers\SupplierController::class);

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

    // Purchasing Section
    Route::resource('purchase-orders', App\Http\Controllers\PurchaseOrderController::class);
    Route::resource('inbound-shipments', App\Http\Controllers\InboundShipmentController::class); // New Module
    Route::post('inbound-shipments/{inboundShipment}/expenses', [App\Http\Controllers\InboundShipmentController::class, 'storeExpense'])->name('inbound-shipments.expenses.store');
    Route::post('inbound-shipments/{inboundShipment}/documents', [App\Http\Controllers\InboundShipmentController::class, 'storeDocument'])->name('inbound-shipments.documents.store');
    Route::post('inbound-shipments/{inboundShipment}/receive', [App\Http\Controllers\InboundShipmentController::class, 'receive'])->name('inbound-shipments.receive');
    Route::resource('stock-ins', App\Http\Controllers\StockInController::class)->except(['edit', 'update']);
    Route::post('purchase-orders/{purchaseOrder}/approve', [App\Http\Controllers\PurchaseOrderController::class, 'approve'])->name('purchase-orders.approve');
    Route::post('purchase-orders/{purchaseOrder}/reject', [App\Http\Controllers\PurchaseOrderController::class, 'reject'])->name('purchase-orders.reject');
    Route::get('purchase-orders/{purchaseOrder}/receive', [App\Http\Controllers\PurchaseOrderController::class, 'receive'])->name('purchase-orders.receive');
    Route::post('purchase-orders/{purchaseOrder}/receive', [App\Http\Controllers\PurchaseOrderController::class, 'processReceive'])->name('purchase-orders.process-receive');

    // Sales Management
    Route::resource('customers', App\Http\Controllers\CustomerController::class);
    Route::post('customers/import', [App\Http\Controllers\CustomerController::class, 'import'])->name('customers.import');
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

    // Payments & AR
    Route::get('payments/aging', [App\Http\Controllers\PaymentController::class, 'aging'])->name('payments.aging');
    Route::resource('payments', App\Http\Controllers\PaymentController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('payments/{payment}/allocate', [App\Http\Controllers\PaymentController::class, 'allocate'])->name('payments.allocate');

    // Sales Returns
    Route::resource('sales-returns', App\Http\Controllers\SalesReturnController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('sales-returns/{salesReturn}/approve', [App\Http\Controllers\SalesReturnController::class, 'approve'])->name('sales-returns.approve');
    Route::post('sales-returns/{salesReturn}/process', [App\Http\Controllers\SalesReturnController::class, 'process'])->name('sales-returns.process');

    // Claims
    Route::resource('claims', App\Http\Controllers\ClaimController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('claims/{claim}/upload-evidence', [App\Http\Controllers\ClaimController::class, 'uploadEvidence'])->name('claims.upload-evidence');
    Route::post('claims/{claim}/submit', [App\Http\Controllers\ClaimController::class, 'submit'])->name('claims.submit');
    Route::post('claims/{claim}/settle', [App\Http\Controllers\ClaimController::class, 'settle'])->name('claims.settle');
    Route::post('claims/{claim}/reject', [App\Http\Controllers\ClaimController::class, 'reject'])->name('claims.reject');

    // Supplier Payments
    Route::resource('supplier-payments', App\Http\Controllers\SupplierPaymentController::class)->only(['index', 'create', 'store', 'show']);
    
    // Payment Reconciliation
    Route::get('reconciliation', [App\Http\Controllers\ReconciliationController::class, 'index'])->name('reconciliation.index');
    Route::patch('reconciliation/{payment}', [App\Http\Controllers\ReconciliationController::class, 'reconcile'])->name('reconciliation.update');
    Route::delete('reconciliation/{payment}', [App\Http\Controllers\ReconciliationController::class, 'unreconcile'])->name('reconciliation.destroy');

    // FTA Schemes
    Route::resource('fta-schemes', App\Http\Controllers\FtaSchemeController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('fta-schemes/{ftaScheme}/rates', [App\Http\Controllers\FtaSchemeController::class, 'storeRate'])->name('fta-schemes.rates.store');
    Route::delete('fta-schemes/{ftaScheme}/rates/{rate}', [App\Http\Controllers\FtaSchemeController::class, 'destroyRate'])->name('fta-schemes.rates.destroy');

    // HS Codes
    Route::get('api/hs-codes/search', [App\Http\Controllers\HsCodeController::class, 'search'])->name('api.hs-codes.search');

    // Outbound Shipments
    Route::resource('outbound-shipments', App\Http\Controllers\OutboundShipmentController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('outbound-shipments/{outboundShipment}/status', [App\Http\Controllers\OutboundShipmentController::class, 'updateStatus'])->name('outbound-shipments.update-status');
    Route::get('outbound-shipments/{outboundShipment}/invoice-pdf', [App\Http\Controllers\OutboundShipmentController::class, 'downloadInvoice'])->name('outbound-shipments.invoice-pdf');
    Route::get('outbound-shipments/{outboundShipment}/packing-list-pdf', [App\Http\Controllers\OutboundShipmentController::class, 'downloadPackingList'])->name('outbound-shipments.packing-list-pdf');
    Route::post('outbound-shipments/{outboundShipment}/expenses', [App\Http\Controllers\OutboundShipmentController::class, 'addExpense'])->name('outbound-shipments.add-expense');
    Route::delete('outbound-shipments/{outboundShipment}/expenses/{expense}', [App\Http\Controllers\OutboundShipmentController::class, 'removeExpense'])->name('outbound-shipments.remove-expense');

    // Container Management
    Route::get('containers', [App\Http\Controllers\ContainerController::class, 'index'])->name('containers.index');
    Route::get('containers/{container}', [App\Http\Controllers\ContainerController::class, 'show'])->name('containers.show');
    Route::post('containers/{container}/stuffing', [App\Http\Controllers\ContainerController::class, 'stuffing'])->name('containers.stuffing');
    Route::post('containers/{container}/seal', [App\Http\Controllers\ContainerController::class, 'seal'])->name('containers.seal');
    Route::delete('container-items/{containerItem}', [App\Http\Controllers\ContainerController::class, 'removeItem'])->name('container-items.destroy');

    // Customs & Compliance
    Route::resource('customs', App\Http\Controllers\CustomsDeclarationController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('customs/{custom}/status', [App\Http\Controllers\CustomsDeclarationController::class, 'updateStatus'])->name('customs.update-status');
    Route::get('customs-permits', [App\Http\Controllers\CustomsDeclarationController::class, 'permits'])->name('customs.permits');
    Route::post('customs-permits', [App\Http\Controllers\CustomsDeclarationController::class, 'storePermit'])->name('customs.store-permit');

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
    Route::get('reports/profit-loss', [App\Http\Controllers\ProfitLossController::class, 'index'])->name('reports.profit-loss');

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

    // Trash / Soft Deletes
    Route::get('trash', [App\Http\Controllers\TrashController::class, 'index'])->name('trash.index');
    Route::post('trash/{type}/{id}/restore', [App\Http\Controllers\TrashController::class, 'restore'])->name('trash.restore');

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

    // Phase M: Commercial Operations (ABANDONED/INCOMPLETE - Requires Inertia/Vue Rebuild)
    
    // Payments & AR
    // Route::resource('payments', App\Http\Controllers\PaymentController::class)->except(['edit', 'update', 'destroy']);
    // Route::post('payments/{payment}/allocate', [App\Http\Controllers\PaymentController::class, 'allocate'])->name('payments.allocate');
    // Route::get('payments/aging-dashboard', [App\Http\Controllers\PaymentController::class, 'agingDashboard'])->name('payments.aging');

    // Sales Returns
    // Route::resource('sales-returns', App\Http\Controllers\SalesReturnController::class)->except(['edit', 'update', 'destroy']);
    // Route::post('sales-returns/{salesReturn}/approve', [App\Http\Controllers\SalesReturnController::class, 'approve'])->name('sales-returns.approve');
    // Route::post('sales-returns/{salesReturn}/process', [App\Http\Controllers\SalesReturnController::class, 'process'])->name('sales-returns.process');

    // Stock Transfers
    // Route::resource('stock-transfers', App\Http\Controllers\StockTransferController::class)->except(['edit', 'update', 'destroy']);
    // Route::post('stock-transfers/{stockTransfer}/dispatch', [App\Http\Controllers\StockTransferController::class, 'dispatch'])->name('stock-transfers.dispatch');
    // Route::post('stock-transfers/{stockTransfer}/receive', [App\Http\Controllers\StockTransferController::class, 'receive'])->name('stock-transfers.receive');

    // Stock Takes (Blind Opname) (INERTIA - DEPRECATED)
    // Route::resource('stock-takes', App\Http\Controllers\StockTakeController::class)->except(['edit', 'update', 'destroy']);
    // Route::post('stock-takes/{stockTake}/update-counts', [App\Http\Controllers\StockTakeController::class, 'updateCounts'])->name('stock-takes.update-counts');
    // Route::post('stock-takes/{stockTake}/complete', [App\Http\Controllers\StockTakeController::class, 'complete'])->name('stock-takes.complete');
    // Route::get('stock-takes/{stockTake}/variance-report', [App\Http\Controllers\StockTakeController::class, 'varianceReport'])->name('stock-takes.variance-report');

    // Claims
    // Route::resource('claims', App\Http\Controllers\ClaimController::class)->except(['edit', 'update', 'destroy']);
    // Route::post('claims/{claim}/upload-evidence', [App\Http\Controllers\ClaimController::class, 'uploadEvidence'])->name('claims.upload-evidence');
    // Route::post('claims/{claim}/submit', [App\Http\Controllers\ClaimController::class, 'submit'])->name('claims.submit');
    // Route::post('claims/{claim}/settle', [App\Http\Controllers\ClaimController::class, 'settle'])->name('claims.settle');

    // Operations
    Route::get('sales-orders/{order}/picking-list', [App\Http\Controllers\OperationsController::class, 'pickingList'])->name('sales-orders.picking-list');
    Route::post('sales-orders/{order}/confirm-picking', [App\Http\Controllers\OperationsController::class, 'confirmPicking'])->name('sales-orders.confirm-picking');
    Route::post('batches/{batch}/split', [App\Http\Controllers\OperationsController::class, 'splitBatch'])->name('batches.split');
    Route::post('batches/{batch}/quarantine', [App\Http\Controllers\OperationsController::class, 'quarantineBatch'])->name('batches.quarantine');
    Route::post('batches/{batch}/release', [App\Http\Controllers\OperationsController::class, 'releaseBatch'])->name('batches.release');
    Route::get('batches/{batch}/traceability', [App\Http\Controllers\OperationsController::class, 'batchTraceability'])->name('batches.traceability');

    // Global Search
    Route::get('api/search', [App\Http\Controllers\GlobalSearchController::class, 'search'])->name('api.search');
    Route::get('api/deep-search', [App\Http\Controllers\GlobalSearchController::class, 'deepSearch'])->name('api.deep-search');

    // Active Background Jobs (for floating progress bar)
    Route::get('api/active-jobs', [App\Http\Controllers\ImportController::class, 'activeJobs'])->name('api.active-jobs');

    // Reports (Phase M)
    Route::get('reports/inventory-aging', [App\Http\Controllers\GlobalSearchController::class, 'agingReport'])->name('reports.inventory-aging');
    Route::get('reports/cbm', [App\Http\Controllers\GlobalSearchController::class, 'cbmReport'])->name('reports.cbm');
    Route::get('reports/stock-reservation', [App\Http\Controllers\GlobalSearchController::class, 'reservationReport'])->name('reports.stock-reservation');

    // Settings (Phase M)
    Route::middleware('admin')->group(function () {
        // Webhooks
        Route::get('settings/webhooks', [App\Http\Controllers\WebhookController::class, 'index'])->name('webhooks.index');
        Route::post('settings/webhooks', [App\Http\Controllers\WebhookController::class, 'store'])->name('webhooks.store');
        Route::put('settings/webhooks/{webhook}', [App\Http\Controllers\WebhookController::class, 'update'])->name('webhooks.update');
        Route::delete('settings/webhooks/{webhook}', [App\Http\Controllers\WebhookController::class, 'destroy'])->name('webhooks.destroy');
        Route::post('settings/webhooks/{webhook}/test', [App\Http\Controllers\WebhookController::class, 'test'])->name('webhooks.test');
        Route::get('settings/webhooks/{webhook}/logs', [App\Http\Controllers\WebhookController::class, 'logs'])->name('webhooks.logs');

        // Bulk Import
        Route::get('imports', [App\Http\Controllers\ImportController::class, 'index'])->name('imports.index');
        Route::get('imports/create', [App\Http\Controllers\ImportController::class, 'create'])->name('imports.create');
        Route::post('imports/upload', [App\Http\Controllers\ImportController::class, 'upload'])->name('imports.upload');
        Route::get('imports/{job}/mapping', [App\Http\Controllers\ImportController::class, 'mapping'])->name('imports.mapping');
        Route::post('imports/{job}/confirm-mapping', [App\Http\Controllers\ImportController::class, 'confirmMapping'])->name('imports.confirm-mapping');
        Route::get('imports/{job}', [App\Http\Controllers\ImportController::class, 'show'])->name('imports.show');
        Route::get('imports/{job}/progress', [App\Http\Controllers\ImportController::class, 'progress'])->name('imports.progress');
        Route::get('imports/{job}/errors', [App\Http\Controllers\ImportController::class, 'errors'])->name('imports.errors');
    });

    // Phase M.Flex: Business Rules & Policies (Admin only)
    Route::middleware('admin')->prefix('settings')->group(function () {
        // Business Rules / Company Policies
        Route::get('business-rules', [App\Http\Controllers\CompanyPoliciesController::class, 'index'])->name('settings.business-rules');
        Route::put('business-rules', [App\Http\Controllers\CompanyPoliciesController::class, 'update'])->name('settings.business-rules.update');
        Route::get('api/policies', [App\Http\Controllers\CompanyPoliciesController::class, 'current'])->name('api.policies');

        // UoM Conversions
        Route::get('uom-conversions', [App\Http\Controllers\UomConversionController::class, 'index'])->name('settings.uom-conversions');
        Route::post('uom-conversions', [App\Http\Controllers\UomConversionController::class, 'store'])->name('settings.uom-conversions.store');
        Route::put('uom-conversions/{conversion}', [App\Http\Controllers\UomConversionController::class, 'update'])->name('settings.uom-conversions.update');
        Route::delete('uom-conversions/{conversion}', [App\Http\Controllers\UomConversionController::class, 'destroy'])->name('settings.uom-conversions.destroy');
        Route::post('uom-conversions/add-common', [App\Http\Controllers\UomConversionController::class, 'addCommon'])->name('settings.uom-conversions.add-common');
    });

    // UoM Conversion API (accessible to all authenticated users)
    Route::post('api/uom/convert', [App\Http\Controllers\UomConversionController::class, 'convert'])->name('api.uom.convert');
    Route::get('api/uom/units', [App\Http\Controllers\UomConversionController::class, 'availableUnits'])->name('api.uom.units');

    // Owner-only Reports
    Route::middleware('owner')->group(function () {
        Route::get('reports/business-health', [App\Http\Controllers\GlobalSearchController::class, 'businessHealth'])->name('reports.business-health');
        Route::get('settings/security-logs', [App\Http\Controllers\OperationsController::class, 'securityLogs'])->name('settings.security-logs');
    });

    // PDF Document Downloads
    Route::prefix('pdf')->name('pdf.')->group(function () {
        Route::get('sales-orders/{salesOrder}/invoice', [App\Http\Controllers\PdfController::class, 'invoice'])->name('invoice');
        Route::get('sales-orders/{salesOrder}/packing-list', [App\Http\Controllers\PdfController::class, 'packingList'])->name('packing-list');
        Route::get('stock-outs/{stockOut}/packing-list', [App\Http\Controllers\PdfController::class, 'stockOutPackingList'])->name('stock-out.packing-list');
        Route::get('stock-ins/{stockIn}/receipt', [App\Http\Controllers\PdfController::class, 'warehouseReceipt'])->name('receipt');
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

// Scanner Verification Route
Route::get('/scanner-test', function () {
    return view('scanner-test');
})->middleware(['auth', 'verified'])->name('scanner.test');

require __DIR__ . '/test_integrations.php';
require __DIR__ . '/auth.php';

// Fallback route for 404 errors
Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});
