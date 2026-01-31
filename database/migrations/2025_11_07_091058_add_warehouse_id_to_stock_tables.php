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
        // Add warehouse_id to stock_ins table
        Schema::table('stock_ins', function (Blueprint $table) {
            $table->foreignId('warehouse_id')->nullable()->after('id')->constrained('warehouses')->onDelete('restrict');
            $table->index('warehouse_id');
        });

        // Add warehouse_id to stock_outs table
        Schema::table('stock_outs', function (Blueprint $table) {
            $table->foreignId('warehouse_id')->nullable()->after('id')->constrained('warehouses')->onDelete('restrict');
            $table->index('warehouse_id');
        });

        // Add warehouse_id to stock_opnames table
        Schema::table('stock_opnames', function (Blueprint $table) {
            $table->foreignId('warehouse_id')->nullable()->after('id')->constrained('warehouses')->onDelete('restrict');
            $table->index('warehouse_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_ins', function (Blueprint $table) {
            // $table->dropForeign(['warehouse_id']);
            // $table->dropColumn('warehouse_id');
        });

        Schema::table('stock_outs', function (Blueprint $table) {
            $table->dropForeign(['warehouse_id']);
            $table->dropColumn('warehouse_id');
        });

        Schema::table('stock_opnames', function (Blueprint $table) {
            $table->dropForeign(['warehouse_id']);
            $table->dropColumn('warehouse_id');
        });
    }
};
