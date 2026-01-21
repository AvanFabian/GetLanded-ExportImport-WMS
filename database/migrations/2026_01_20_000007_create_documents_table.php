<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create documents table for S3 file storage.
     */
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('batch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('sales_order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('purchase_order_id')->nullable()->constrained()->nullOnDelete();
            
            $table->string('document_type', 50); // COA, PHYTO, LAB_REPORT, BILL_OF_LADING, etc.
            $table->string('title');
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_disk')->default('local'); // local, s3
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->default(0);
            
            $table->date('issue_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('document_number', 100)->nullable();
            $table->text('notes')->nullable();
            
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['company_id', 'batch_id']);
            $table->index(['company_id', 'document_type']);
            $table->index('document_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
