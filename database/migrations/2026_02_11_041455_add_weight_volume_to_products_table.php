<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add weight and volume fields for Landed Cost allocation by weight/volume.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('net_weight', 10, 4)->nullable()->after('weight_unit')
                  ->comment('Weight per unit in KG — used for freight cost allocation');
            $table->decimal('cbm_volume', 10, 6)->nullable()->after('net_weight')
                  ->comment('Volume per unit in CBM — used for container cost allocation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['net_weight', 'cbm_volume']);
        });
    }
};
