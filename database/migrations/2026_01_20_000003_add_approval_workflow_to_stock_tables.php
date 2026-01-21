<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add approval workflow fields to stock transaction tables.
     * Implements maker-checker dual control system.
     */
    public function up(): void
    {
        // Add to stock_ins
        Schema::table('stock_ins', function (Blueprint $table) {
            $table->string('status')->default('pending_approval')->after('notes');
            $table->foreignId('created_by')->nullable()->after('status')->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            $table->foreignId('rejected_by')->nullable()->after('approved_by')->constrained('users')->nullOnDelete();
            $table->text('rejection_notes')->nullable()->after('rejected_by');
            $table->timestamp('approved_at')->nullable()->after('rejection_notes');
            
            $table->index(['status', 'company_id']);
        });

        // Add to stock_outs
        Schema::table('stock_outs', function (Blueprint $table) {
            $table->string('status')->default('pending_approval')->after('notes');
            $table->foreignId('created_by')->nullable()->after('status')->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            $table->foreignId('rejected_by')->nullable()->after('approved_by')->constrained('users')->nullOnDelete();
            $table->text('rejection_notes')->nullable()->after('rejected_by');
            $table->timestamp('approved_at')->nullable()->after('rejection_notes');
            
            $table->index(['status', 'company_id']);
        });

        // Add to stock_opnames  
        Schema::table('stock_opnames', function (Blueprint $table) {
            $table->string('status')->default('pending_approval')->after('reason');
            $table->foreignId('approved_by')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
            $table->foreignId('rejected_by')->nullable()->after('approved_by')->constrained('users')->nullOnDelete();
            $table->text('rejection_notes')->nullable()->after('rejected_by');
            $table->timestamp('approved_at')->nullable()->after('rejection_notes');
            
            $table->index(['status', 'company_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_ins', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['approved_by']);
            $table->dropForeign(['rejected_by']);
            $table->dropColumn(['status', 'created_by', 'approved_by', 'rejected_by', 'rejection_notes', 'approved_at']);
        });

        Schema::table('stock_outs', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['approved_by']);
            $table->dropForeign(['rejected_by']);
            $table->dropColumn(['status', 'created_by', 'approved_by', 'rejected_by', 'rejection_notes', 'approved_at']);
        });

        Schema::table('stock_opnames', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropForeign(['rejected_by']);
            $table->dropColumn(['status', 'approved_by', 'rejected_by', 'rejection_notes', 'approved_at']);
        });
    }
};
