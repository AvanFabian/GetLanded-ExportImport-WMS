<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Sales returns
        Schema::create('sales_returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sales_order_id')->constrained()->cascadeOnDelete();
            $table->string('return_number');
            $table->date('return_date');
            $table->decimal('credit_amount', 15, 2);
            $table->text('reason');
            $table->string('status')->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('sales_return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_return_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained();
            $table->foreignId('batch_id')->nullable()->constrained();
            $table->integer('quantity');
            $table->decimal('unit_price', 15, 2);
            $table->timestamps();
        });

        // Order expenses
        Schema::create('order_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sales_order_id')->constrained()->cascadeOnDelete();
            $table->string('category'); // freight, insurance, customs_clearance, fumigation, other
            $table->decimal('amount', 15, 2);
            $table->string('currency_code', 3)->default('IDR');
            $table->decimal('withholding_tax_rate', 5, 2)->nullable();
            $table->decimal('withholding_tax_amount', 15, 2)->default(0);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Supplier payments (Accounts Payable)
        Schema::create('supplier_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('stock_in_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount_owed', 15, 2);
            $table->decimal('amount_paid', 15, 2)->default(0);
            $table->date('due_date')->nullable();
            $table->string('payment_status')->default('unpaid');
            $table->timestamps();
        });

        // Stock transfers (inter-warehouse)
        Schema::create('stock_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('source_warehouse_id')->constrained('warehouses');
            $table->foreignId('destination_warehouse_id')->constrained('warehouses');
            $table->string('transfer_number');
            $table->string('status')->default('pending');
            $table->date('transfer_date');
            $table->date('received_date')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('received_by')->nullable()->constrained('users');
            $table->timestamps();
        });

        Schema::create('stock_transfer_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_transfer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('batch_id')->constrained();
            $table->foreignId('source_bin_id')->constrained('warehouse_bins');
            $table->foreignId('destination_bin_id')->nullable()->constrained('warehouse_bins');
            $table->integer('quantity');
            $table->timestamps();
        });

        // Claims
        Schema::create('claims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sales_order_id')->constrained()->cascadeOnDelete();
            $table->string('claim_type'); // damage, shortage, delay
            $table->decimal('claimed_amount', 15, 2);
            $table->string('insurance_policy_number')->nullable();
            $table->string('status')->default('open');
            $table->decimal('settled_amount', 15, 2)->nullable();
            $table->text('description');
            $table->timestamps();
        });

        Schema::create('claim_evidences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('claim_id')->constrained()->cascadeOnDelete();
            $table->string('file_path');
            $table->string('file_type');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('claim_evidences');
        Schema::dropIfExists('claims');
        Schema::dropIfExists('stock_transfer_items');
        Schema::dropIfExists('stock_transfers');
        Schema::dropIfExists('supplier_payments');
        Schema::dropIfExists('order_expenses');
        Schema::dropIfExists('sales_return_items');
        Schema::dropIfExists('sales_returns');
    }
};
