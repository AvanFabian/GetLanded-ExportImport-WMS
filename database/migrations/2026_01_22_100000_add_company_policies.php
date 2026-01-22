<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add company policy fields for adaptive workflow.
     */
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            // Approval Workflow Policy
            if (!Schema::hasColumn('companies', 'require_approval_workflow')) {
                $table->boolean('require_approval_workflow')->default(true)
                    ->comment('If false, creator can self-approve transactions');
            }
            
            // Invoice Sequence Policy
            if (!Schema::hasColumn('companies', 'invoice_sequence_logic')) {
                $table->enum('invoice_sequence_logic', ['strict', 'flexible'])->default('strict')
                    ->comment('strict = stock-out first, flexible = invoice anytime');
            }
            
            // UoM Conversion Toggle
            if (!Schema::hasColumn('companies', 'uom_conversion_enabled')) {
                $table->boolean('uom_conversion_enabled')->default(true)
                    ->comment('Enable unit of measurement conversions');
            }
            
            // Auto-void on Rejection
            if (!Schema::hasColumn('companies', 'auto_void_on_rejection')) {
                $table->boolean('auto_void_on_rejection')->default(false)
                    ->comment('Automatically void documents when rejected');
            }
            
            // Stock Limit Mode
            if (!Schema::hasColumn('companies', 'stock_limit_mode')) {
                $table->enum('stock_limit_mode', ['block', 'warning'])->default('block')
                    ->comment('block = hard block on insufficient stock, warning = allow with warning');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $columns = [
                'require_approval_workflow',
                'invoice_sequence_logic', 
                'uom_conversion_enabled',
                'auto_void_on_rejection',
                'stock_limit_mode',
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('companies', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
