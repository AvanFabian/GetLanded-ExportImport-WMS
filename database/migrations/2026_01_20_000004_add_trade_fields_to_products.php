<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add international trade compliance fields to products.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('hs_code', 10)->nullable()->after('batch_method')->comment('Harmonized System code');
            $table->string('origin_country', 2)->nullable()->after('hs_code')->comment('ISO 2-letter country code');
            $table->enum('weight_unit', ['KG', 'MT', 'LB'])->default('KG')->after('origin_country');
            $table->enum('dimension_unit', ['CM', 'IN', 'M'])->default('CM')->after('weight_unit');
            
            $table->index('hs_code');
            $table->index('origin_country');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['hs_code', 'origin_country', 'weight_unit', 'dimension_unit']);
        });
    }
};
