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
        Schema::create('inbound_shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            
            // Core Identity
            $table->string('shipment_number')->unique(); // SHP-2024-001
            $table->string('reference_number')->nullable(); // Container Number, AWB, BOL
            
            // Logistics
            $table->string('carrier_name')->nullable(); // Maersk, DHL
            $table->string('vessel_flight_number')->nullable(); // MAERSK ALABAMA V.102
            $table->string('origin_port')->nullable(); 
            $table->string('destination_port')->nullable();
            
            // Dates
            $table->date('etd')->nullable(); // Estimated Time of Departure
            $table->date('eta')->nullable(); // Estimated Time of Arrival
            $table->date('actual_arrival_date')->nullable();
            
            // Status Management
            $table->enum('status', [
                'planned',      // Draft phase
                'booked',       // Space confirm
                'on_water',     // In Transit
                'customs',      // Port / Clearance
                'arrived',      // At Warehouse
                'received',     // Stock count done
                'cancelled'
            ])->default('planned');
            
            $table->text('notes')->nullable();
            
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        // Add linkage to Purchase Orders
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->foreignId('inbound_shipment_id')
                  ->nullable()
                  ->after('supplier_id')
                  ->constrained('inbound_shipments')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropForeign(['inbound_shipment_id']);
            $table->dropColumn('inbound_shipment_id');
        });

        Schema::dropIfExists('inbound_shipments');
    }
};
