<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hs_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique(); // e.g. 0901.11.00
            $table->string('description', 255);
            $table->decimal('bm_rate', 5, 2)->default(0); // Standard MFN Duty Rate %
            $table->decimal('ppn_rate', 5, 2)->default(11); // Standard VAT %
            $table->decimal('pph_api_rate', 5, 2)->default(2.5); // PPh with API-U
            $table->decimal('pph_non_api_rate', 5, 2)->default(7.5); // PPh without API-U
            $table->timestamps();
            
            $table->index(['code', 'description']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hs_codes');
    }
};
