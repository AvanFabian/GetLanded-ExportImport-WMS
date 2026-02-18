<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fta_schemes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name', 50); // e.g. ACFTA
            $table->string('description')->nullable(); // e.g. ASEAN-China Free Trade Area
            $table->json('member_countries')->nullable(); // Array of country codes
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('fta_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fta_scheme_id')->constrained()->cascadeOnDelete();
            $table->string('hs_code', 20)->index();
            $table->decimal('rate', 8, 4); // Preferential duty rate
            $table->timestamps();

            $table->unique(['fta_scheme_id', 'hs_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fta_rates');
        Schema::dropIfExists('fta_schemes');
    }
};
