<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customs_declarations', function (Blueprint $table) {
            $table->decimal('pph_rate', 8, 4)->default(0)->after('vat_amount');
            $table->decimal('pph_amount', 15, 2)->default(0)->after('pph_rate');
            $table->decimal('anti_dumping_rate', 8, 4)->default(0)->after('pph_amount');
            $table->decimal('anti_dumping_amount', 15, 2)->default(0)->after('anti_dumping_rate');
            $table->string('fta_scheme', 50)->nullable()->after('anti_dumping_amount');
        });
    }

    public function down(): void
    {
        Schema::table('customs_declarations', function (Blueprint $table) {
            $table->dropColumn(['pph_rate', 'pph_amount', 'anti_dumping_rate', 'anti_dumping_amount', 'fta_scheme']);
        });
    }
};
