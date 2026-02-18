<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shipment_expenses', function (Blueprint $table) {
            // Make inbound_shipment_id nullable (was required before)
            $table->foreignId('outbound_shipment_id')
                ->nullable()
                ->after('inbound_shipment_id')
                ->constrained()
                ->cascadeOnDelete();
        });

        // Make inbound_shipment_id nullable so expenses can belong to either type
        Schema::table('shipment_expenses', function (Blueprint $table) {
            $table->unsignedBigInteger('inbound_shipment_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('shipment_expenses', function (Blueprint $table) {
            $table->dropForeign(['outbound_shipment_id']);
            $table->dropColumn('outbound_shipment_id');
        });
    }
};
