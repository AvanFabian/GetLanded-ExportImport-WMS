<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Batch enhancements
        Schema::table('batches', function (Blueprint $table) {
            if (!Schema::hasColumn('batches', 'is_quarantined')) {
                $table->boolean('is_quarantined')->default(false);
            }
            if (!Schema::hasColumn('batches', 'quarantine_reason')) {
                $table->text('quarantine_reason')->nullable();
            }
            if (!Schema::hasColumn('batches', 'unit_purchase_price')) {
                $table->decimal('unit_purchase_price', 15, 2)->nullable();
            }
            if (!Schema::hasColumn('batches', 'additional_landed_costs')) {
                $table->decimal('additional_landed_costs', 15, 2)->default(0);
            }
            if (!Schema::hasColumn('batches', 'moisture_percentage')) {
                $table->decimal('moisture_percentage', 5, 2)->nullable();
            }
            if (!Schema::hasColumn('batches', 'purity_percentage')) {
                $table->decimal('purity_percentage', 5, 2)->nullable();
            }
            if (!Schema::hasColumn('batches', 'grade')) {
                $table->string('grade')->nullable();
            }
            if (!Schema::hasColumn('batches', 'parent_batch_id')) {
                $table->unsignedBigInteger('parent_batch_id')->nullable();
            }
        });

        // Product volumetric fields
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'length_cm')) {
                $table->decimal('length_cm', 8, 2)->nullable();
            }
            if (!Schema::hasColumn('products', 'width_cm')) {
                $table->decimal('width_cm', 8, 2)->nullable();
            }
            if (!Schema::hasColumn('products', 'height_cm')) {
                $table->decimal('height_cm', 8, 2)->nullable();
            }
        });

        // Invoice enhancements
        if (Schema::hasTable('invoices')) {
            Schema::table('invoices', function (Blueprint $table) {
                if (!Schema::hasColumn('invoices', 'is_proforma')) {
                    $table->boolean('is_proforma')->default(false);
                }
                if (!Schema::hasColumn('invoices', 'converted_from_proforma_id')) {
                    $table->unsignedBigInteger('converted_from_proforma_id')->nullable();
                }
                if (!Schema::hasColumn('invoices', 'invoice_sequence')) {
                    $table->integer('invoice_sequence')->nullable();
                }
            });
        }

        // Company enhancements
        Schema::table('companies', function (Blueprint $table) {
            if (!Schema::hasColumn('companies', 'timezone')) {
                $table->string('timezone')->default('Asia/Jakarta');
            }
            if (!Schema::hasColumn('companies', 'smtp_host')) {
                $table->string('smtp_host')->nullable();
            }
            if (!Schema::hasColumn('companies', 'smtp_port')) {
                $table->integer('smtp_port')->nullable();
            }
            if (!Schema::hasColumn('companies', 'smtp_username')) {
                $table->string('smtp_username')->nullable();
            }
            if (!Schema::hasColumn('companies', 'smtp_password')) {
                $table->text('smtp_password')->nullable();
            }
            if (!Schema::hasColumn('companies', 'smtp_encryption')) {
                $table->string('smtp_encryption')->nullable();
            }
            if (!Schema::hasColumn('companies', 'mail_from_address')) {
                $table->string('mail_from_address')->nullable();
            }
            if (!Schema::hasColumn('companies', 'mail_from_name')) {
                $table->string('mail_from_name')->nullable();
            }
        });

        // User 2FA fields
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'two_factor_enabled')) {
                $table->boolean('two_factor_enabled')->default(false);
            }
            if (!Schema::hasColumn('users', 'two_factor_method')) {
                $table->string('two_factor_method')->nullable();
            }
            if (!Schema::hasColumn('users', 'two_factor_secret')) {
                $table->string('two_factor_secret')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = ['two_factor_enabled', 'two_factor_method', 'two_factor_secret'];
            foreach ($columns as $col) {
                if (Schema::hasColumn('users', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('companies', function (Blueprint $table) {
            $columns = ['timezone', 'smtp_host', 'smtp_port', 'smtp_username', 'smtp_password', 'smtp_encryption', 'mail_from_address', 'mail_from_name'];
            foreach ($columns as $col) {
                if (Schema::hasColumn('companies', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        if (Schema::hasTable('invoices')) {
            Schema::table('invoices', function (Blueprint $table) {
                $columns = ['is_proforma', 'converted_from_proforma_id', 'invoice_sequence'];
                foreach ($columns as $col) {
                    if (Schema::hasColumn('invoices', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        Schema::table('products', function (Blueprint $table) {
            $columns = ['length_cm', 'width_cm', 'height_cm'];
            foreach ($columns as $col) {
                if (Schema::hasColumn('products', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('batches', function (Blueprint $table) {
            $columns = ['is_quarantined', 'quarantine_reason', 'unit_purchase_price', 'additional_landed_costs', 'moisture_percentage', 'purity_percentage', 'grade', 'parent_batch_id'];
            foreach ($columns as $col) {
                if (Schema::hasColumn('batches', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
