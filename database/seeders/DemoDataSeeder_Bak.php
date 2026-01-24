<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Carbon\Carbon;

/**
 * Robust Demo Data Seeder using DB::table() + Schema checks.
 * Completely bypasses Model scopes and adapts to schema state.
 */
class DemoDataSeeder extends Seeder
{
    protected $companyId;
    protected $userId;
    protected $warehouses = [];
    protected $products = [];
    protected $suppliers = [];
    protected $customers = [];
    protected $binIds = [];

    public function run(): void
    {
        $this->command->info('🚀 Starting Robust Transactional Seeder...');
        
        DB::beginTransaction();

        try {
            // 1. Company & User
            $this->createCompanyAndUser();

            // 2. Master Data (Warehouses, Suppliers, Customers, Products, UoM)
            $this->createMasterData();

            // 3. Historical Transactions (StockIn, Sales, Payments, Expenses)
            $this->createHistoricalTransactions();

            // 4. Special Data Scenarios for Charts
            $this->createChartSpecificData();

            DB::commit();
            $this->command->info('✅ Demo environment created successfully!');
            $this->command->info("Login: demo@avandigital.id / demo1234");
            $this->command->info("Charts should now show 6 months of data.");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('❌ Fatal Error: ' . $e->getMessage());
            $this->command->error('   Location: ' . basename($e->getFile()) . ':' . $e->getLine());
            throw $e;
        }
    }

    /**
     * Dynamically insert data, stripping company_id if column missing.
     */
    protected function insertWithCompany(string $table, array $data): int
    {
        if ($table === 'categories') { 
            if (array_key_exists('slug', $data)) {
                 dd('CRITICAL: SLUG FOUND IN DATA', $data);
            } else {
                 dd('CLEAN: NO SLUG IN DATA', $data);
            }
        }
        if (!Schema::hasColumn($table, 'company_id')) {
            unset($data['company_id']);
        }
        
        if (!isset($data['created_at'])) {
            $data['created_at'] = now();
            $data['updated_at'] = now();
        }

        return DB::table($table)->insertGetId($data);
    }
    
    protected function insertSafe(string $table, array $data): void
    {
        if (!Schema::hasColumn($table, 'company_id')) {
            unset($data['company_id']);
        }
        if (!isset($data['created_at'])) {
            $data['created_at'] = now();
            $data['updated_at'] = now();
        }
        DB::table($table)->insert($data);
    }

    protected function createCompanyAndUser()
    {
        $this->command->info('Creating Company Context...');

        // Company (Use the one created by UserSeeder)
        $companyId = DB::table('companies')->where('code', 'AVANDIGITAL')->value('id');
        if (!$companyId) {
            // Fallback if UserSeeder didn't run (shouldn't happen in this flow but good for safety)
            $companyId = DB::table('companies')->insertGetId([
                'uuid' => Str::uuid()->toString(),
                'code' => 'AVANDIGITAL',
                'name' => 'AvanDigital Demo',
                'base_currency_code' => 'IDR',
                'subscription_plan' => 'enterprise',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        $this->companyId = $companyId;

        // User (Use the one created by UserSeeder)
        $userId = DB::table('users')->where('email', 'demo@avandigital.id')->value('id');
        
        if (!$userId) {
             // Fallback create if missing
            $this->userId = $this->insertWithCompany('users', [
                'company_id' => $this->companyId,
                'name' => 'Demo Owner',
                'email' => 'demo@avandigital.id',
                'password' => Hash::make('demo1234'),
                'role' => 'admin',
                'is_active' => true,
            ]);
        } else {
            $this->userId = $userId;
        }
    }

    protected function createMasterData()
    {
        $this->command->info('Step A: Seeding Master Data...');

        // 1. Warehouses
        $warehouseData = [
            ['code' => 'WH-SBY', 'name' => 'Surabaya Main Hub', 'zones' => ['Dry', 'Cold']],
            ['code' => 'WH-JKT', 'name' => 'Jakarta Transit', 'zones' => ['Bonded', 'General']],
        ];

        foreach ($warehouseData as $w) {
            $whId = DB::table('warehouses')->where('code', $w['code'])->value('id');
            if (!$whId) {
                $whId = $this->insertWithCompany('warehouses', [
                    'company_id' => $this->companyId,
                    'code' => $w['code'],
                    'name' => $w['name'],
                    'address' => 'Demo Location',
                    'is_active' => true,
                ]);
            }
            $this->warehouses[] = $whId;

            // Zones, Racks, Bins
            foreach ($w['zones'] as $zName) {
                $zoneCode = strtoupper(substr($zName, 0, 3)) . '-' . $whId;
                $zoneId = DB::table('warehouse_zones')->where('code', $zoneCode)->where('warehouse_id', $whId)->value('id');
                if (!$zoneId) {
                    $zoneId = $this->insertWithCompany('warehouse_zones', [
                        'warehouse_id' => $whId,
                        'code' => $zoneCode,
                        'name' => $zName,
                        'type' => 'storage',
                        'is_active' => true,
                    ]);
                }

                $rackCode = 'R1-' . $zoneId;
                $rackId = DB::table('warehouse_racks')->where('code', $rackCode)->where('zone_id', $zoneId)->value('id');
                if (!$rackId) {
                    $rackId = $this->insertWithCompany('warehouse_racks', [
                        'zone_id' => $zoneId,
                        'code' => $rackCode,
                        'name' => 'Rack 1',
                        'levels' => 4,
                        'is_active' => true,
                    ]);
                }

                for ($i = 1; $i <= 3; $i++) {
                    $binCode = $rackCode . '-B' . $i;
                    $binId = DB::table('warehouse_bins')->where('code', $binCode)->where('rack_id', $rackId)->value('id');
                    if (!$binId) {
                        $binId = $this->insertWithCompany('warehouse_bins', [
                            'rack_id' => $rackId,
                            'code' => $binCode,
                            'pick_priority' => 'medium',
                            'is_active' => true,
                        ]);
                    }
                    $this->binIds[] = $binId;
                }
            }
        }

        // 2. Suppliers
        $suppliers = ['Agro Mandiri', 'Borneo Spices', 'Sumatra Coffee', 'Java Cocoa', 'Sulawesi Traders'];
        foreach ($suppliers as $s) {
            $email = Str::slug($s) . '@example.com';
            $supId = DB::table('suppliers')->where('email', $email)->value('id');
            if (!$supId) {
                $supId = $this->insertWithCompany('suppliers', [
                    'company_id' => $this->companyId,
                    'name' => $s,
                    'email' => $email,
                    'phone' => '0812345678',
                    'contact_person' => 'Manager',
                ]);
            }
            $this->suppliers[] = $supId;
        }

        // 3. Customers
        $customers = ['Starbucks Global', 'Nestle S.A.', 'Cargill Inc.', 'Olam Intl', 'Barry Callebaut'];
        foreach ($customers as $c) {
            $email = Str::slug($c) . '@example.com';
            $custId = DB::table('customers')->where('email', $email)->value('id');
            if (!$custId) {
                $custId = $this->insertWithCompany('customers', [
                    'company_id' => $this->companyId,
                    'name' => $c,
                    'email' => $email,
                    'address' => 'Global HQ',
                    'is_active' => true,
                ]);
            }
            $this->customers[] = $custId;
        }

        // 4. Products & Categories
        $this->command->info('Debug: Before Categories Loop');
        $categories = [
            'Coffee' => ['Arabica Gayo', 'Robusta Lampung'],
            'Spices' => ['Black Pepper', 'Nutmeg', 'Cinnamon'],
            'Cocoa' => ['Cocoa Beans', 'Cocoa Butter'],
            'Palm Oil' => ['CPO', 'RBD Olein'],
        ];

        foreach ($categories as $catName => $prods) {
            $catId = DB::table('categories')->where('name', $catName)->value('id');
            if (!$catId) {
                //$this->command->info("Debug: Inserting Category $catName");
                $catId = $this->insertWithCompany('categories', [
                    'company_id' => $this->companyId,
                    'name' => $catName,
                ]);
            }
            
            foreach ($prods as $pName) {
                $code = strtoupper(Str::slug($pName));
                $prodId = DB::table('products')->where('code', $code)->value('id');
                if (!$prodId) {
                    $prodId = $this->insertWithCompany('products', [
                        'company_id' => $this->companyId,
                        'code' => $code,
                        'name' => $pName,
                        'category_id' => $catId,
                        'unit' => 'KG',
                        'min_stock' => 100,
                        'purchase_price' => rand(50000, 150000),
                        'selling_price' => rand(160000, 250000),
                        'status' => true,
                    ]);
                }
                $this->products[] = $prodId;

                // Bind to warehouses
                foreach ($this->warehouses as $whId) {
                    DB::table('product_warehouse')->updateOrInsert(
                        ['product_id' => $prodId, 'warehouse_id' => $whId],
                        ['stock' => 0, 'created_at' => now(), 'updated_at' => now()]
                    );
                }
            }
        }

        // 5. UoM (Skip if exists)
        if (DB::table('uom_conversions')->count() == 0) {
            $this->insertSafe('uom_conversions', ['company_id' => $this->companyId, 'from_unit' => 'MT', 'to_unit' => 'KG', 'conversion_factor' => 1000, 'is_active' => true, 'is_default' => false]);
            $this->insertSafe('uom_conversions', ['company_id' => $this->companyId, 'from_unit' => 'BAG', 'to_unit' => 'KG', 'conversion_factor' => 60, 'is_active' => true, 'is_default' => true]);
        }
    }

    protected function createHistoricalTransactions()
    {
        $this->command->info('Step B-E: Creating Transaction History...');

        if (empty($this->suppliers) || empty($this->products) || empty($this->warehouses)) {
             $this->command->warn('Skipping history - master data missing');
             return;
        }

        $startDate = Carbon::now()->subMonths(6);
        $endDate = Carbon::now();
        $currentDate = $startDate->copy();

        while ($currentDate <= $endDate) {
            try {
                // Stock Ins
                if (rand(0, 10) > 3) $this->createStockIn($currentDate);
                
                // Sales
                if (rand(0, 10) > 2) $this->createSalesOrder($currentDate);
            } catch (\Exception $e) {
                $this->command->warn('⚠️ Transaction skipped on ' . $currentDate->toDateString() . ': ' . $e->getMessage());
                // Continue loop
            }

            $currentDate->addDays(rand(2, 4));
        }
    }

    protected function createStockIn($date)
    {
        $supplierId = $this->suppliers[array_rand($this->suppliers)];
        $warehouseId = $this->warehouses[array_rand($this->warehouses)];
        
        // Ensure code is safe length
        $code = Str::limit('SI-' . $date->format('ymd') . '-' . Str::random(3), 20, '');
        
        $stockInId = $this->insertWithCompany('stock_ins', [
            'company_id' => $this->companyId,
            'warehouse_id' => $warehouseId,
            'supplier_id' => $supplierId,
            'transaction_code' => $code,
            'date' => $date->format('Y-m-d'),
            'status' => 'approved',
            'total' => 0,
            'notes' => 'Historical Import',
            'created_at' => $date,
            'updated_at' => $date,
        ]);

        $totalVal = 0;
        $items = rand(1, 3);
        for ($i = 0; $i < $items; $i++) {
            $prodId = $this->products[array_rand($this->products)];
            $qty = rand(500, 2000);
            $price = rand(50000, 100000);
            $subtotal = $qty * $price;
            $totalVal += $subtotal;

            $this->insertWithCompany('stock_in_details', [
                'stock_in_id' => $stockInId,
                'product_id' => $prodId,
                'quantity' => $qty,
                'purchase_price' => $price,
                'total' => $subtotal,
                'created_at' => $date,
                'updated_at' => $date,
            ]);

            // Batch
            $batchNo = Str::limit('B-' . $date->format('ymd') . '-' . Str::upper(Str::random(3)), 20, '');
            $batchId = $this->insertWithCompany('batches', [
                'company_id' => $this->companyId,
                'product_id' => $prodId,
                'stock_in_id' => $stockInId,
                'batch_number' => $batchNo,
                'supplier_id' => $supplierId,
                'manufacture_date' => $date->copy()->subDays(15)->format('Y-m-d'),
                'expiry_date' => $date->copy()->addMonths(12)->format('Y-m-d'),
                'cost_price' => $price,
                'status' => 'available',
                'created_at' => $date,
                'updated_at' => $date,
            ]);

            // Stock Location
            if (!empty($this->binIds)) {
                $this->insertWithCompany('stock_locations', [
                    'batch_id' => $batchId,
                    'bin_id' => $this->binIds[array_rand($this->binIds)],
                    'quantity' => $qty,
                    'created_at' => $date,
                    'updated_at' => $date,
                ]);
            }

            // Update Warehouse Stock Pivot
            DB::table('product_warehouse')
                ->where('product_id', $prodId)
                ->where('warehouse_id', $warehouseId)
                ->increment('stock', $qty);
        }

        DB::table('stock_ins')->where('id', $stockInId)->update(['total' => $totalVal]);
    }

    protected function createSalesOrder($date)
    {
        if (empty($this->customers)) return;
        
        $custId = $this->customers[array_rand($this->customers)];
        $whId = $this->warehouses[array_rand($this->warehouses)];
        $isDelivered = $date->diffInMonths(Carbon::now()) > 1;

        $soNo = Str::limit('SO-' . $date->format('ymd') . '-' . Str::random(3), 20, '');

        $soId = $this->insertWithCompany('sales_orders', [
            'company_id' => $this->companyId,
            'customer_id' => $custId,
            'warehouse_id' => $whId,
            'so_number' => $soNo,
            'order_date' => $date->format('Y-m-d'),
            'delivery_date' => $date->copy()->addDays(7)->format('Y-m-d'),
            'status' => $isDelivered ? 'delivered' : 'confirmed',
            'payment_status' => $isDelivered ? 'paid' : 'partial',
            'currency_code' => 'USD',
            'exchange_rate_at_transaction' => 16000,
            'subtotal' => 0,
            'total' => 0,
            'notes' => 'Export',
            'created_at' => $date,
            'updated_at' => $date,
            'created_by' => $this->userId
        ]);

        $totalVal = 0;
        $items = rand(1, 4);

        for ($i = 0; $i < $items; $i++) {
            $prodId = $this->products[array_rand($this->products)];
            $qty = rand(100, 500);
            $price = rand(15, 30);
            $subtotal = $qty * $price;
            $totalVal += $subtotal;

            $this->insertWithCompany('sales_order_items', [
                'sales_order_id' => $soId,
                'product_id' => $prodId,
                'quantity' => $qty,
                'unit_price' => $price,
                'subtotal' => $subtotal,
                'created_at' => $date,
                'updated_at' => $date,
            ]);
        }

        DB::table('sales_orders')->where('id', $soId)->update([
            'subtotal' => $totalVal,
            'total' => $totalVal,
            'net_amount' => $totalVal,
            'amount_paid' => $isDelivered ? $totalVal : $totalVal * 0.5
        ]);

        if ($isDelivered) {
            $ref = Str::limit('PAY-' . Str::random(5), 20, '');
            $this->insertWithCompany('payments', [
                'company_id' => $this->companyId,
                'sales_order_id' => $soId,
                'customer_id' => $custId,
                'amount' => $totalVal,
                'currency_code' => 'USD',
                'exchange_rate' => 16000,
                'base_currency_amount' => $totalVal * 16000,
                'payment_date' => $date->copy()->addDays(3),
                'payment_method' => 'tt',
                'reference' => $ref,
                'created_at' => $date->copy()->addDays(3),
                'updated_at' => $date->copy()->addDays(3),
            ]);
        }

        if (rand(0, 1)) {
            $this->insertWithCompany('order_expenses', [
                'company_id' => $this->companyId,
                'sales_order_id' => $soId,
                'category' => 'freight',
                'amount' => rand(500, 1500),
                'currency_code' => 'USD',
                'description' => 'Freight',
                'created_at' => $date,
                'updated_at' => $date,
            ]);
        }
    }

    protected function createChartSpecificData()
    {
        $this->command->info('Step F: Seeding Chart Specific Data (Aging, Alerts, Logs)...');

        // A. Inventory Aging (Old Batches)
        // Ensure we have "Slow Moving" (> 90 days)
        $oldDate = Carbon::now()->subDays(100);
        $this->createStockIn($oldDate); // Create a transaction 100 days ago

        // B. Expiring Soon (For Alerts)
        if (!empty($this->products) && !empty($this->warehouses)) {
            $expiringProd = $this->products[array_rand($this->products)];
            $whId = $this->warehouses[0];
            $supplierId = $this->suppliers[0];
            
            $batchId = $this->insertWithCompany('batches', [
                'company_id' => $this->companyId,
                'product_id' => $expiringProd,
                'stock_in_id' => null, // Manual adjust
                'batch_number' => 'EXP-ALERT-' . rand(100, 999),
                'supplier_id' => $supplierId,
                'manufacture_date' => now()->subMonths(11),
                'expiry_date' => now()->addDays(10), // Expiring in 10 days
                'cost_price' => 50000,
                'status' => 'available',
                'created_at' => now()->subMonths(6),
                'updated_at' => now(),
            ]);

            if (!empty($this->binIds)) {
                $this->insertWithCompany('stock_locations', [
                    'batch_id' => $batchId,
                    'bin_id' => $this->binIds[0],
                    'quantity' => 50,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                DB::table('product_warehouse')
                    ->where('product_id', $expiringProd)
                    ->where('warehouse_id', $whId)
                    ->increment('stock', 50);
            }
        }

        // C. Recent Activity Logs
        $actions = [
            ['event' => 'created', 'desc' => 'User logged in'], 
            ['event' => 'created', 'desc' => 'Created Stock In SI-2401'],
            ['event' => 'updated', 'desc' => 'Updated Product prices'],
            ['event' => 'updated', 'desc' => 'Approved Sales Order SO-9921'], 
            ['event' => 'created', 'desc' => 'Printed Invoice INV-001'],
        ];
        
        foreach ($actions as $index => $act) {
            $this->insertWithCompany('audit_logs', [
                'company_id' => $this->companyId,
                'user_id' => $this->userId,
                'event' => $act['event'], // Correct column
                'auditable_type' => 'App\Models\User', // Placeholder
                'auditable_id' => $this->userId, // Placeholder
                'old_values' => null,
                'new_values' => json_encode(['description' => $act['desc']]),
                'url' => 'http://localhost/dashboard',
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 (Demo)',
                'created_at' => now()->subMinutes($index * 15),
            ]);
        }
    }
}
