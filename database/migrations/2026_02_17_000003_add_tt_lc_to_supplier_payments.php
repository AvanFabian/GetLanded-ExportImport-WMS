<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('supplier_payments', function (Blueprint $table) {
            $table->string('payment_method', 20)->default('bank_transfer')->after('payment_status');
            $table->string('currency_code', 3)->default('IDR')->after('payment_method');
            $table->string('bank_reference', 100)->nullable()->after('currency_code');
            $table->string('lc_number', 100)->nullable()->after('bank_reference');
            $table->date('lc_expiry_date')->nullable()->after('lc_number');
            $table->string('lc_issuing_bank', 255)->nullable()->after('lc_expiry_date');
            $table->text('payment_notes')->nullable()->after('lc_issuing_bank');
        });
    }

    public function down(): void
    {
        Schema::table('supplier_payments', function (Blueprint $table) {
            $table->dropColumn([
                'payment_method', 'currency_code', 'bank_reference',
                'lc_number', 'lc_expiry_date', 'lc_issuing_bank', 'payment_notes',
            ]);
        });
    }
};
