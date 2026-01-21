<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add super-admin flag and snapshot columns.
     */
    public function up(): void
    {
        // Add is_super_admin to users
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_super_admin')->default(false)->after('is_active');
        });

        // Add snapshot_data to audit_logs for persistence after deletion
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->json('snapshot_data')->nullable()->after('new_values');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_super_admin');
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropColumn('snapshot_data');
        });
    }
};
