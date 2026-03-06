<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Carbon\Carbon;

/**
 * FullDemoSeeder — Single, fully-connected demo dataset.
 *
 * Covers:
 *  - Multi-tenancy (Company, Users, Roles, Permissions)
 *  - Warehouse hierarchy (Zone > Rack > Bin)
 *  - Products, Variants, Categories, UoM
 *  - Suppliers + Customers
 *  - Purchase Orders + approval + inbound shipments + landed cost
 *  - Stock In + Batch tracking + Stock Locations
 *  - Stock Opname (cycle count)
 *  - Inter-Warehouse Transfer
 *  - Sales Orders + Items + Invoices
 *  - Payments (TT/LC) + allocations
 *  - Outbound Shipments + Containers
 *  - Customs Declaration (BM, PPN, PPh, Anti-Dumping, FTA)
 *  - Supplier Payments
 *  - Sales Returns + Claims
 *  - Currencies
 *  - FTA Schemes + HS Codes
 *  - Webhooks
 *  - Audit Logs + Activity
 *  - 6 months of historical data for charts
 */
class FullDemoSeeder extends Seeder
{
    protected int $companyId;
    protected int $adminId;
    protected int $managerId;
    protected array $warehouseIds = [];
    protected array $binIds = [];
    protected array $productIds = [];
    protected array $supplierIds = [];
    protected array $customerIds = [];
    protected array $categoryMap = [];

    public function run(): void
    {
        $this->command->info('🚀 FullDemoSeeder — Clearing products & seeding all features...');

        $this->disableForeignKeys();
        $this->clearProductData();
        $this->enableForeignKeys();

        DB::beginTransaction();
        try {
            $this->resolveCompanyAndUsers();
            $this->seedCurrencies();
            $this->seedWarehouseHierarchy();
            $this->seedMasterData();
            $this->seedFtaAndHsCodes();
            $this->seedUomConversions();
            $this->seedHistoricalData();
            $this->seedAdvancedFeatures();
            $this->seedSystemData();

            DB::commit();
            $this->command->info('✅ FullDemoSeeder completed successfully!');
            $this->command->info('🔑 Login: owner@avandigital.id / demo1234');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('❌ ' . $e->getMessage() . ' @ ' . basename($e->getFile()) . ':' . $e->getLine());
            throw $e;
        }
    }

    // ============================================
    // CLEANUP
    // ============================================

    protected function clearProductData(): void
    {
        $this->command->info('🧹 Clearing product-related tables...');
        $tables = [
            'stock_locations', 'batch_movements', 'batches',
            'stock_in_details', 'stock_out_details',
            'sales_order_items', 'purchase_order_details',
            'product_warehouse',
            'uom_conversions',
            'stock_ins', 'stock_outs', 'stock_opnames',
            'sales_orders', 'purchase_orders',
            'payments', 'payment_allocations', 'invoices',
            'inbound_shipments', 'outbound_shipments',
            'container_items', 'containers',
            'shipment_expenses',
            'customs_declaration_items', 'customs_declarations',
            'supplier_payments',
            'sales_return_items', 'sales_returns',
            'claim_evidences', 'claims',
            'inter_warehouse_transfer_items', 'inter_warehouse_transfers',
            'order_expenses',
            'products',
        ];

        // Filter out tables that don't exist
        $existingTables = array_filter($tables, fn($t) => Schema::hasTable($t));

        if (DB::getDriverName() === 'pgsql') {
            // For PostgreSQL, TRUNCATE with CASCADE is the best way to handle foreign keys 
            // in managed environments where we don't have superuser/session_replication_role permissions.
            $tableList = implode(', ', array_map(fn($t) => "\"$t\"", $existingTables));
            if (!empty($tableList)) {
                DB::statement("TRUNCATE TABLE $tableList RESTART IDENTITY CASCADE");
            }
        } else {
            foreach ($existingTables as $table) {
                DB::table($table)->truncate();
            }
        }
    }

    // ============================================
    // COMPANY & USERS
    // ============================================

    protected function resolveCompanyAndUsers(): void
    {
        $this->command->info('👥 Resolving company & users...');

        $company = DB::table('companies')->where('code', 'AVANDIGITAL')->first();
        if (!$company) {
            $this->companyId = DB::table('companies')->insertGetId([
                'uuid' => Str::uuid(),
                'code' => 'AVANDIGITAL',
                'name' => 'PT AvanDigital Nusantara',
                'base_currency_code' => 'IDR',
                'subscription_plan' => 'enterprise',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $this->companyId = $company->id;
        }

        $admin = DB::table('users')->where('email', 'owner@avandigital.id')->first();
        $this->adminId = $admin?->id ?? DB::table('users')->insertGetId([
            'company_id' => $this->companyId,
            'name' => 'Avan (Owner)',
            'email' => 'owner@avandigital.id',
            'password' => Hash::make('demo1234'),
            'role' => 'admin',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $manager = DB::table('users')->where('email', 'manager@avandigital.id')->first();
        $this->managerId = $manager?->id ?? DB::table('users')->insertGetId([
            'company_id' => $this->companyId,
            'name' => 'Budi (Manager)',
            'email' => 'manager@avandigital.id',
            'password' => Hash::make('demo1234'),
            'role' => 'manager',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    // ============================================
    // CURRENCIES
    // ============================================

    protected function seedCurrencies(): void
    {
        $this->command->info('💱 Seeding currencies...');
        $currencies = [
            ['code' => 'IDR', 'name' => 'Indonesian Rupiah', 'symbol' => 'Rp', 'exchange_rate' => 1.0, 'is_base' => true],
            ['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$', 'exchange_rate' => 16000.0, 'is_base' => false],
            ['code' => 'EUR', 'name' => 'Euro', 'symbol' => '€', 'exchange_rate' => 17200.0, 'is_base' => false],
            ['code' => 'CNY', 'name' => 'Chinese Yuan', 'symbol' => '¥', 'exchange_rate' => 2200.0, 'is_base' => false],
            ['code' => 'SGD', 'name' => 'Singapore Dollar', 'symbol' => 'S$', 'exchange_rate' => 11800.0, 'is_base' => false],
        ];
        foreach ($currencies as $c) {
            DB::table('currencies')->updateOrInsert(['code' => $c['code']], array_merge($c, [
                'rate_updated_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    // ============================================
    // WAREHOUSE HIERARCHY
    // ============================================

    protected function seedWarehouseHierarchy(): void
    {
        $this->command->info('🏭 Seeding warehouse hierarchy...');

        $warehouses = [
            ['code' => 'WH-JKT-01', 'name' => 'Jakarta Bonded Warehouse', 'city' => 'Jakarta', 'is_default' => true],
            ['code' => 'WH-SBY-01', 'name' => 'Surabaya Export Hub',      'city' => 'Surabaya', 'is_default' => false],
        ];

        foreach ($warehouses as $wh) {
            $whId = DB::table('warehouses')->where('code', $wh['code'])
                ->where('company_id', $this->companyId)->value('id');

            if (!$whId) {
                $whId = DB::table('warehouses')->insertGetId([
                    'company_id' => $this->companyId,
                    'code' => $wh['code'],
                    'name' => $wh['name'],
                    'city' => $wh['city'],
                    'is_active' => true,
                    'is_default' => $wh['is_default'],
                    'created_by' => $this->adminId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            $this->warehouseIds[] = $whId;

            // Zones
            foreach (['Dry Storage', 'Cold Storage', 'Hazmat', 'Bonded'] as $zi => $zoneName) {
                $zoneCode = 'Z' . ($zi + 1) . '-' . substr($wh['code'], -3);
                $zoneId = DB::table('warehouse_zones')
                    ->where('code', $zoneCode)->where('warehouse_id', $whId)->value('id');
                if (!$zoneId) {
                    $zoneId = DB::table('warehouse_zones')->insertGetId([
                        'warehouse_id' => $whId,
                        'code' => $zoneCode,
                        'name' => $zoneName,
                        'type' => 'storage',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                // Racks per zone
                for ($r = 1; $r <= 2; $r++) {
                    $rackCode = $zoneCode . '-R' . $r;
                    $rackId = DB::table('warehouse_racks')
                        ->where('code', $rackCode)->where('zone_id', $zoneId)->value('id');
                    if (!$rackId) {
                        $rackId = DB::table('warehouse_racks')->insertGetId([
                            'zone_id' => $zoneId,
                            'code' => $rackCode,
                            'name' => 'Rack ' . $r,
                            'levels' => 4,
                            'is_active' => true,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }

                    // Bins per rack
                    for ($b = 1; $b <= 3; $b++) {
                        $binCode = $rackCode . '-B' . $b;
                        $binId = DB::table('warehouse_bins')
                            ->where('code', $binCode)->where('rack_id', $rackId)->value('id');
                        if (!$binId) {
                            $binId = DB::table('warehouse_bins')->insertGetId([
                                'rack_id' => $rackId,
                                'code' => $binCode,
                                'max_capacity' => 2000,
                                'pick_priority' => 'medium',
                                'is_active' => true,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                        $this->binIds[] = $binId;
                    }
                }
            }
        }
    }

    // ============================================
    // MASTER DATA
    // ============================================

    protected function seedMasterData(): void
    {
        $this->command->info('📦 Seeding products, suppliers, customers...');

        // Categories
        $cats = ['Coffee & Cocoa', 'Spices & Herbs', 'Palm Products', 'Rubber & Latex', 'Machinery & Parts'];
        foreach ($cats as $cat) {
            $catId = DB::table('categories')
                ->where('company_id', $this->companyId)->where('name', $cat)->value('id');
            if (!$catId) {
                $catId = DB::table('categories')->insertGetId([
                    'company_id' => $this->companyId,
                    'name' => $cat,
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }
            $this->categoryMap[$cat] = $catId;
        }

        // Products (export-import commodities + machinery)
        $products = [
            // Coffee & Cocoa
            ['code' => 'COF-ARA-GAY', 'name' => 'Arabica Coffee Gayo Grade 1', 'cat' => 'Coffee & Cocoa', 'unit' => 'kg', 'buy' => 95000, 'sell' => 125000, 'hs' => '0901.11.10', 'weight' => 1.0, 'cbm' => 0.0012, 'min' => 500],
            ['code' => 'COF-ROB-LMP', 'name' => 'Robusta Coffee Lampung G2',   'cat' => 'Coffee & Cocoa', 'unit' => 'kg', 'buy' => 52000, 'sell' => 72000,  'hs' => '0901.11.10', 'weight' => 1.0, 'cbm' => 0.0012, 'min' => 500],
            ['code' => 'COC-BEA-SUL', 'name' => 'Cocoa Beans Sulawesi Bulk',   'cat' => 'Coffee & Cocoa', 'unit' => 'kg', 'buy' => 47000, 'sell' => 65000,  'hs' => '1801.00.10', 'weight' => 1.0, 'cbm' => 0.0010, 'min' => 500],
            ['code' => 'COC-BUT-001', 'name' => 'Cocoa Butter Deodorized',     'cat' => 'Coffee & Cocoa', 'unit' => 'kg', 'buy' => 85000, 'sell' => 115000, 'hs' => '1804.00.00', 'weight' => 1.0, 'cbm' => 0.0011, 'min' => 200],
            // Spices & Herbs
            ['code' => 'SPI-PEP-BLK', 'name' => 'Black Pepper Whole Muntok',   'cat' => 'Spices & Herbs', 'unit' => 'kg', 'buy' => 115000, 'sell' => 155000, 'hs' => '0904.11.00', 'weight' => 1.0, 'cbm' => 0.0013, 'min' => 200],
            ['code' => 'SPI-NUT-001', 'name' => 'Nutmeg Whole Banda Grade A',  'cat' => 'Spices & Herbs', 'unit' => 'kg', 'buy' => 135000, 'sell' => 185000, 'hs' => '0908.11.00', 'weight' => 1.0, 'cbm' => 0.0009, 'min' => 100],
            ['code' => 'SPI-CIN-001', 'name' => 'Cassia Cinnamon Sticks',      'cat' => 'Spices & Herbs', 'unit' => 'kg', 'buy' => 48000,  'sell' => 70000,  'hs' => '0906.11.00', 'weight' => 1.0, 'cbm' => 0.0015, 'min' => 200],
            // Palm Products
            ['code' => 'PLM-CPO-001', 'name' => 'Crude Palm Oil (CPO)',         'cat' => 'Palm Products', 'unit' => 'mt', 'buy' => 11500000, 'sell' => 13800000, 'hs' => '1511.10.00', 'weight' => 1000, 'cbm' => 1.10, 'min' => 50],
            ['code' => 'PLM-OLE-001', 'name' => 'RBD Palm Olein',               'cat' => 'Palm Products', 'unit' => 'mt', 'buy' => 12200000, 'sell' => 14500000, 'hs' => '1511.90.10', 'weight' => 1000, 'cbm' => 1.10, 'min' => 20],
            // Rubber
            ['code' => 'RUB-SIR-001', 'name' => 'SIR 20 Natural Rubber',       'cat' => 'Rubber & Latex', 'unit' => 'kg', 'buy' => 22500, 'sell' => 30000,  'hs' => '4001.22.00', 'weight' => 1.0, 'cbm' => 0.0013, 'min' => 500],
            // Machinery (import)
            ['code' => 'MCH-VLV-001', 'name' => 'Industrial Hydraulic Valve',   'cat' => 'Machinery & Parts', 'unit' => 'unit', 'buy' => 2400000, 'sell' => 3800000, 'hs' => '8481.80.50', 'weight' => 5.5, 'cbm' => 0.020, 'min' => 10],
            ['code' => 'MCH-PMP-001', 'name' => 'Centrifugal Pump 5.5kW',      'cat' => 'Machinery & Parts', 'unit' => 'unit', 'buy' => 8500000, 'sell' => 13000000,'hs' => '8413.70.10', 'weight' => 45.0, 'cbm' => 0.180, 'min' => 5],
        ];

        foreach ($products as $p) {
            $prodId = DB::table('products')
                ->where('company_id', $this->companyId)->where('code', $p['code'])->value('id');
            if (!$prodId) {
                $prodId = DB::table('products')->insertGetId([
                    'company_id'     => $this->companyId,
                    'code'           => $p['code'],
                    'name'           => $p['name'],
                    'category_id'    => $this->categoryMap[$p['cat']],
                    'unit'           => $p['unit'],
                    'purchase_price' => $p['buy'],
                    'selling_price'  => $p['sell'],
                    'hs_code'        => $p['hs'],
                    'net_weight'     => $p['weight'],
                    'cbm_volume'     => $p['cbm'],
                    'min_stock'      => $p['min'],
                    'enable_batch_tracking' => true,
                    'batch_method'   => 'FIFO',
                    'status'         => true,
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);
            }
            $this->productIds[] = $prodId;

            // Attach to all warehouses
            foreach ($this->warehouseIds as $whId) {
                DB::table('product_warehouse')->updateOrInsert(
                    ['product_id' => $prodId, 'warehouse_id' => $whId],
                    ['stock' => 0, 'min_stock' => $p['min'], 'created_at' => now(), 'updated_at' => now()]
                );
            }
        }

        // Suppliers
        $suppliers = [
            ['email' => 'export@gayocoffee.co.id',  'name' => 'PT Gayo Coffee Aceh',           'phone' => '0651-22345',       'address' => 'Aceh, Indonesia'],
            ['email' => 'sales@borneospices.my',     'name' => 'Borneo Spices Sdn Bhd',         'phone' => '+60-82-123456',    'address' => 'Kuching, Malaysia'],
            ['email' => 'trade@globalmachines.cn',   'name' => 'Global Machines Ltd Shanghai',  'phone' => '+86-21-9988776',   'address' => 'Shanghai, China'],
            ['email' => 'palm@wilmar-intl.com',      'name' => 'Wilmar International (Medan)',  'phone' => '061-4568900',      'address' => 'Medan, Indonesia'],
            ['email' => 'rubber@guthrie.my',         'name' => 'Sime Darby Rubber Products',   'phone' => '+60-3-2099-8888',  'address' => 'Kuala Lumpur, Malaysia'],
        ];
        foreach ($suppliers as $s) {
            $id = DB::table('suppliers')
                ->where('company_id', $this->companyId)->where('email', $s['email'])->value('id');
            if (!$id) {
                $id = DB::table('suppliers')->insertGetId([
                    'company_id' => $this->companyId,
                    'name'       => $s['name'],
                    'email'      => $s['email'],
                    'phone'      => $s['phone'],
                    'address'    => $s['address'],
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }
            $this->supplierIds[] = $id;
        }

        // Customers
        $customers = [
            ['email' => 'buying@starbucks-supply.com', 'name' => 'Starbucks Coffee Global Supply', 'phone' => '+1-206-555-0101', 'address' => 'Seattle, USA'],
            ['email' => 'cocoa@barry-callebaut.com',   'name' => 'Barry Callebaut AG',              'phone' => '+41-44-801-2222', 'address' => 'Zurich, Switzerland'],
            ['email' => 'palm@nestle-supply.com',      'name' => 'Nestlé S.A. Global Procurement',  'phone' => '+41-21-924-2111', 'address' => 'Vevey, Switzerland'],
            ['email' => 'spice@mccormick.com',         'name' => 'McCormick & Company',             'phone' => '+1-410-771-7301', 'address' => 'Hunt Valley, USA'],
            ['email' => 'parts@petrobras-supply.com',  'name' => 'Petrobras Industrial Supply',     'phone' => '+55-21-3224-4477','address' => 'Rio de Janeiro, Brazil'],
        ];
        foreach ($customers as $c) {
            $id = DB::table('customers')
                ->where('company_id', $this->companyId)->where('email', $c['email'])->value('id');
            if (!$id) {
                $id = DB::table('customers')->insertGetId([
                    'company_id' => $this->companyId,
                    'name'       => $c['name'],
                    'email'      => $c['email'],
                    'phone'      => $c['phone'],
                    'address'    => $c['address'],
                    'is_active'  => true,
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }
            $this->customerIds[] = $id;
        }
    }

    // ============================================
    // FTA, HS CODES
    // ============================================

    protected function seedFtaAndHsCodes(): void
    {
        $this->command->info('📋 Seeding FTA schemes & HS codes...');

        $hsCodes = [
            ['code' => '0901.11.10', 'description' => 'Coffee, not roasted, not decaffeinated',           'bm_rate' => 0.00, 'ppn_rate' => 11, 'pph_api_rate' => 2.5, 'pph_non_api_rate' => 7.5],
            ['code' => '1801.00.10', 'description' => 'Cocoa beans, whole or broken, raw or roasted',     'bm_rate' => 0.00, 'ppn_rate' => 11, 'pph_api_rate' => 2.5, 'pph_non_api_rate' => 7.5],
            ['code' => '1804.00.00', 'description' => 'Cocoa butter, fat and oil',                        'bm_rate' => 0.00, 'ppn_rate' => 11, 'pph_api_rate' => 2.5, 'pph_non_api_rate' => 7.5],
            ['code' => '0904.11.00', 'description' => 'Pepper of the genus Piper; dried/ground',          'bm_rate' => 0.00, 'ppn_rate' => 11, 'pph_api_rate' => 2.5, 'pph_non_api_rate' => 7.5],
            ['code' => '0908.11.00', 'description' => 'Nutmeg',                                            'bm_rate' => 0.00, 'ppn_rate' => 11, 'pph_api_rate' => 2.5, 'pph_non_api_rate' => 7.5],
            ['code' => '0906.11.00', 'description' => 'Cinnamon and cinnamon-tree flowers',               'bm_rate' => 0.00, 'ppn_rate' => 11, 'pph_api_rate' => 2.5, 'pph_non_api_rate' => 7.5],
            ['code' => '1511.10.00', 'description' => 'Crude palm oil',                                   'bm_rate' => 0.00, 'ppn_rate' => 11, 'pph_api_rate' => 2.5, 'pph_non_api_rate' => 7.5],
            ['code' => '1511.90.10', 'description' => 'Refined bleached deodorized palm olein',           'bm_rate' => 5.00, 'ppn_rate' => 11, 'pph_api_rate' => 2.5, 'pph_non_api_rate' => 7.5],
            ['code' => '4001.22.00', 'description' => 'Technically specified natural rubber (TSNR)',       'bm_rate' => 0.00, 'ppn_rate' => 11, 'pph_api_rate' => 2.5, 'pph_non_api_rate' => 7.5],
            ['code' => '8481.80.50', 'description' => 'Industrial hydraulic valves',                      'bm_rate' => 5.00, 'ppn_rate' => 11, 'pph_api_rate' => 2.5, 'pph_non_api_rate' => 7.5],
            ['code' => '8413.70.10', 'description' => 'Centrifugal pumps for liquids',                   'bm_rate' => 5.00, 'ppn_rate' => 11, 'pph_api_rate' => 2.5, 'pph_non_api_rate' => 7.5],
        ];

        foreach ($hsCodes as $hs) {
            DB::table('hs_codes')->updateOrInsert(
                ['code' => $hs['code']],
                array_merge($hs, ['created_at' => now(), 'updated_at' => now()])
            );
        }

        $ftaSchemes = [
            ['name' => 'ACFTA',   'description' => 'ASEAN-China Free Trade Area',                'member_countries' => json_encode(['CN', 'ID', 'MY', 'SG', 'TH', 'VN'])],
            ['name' => 'AIFTA',   'description' => 'ASEAN-India Free Trade Area',                'member_countries' => json_encode(['IN', 'ID', 'MY', 'SG', 'TH', 'VN'])],
            ['name' => 'AANZFTA', 'description' => 'ASEAN-Australia-New Zealand FTA',            'member_countries' => json_encode(['AU', 'NZ', 'ID', 'MY', 'SG'])],
        ];

        foreach ($ftaSchemes as $fta) {
            $ftaId = DB::table('fta_schemes')
                ->where('company_id', $this->companyId)->where('name', $fta['name'])->value('id');
            if (!$ftaId) {
                $ftaId = DB::table('fta_schemes')->insertGetId([
                    'company_id'       => $this->companyId,
                    'name'             => $fta['name'],
                    'description'      => $fta['description'],
                    'member_countries' => $fta['member_countries'],
                    'is_active'        => true,
                    'created_at'       => now(), 'updated_at' => now(),
                ]);
            }

            // FTA rates for machinery HS codes (preferential 0% for ACFTA)
            foreach (['8481.80.50', '8413.70.10'] as $hsCode) {
                DB::table('fta_rates')->updateOrInsert(
                    ['fta_scheme_id' => $ftaId, 'hs_code' => $hsCode],
                    ['rate' => 0.00, 'created_at' => now(), 'updated_at' => now()]
                );
            }
        }
    }

    // ============================================
    // UOM CONVERSIONS
    // ============================================

    protected function seedUomConversions(): void
    {
        $this->command->info('⚖️ Seeding UoM conversions...');
        $conversions = [
            ['from_unit' => 'MT',  'to_unit' => 'KG',  'factor' => 1000],
            ['from_unit' => 'BAG', 'to_unit' => 'KG',  'factor' => 60],
            ['from_unit' => 'LBS', 'to_unit' => 'KG',  'factor' => 0.453592],
            ['from_unit' => 'OZ',  'to_unit' => 'KG',  'factor' => 0.0283495],
            ['from_unit' => 'CBM', 'to_unit' => 'LTR', 'factor' => 1000],
        ];
        foreach ($conversions as $cv) {
            DB::table('uom_conversions')->updateOrInsert(
                ['company_id' => $this->companyId, 'from_unit' => $cv['from_unit'], 'to_unit' => $cv['to_unit']],
                ['conversion_factor' => $cv['factor'], 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }

    // ============================================
    // HISTORICAL DATA (6 months)
    // ============================================

    protected function seedHistoricalData(): void
    {
        $this->command->info('📅 Seeding 6 months of historical transactions...');

        $start = Carbon::now()->subMonths(6)->startOfMonth();
        $end = Carbon::now()->subDays(3);
        $current = $start->copy();

        while ($current->lte($end)) {
            // Inbound: 40% chance per date tick
            if (rand(0, 9) < 4) {
                try {
                    $this->createInboundCycle($current->copy());
                } catch (\Exception $e) {
                    $this->command->warn('Inbound cycle error: ' . $e->getMessage());
                }
            }
            // Sales: 60% chance
            if (rand(0, 9) < 6) {
                try {
                    $this->createSaleCycle($current->copy());
                } catch (\Exception $e) {
                    $this->command->warn('Sale cycle error: ' . $e->getMessage());
                }
            }
            $current->addDays(rand(3, 7));
        }
    }

    protected function createInboundCycle(Carbon $date): void
    {
        $supplierId = $this->supplierIds[array_rand($this->supplierIds)];
        $warehouseId = $this->warehouseIds[0]; // Main warehouse
        $productId = $this->productIds[array_rand($this->productIds)];
        $qty = rand(500, 5000);
        $price = rand(50000, 150000);
        $subtotal = $qty * $price;
        $isImport = rand(0, 1);

        // Inbound Shipment
        $shipNo = 'SHP-' . $date->format('Ym') . '-' . Str::padLeft(rand(1, 999), 3, '0');
        $shipId = DB::table('inbound_shipments')->insertGetId([
            'company_id'           => $this->companyId,
            'shipment_number'      => $shipNo . '-' . Str::random(3),
            'carrier_name'         => $isImport ? 'Evergreen Marine' : 'Meratus Line',
            'origin_port'          => $isImport ? 'Shanghai, CN' : 'Belawan, ID',
            'destination_port'     => 'Tanjung Priok, ID',
            'etd'                  => $date->copy()->subDays(14)->format('Y-m-d'),
            'eta'                  => $date->format('Y-m-d'),
            'actual_arrival_date'  => $date->format('Y-m-d'),
            'status'               => 'arrived',
            'notes'                => 'Historical import',
            'created_by'           => $this->adminId,
            'created_at'           => $date, 'updated_at' => $date,
        ]);

        // Purchase Order (approved, linked to shipment)
        $poNo = 'PO-' . $date->format('Ymd') . '-' . Str::padLeft(rand(1, 99), 3, '0');
        $poId = DB::table('purchase_orders')->insertGetId([
            'company_id'                  => $this->companyId,
            'po_number'                   => $poNo,
            'supplier_id'                 => $supplierId,
            'warehouse_id'                => $warehouseId,
            'inbound_shipment_id'         => $shipId,
            'order_date'                  => $date->copy()->subDays(20)->format('Y-m-d'),
            'expected_delivery_date'      => $date->format('Y-m-d'),
            'status'                      => 'completed',
            'total_amount'                => $subtotal,
            'currency_code'               => $isImport ? 'USD' : 'IDR',
            'exchange_rate_at_transaction'=> $isImport ? 16000 : 1,
            'transaction_fees'            => $subtotal * 0.005,
            'net_amount'                  => $subtotal * 0.995,
            'created_by'                  => $this->adminId,
            'approved_by'                 => $this->managerId,
            'approved_at'                 => $date->copy()->subDays(18),
            'created_at'                  => $date->copy()->subDays(20),
            'updated_at'                  => $date,
        ]);

        DB::table('purchase_order_details')->insert([
            'purchase_order_id' => $poId,
            'product_id'        => $productId,
            'quantity_ordered'  => $qty,
            'quantity_received' => $qty,
            'unit_price'        => $price,
            'subtotal'          => $subtotal,
            'created_at'        => $date, 'updated_at' => $date,
        ]);

        // Shipment Expenses (Landed Cost)
        foreach ([
            'Freight' => rand(5000000, 20000000),
            'Insurance' => rand(500000, 2000000),
            'Customs Handling' => rand(1000000, 3000000)
        ] as $expName => $amt) {
            DB::table('shipment_expenses')->insert([
                'inbound_shipment_id' => $shipId,
                'name'                => $expName,
                'amount'              => $amt,
                'currency_code'       => 'IDR',
                'allocation_method'   => 'value',
                'created_at'          => $date, 'updated_at' => $date,
            ]);
        }

        // Stock In
        $siCode = 'SI-' . $date->format('ymd') . '-' . Str::random(3);
        $stockInId = DB::table('stock_ins')->insertGetId([
            'company_id'       => $this->companyId,
            'warehouse_id'     => $warehouseId,
            'supplier_id'      => $supplierId,
            'transaction_code' => $siCode,
            'date'             => $date->format('Y-m-d'),
            'status'           => 'approved',
            'total'            => $subtotal,
            'notes'            => 'Received from ' . ($isImport ? 'import' : 'domestic'),
            'created_at'       => $date, 'updated_at' => $date,
        ]);

        DB::table('stock_in_details')->insert([
            'stock_in_id'    => $stockInId,
            'product_id'     => $productId,
            'quantity'       => $qty,
            'purchase_price' => $price,
            'total'          => $subtotal,
            'created_at'     => $date, 'updated_at' => $date,
        ]);

        // Batch
        $batchNo = 'LOT-' . $date->format('Ymd') . '-' . strtoupper(Str::random(4));
        $batchId = DB::table('batches')->insertGetId([
            'company_id'       => $this->companyId,
            'batch_number'     => $batchNo,
            'product_id'       => $productId,
            'stock_in_id'      => $stockInId,
            'supplier_id'      => $supplierId,
            'manufacture_date' => $date->copy()->subDays(30)->format('Y-m-d'),
            'expiry_date'      => $date->copy()->addMonths(rand(6, 18))->format('Y-m-d'),
            'cost_price'       => $price,
            'status'           => 'active',
            'created_at'       => $date, 'updated_at' => $date,
        ]);

        // Stock Location (in a bin)
        if (!empty($this->binIds)) {
            $binId = $this->binIds[array_rand($this->binIds)];
            DB::table('stock_locations')->insert([
                'batch_id'  => $batchId,
                'bin_id'    => $binId,
                'quantity'  => $qty,
                'created_at'=> $date, 'updated_at' => $date,
            ]);

            if (Schema::hasTable('batch_movements')) {
                DB::table('batch_movements')->insert([
                    'batch_id'        => $batchId,
                    'bin_id'          => $binId,
                    'movement_type'   => 'stock_in',
                    'quantity'        => $qty,
                    'quantity_before' => 0,
                    'quantity_after'  => $qty,
                    'reference_type'  => 'App\Models\StockIn',
                    'reference_id'    => $stockInId,
                    'user_id'         => $this->adminId,
                    'created_at'      => $date, 'updated_at' => $date,
                ]);
            }
        }

        // Update product_warehouse pivot stock
        DB::table('product_warehouse')
            ->where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->increment('stock', $qty);

        // Supplier Payment (simplified to match actual schema)
        DB::table('supplier_payments')->insert([
            'company_id'     => $this->companyId,
            'stock_in_id'    => $stockInId,
            'supplier_id'    => $supplierId,
            'amount_owed'    => $subtotal,
            'amount_paid'    => $subtotal * 0.995,
            'due_date'       => $date->copy()->addDays(30)->format('Y-m-d'),
            'payment_status' => 'paid',
            'created_at'     => $date, 'updated_at' => $date,
        ]);
    }

    protected function createSaleCycle(Carbon $date): void
    {
        $customerId = $this->customerIds[array_rand($this->customerIds)];
        $warehouseId = $this->warehouseIds[array_rand($this->warehouseIds)];
        $isDelivered = $date->diffInMonths(Carbon::now()) >= 1;
        $isExport = rand(0, 1);

        $items = rand(1, 3);
        $soSubtotal = 0;
        $lineItems = [];

        for ($i = 0; $i < $items; $i++) {
            $prodId = $this->productIds[array_rand($this->productIds)];
            $qty = rand(50, 500);
            $price = $isExport ? rand(10, 40) : rand(150000, 3500000);
            $sub = $qty * $price;
            $soSubtotal += $sub;
            $lineItems[] = ['product_id' => $prodId, 'quantity' => $qty, 'unit_price' => $price, 'subtotal' => $sub];
        }

        $tax = round($soSubtotal * 0.11);
        $total = $soSubtotal + $tax;
        $soNo = 'SO-' . $date->format('Ymd') . '-' . Str::padLeft(rand(1, 9999), 5, '0');

        $soId = DB::table('sales_orders')->insertGetId([
            'company_id'                  => $this->companyId,
            'customer_id'                 => $customerId,
            'warehouse_id'                => $warehouseId,
            'so_number'                   => $soNo,
            'order_date'                  => $date->format('Y-m-d'),
            'delivery_date'               => $date->copy()->addDays(14)->format('Y-m-d'),
            'status'                      => $isDelivered ? 'delivered' : 'confirmed',
            'payment_status'              => $isDelivered ? 'paid' : 'unpaid',
            'currency_code'               => $isExport ? 'USD' : 'IDR',
            'exchange_rate_at_transaction'=> $isExport ? 16000 : 1,
            'subtotal'                    => $soSubtotal,
            'tax'                         => $tax,
            'total'                       => $total,
            'net_amount'                  => $total,
            'amount_paid'                 => $isDelivered ? $total : 0,
            'notes'                       => $isExport ? 'Export to ' . $date->format('M Y') : 'Domestic sale',
            'created_by'                  => $this->adminId,
            'created_at'                  => $date, 'updated_at' => $date,
        ]);

        foreach ($lineItems as $li) {
            DB::table('sales_order_items')->insert(array_merge($li, [
                'sales_order_id' => $soId,
                'created_at' => $date, 'updated_at' => $date,
            ]));
        }

        // Invoice
        $invNo = 'INV-' . $date->format('Ymd') . '-' . Str::padLeft(rand(1, 999), 4, '0');
        $invoiceId = DB::table('invoices')->insertGetId([
            'company_id'     => $this->companyId,
            'sales_order_id' => $soId,
            'invoice_number' => $invNo,
            'invoice_date'   => $date->format('Y-m-d'),
            'due_date'       => $date->copy()->addDays(30)->format('Y-m-d'),
            'total_amount'   => $total,
            'paid_amount'    => $isDelivered ? $total : 0,
            'payment_status' => $isDelivered ? 'paid' : 'unpaid',
            'created_by'     => $this->adminId,
            'created_at'     => $date, 'updated_at' => $date,
        ]);

        if ($isDelivered) {
            // Payment
            $payId = DB::table('payments')->insertGetId([
                'company_id'          => $this->companyId,
                'sales_order_id'      => $soId,
                'customer_id'         => $customerId,
                'payment_date'        => $date->copy()->addDays(rand(3, 15))->format('Y-m-d'),
                'amount'              => $total,
                'bank_fees'           => round($total * 0.001),
                'currency_code'       => $isExport ? 'USD' : 'IDR',
                'exchange_rate'       => $isExport ? 16000 : 1,
                'base_currency_amount'=> $isExport ? $total * 16000 : $total,
                'payment_method'      => $isExport ? 'tt' : 'bank_transfer',
                'reference'           => 'PAY-' . strtoupper(Str::random(8)),
                'created_at'          => $date, 'updated_at' => $date,
            ]);

            // Outbound Shipment (export)
            if ($isExport) {
                $obId = DB::table('outbound_shipments')->insertGetId([
                    'company_id'        => $this->companyId,
                    'sales_order_id'    => $soId,
                    'shipment_number'   => 'OBS-' . $date->format('Ym') . '-' . Str::random(3),
                    'shipment_date'     => $date->copy()->addDays(7)->format('Y-m-d'),
                    'carrier_name'      => 'Maersk Line',
                    'vessel_name'       => 'MV Courage',
                    'bill_of_lading'    => 'BL-' . strtoupper(Str::random(10)),
                    'port_of_loading'   => 'Tanjung Priok, ID',
                    'port_of_discharge' => 'Rotterdam, NL',
                    'incoterm'          => 'FOB',
                    'freight_cost'      => rand(2000, 5000),
                    'insurance_cost'    => rand(100, 300),
                    'currency_code'     => 'USD',
                    'status'            => 'delivered',
                    'created_at'        => $date, 'updated_at' => $date,
                ]);

                // Container
                $containerId = DB::table('containers')->insertGetId([
                    'company_id'           => $this->companyId,
                    'outbound_shipment_id' => $obId,
                    'container_number'     => 'MSCU' . rand(1000000, 9999999),
                    'container_type'       => '20GP',
                    'seal_number'          => 'SEAL' . rand(100000, 999999),
                    'max_weight_kg'        => 25000,
                    'max_volume_cbm'       => 33,
                    'used_weight_kg'       => rand(5000, 18000),
                    'used_volume_cbm'      => rand(10, 30),
                    'status'               => 'shipped',
                    'created_at'           => $date, 'updated_at' => $date,
                ]);

                // Customs Declaration
                $declaredVal = $total * 0.85;
                DB::table('customs_declarations')->insertGetId([
                    'company_id'           => $this->companyId,
                    'outbound_shipment_id' => $obId,
                    'declaration_number'   => 'PEB-' . $date->format('Y') . '-' . rand(100000, 999999),
                    'declaration_type'     => 'export',
                    'declaration_date'     => $date->copy()->addDays(6)->format('Y-m-d'),
                    'hs_code'              => '0901.11.10',
                    'declared_value'       => $declaredVal,
                    'currency_code'        => 'USD',
                    'duty_rate'            => 0.00,
                    'duty_amount'          => 0,
                    'vat_rate'             => 0.00,
                    'vat_amount'           => 0,
                    'excise_amount'        => 0,
                    'total_tax'            => 0,
                    'status'               => 'cleared',
                    'created_at'           => $date, 'updated_at' => $date,
                ]);
            }
        }
    }

    // ============================================
    // ADVANCED FEATURES (Opname, Transfer, Return, Claim, Webhook)
    // ============================================

    protected function seedAdvancedFeatures(): void
    {
        $this->command->info('🔬 Seeding advanced features...');
        try { $this->seedStockOpnames(); } catch (\Exception $e) { $this->command->warn('seedStockOpnames: ' . $e->getMessage()); }
        try { $this->seedInterWarehouseTransfer(); } catch (\Exception $e) { $this->command->warn('seedInterWarehouseTransfer: ' . $e->getMessage()); }
        try { $this->seedSalesReturn(); } catch (\Exception $e) { $this->command->warn('seedSalesReturn: ' . $e->getMessage()); }
        try { $this->seedExpiringBatch(); } catch (\Exception $e) { $this->command->warn('seedExpiringBatch: ' . $e->getMessage()); }
        try { $this->seedWebhooks(); } catch (\Exception $e) { $this->command->warn('seedWebhooks: ' . $e->getMessage()); }
    }

    protected function seedStockOpnames(): void
    {
        foreach (array_slice($this->productIds, 0, 3) as $prodId) {
            $whId = $this->warehouseIds[0];
            $currentStock = (int) DB::table('product_warehouse')
                ->where('product_id', $prodId)->where('warehouse_id', $whId)->value('stock') ?? 0;
            $physicalStock = max(0, $currentStock - rand(0, 50));

            $opnameData = [
                'company_id'   => $this->companyId,
                'warehouse_id' => $whId,
                'product_id'   => $prodId,
                'difference'   => $physicalStock - $currentStock,
                'notes'        => 'Monthly cycle count Q1',
                'created_at'   => Carbon::now()->subDays(15),
                'updated_at'   => Carbon::now()->subDays(15),
            ];

            // Handle both old and new column names
            if (Schema::hasColumn('stock_opnames', 'system_qty')) {
                $opnameData['system_qty']  = $currentStock;
                $opnameData['counted_qty'] = $physicalStock;
                $opnameData['reason']      = 'Monthly cycle count Q1';
                $opnameData['date']        = Carbon::now()->subDays(15)->format('Y-m-d');
                unset($opnameData['notes']);
            } else {
                $opnameData['system_stock']   = $currentStock;
                $opnameData['physical_stock'] = $physicalStock;
                $opnameData['status']         = 'approved';
            }

            // Remove warehouse_id if it doesn't exist
            if (!Schema::hasColumn('stock_opnames', 'warehouse_id')) {
                unset($opnameData['warehouse_id']);
            }
            // Remove company_id if it doesn't exist
            if (!Schema::hasColumn('stock_opnames', 'company_id')) {
                unset($opnameData['company_id']);
            }

            DB::table('stock_opnames')->insert($opnameData);
        }
    }

    protected function seedInterWarehouseTransfer(): void
    {
        $prodId = $this->productIds[0];
        $fromWh = $this->warehouseIds[0];
        $toWh = $this->warehouseIds[1] ?? $this->warehouseIds[0];
        $qty = 500;
        $transferDate = Carbon::now()->subDays(10);

        $transferId = DB::table('inter_warehouse_transfers')->insertGetId([
            'transfer_number'   => 'IWT-' . $transferDate->format('Ymd') . '-001',
            'from_warehouse_id' => $fromWh,
            'to_warehouse_id'   => $toWh,
            'transfer_date'     => $transferDate->format('Y-m-d'),
            'status'            => 'completed',
            'notes'             => 'Rebalancing stock between hubs',
            'created_by'        => $this->adminId,
            'approved_by'       => $this->managerId,
            'approved_at'       => $transferDate,
            'completed_by'      => $this->adminId,
            'completed_at'      => $transferDate->copy()->addDays(1),
            'created_at'        => $transferDate, 'updated_at' => $transferDate,
        ]);

        DB::table('inter_warehouse_transfer_items')->insert([
            'transfer_id' => $transferId,
            'product_id'  => $prodId,
            'quantity'    => $qty,
            'created_at'  => $transferDate, 'updated_at' => $transferDate,
        ]);

        // Adjust pivot
        DB::table('product_warehouse')->where('product_id', $prodId)->where('warehouse_id', $fromWh)->decrement('stock', $qty);
        DB::table('product_warehouse')->where('product_id', $prodId)->where('warehouse_id', $toWh)->increment('stock', $qty);
    }

    protected function seedSalesReturn(): void
    {
        // Find a delivered SO
        $so = DB::table('sales_orders')
            ->where('company_id', $this->companyId)
            ->where('status', 'delivered')
            ->first();

        if (!$so) return;

        $returnId = DB::table('sales_returns')->insertGetId([
            'company_id'     => $this->companyId,
            'sales_order_id' => $so->id,
            'return_number'  => 'RTN-' . now()->format('Ymd') . '-001',
            'return_date'    => Carbon::now()->subDays(5)->format('Y-m-d'),
            'reason'         => 'Quality does not meet Grade A specification',
            'status'         => 'approved',
            'credit_amount'  => $so->total * 0.1,
            'created_at'     => now(), 'updated_at' => now(),
        ]);

            // Insurance / quality claim
            $soItem = DB::table('sales_order_items')->where('sales_order_id', $so->id)->first();
            if ($soItem) {
                DB::table('claims')->insertGetId([
                    'company_id'    => $this->companyId,
                    'sales_order_id'=> $so->id,
                    'claim_type'    => 'damage',
                    'claimed_amount'=> $so->total * 0.1,
                    'description'   => 'Quality claim: product does not meet specification',
                    'status'        => 'open',
                    'created_at'    => now(), 'updated_at' => now(),
                ]);
            }
    }

    protected function seedExpiringBatch(): void
    {
        // Create a batch that expires in 10 days (triggers alert)
        $prodId = $this->productIds[0];
        $batchId = DB::table('batches')->insertGetId([
            'company_id'     => $this->companyId,
            'batch_number'   => 'LOT-EXPIRING-SOON',
            'product_id'     => $prodId,
            'supplier_id'    => $this->supplierIds[0],
            'manufacture_date'=> Carbon::now()->subMonths(12)->format('Y-m-d'),
            'expiry_date'    => Carbon::now()->addDays(10)->format('Y-m-d'),
            'cost_price'     => 95000,
            'status'         => 'active',
            'created_at'     => now(), 'updated_at' => now(),
        ]);

        if (!empty($this->binIds)) {
            DB::table('stock_locations')->insert([
                'batch_id'  => $batchId,
                'bin_id'    => $this->binIds[0],
                'quantity'  => 75,
                'created_at'=> now(), 'updated_at' => now(),
            ]);
        }

        DB::table('product_warehouse')
            ->where('product_id', $prodId)
            ->where('warehouse_id', $this->warehouseIds[0])
            ->increment('stock', 75);
    }

    protected function seedWebhooks(): void
    {
        $hookId = DB::table('webhooks')->insertGetId([
            'company_id' => $this->companyId,
            'url'        => 'https://webhook.site/demo-getlanded',
            'events'     => json_encode(['sales_order.created', 'stock_in.approved', 'payment.received']),
            'is_active'  => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        DB::table('webhook_logs')->insert([
            'webhook_id'    => $hookId,
            'event'         => 'sales_order.created',
            'payload'       => json_encode(['so_number' => 'SO-DEMO-001', 'status' => 'confirmed']),
            'response_code' => 200,
            'response_body' => '{"status":"received"}',
            'created_at'    => now(), 'updated_at' => now(),
        ]);
    }

    // ============================================
    // SYSTEM DATA
    // ============================================

    protected function seedSystemData(): void
    {
        $this->command->info('🔧 Seeding system logs & settings...');

        $actions = [
            ['event' => 'created', 'desc' => 'Owner logged in from Jakarta'],
            ['event' => 'created', 'desc' => 'Approved PO for Gayo Coffee Aceh'],
            ['event' => 'updated', 'desc' => 'Updated WAC for Arabica Coffee after new shipment'],
            ['event' => 'created', 'desc' => 'Exported 2,000 kg to Starbucks — SO confirmed'],
            ['event' => 'created', 'desc' => 'Customs declaration PEB filed for OBS-2026-001'],
            ['event' => 'updated', 'desc' => 'Currency rates synced — USD/IDR: 16,000'],
            ['event' => 'updated', 'desc' => 'Monthly stock opname completed — 3 products adjusted'],
        ];

        foreach ($actions as $i => $act) {
            DB::table('audit_logs')->insert([
                'company_id'     => $this->companyId,
                'user_id'        => $this->adminId,
                'event'          => $act['event'],
                'auditable_type' => 'App\Models\User',
                'auditable_id'   => $this->adminId,
                'old_values'     => null,
                'new_values'     => json_encode(['description' => $act['desc']]),
                'url'            => 'https://app.getlanded.id/dashboard',
                'ip_address'     => '180.246.' . rand(1, 254) . '.' . rand(1, 254),
                'user_agent'     => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'created_at'     => Carbon::now()->subMinutes($i * 20),
            ]);
        }

        // Company bank account
        DB::table('company_bank_accounts')->updateOrInsert(
            ['company_id' => $this->companyId, 'account_number' => '0072-01-001234-56-7'],
            [
                'bank_name'      => 'Bank Mandiri',
                'account_name'   => 'PT AvanDigital Nusantara',
                'currency_code'  => 'IDR',
                'is_default'     => true,
                'created_at'     => now(), 'updated_at' => now(),
            ]
        );

        DB::table('company_bank_accounts')->updateOrInsert(
            ['company_id' => $this->companyId, 'account_number' => '007-USD-99887766'],
            [
                'bank_name'      => 'Bank Mandiri (USD)',
                'account_name'   => 'PT AvanDigital Nusantara',
                'currency_code'  => 'USD',
                'is_default'     => false,
                'created_at'     => now(), 'updated_at' => now(),
            ]
        );

        $this->command->info('🎯 Summary:');
        $this->command->info('  Products     : ' . DB::table('products')->where('company_id', $this->companyId)->count());
        $this->command->info('  Suppliers    : ' . DB::table('suppliers')->where('company_id', $this->companyId)->count());
        $this->command->info('  Customers    : ' . DB::table('customers')->where('company_id', $this->companyId)->count());
        $this->command->info('  Stock Ins    : ' . DB::table('stock_ins')->where('company_id', $this->companyId)->count());
        $this->command->info('  Batches      : ' . DB::table('batches')->where('company_id', $this->companyId)->count());
        $this->command->info('  Sales Orders : ' . DB::table('sales_orders')->where('company_id', $this->companyId)->count());
        $this->command->info('  Invoices     : ' . DB::table('invoices')->where('company_id', $this->companyId)->count());
        $this->command->info('  Payments     : ' . DB::table('payments')->where('company_id', $this->companyId)->count());
        $this->command->info('  Outbound Ship: ' . DB::table('outbound_shipments')->where('company_id', $this->companyId)->count());
        $this->command->info('  Customs Decl : ' . DB::table('customs_declarations')->where('company_id', $this->companyId)->count());
    }

    private function disableForeignKeys(): void
    {
        $driver = DB::getDriverName();
        try {
            match ($driver) {
                'mysql' => DB::statement('SET FOREIGN_KEY_CHECKS=0'),
                'sqlite' => DB::statement('PRAGMA foreign_keys = OFF'),
                'pgsql' => null, // Skip for PG here as it requires superuser usually
                default => null,
            };
        } catch (\Throwable $e) {
            $this->command->warn('Warning: Could not disable foreign keys globally: ' . $e->getMessage());
        }
    }

    private function enableForeignKeys(): void
    {
        $driver = DB::getDriverName();
        try {
            match ($driver) {
                'mysql' => DB::statement('SET FOREIGN_KEY_CHECKS=1'),
                'sqlite' => DB::statement('PRAGMA foreign_keys = ON'),
                'pgsql' => null, // Skip for PG
                default => null,
            };
        } catch (\Throwable $e) {
            // Silently ignore
        }
    }
}
