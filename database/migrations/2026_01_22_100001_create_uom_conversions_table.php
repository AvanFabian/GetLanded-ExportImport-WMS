<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create UoM conversions table for dynamic unit conversions.
     */
    public function up(): void
    {
        Schema::create('uom_conversions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->cascadeOnDelete()
                ->comment('Null = global conversion for company');
            $table->string('from_unit', 20);
            $table->string('to_unit', 20);
            $table->decimal('conversion_factor', 20, 10)
                ->comment('e.g., 1 Bag = 50 KG means factor = 50');
            $table->boolean('is_default')->default(false)
                ->comment('Default display unit for this product');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Unique constraint per company+product+unit pair
            $table->unique(['company_id', 'product_id', 'from_unit', 'to_unit'], 'uom_conversion_unique');
            
            // Index for efficient lookups
            $table->index(['company_id', 'from_unit', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('uom_conversions');
    }
};
