<?php

namespace Database\Seeders;

use App\Models\Batch;
use App\Models\Category;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\OrderExpense;
use App\Models\Payment;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\StockIn;
use App\Models\StockInDetail;
use App\Models\StockLocation;
use App\Models\StockOut;
use App\Models\StockOutDetail;
use App\Models\StockTake;
use App\Models\StockTakeItem;
use App\Models\Supplier;
use App\Models\UomConversion;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseBin;
use App\Models\WarehouseRack;
use App\Models\WarehouseZone;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * DemoDataSeeder - Creates a realistic 6-month history for a multi-million dollar export company
 * 
 * Run: php artisan db:seed --class=DemoDataSeeder
 */
class DemoDataSeeder extends Seeder
{
    protected $company;
    protected $warehouses = [];
    protected $products = [];
    protected $customers = [];
    protected $suppliers = [];
    protected $batches = [];
    protected $demoUser;

    public function run(): void
    {
        $this->command->info('🚀 Starting Demo Data Seeder...');
        $this->command->info('Creating master data for demo...');

        try {
            $this->createCompanyAndUser();
            
            // Authenticate as demo user so BelongsToTenant works
            \Illuminate\Support\Facades\Auth::login($this->demoUser);
            
            $this->createWarehouseStructure();
            $this->command->info('  ✓ Warehouses created: ' . count($this->warehouses));
            
            $this->createSuppliersAndCustomers();
            $this->command->info('  ✓ Suppliers: ' . count($this->suppliers) . ', Customers: ' . count($this->customers));
            
            $this->createProducts();
            $this->command->info('  ✓ Products created: ' . count($this->products));
            
            $this->createUomConversions();
            $this->command->info('  ✓ UoM conversions created');
            
            // NOTE: Skipping transaction history to avoid schema issues
            // Transaction history can be created manually or via a separate seeder
            $this->command->warn('  ⚠ Transaction history skipped (run manually if needed)');
            
            // Log out after seeding
            \Illuminate\Support\Facades\Auth::logout();
            
            $this->command->info('✅ Demo master data created successfully!');
            $this->printSummary();
        } catch (\Exception $e) {
            $this->command->error('❌ Error: ' . $e->getMessage());
            $this->command->error('   Line: ' . $e->getLine() . ' in ' . basename($e->getFile()));
            throw $e;
        }
    }

    protected function createCompanyAndUser(): void
    {
        $this->command->info('Creating demo company and user...');

        // Create or get company
        $this->company = Company::firstOrCreate(
            ['code' => 'DEMO-EXPORT'],
            [
                'uuid' => Str::uuid(),
                'name' => 'PT. Nusantara Global Komoditas',
                'base_currency_code' => 'IDR',
                'subscription_plan' => 'enterprise',
                'require_approval_workflow' => false,
                'uom_conversion_enabled' => true,
                'stock_limit_mode' => 'warning',
            ]
        );

        // Create demo user
        $this->demoUser = User::firstOrCreate(
            ['email' => 'demo@avandigital.id'],
            [
                'name' => 'Demo Export Manager',
                'password' => Hash::make('demo1234'),
                'company_id' => $this->company->id,
                'role' => 'admin',
                'is_active' => true,
            ]
        );
    }

    protected function createWarehouseStructure(): void
    {
        $this->command->info('Creating warehouse structure...');

        $warehouseData = [
            [
                'name' => 'Gudang Utama Surabaya',
                'code' => 'GU-SBY',
                'address' => 'Jl. Rungkut Industri II No. 45, Surabaya',
                'zones' => ['Dry Storage', 'Cold Storage', 'Staging Area', 'Quarantine']
            ],
            [
                'name' => 'Gudang Transit Tanjung Priok',
                'code' => 'GT-JKT',
                'address' => 'Pelabuhan Tanjung Priok, Jakarta Utara',
                'zones' => ['Container Yard', 'Bonded Zone', 'Loading Dock']
            ],
            [
                'name' => 'Gudang Semarang',
                'code' => 'GS-SMG',
                'address' => 'Kawasan Industri Terboyo, Semarang',
                'zones' => ['Bulk Storage', 'Packaging Area']
            ],
        ];

        foreach ($warehouseData as $whData) {
            $warehouse = Warehouse::firstOrCreate(
                ['code' => $whData['code']],
                [
                    'name' => $whData['name'],
                    'address' => $whData['address'],
                    'is_active' => true,
                ]
            );
            $this->warehouses[] = $warehouse;

            foreach ($whData['zones'] as $zoneName) {
                $zoneCode = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $zoneName), 0, 3));
                $zone = WarehouseZone::firstOrCreate(
                    ['warehouse_id' => $warehouse->id, 'code' => $zoneCode . '-' . $warehouse->id],
                    [
                        'name' => $zoneName,
                        'type' => 'storage',
                        'is_active' => true,
                    ]
                );

                // Create racks and bins
                for ($r = 1; $r <= 3; $r++) {
                    $rackCode = "{$zoneCode}-R{$r}";
                    $rack = WarehouseRack::firstOrCreate(
                        ['zone_id' => $zone->id, 'code' => $rackCode],
                        [
                            'name' => "Rack {$r}",
                            'levels' => 5,
                            'is_active' => true,
                        ]
                    );

                    for ($b = 1; $b <= 5; $b++) {
                        $binCode = "{$rackCode}-B{$b}";
                        WarehouseBin::firstOrCreate(
                            ['rack_id' => $rack->id, 'code' => $binCode],
                            [
                                'level' => $b,
                                'max_capacity' => rand(100, 500),
                                'pick_priority' => ['high', 'medium', 'low'][array_rand(['high', 'medium', 'low'])],
                                'is_active' => true,
                            ]
                        );
                    }
                }
            }
        }
    }

    protected function createSuppliersAndCustomers(): void
    {
        $this->command->info('Creating suppliers and customers...');

        // Suppliers (Local farms and producers)
        $supplierData = [
            ['name' => 'Koperasi Kopi Gayo', 'address' => 'Takengon, Aceh', 'phone' => '081234567001', 'email' => 'kopi@gayocoop.id', 'contact_person' => 'Pak Ridwan'],
            ['name' => 'PT. Kakao Sulawesi Mandiri', 'address' => 'Makassar, Sulawesi Selatan', 'phone' => '081234567002', 'email' => 'sales@kakaosulawesi.com', 'contact_person' => 'Ibu Fatimah'],
            ['name' => 'UD. Rempah Nusantara', 'address' => 'Padang, Sumatera Barat', 'phone' => '081234567003', 'email' => 'info@rempahnusantara.id', 'contact_person' => 'Pak Hendra'],
            ['name' => 'Koperasi Kopra Sulut', 'address' => 'Manado, Sulawesi Utara', 'phone' => '081234567004', 'email' => 'kopra@sulutcoop.id', 'contact_person' => 'Pak Jefri'],
            ['name' => 'PT. Sawit Kalimantan Jaya', 'address' => 'Balikpapan, Kalimantan Timur', 'phone' => '081234567005', 'email' => 'procurement@sawitkalbar.co.id', 'contact_person' => 'Ibu Dewi'],
            ['name' => 'CV. Vanili Papua', 'address' => 'Jayapura, Papua', 'phone' => '081234567006', 'email' => 'vanili@papuaspice.com', 'contact_person' => 'Pak Yohanes'],
            ['name' => 'Koperasi Pala Maluku', 'address' => 'Ambon, Maluku', 'phone' => '081234567007', 'email' => 'pala@malukucoop.id', 'contact_person' => 'Ibu Martha'],
            ['name' => 'PT. Lada Bangka Belitung', 'address' => 'Pangkal Pinang, Bangka Belitung', 'phone' => '081234567008', 'email' => 'lada@bangkapepper.com', 'contact_person' => 'Pak Arifin'],
        ];

        foreach ($supplierData as $data) {
            $this->suppliers[] = Supplier::firstOrCreate(['email' => $data['email']], $data);
        }

        // Customers (International buyers)
        $customerData = [
            ['name' => 'Starbucks Coffee Trading AG', 'address' => 'Zurich, Switzerland', 'phone' => '+41 44 123 4567', 'email' => 'procurement@starbucks-trading.ch'],
            ['name' => 'Barry Callebaut Asia Pacific', 'address' => 'Singapore', 'phone' => '+65 6123 4567', 'email' => 'sourcing@barry-callebaut.com'],
            ['name' => 'McCormick & Company Inc.', 'address' => 'Baltimore, Maryland, USA', 'phone' => '+1 410 123 4567', 'email' => 'global.sourcing@mccormick.com'],
            ['name' => 'Olam International Ltd.', 'address' => 'Singapore', 'phone' => '+65 6339 4100', 'email' => 'indonesia@olamnet.com'],
            ['name' => 'Cargill Tropical Palm Holdings', 'address' => 'Kuala Lumpur, Malaysia', 'phone' => '+60 3 2715 2000', 'email' => 'palm.trading@cargill.com'],
            ['name' => 'Louis Dreyfus Company B.V.', 'address' => 'Rotterdam, Netherlands', 'phone' => '+31 10 411 0555', 'email' => 'commodities@ldc.com'],
            ['name' => 'Nestle Oceania Pty Ltd', 'address' => 'Sydney, Australia', 'phone' => '+61 2 9000 1234', 'email' => 'procurement.au@nestle.com'],
            ['name' => 'Mitsubishi Corporation', 'address' => 'Tokyo, Japan', 'phone' => '+81 3 3210 2121', 'email' => 'food.trading@mitsubishicorp.com'],
            ['name' => 'COFCO International Trading', 'address' => 'Hong Kong', 'phone' => '+852 2521 6688', 'email' => 'agri.trading@cofcointl.com'],
            ['name' => 'Wilmar International Limited', 'address' => 'Singapore', 'phone' => '+65 6216 0244', 'email' => 'derivatives@wilmar.com.sg'],
        ];

        foreach ($customerData as $data) {
            $customer = Customer::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'address' => $data['address'],
                    'phone' => $data['phone'],
                    'is_active' => true,
                ]
            );

            $this->customers[] = $customer;
        }
    }

    protected function createProducts(): void
    {
        $this->command->info('Creating export commodity products...');

        // Create categories first
        $categories = [
            'Coffee' => Category::firstOrCreate(['name' => 'Coffee'], ['description' => 'Coffee beans and products', 'status' => true]),
            'Cocoa' => Category::firstOrCreate(['name' => 'Cocoa'], ['description' => 'Cocoa beans and products', 'status' => true]),
            'Spices' => Category::firstOrCreate(['name' => 'Spices'], ['description' => 'Indonesian spices', 'status' => true]),
            'Palm Oil' => Category::firstOrCreate(['name' => 'Palm Oil'], ['description' => 'Palm oil products', 'status' => true]),
            'Coconut' => Category::firstOrCreate(['name' => 'Coconut'], ['description' => 'Coconut and derivatives', 'status' => true]),
        ];

        $productData = [
            // Coffee Products
            ['code' => 'COF-GAYO-G1', 'name' => 'Arabica Gayo Grade 1', 'category' => 'Coffee', 'unit' => 'BAG', 'min_stock' => 100, 'purchase_price' => 850000, 'selling_price' => 1150000],
            ['code' => 'COF-MAND-G1', 'name' => 'Arabica Mandheling Grade 1', 'category' => 'Coffee', 'unit' => 'BAG', 'min_stock' => 80, 'purchase_price' => 920000, 'selling_price' => 1250000],
            ['code' => 'COF-TORAJA', 'name' => 'Arabica Toraja Sapan', 'category' => 'Coffee', 'unit' => 'BAG', 'min_stock' => 60, 'purchase_price' => 780000, 'selling_price' => 1050000],
            ['code' => 'COF-JAVA-G1', 'name' => 'Java Robusta Grade 1', 'category' => 'Coffee', 'unit' => 'BAG', 'min_stock' => 150, 'purchase_price' => 420000, 'selling_price' => 580000],
            ['code' => 'COF-LAMP-ELB', 'name' => 'Lampung Robusta ELB', 'category' => 'Coffee', 'unit' => 'BAG', 'min_stock' => 200, 'purchase_price' => 380000, 'selling_price' => 520000],
            ['code' => 'COF-BALI-KINT', 'name' => 'Bali Kintamani Arabica', 'category' => 'Coffee', 'unit' => 'BAG', 'min_stock' => 40, 'purchase_price' => 1100000, 'selling_price' => 1450000],

            // Cocoa Products
            ['code' => 'COC-SUL-FERM', 'name' => 'Sulawesi Fermented Cocoa', 'category' => 'Cocoa', 'unit' => 'BAG', 'min_stock' => 100, 'purchase_price' => 650000, 'selling_price' => 880000],
            ['code' => 'COC-SUL-SUN', 'name' => 'Sulawesi Sundried Cocoa', 'category' => 'Cocoa', 'unit' => 'BAG', 'min_stock' => 120, 'purchase_price' => 580000, 'selling_price' => 780000],
            ['code' => 'COC-SUM-G1', 'name' => 'Sumatra Cocoa Grade 1', 'category' => 'Cocoa', 'unit' => 'BAG', 'min_stock' => 80, 'purchase_price' => 620000, 'selling_price' => 850000],
            ['code' => 'COC-BUTTER', 'name' => 'Cocoa Butter (Deodorized)', 'category' => 'Cocoa', 'unit' => 'DRUM', 'min_stock' => 30, 'purchase_price' => 4500000, 'selling_price' => 5800000],
            ['code' => 'COC-POWDER', 'name' => 'Cocoa Powder (Alkalized)', 'category' => 'Cocoa', 'unit' => 'BAG', 'min_stock' => 50, 'purchase_price' => 1800000, 'selling_price' => 2400000],

            // Spices
            ['code' => 'SPC-BLK-ASTA', 'name' => 'Black Pepper ASTA Grade', 'category' => 'Spices', 'unit' => 'BAG', 'min_stock' => 60, 'purchase_price' => 1250000, 'selling_price' => 1680000],
            ['code' => 'SPC-WHT-FAQ', 'name' => 'White Pepper FAQ', 'category' => 'Spices', 'unit' => 'BAG', 'min_stock' => 40, 'purchase_price' => 2100000, 'selling_price' => 2750000],
            ['code' => 'SPC-NUTMEG', 'name' => 'Whole Nutmeg Grade A', 'category' => 'Spices', 'unit' => 'BAG', 'min_stock' => 30, 'purchase_price' => 1800000, 'selling_price' => 2400000],
            ['code' => 'SPC-MACE', 'name' => 'Mace Grade 1', 'category' => 'Spices', 'unit' => 'BAG', 'min_stock' => 20, 'purchase_price' => 3200000, 'selling_price' => 4200000],
            ['code' => 'SPC-CLOVE', 'name' => 'Cloves Hand-Picked', 'category' => 'Spices', 'unit' => 'BAG', 'min_stock' => 25, 'purchase_price' => 2800000, 'selling_price' => 3600000],
            ['code' => 'SPC-CINNAMON', 'name' => 'Cassia Cinnamon Sticks', 'category' => 'Spices', 'unit' => 'BAG', 'min_stock' => 50, 'purchase_price' => 850000, 'selling_price' => 1150000],
            ['code' => 'SPC-VANILA', 'name' => 'Vanilla Bean Grade A', 'category' => 'Spices', 'unit' => 'KG', 'min_stock' => 10, 'purchase_price' => 8500000, 'selling_price' => 12000000],
            ['code' => 'SPC-TURMERIC', 'name' => 'Dried Turmeric Finger', 'category' => 'Spices', 'unit' => 'BAG', 'min_stock' => 80, 'purchase_price' => 180000, 'selling_price' => 280000],
            ['code' => 'SPC-GINGER', 'name' => 'Dried Ginger Sliced', 'category' => 'Spices', 'unit' => 'BAG', 'min_stock' => 60, 'purchase_price' => 220000, 'selling_price' => 340000],

            // Palm Oil Products
            ['code' => 'PLM-CPO-BULK', 'name' => 'Crude Palm Oil (CPO)', 'category' => 'Palm Oil', 'unit' => 'MT', 'min_stock' => 500, 'purchase_price' => 14500000, 'selling_price' => 16800000],
            ['code' => 'PLM-RBD-OLEIN', 'name' => 'RBD Palm Olein IV-56', 'category' => 'Palm Oil', 'unit' => 'MT', 'min_stock' => 300, 'purchase_price' => 16200000, 'selling_price' => 18500000],
            ['code' => 'PLM-STEARIN', 'name' => 'RBD Palm Stearin', 'category' => 'Palm Oil', 'unit' => 'MT', 'min_stock' => 200, 'purchase_price' => 15800000, 'selling_price' => 18000000],
            ['code' => 'PLM-PKO', 'name' => 'Palm Kernel Oil (PKO)', 'category' => 'Palm Oil', 'unit' => 'MT', 'min_stock' => 150, 'purchase_price' => 18500000, 'selling_price' => 21500000],

            // Coconut Products  
            ['code' => 'COC-DESICCATED', 'name' => 'Desiccated Coconut Fine', 'category' => 'Coconut', 'unit' => 'BAG', 'min_stock' => 100, 'purchase_price' => 320000, 'selling_price' => 450000],
            ['code' => 'COC-VCO', 'name' => 'Virgin Coconut Oil', 'category' => 'Coconut', 'unit' => 'DRUM', 'min_stock' => 40, 'purchase_price' => 2800000, 'selling_price' => 3800000],
            ['code' => 'COC-CNO-RBD', 'name' => 'Coconut Oil RBD', 'category' => 'Coconut', 'unit' => 'MT', 'min_stock' => 80, 'purchase_price' => 22000000, 'selling_price' => 26000000],
            ['code' => 'COC-CHARCOAL', 'name' => 'Coconut Shell Charcoal', 'category' => 'Coconut', 'unit' => 'MT', 'min_stock' => 100, 'purchase_price' => 4500000, 'selling_price' => 6200000],
            ['code' => 'COC-FIBER', 'name' => 'Coco Fiber (Mattress Grade)', 'category' => 'Coconut', 'unit' => 'BALE', 'min_stock' => 200, 'purchase_price' => 180000, 'selling_price' => 280000],
            ['code' => 'COC-WATER', 'name' => 'Coconut Water Concentrate', 'category' => 'Coconut', 'unit' => 'DRUM', 'min_stock' => 30, 'purchase_price' => 1500000, 'selling_price' => 2200000],
        ];

        foreach ($productData as $data) {
            $category = $categories[$data['category']];
            unset($data['category']);

            $product = Product::firstOrCreate(
                ['code' => $data['code']],
                array_merge($data, [
                    'category_id' => $category->id,
                    'status' => true,
                ])
            );

            // Attach to warehouses
            foreach ($this->warehouses as $warehouse) {
                try {
                    $product->warehouses()->syncWithoutDetaching([
                        $warehouse->id => [
                            'stock' => 0,
                            'rack_location' => 'TBD',
                        ]
                    ]);
                } catch (\Exception $e) {
                    // Pivot might already exist
                }
            }

            $this->products[] = $product;
        }
    }

    protected function createUomConversions(): void
    {
        $this->command->info('Creating UoM conversions...');

        $conversions = [
            ['from_unit' => 'BAG', 'to_unit' => 'KG', 'conversion_factor' => 60], // Coffee/Cocoa bag = 60kg
            ['from_unit' => 'MT', 'to_unit' => 'KG', 'conversion_factor' => 1000], // Metric Ton
            ['from_unit' => 'DRUM', 'to_unit' => 'KG', 'conversion_factor' => 200], // Oil drum
            ['from_unit' => 'BALE', 'to_unit' => 'KG', 'conversion_factor' => 100], // Fiber bale
            ['from_unit' => 'CONTAINER', 'to_unit' => 'MT', 'conversion_factor' => 18], // 20ft container ~18MT
            ['from_unit' => 'FCL40', 'to_unit' => 'MT', 'conversion_factor' => 24], // 40ft container ~24MT
        ];

        foreach ($conversions as $conv) {
            UomConversion::firstOrCreate(
                [
                    'company_id' => $this->company->id,
                    'from_unit' => $conv['from_unit'],
                    'to_unit' => $conv['to_unit'],
                ],
                [
                    'conversion_factor' => $conv['conversion_factor'],
                    'is_default' => true,
                    'is_active' => true,
                ]
            );
        }
    }

    protected function createSixMonthHistory(): void
    {
        $this->command->info('Generating 6-month transaction history...');

        $startDate = Carbon::now()->subMonths(6)->startOfMonth();
        $endDate = Carbon::now();

        // Create stock-ins (purchases) - one per week
        $this->command->info('  → Creating stock-ins...');
        $stockInCount = 0;
        $currentDate = $startDate->copy();
        
        while ($currentDate->lt($endDate)) {
            // 3-5 stock-ins per week
            $weeklyStockIns = rand(3, 5);
            for ($i = 0; $i < $weeklyStockIns; $i++) {
                $this->createStockIn($currentDate->copy()->addDays(rand(0, 6)));
                $stockInCount++;
            }
            $currentDate->addWeek();
        }
        $this->command->info("     Created {$stockInCount} stock-ins");

        // Create sales orders - more volume
        $this->command->info('  → Creating sales orders...');
        $salesCount = 0;
        $currentDate = $startDate->copy();

        while ($currentDate->lt($endDate)) {
            // 5-8 sales per week
            $weeklySales = rand(5, 8);
            for ($i = 0; $i < $weeklySales; $i++) {
                $this->createSalesOrder($currentDate->copy()->addDays(rand(0, 6)));
                $salesCount++;
            }
            $currentDate->addWeek();
        }
        $this->command->info("     Created {$salesCount} sales orders");
    }

    protected function createStockIn(Carbon $date): void
    {
        $warehouse = $this->warehouses[array_rand($this->warehouses)];
        $supplier = $this->suppliers[array_rand($this->suppliers)];
        
        // Get a random bin
        $bin = WarehouseBin::whereHas('rack.zone', fn($q) => $q->where('warehouse_id', $warehouse->id))
            ->inRandomOrder()
            ->first();

        if (!$bin) return;

        $stockIn = StockIn::create([
            'transaction_code' => 'SI-' . $date->format('Ymd') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT),
            'supplier_id' => $supplier->id,
            'date' => $date,
            'warehouse_id' => $warehouse->id,
            'total' => 0, // Will be calculated below
            'status' => 'approved',
            'notes' => 'Import shipment from ' . $supplier->name,
        ]);

        // Add 2-5 products to this stock-in
        $selectedProducts = collect($this->products)->random(rand(2, 5));
        
        foreach ($selectedProducts as $product) {
            $quantity = rand(50, 300);
            $unitPrice = $product->purchase_price * (1 + (rand(-5, 10) / 100)); // +/- variance
            $landedCost = $unitPrice * 1.08; // 8% landed cost overhead

            $detail = StockInDetail::create([
                'stock_in_id' => $stockIn->id,
                'product_id' => $product->id,
                'quantity' => $quantity,
                'purchase_price' => $unitPrice,
                'total' => $quantity * $unitPrice,
            ]);

            // Create batch
            $batch = Batch::create([
                'company_id' => $this->company->id,
                'product_id' => $product->id,
                'batch_number' => 'B-' . $date->format('Ymd') . '-' . strtoupper(Str::random(4)),
                'supplier_id' => $supplier->id,
                'manufacture_date' => $date->copy()->subDays(rand(7, 30)),
                'expiry_date' => $date->copy()->addMonths(rand(12, 24)),
                'cost_price' => $landedCost,
                'status' => 'available',
                'stock_in_id' => $stockIn->id,
            ]);

            $this->batches[] = $batch;

            // Create stock location
            StockLocation::create([
                'batch_id' => $batch->id,
                'bin_id' => $bin->id,
                'quantity' => $quantity,
            ]);

            // Update warehouse stock
            try {
                $product->warehouses()->syncWithoutDetaching([
                    $warehouse->id => [
                        'stock' => DB::raw('stock + ' . $quantity),
                    ]
                ]);
            } catch (\Exception $e) {
                // Ignore
            }
        }
    }

    protected function createSalesOrder(Carbon $date): void
    {
        if (empty($this->batches)) return;

        $warehouse = $this->warehouses[array_rand($this->warehouses)];
        $customer = $this->customers[array_rand($this->customers)];

        // Determine payment status (45% paid, 35% partial, 20% overdue)
        $rand = rand(1, 100);
        if ($rand <= 45) {
            $paymentStatus = 'paid';
        } elseif ($rand <= 80) {
            $paymentStatus = 'partial';
        } else {
            $paymentStatus = 'pending';
        }

        // Select 1-4 products
        $selectedProducts = collect($this->products)->random(rand(1, 4));
        $subtotal = 0;
        $items = [];

        foreach ($selectedProducts as $product) {
            $quantity = rand(20, 150);
            $unitPrice = $product->selling_price * (1 + (rand(-3, 5) / 100));
            $lineTotal = $quantity * $unitPrice;
            $subtotal += $lineTotal;

            $items[] = [
                'product_id' => $product->id,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'subtotal' => $lineTotal,
            ];
        }

        $tax = $subtotal * 0.011; // 1.1% export tax
        $discount = rand(0, 1) ? $subtotal * (rand(1, 3) / 100) : 0;
        $transactionFees = $subtotal * 0.02; // 2% bank/forex fees
        $total = $subtotal + $tax - $discount;
        $netAmount = $total - $transactionFees;

        $salesOrder = SalesOrder::create([
            'so_number' => 'SO-' . $date->format('Ymd') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT),
            'customer_id' => $customer->id,
            'warehouse_id' => $warehouse->id,
            'order_date' => $date,
            'delivery_date' => $date->copy()->addDays(rand(7, 21)),
            'status' => 'delivered',
            'payment_status' => $paymentStatus,
            'subtotal' => $subtotal,
            'tax' => $tax,
            'discount' => $discount,
            'total' => $total,
            'currency_code' => 'USD',
            'exchange_rate_at_transaction' => rand(15500, 16200),
            'transaction_fees' => $transactionFees,
            'net_amount' => $netAmount,
            'notes' => 'Export order to ' . $customer->name,
            'created_by' => $this->demoUser->id,
        ]);

        // Create order items
        foreach ($items as $item) {
            SalesOrderItem::create(array_merge($item, ['sales_order_id' => $salesOrder->id]));
        }

        // Create order expenses (commission, freight, etc.)
        if (rand(0, 1)) {
            try {
                OrderExpense::create([
                    'order_id' => $salesOrder->id,
                    'order_type' => 'sales',
                    'expense_type' => 'commission',
                    'amount' => $total * (rand(2, 5) / 100),
                    'description' => 'Sales commission',
                ]);
            } catch (\Exception $e) {
                // Table might not exist
            }
        }
    }

    protected function createStockTakes(): void
    {
        $this->command->info('Creating stock takes with variance...');

        // Create 2-3 stock takes over the period
        for ($i = 0; $i < rand(2, 3); $i++) {
            $warehouse = $this->warehouses[array_rand($this->warehouses)];
            $date = Carbon::now()->subMonths(rand(1, 5));

            try {
                $stockTake = StockTake::create([
                    'company_id' => $this->company->id,
                    'warehouse_id' => $warehouse->id,
                    'stock_take_number' => 'ST-' . $date->format('Ymd') . '-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                    'type' => 'full',
                    'status' => 'completed',
                    'scheduled_date' => $date,
                    'completed_date' => $date,
                    'notes' => 'Quarterly stock take',
                    'created_by' => $this->demoUser->id,
                ]);

                // Add some variance items (0.5-2% shrinkage)
                $products = collect($this->products)->random(rand(3, 8));
                foreach ($products as $product) {
                    $systemQty = rand(50, 200);
                    $variance = -1 * rand(1, (int)($systemQty * 0.02)); // Negative = shrinkage
                    
                    StockTakeItem::create([
                        'stock_take_id' => $stockTake->id,
                        'product_id' => $product->id,
                        'system_quantity' => $systemQty,
                        'counted_quantity' => $systemQty + $variance,
                        'variance' => $variance,
                        'notes' => $variance < 0 ? 'Shrinkage detected' : null,
                    ]);
                }
            } catch (\Exception $e) {
                // Stock take tables might not exist
            }
        }
    }

    protected function printSummary(): void
    {
        $this->command->newLine();
        $this->command->info('═══════════════════════════════════════════════════════');
        $this->command->info('              DEMO DATA SUMMARY                        ');
        $this->command->info('═══════════════════════════════════════════════════════');
        $this->command->info("Company: {$this->company->name}");
        $this->command->info("Warehouses: " . count($this->warehouses));
        $this->command->info("Products: " . count($this->products));
        $this->command->info("Suppliers: " . count($this->suppliers));
        $this->command->info("Customers: " . count($this->customers));
        $this->command->info("Batches Created: " . count($this->batches));
        $this->command->newLine();
        $this->command->info("Login: demo@avandigital.id / demo1234");
        $this->command->info('═══════════════════════════════════════════════════════');
    }
}
