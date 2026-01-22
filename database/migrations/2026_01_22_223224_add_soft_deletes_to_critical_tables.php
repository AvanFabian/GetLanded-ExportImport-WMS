<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tables = ['sales_orders', 'batches', 'customers', 'suppliers', 'products'];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName) && !Schema::hasColumn($tableName, 'deleted_at')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->softDeletes();
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Only drop from tables where we supposedly added them.
        // However, standard rollback might drop from products too if we aren't careful.
        // Ideally we only drop if we added it. But verifying if WE added it is hard.
        // Given this is a 'add_soft_deletes' migration, rolling it back implies removing them.
        // But Products had it from creation. We shouldn't remove it from Products.
        
        $tables = ['sales_orders', 'batches', 'customers']; // Exclude Products/Suppliers if they had it?
        
        // Actually, safest is to check if it exists and remove? But that destroys data.
        // I will exclude 'products' from rollback as it was in original schema.
        
        foreach ($tables as $tableName) {
             if (Schema::hasColumn($tableName, 'deleted_at')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropSoftDeletes();
                });
            }
        }
    }
};
