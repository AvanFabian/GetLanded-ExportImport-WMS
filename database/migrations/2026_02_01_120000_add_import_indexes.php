<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add indexes for import performance and barcode scanning lookups.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Categories - Indexed by name for Import lookups
        Schema::table('categories', function (Blueprint $table) {
            // Check if index exists to prevent duplication using native Laravel method
            // We check both the conventional name and the explicit column check to be safe
            if (! Schema::hasIndex('categories', 'categories_name_index') && ! Schema::hasIndex('categories', ['name'])) {
                 $table->index('name');
            }
        });

        // Products - Ensure code (SKU) is indexed (usually unique, but good to verify)
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasIndex('products', 'idx_products_code')) {
                $table->index('code', 'idx_products_code'); // SKU lookup
            }
            
            // If barcode field exists, index it too
            if (Schema::hasColumn('products', 'barcode') && ! Schema::hasIndex('products', 'idx_products_barcode')) {
                $table->index('barcode', 'idx_products_barcode');
            }
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
             $table->dropIndex(['name']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('idx_products_code');
            if (Schema::hasIndex('products', 'idx_products_barcode')) {
                $table->dropIndex('idx_products_barcode');
            }
        });
    }
};
