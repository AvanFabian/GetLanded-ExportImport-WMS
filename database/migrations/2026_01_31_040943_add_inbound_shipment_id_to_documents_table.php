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
        Schema::table('documents', function (Blueprint $table) {
            $table->foreignId('inbound_shipment_id')
                  ->nullable()
                  ->after('purchase_order_id')
                  ->constrained('inbound_shipments')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropForeign(['inbound_shipment_id']);
            $table->dropColumn('inbound_shipment_id');
        });
    }
};
