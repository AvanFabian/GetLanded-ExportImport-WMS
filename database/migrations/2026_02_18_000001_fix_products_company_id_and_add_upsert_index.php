<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Fix products with NULL company_id (from import bug) and add
     * unique composite index for bulk upsert performance.
     */
    public function up(): void
    {
        // Step 1: Fix broken data — assign orphaned products to the correct company
        // Uses the import_job that created them to determine the correct company_id
        $latestJob = DB::table('import_jobs')
            ->whereNotNull('company_id')
            ->orderByDesc('id')
            ->first();

        if ($latestJob) {
            DB::table('products')
                ->whereNull('company_id')
                ->update(['company_id' => $latestJob->company_id]);
        }

        // Step 2: Add unique composite index for upsert performance
        // This enables Product::upsert() to detect conflicts on (company_id, code)
        Schema::table('products', function (Blueprint $table) {
            // Drop the old non-unique index on code alone (if exists)
            if (Schema::hasIndex('products', 'idx_products_code')) {
                $table->dropIndex('idx_products_code');
            }

            // Add composite unique index
            $table->unique(['company_id', 'code'], 'uq_products_company_code');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique('uq_products_company_code');
            $table->index('code', 'idx_products_code');
        });
    }
};
