<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add weight fields to batches for shipping calculations.
     */
    public function up(): void
    {
        Schema::table('batches', function (Blueprint $table) {
            $table->decimal('gross_weight', 12, 3)->nullable()->after('notes')->comment('Total weight including packaging');
            $table->decimal('net_weight', 12, 3)->nullable()->after('gross_weight')->comment('Weight of product only');
            $table->decimal('tare_weight', 12, 3)->nullable()->after('net_weight')->comment('Weight of packaging');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('batches', function (Blueprint $table) {
            $table->dropColumn(['gross_weight', 'net_weight', 'tare_weight']);
        });
    }
};
