<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add performance indexes for dashboard and reporting queries.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Sales Orders - for date range and status queries
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->index(['order_date', 'status'], 'idx_so_date_status');
            $table->index(['order_date', 'payment_status'], 'idx_so_date_payment');
            $table->index(['customer_id', 'order_date'], 'idx_so_customer_date');
        });

        // Batches - for expiry and aging queries
        Schema::table('batches', function (Blueprint $table) {
            $table->index(['expiry_date', 'status'], 'idx_batch_expiry_status');
            $table->index(['created_at', 'status'], 'idx_batch_created_status');
            $table->index(['product_id', 'status'], 'idx_batch_product_status');
        });

        // Stock Locations - for inventory queries
        Schema::table('stock_locations', function (Blueprint $table) {
            $table->index(['bin_id', 'quantity'], 'idx_stock_loc_bin_qty');
            $table->index(['batch_id', 'quantity'], 'idx_stock_loc_batch_qty');
        });

        // Sales Order Items - for product analytics
        Schema::table('sales_order_items', function (Blueprint $table) {
            $table->index(['product_id', 'sales_order_id'], 'idx_soi_product_order');
        });

        // Stock Ins - for receiving analytics
        Schema::table('stock_ins', function (Blueprint $table) {
            $table->index(['date', 'status'], 'idx_stockin_date_status');
            $table->index(['supplier_id', 'date'], 'idx_stockin_supplier_date');
        });

        // Audit Logs - for activity queries
        if (Schema::hasTable('audit_logs')) {
            Schema::table('audit_logs', function (Blueprint $table) {
                $table->index(['created_at'], 'idx_audit_created');
                $table->index(['user_id', 'created_at'], 'idx_audit_user_created');
            });
        }
    }

    public function down(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropIndex('idx_so_date_status');
            $table->dropIndex('idx_so_date_payment');
            $table->dropIndex('idx_so_customer_date');
        });

        Schema::table('batches', function (Blueprint $table) {
            $table->dropIndex('idx_batch_expiry_status');
            $table->dropIndex('idx_batch_created_status');
            $table->dropIndex('idx_batch_product_status');
        });

        Schema::table('stock_locations', function (Blueprint $table) {
            $table->dropIndex('idx_stock_loc_bin_qty');
            $table->dropIndex('idx_stock_loc_batch_qty');
        });

        Schema::table('sales_order_items', function (Blueprint $table) {
            $table->dropIndex('idx_soi_product_order');
        });

        Schema::table('stock_ins', function (Blueprint $table) {
            $table->dropIndex('idx_stockin_date_status');
            $table->dropIndex('idx_stockin_supplier_date');
        });

        if (Schema::hasTable('audit_logs')) {
            Schema::table('audit_logs', function (Blueprint $table) {
                $table->dropIndex('idx_audit_created');
                $table->dropIndex('idx_audit_user_created');
            });
        }
    }
};
