<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\Warehouse;
use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Customer;
use App\Models\StockIn;
use App\Models\SalesOrder;
use App\Models\StockTransfer; 
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GeneralSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();
        if (!$company) {
            $this->command->error('Company not found. Please run UserSeeder/Phase3Seeder first or ensure Company exists.');
            return;
        }

        $this->command->info('Seeding General Domestic Data for: ' . $company->name);

        DB::transaction(function () use ($company) {
            // 1. Warehouses (Expanded Network)
            $mainWh = Warehouse::where('company_id', $company->id)->where('name', 'Main Warehouse')->first();
            if (!$mainWh) {
                 $mainWh = Warehouse::factory()->create(['company_id' => $company->id, 'name' => 'Main Warehouse', 'code' => 'WH-001']);
            }

            $branchWh = Warehouse::firstOrCreate(
                ['company_id' => $company->id, 'code' => 'WH-SBY'],
                [
                    'name' => 'Surabaya Branch',
                    'address' => 'Jl. Darmo No. 12, Surabaya',
                    'is_active' => true,
                ]
            );

            // 2. Categories
            $catElectronics = Category::firstOrCreate(['company_id' => $company->id, 'name' => 'Electronics']);
            $catFurniture = Category::firstOrCreate(['company_id' => $company->id, 'name' => 'Furniture']);

            // 3. Domestic Products
            $laptop = Product::firstOrCreate(
                ['company_id' => $company->id, 'code' => 'ELC-LPT-001'],
                [
                    'name' => 'Gaming Laptop 15"',
                    'category_id' => $catElectronics->id,
                    'unit' => 'unit',
                    'purchase_price' => 15000000,
                    'selling_price' => 20000000,
                    'min_stock' => 5,
                    'status' => true,
                ]
            );

            $chair = Product::firstOrCreate(
                ['company_id' => $company->id, 'code' => 'FUR-CHR-001'],
                [
                    'name' => 'Ergonomic Office Chair',
                    'category_id' => $catFurniture->id,
                    'unit' => 'unit',
                    'purchase_price' => 1500000,
                    'selling_price' => 2500000,
                    'min_stock' => 10,
                    'status' => true,
                ]
            );

            // 4. Local Partners
            $localSupplier = Supplier::firstOrCreate(
                ['company_id' => $company->id, 'email' => 'sales@indo-tech.com'],
                [
                    'name' => 'PT. Indo Tech Distributor',
                    'phone' => '021-555-8888',
                    'address' => 'Mangga Dua, Jakarta',
                    'contact_person' => 'Budi Santoso',
                ]
            );

            $localCustomer = Customer::firstOrCreate(
                ['company_id' => $company->id, 'email' => 'admin@startuphub.id'],
                [
                    'name' => 'Startup Hub Indonesia',
                    'phone' => '021-999-7777',
                    'address' => 'Sudirman SCBD, Jakarta',
                ]
            );

            // 5. Domestic Purchase (Stock In)
            $stockIn = StockIn::create([
                'company_id' => $company->id,
                'warehouse_id' => $mainWh->id,
                'supplier_id' => $localSupplier->id,
                'transaction_code' => 'GR-LOC-2402-001',
                'date' => Carbon::now()->subDays(10),
                'status' => 'completed',
                'total' => 0, // Will update
            ]);

            $stockIn->details()->createMany([
                [
                    'product_id' => $laptop->id,
                    'quantity' => 10,
                    'purchase_price' => 15000000,
                    'total' => 150000000,
                ],
                [
                    'product_id' => $chair->id,
                    'quantity' => 20,
                    'purchase_price' => 1500000,
                    'total' => 30000000,
                ]
            ]);
            $stockIn->update(['total' => 180000000]);

            // 6. Domestic Sales Order
            $so = SalesOrder::create([
                'company_id' => $company->id,
                'customer_id' => $localCustomer->id,
                'warehouse_id' => $mainWh->id,
                'so_number' => 'SO-LOC-2402-055',
                'order_date' => Carbon::now()->subDays(5),
                'status' => 'confirmed',
                'payment_status' => 'unpaid',
                'currency_code' => 'IDR',
                'subtotal' => 45000000,
                'tax' => 4950000, // 11%
                'total' => 49950000,
                'notes' => 'Urgent delivery for new office',
            ]);

            $so->items()->create([
                'product_id' => $chair->id,
                'quantity' => 10,
                'unit_price' => 2500000,
                'subtotal' => 25000000,
            ]);
            
            $so->items()->create([
                 'product_id' => $laptop->id,
                 'quantity' => 1,
                 'unit_price' => 20000000,
                 'subtotal' => 20000000,
            ]);

            // 7. Internal Transfer (Main -> Branch)
            // Assuming StockTransfer model exists, if not code needs check. 
            // Checking codebase for StockTransfer... 
            // If StockTransfer doesn't exist yet (Phase 4?), I will skip or check existence first.
            // For now, let's stick to Sales/Purchase which are core.
        });
        
        $this->command->info('General Domestic Data Seeded Successfully!');
    }
}
