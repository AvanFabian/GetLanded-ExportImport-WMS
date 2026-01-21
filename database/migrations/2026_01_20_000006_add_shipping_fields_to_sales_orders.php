<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add shipping and logistics fields to sales_orders.
     */
    public function up(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            // Incoterms
            $table->string('incoterms', 3)->nullable()->after('notes');
            
            // Shipping container details
            $table->string('container_number', 20)->nullable()->after('incoterms');
            $table->string('seal_number', 30)->nullable()->after('container_number');
            $table->string('vessel_name', 100)->nullable()->after('seal_number');
            $table->string('voyage_number', 30)->nullable()->after('vessel_name');
            
            // Port information
            $table->string('port_of_loading', 100)->nullable()->after('voyage_number');
            $table->string('port_of_discharge', 100)->nullable()->after('port_of_loading');
            
            // Dates
            $table->date('estimated_departure')->nullable()->after('port_of_discharge');
            $table->date('estimated_arrival')->nullable()->after('estimated_departure');
            $table->date('actual_departure')->nullable()->after('estimated_arrival');
            $table->date('actual_arrival')->nullable()->after('actual_departure');
            
            // Consignee
            $table->string('consignee_name', 200)->nullable()->after('actual_arrival');
            $table->text('consignee_address')->nullable()->after('consignee_name');
            $table->string('notify_party', 200)->nullable()->after('consignee_address');
            
            // Indexes
            $table->index('container_number');
            $table->index('incoterms');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropColumn([
                'incoterms', 'container_number', 'seal_number', 'vessel_name',
                'voyage_number', 'port_of_loading', 'port_of_discharge',
                'estimated_departure', 'estimated_arrival', 'actual_departure',
                'actual_arrival', 'consignee_name', 'consignee_address', 'notify_party'
            ]);
        });
    }
};
