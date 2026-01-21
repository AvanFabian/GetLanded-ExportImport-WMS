<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add company profile fields for PDF headers and export.
     */
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            // Branding
            if (!Schema::hasColumn('companies', 'logo_path')) {
                $table->string('logo_path')->nullable()->after('code');
            }
            
            // Contact Info
            if (!Schema::hasColumn('companies', 'email')) {
                $table->string('email')->nullable()->after('logo_path');
            }
            if (!Schema::hasColumn('companies', 'phone')) {
                $table->string('phone')->nullable()->after('email');
            }
            if (!Schema::hasColumn('companies', 'website')) {
                $table->string('website')->nullable()->after('phone');
            }
            
            // Address
            if (!Schema::hasColumn('companies', 'address')) {
                $table->text('address')->nullable()->after('website');
            }
            if (!Schema::hasColumn('companies', 'city')) {
                $table->string('city')->nullable()->after('address');
            }
            if (!Schema::hasColumn('companies', 'state')) {
                $table->string('state')->nullable()->after('city');
            }
            if (!Schema::hasColumn('companies', 'postal_code')) {
                $table->string('postal_code')->nullable()->after('state');
            }
            if (!Schema::hasColumn('companies', 'country')) {
                $table->string('country')->default('Indonesia')->after('postal_code');
            }
            
            // Tax & Legal
            if (!Schema::hasColumn('companies', 'tax_id')) {
                $table->string('tax_id')->nullable()->after('country');
            }
            if (!Schema::hasColumn('companies', 'tax_registration_number')) {
                $table->string('tax_registration_number')->nullable()->after('tax_id');
            }
            if (!Schema::hasColumn('companies', 'default_vat_percentage')) {
                $table->decimal('default_vat_percentage', 5, 2)->default(11.00)->after('tax_registration_number');
            }
        });

        // Transporter info for logistics
        if (!Schema::hasColumn('stock_outs', 'transporter_info')) {
            Schema::table('stock_outs', function (Blueprint $table) {
                $table->json('transporter_info')->nullable()->after('notes');
            });
        }

        if (!Schema::hasColumn('sales_orders', 'transporter_info')) {
            Schema::table('sales_orders', function (Blueprint $table) {
                $table->json('transporter_info')->nullable()->after('notes');
            });
        }
        if (!Schema::hasColumn('sales_orders', 'tax_amount')) {
            Schema::table('sales_orders', function (Blueprint $table) {
                $table->decimal('tax_amount', 15, 2)->default(0)->after('discount');
            });
        }
        if (!Schema::hasColumn('sales_orders', 'grand_total')) {
            Schema::table('sales_orders', function (Blueprint $table) {
                $table->decimal('grand_total', 15, 2)->default(0)->after('tax_amount');
            });
        }

        // Package count for batches
        if (!Schema::hasColumn('batches', 'package_count')) {
            Schema::table('batches', function (Blueprint $table) {
                $table->integer('package_count')->nullable()->after('net_weight');
            });
        }
        if (!Schema::hasColumn('batches', 'package_unit')) {
            Schema::table('batches', function (Blueprint $table) {
                $table->string('package_unit')->nullable()->after('package_count');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'logo_path', 'email', 'phone', 'website',
                'address', 'city', 'state', 'postal_code', 'country',
                'tax_id', 'tax_registration_number', 'default_vat_percentage'
            ]);
        });

        Schema::table('stock_outs', function (Blueprint $table) {
            $table->dropColumn('transporter_info');
        });

        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropColumn(['transporter_info', 'tax_amount', 'grand_total']);
        });

        Schema::table('batches', function (Blueprint $table) {
            $table->dropColumn(['package_count', 'package_unit']);
        });
    }
};
