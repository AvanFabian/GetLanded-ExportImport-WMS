<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Customs declarations
        Schema::create('customs_declarations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('outbound_shipment_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('declaration_number')->nullable();
            $table->string('declaration_type'); // export, import
            $table->date('declaration_date');
            $table->string('customs_office')->nullable();
            $table->string('hs_code')->nullable();
            $table->decimal('declared_value', 15, 2);
            $table->string('currency_code', 3)->default('USD');
            $table->decimal('duty_rate', 8, 4)->default(0);
            $table->decimal('duty_amount', 15, 2)->default(0);
            $table->decimal('vat_rate', 8, 4)->default(0);
            $table->decimal('vat_amount', 15, 2)->default(0);
            $table->decimal('excise_amount', 15, 2)->default(0);
            $table->decimal('total_tax', 15, 2)->default(0);
            $table->string('status')->default('draft'); // draft, submitted, assessed, paid, cleared, rejected
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Customs declaration items
        Schema::create('customs_declaration_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customs_declaration_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained();
            $table->string('hs_code')->nullable();
            $table->string('description');
            $table->integer('quantity');
            $table->string('unit_of_measure')->default('KGS');
            $table->decimal('unit_value', 15, 2);
            $table->decimal('total_value', 15, 2);
            $table->string('country_of_origin')->nullable();
            $table->timestamps();
        });

        // Customs permits / licenses
        Schema::create('customs_permits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('permit_number');
            $table->string('permit_type'); // SIUP, NIB, API-U, API-P, IT, PI, etc.
            $table->date('issue_date');
            $table->date('expiry_date');
            $table->string('issuing_authority')->nullable();
            $table->string('status')->default('active'); // active, expired, revoked
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customs_permits');
        Schema::dropIfExists('customs_declaration_items');
        Schema::dropIfExists('customs_declarations');
    }
};
