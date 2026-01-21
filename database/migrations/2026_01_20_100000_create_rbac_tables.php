<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create RBAC tables for granular permissions.
     */
    public function up(): void
    {
        // Permissions table (global)
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // e.g., 'stock.in.create'
            $table->string('display_name');
            $table->string('group')->nullable(); // e.g., 'stock', 'transaction'
            $table->text('description')->nullable();
            $table->timestamps();
            
            $table->index('group');
        });

        // Roles table (per-company)
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('display_name');
            $table->boolean('is_system')->default(false); // System roles can't be deleted
            $table->text('description')->nullable();
            $table->timestamps();
            
            $table->unique(['company_id', 'name']);
            $table->index('company_id');
        });

        // Role-Permission pivot
        Schema::create('role_permission', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            
            $table->primary(['role_id', 'permission_id']);
        });

        // User-Role pivot
        Schema::create('role_user', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            
            $table->primary(['user_id', 'role_id']);
        });

        // Direct User-Permission pivot (for exceptions)
        Schema::create('permission_user', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            
            $table->primary(['user_id', 'permission_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permission_user');
        Schema::dropIfExists('role_user');
        Schema::dropIfExists('role_permission');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('permissions');
    }
};
