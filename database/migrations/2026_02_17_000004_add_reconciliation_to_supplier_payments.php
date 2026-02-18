<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('supplier_payments', function (Blueprint $table) {
            $table->timestamp('reconciled_at')->nullable()->after('payment_notes');
            $table->string('bank_statement_ref', 100)->nullable()->after('reconciled_at');
        });
    }

    public function down(): void
    {
        Schema::table('supplier_payments', function (Blueprint $table) {
            $table->dropColumn(['reconciled_at', 'bank_statement_ref']);
        });
    }
};
