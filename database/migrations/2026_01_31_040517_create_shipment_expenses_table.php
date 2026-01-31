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
        Schema::create('shipment_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inbound_shipment_id')->constrained()->cascadeOnDelete();
            
            $table->string('name'); // Freight, Insurance, Duty
            $table->decimal('amount', 15, 2);
            $table->string('currency_code', 3)->default('IDR');
            
            // Allocation Strategy
            $table->enum('allocation_method', [
                'value',    // Distribute by Price (Expensive items take more cost)
                'weight',   // Distribute by Weight (Heavy items take more cost)
                'volume',   // Distribute by Volume (Bulky items take more cost)
                'quantity'  // Distribute Evenly (Simple)
            ])->default('value');
            
            $table->text('notes')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });

        // Add Landed Cost columns to Stock In (The Result)
        Schema::table('stock_in_details', function (Blueprint $table) {
            $table->decimal('allocated_landed_cost', 15, 2)->default(0)->after('purchase_price'); 
            // final_cost = purchase_price + allocated_landed_cost
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_in_details', function (Blueprint $table) {
            $table->dropColumn('allocated_landed_cost');
        });

        Schema::dropIfExists('shipment_expenses');
    }
};
