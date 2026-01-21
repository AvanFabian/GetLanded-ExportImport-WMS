<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            // Bank details for PDF footer
            if (!Schema::hasColumn('companies', 'bank_name')) {
                $table->string('bank_name')->nullable()->after('tax_id');
            }
            if (!Schema::hasColumn('companies', 'bank_account_number')) {
                $table->string('bank_account_number')->nullable()->after('bank_name');
            }
            if (!Schema::hasColumn('companies', 'bank_swift_code')) {
                $table->string('bank_swift_code')->nullable()->after('bank_account_number');
            }
            // Invoice terms & conditions
            if (!Schema::hasColumn('companies', 'invoice_terms')) {
                $table->text('invoice_terms')->nullable()->after('bank_swift_code');
            }
        });

        // Add document UUID to stock tables
        Schema::table('stock_ins', function (Blueprint $table) {
            if (!Schema::hasColumn('stock_ins', 'document_uuid')) {
                $table->uuid('document_uuid')->nullable()->unique()->after('id');
            }
        });

        Schema::table('stock_outs', function (Blueprint $table) {
            if (!Schema::hasColumn('stock_outs', 'document_uuid')) {
                $table->uuid('document_uuid')->nullable()->unique()->after('id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['bank_name', 'bank_account_number', 'bank_swift_code', 'invoice_terms']);
        });

        Schema::table('stock_ins', function (Blueprint $table) {
            $table->dropColumn('document_uuid');
        });

        Schema::table('stock_outs', function (Blueprint $table) {
            $table->dropColumn('document_uuid');
        });
    }
};
