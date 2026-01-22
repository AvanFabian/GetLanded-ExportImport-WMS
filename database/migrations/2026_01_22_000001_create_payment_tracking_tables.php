<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Payments table
        if (!Schema::hasTable('payments')) {
            Schema::create('payments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained()->cascadeOnDelete();
                $table->foreignId('sales_order_id')->nullable()->constrained()->cascadeOnDelete();
                $table->foreignId('customer_id')->nullable()->constrained()->cascadeOnDelete();
                $table->foreignId('bank_account_id')->nullable()->constrained('company_bank_accounts');
                $table->date('payment_date');
                $table->decimal('amount', 15, 2);
                $table->decimal('bank_fees', 15, 2)->default(0);
                $table->string('currency_code', 3)->default('IDR');
                $table->decimal('exchange_rate', 15, 8)->nullable();
                $table->decimal('base_currency_amount', 15, 2);
                $table->string('payment_method');
                $table->string('reference')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        // Payment allocations (for deposits)
        if (!Schema::hasTable('payment_allocations')) {
            Schema::create('payment_allocations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('payment_id')->constrained()->cascadeOnDelete();
                $table->foreignId('sales_order_id')->constrained()->cascadeOnDelete();
                $table->decimal('amount', 15, 2);
                $table->timestamps();
            });
        }

        // Add NEW payment fields to sales_orders
        Schema::table('sales_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('sales_orders', 'amount_paid')) {
                $table->decimal('amount_paid', 15, 2)->default(0)->after('total');
            }
            if (!Schema::hasColumn('sales_orders', 'total_bank_fees')) {
                $table->decimal('total_bank_fees', 15, 2)->default(0)->after('amount_paid');
            }
            if (!Schema::hasColumn('sales_orders', 'credit_note_amount')) {
                $table->decimal('credit_note_amount', 15, 2)->default(0)->after('total_bank_fees');
            }
            if (!Schema::hasColumn('sales_orders', 'exchange_gain_loss')) {
                $table->decimal('exchange_gain_loss', 15, 2)->default(0)->after('credit_note_amount');
            }
            if (!Schema::hasColumn('sales_orders', 'due_date')) {
                $table->date('due_date')->nullable()->after('delivery_date');
            }
            if (!Schema::hasColumn('sales_orders', 'shipment_status')) {
                $table->string('shipment_status')->default('PENDING')->after('status');
            }
            if (!Schema::hasColumn('sales_orders', 'bill_of_lading_number')) {
                $table->string('bill_of_lading_number')->nullable()->after('voyage_number');
            }
            if (!Schema::hasColumn('sales_orders', 'carrier_tracking_url')) {
                $table->string('carrier_tracking_url')->nullable()->after('bill_of_lading_number');
            }
            if (!Schema::hasColumn('sales_orders', 'shipping_documents_checklist')) {
                $table->json('shipping_documents_checklist')->nullable()->after('carrier_tracking_url');
            }
            if (!Schema::hasColumn('sales_orders', 'container_type')) {
                $table->string('container_type')->nullable()->after('shipping_documents_checklist');
            }
            if (!Schema::hasColumn('sales_orders', 'temperature_setting')) {
                $table->string('temperature_setting')->nullable()->after('container_type');
            }
            if (!Schema::hasColumn('sales_orders', 'loading_instructions')) {
                $table->text('loading_instructions')->nullable()->after('temperature_setting');
            }
            if (!Schema::hasColumn('sales_orders', 'gate_in_date')) {
                $table->datetime('gate_in_date')->nullable()->after('loading_instructions');
            }
            if (!Schema::hasColumn('sales_orders', 'container_free_time_expiry')) {
                $table->datetime('container_free_time_expiry')->nullable()->after('gate_in_date');
            }
            if (!Schema::hasColumn('sales_orders', 'commission_rate')) {
                $table->decimal('commission_rate', 5, 2)->nullable()->after('notify_party');
            }
            if (!Schema::hasColumn('sales_orders', 'commission_total')) {
                $table->decimal('commission_total', 15, 2)->default(0)->after('commission_rate');
            }
            if (!Schema::hasColumn('sales_orders', 'commission_withholding_tax')) {
                $table->decimal('commission_withholding_tax', 15, 2)->default(0)->after('commission_total');
            }
            if (!Schema::hasColumn('sales_orders', 'document_courier_name')) {
                $table->string('document_courier_name')->nullable()->after('commission_withholding_tax');
            }
            if (!Schema::hasColumn('sales_orders', 'document_awb_number')) {
                $table->string('document_awb_number')->nullable()->after('document_courier_name');
            }
            if (!Schema::hasColumn('sales_orders', 'document_dispatched_at')) {
                $table->datetime('document_dispatched_at')->nullable()->after('document_awb_number');
            }
        });

        // Add shipped_quantity to sales_order_items
        if (!Schema::hasColumn('sales_order_items', 'shipped_quantity')) {
            Schema::table('sales_order_items', function (Blueprint $table) {
                $table->integer('shipped_quantity')->default(0)->after('quantity');
            });
        }

        // Add credit_balance to customers
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'credit_balance')) {
                $table->decimal('credit_balance', 15, 2)->default(0)->after('email');
            }
            if (!Schema::hasColumn('customers', 'credit_limit')) {
                $table->decimal('credit_limit', 15, 2)->nullable()->after('credit_balance');
            }
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['credit_balance', 'credit_limit']);
        });

        Schema::table('sales_order_items', function (Blueprint $table) {
            $table->dropColumn('shipped_quantity');
        });

        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropColumn([
                'amount_paid', 'total_bank_fees', 'credit_note_amount', 'exchange_gain_loss',
                'due_date', 'shipment_status', 'bill_of_lading_number',
                'carrier_tracking_url', 'shipping_documents_checklist', 'container_type',
                'temperature_setting', 'loading_instructions', 'gate_in_date',
                'container_free_time_expiry', 'commission_rate', 'commission_total',
                'commission_withholding_tax', 'document_courier_name', 'document_awb_number',
                'document_dispatched_at'
            ]);
        });

        Schema::dropIfExists('payment_allocations');
        Schema::dropIfExists('payments');
    }
};
