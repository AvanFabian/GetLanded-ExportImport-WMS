<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('stock_ins', function (Blueprint $table) {
            if (!Schema::hasColumn('stock_ins', 'warehouse_id')) {
                $table->foreignId('warehouse_id')->nullable()->after('id')->constrained('warehouses')->nullOnDelete();
            }
            
            // Status field (draft, approved)
            if (!Schema::hasColumn('stock_ins', 'status')) {
                $table->string('status')->default('draft')->after('total');
            }

            // Approval workflow
            if (!Schema::hasColumn('stock_ins', 'approved_by')) {
                $table->foreignId('approved_by')->nullable()->constrained('users');
            }
            if (!Schema::hasColumn('stock_ins', 'approved_at')) {
                $table->timestamp('approved_at')->nullable();
            }
            
            // Document UUID if missing
            if (!Schema::hasColumn('stock_ins', 'document_uuid')) {
                 $table->uuid('document_uuid')->nullable();
            }

            // Created By if missing
             if (!Schema::hasColumn('stock_ins', 'created_by')) {
                 $table->foreignId('created_by')->nullable()->constrained('users');
             }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reverse needed as this is a fix
    }
};
