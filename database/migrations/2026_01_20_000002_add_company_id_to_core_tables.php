<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Add company_id to all core tables for multi-tenancy.
     * Creates default "AvanDigital" company and migrates existing data.
     */
    public function up(): void
    {
        // Step 1: Create default company
        $companyId = DB::table('companies')->insertGetId([
            'uuid' => \Illuminate\Support\Str::uuid()->toString(),
            'name' => 'AvanDigital',
            'code' => 'AVANDIGITAL',
            'base_currency_code' => 'IDR',
            'is_active' => true,
            'subscription_plan' => 'enterprise',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Step 2: Add company_id to users
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->index('company_id');
        });
        DB::table('users')->update(['company_id' => $companyId]);

        // Step 3: Add company_id to warehouses
        Schema::table('warehouses', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->index('company_id');
        });
        DB::table('warehouses')->update(['company_id' => $companyId]);

        // Step 4: Add company_id to categories
        Schema::table('categories', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->index('company_id');
        });
        DB::table('categories')->update(['company_id' => $companyId]);

        // Step 5: Add company_id to suppliers
        Schema::table('suppliers', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->index('company_id');
        });
        DB::table('suppliers')->update(['company_id' => $companyId]);

        // Step 6: Add company_id to products
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->index('company_id');
        });
        DB::table('products')->update(['company_id' => $companyId]);

        // Step 7: Add company_id to customers
        Schema::table('customers', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->index('company_id');
        });
        DB::table('customers')->update(['company_id' => $companyId]);

        // Step 8: Add company_id to batches
        Schema::table('batches', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->index('company_id');
        });
        DB::table('batches')->update(['company_id' => $companyId]);

        // Step 9: Add company_id to stock_ins
        Schema::table('stock_ins', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->index('company_id');
        });
        DB::table('stock_ins')->update(['company_id' => $companyId]);

        // Step 10: Add company_id to stock_outs
        Schema::table('stock_outs', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->index('company_id');
        });
        DB::table('stock_outs')->update(['company_id' => $companyId]);

        // Step 11: Add company_id to purchase_orders
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->index('company_id');
        });
        DB::table('purchase_orders')->update(['company_id' => $companyId]);

        // Step 12: Add company_id to sales_orders
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->index('company_id');
        });
        DB::table('sales_orders')->update(['company_id' => $companyId]);

        // Step 13: Add company_id to audit_logs
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->index('company_id');
        });
        DB::table('audit_logs')->update(['company_id' => $companyId]);

        // Step 14: Add company_id to stock_opnames
        Schema::table('stock_opnames', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->index('company_id');
        });
        DB::table('stock_opnames')->update(['company_id' => $companyId]);

        // Step 15: Add company_id to inter_warehouse_transfers
        Schema::table('inter_warehouse_transfers', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->index('company_id');
        });
        DB::table('inter_warehouse_transfers')->update(['company_id' => $companyId]);

        // Step 16: Add company_id to invoices
        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->index('company_id');
        });
        DB::table('invoices')->update(['company_id' => $companyId]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'users', 'warehouses', 'categories', 'suppliers', 'products',
            'customers', 'batches', 'stock_ins', 'stock_outs', 'purchase_orders',
            'sales_orders', 'audit_logs', 'stock_opnames', 'inter_warehouse_transfers',
            'invoices'
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropForeign(['company_id']);
                $table->dropColumn('company_id');
            });
        }

        DB::table('companies')->where('code', 'AVANDIGITAL')->delete();
    }
};
