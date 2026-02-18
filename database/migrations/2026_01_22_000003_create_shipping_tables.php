<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Outbound Shipments (export shipment tracking)
        Schema::create('outbound_shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sales_order_id')->constrained()->cascadeOnDelete();
            $table->string('shipment_number');
            $table->date('shipment_date');
            $table->date('estimated_arrival')->nullable();
            $table->date('actual_arrival')->nullable();
            $table->string('carrier_name')->nullable();
            $table->string('vessel_name')->nullable();
            $table->string('voyage_number')->nullable();
            $table->string('bill_of_lading')->nullable();
            $table->string('booking_number')->nullable();
            $table->string('port_of_loading')->nullable();
            $table->string('port_of_discharge')->nullable();
            $table->string('destination_country')->nullable();
            $table->string('incoterm')->nullable(); // FOB, CIF, etc.
            $table->decimal('freight_cost', 15, 2)->default(0);
            $table->decimal('insurance_cost', 15, 2)->default(0);
            $table->string('currency_code', 3)->default('USD');
            $table->string('status')->default('draft'); // draft, booked, shipped, in_transit, arrived, delivered
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Containers
        Schema::create('containers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('outbound_shipment_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('container_number');
            $table->string('container_type'); // 20ft, 40ft, 40ft_hc, reefer_20ft, reefer_40ft
            $table->decimal('max_weight_kg', 10, 2)->default(0);
            $table->decimal('max_volume_cbm', 10, 4)->default(0);
            $table->string('seal_number')->nullable();
            $table->string('status')->default('empty'); // empty, loading, sealed, shipped, arrived, returned
            $table->decimal('used_weight_kg', 10, 2)->default(0);
            $table->decimal('used_volume_cbm', 10, 4)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Container items (stuffing plan)
        Schema::create('container_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('container_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained();
            $table->foreignId('batch_id')->nullable()->constrained();
            $table->integer('quantity');
            $table->decimal('weight_kg', 10, 2)->default(0);
            $table->decimal('volume_cbm', 10, 4)->default(0);
            $table->integer('carton_count')->default(0);
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('container_items');
        Schema::dropIfExists('containers');
        Schema::dropIfExists('outbound_shipments');
    }
};
