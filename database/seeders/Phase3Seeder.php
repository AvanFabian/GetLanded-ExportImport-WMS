<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Company;
use App\Models\Supplier;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\InboundShipment;
use App\Models\OutboundShipment;
use App\Models\StockIn;
use App\Models\StockOut;
use App\Models\SupplierPayment;
use App\Models\ShipmentExpense;
use App\Models\CustomsDeclaration;
use App\Models\SalesOrder;
use App\Models\FtaScheme;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Phase3Seeder extends Seeder
{
    public function run(): void
    {
        // Get the first company (assuming single tenant for pilot)
        $company = Company::first() ?? Company::factory()->create(['name' => 'Pilot Company']);
        $warehouse = Warehouse::where('company_id', $company->id)->first() ?? Warehouse::factory()->create(['company_id' => $company->id, 'name' => 'Main Warehouse']);
        $user = User::where('company_id', $company->id)->first(); // Ensure we have a user context if needed, though models use created_by

        $this->command->info('Seeding Phase 3 Pilot Data for Company: ' . $company->name);

        try {
            DB::transaction(function () use ($company, $warehouse) {
                // ... (existing code) ...
                
                // 1. Create Suppliers (Import & Local)
                $intlSupplier = Supplier::firstOrCreate(
                    ['company_id' => $company->id, 'email' => 'sales@globalmachines.com'],
                    [
                        'name' => 'Global Machines Ltd (China)',
                        'phone' => '+86 21 1234 5678',
                        'address' => '123 Industrial Park, Shanghai, China',
                    ]
                );

                // 2. Create Customers (Export)
                $intlCustomer = Customer::firstOrCreate(
                    ['company_id' => $company->id, 'email' => 'buyer@usacoffee.com'],
                    [
                        'name' => 'USA Coffee Importers Inc',
                        'phone' => '+1 555 0199',
                        'address' => '456 Market St, San Francisco, USA',
                    ]
                );

                // 3. Create Products
                // Import Product
                $valveProduct = Product::firstOrCreate(
                    ['company_id' => $company->id, 'code' => 'VLV-IND-001'],
                    [
                        'name' => 'Industrial Hydraulic Valve',
                        'description' => 'High pressure hydraulic valve for heavy machinery',
                        'category_id' => null, // Optional
                        'unit' => 'unit',
                        'net_weight' => 5.5, // kg
                        'cbm_volume' => 0.02, // cbm
                        'hs_code' => '8481.80.50', // Matching seeded HS code
                        'purchase_price' => 150.00, // USD usually, but system might base in IDR. Let's assume converted 2,400,000
                        'selling_price' => 3500000,
                        'status' => true,
                    ]
                );

                // Export Product
                $coffeeProduct = Product::firstOrCreate(
                    ['company_id' => $company->id, 'code' => 'COF-ARA-001'],
                    [
                        'name' => 'Arabica Coffee Beans (Green)',
                        'description' => 'Premium Grade A Arabica Green Beans',
                        'unit' => 'kg',
                        'net_weight' => 1.0,
                        'cbm_volume' => 0.002,
                        'hs_code' => '0901.11.10', // Matching seeded HS code
                        'purchase_price' => 85000,
                        'selling_price' => 125000,
                        'status' => true,
                    ]
                );

                // 4. FTA Scheme
                $acfta = FtaScheme::firstOrCreate(
                    ['company_id' => $company->id, 'name' => 'ACFTA'],
                    [
                        'description' => 'ASEAN-China Free Trade Area',
                        'member_countries' => ['CN', 'ID', 'MY', 'SG', 'TH', 'VN', 'PH', 'BN', 'KH', 'LA', 'MM'],
                        'is_active' => true,
                    ]
                );
                
                // Add preferential rate for the valve (0% instead of standard 15%)
                $acfta->rates()->updateOrCreate(
                    ['hs_code' => '8481.80.50'],
                    ['rate' => 0.00]
                );

                // 5. IMPORT FLOW: Inbound Shipment + Customs + Payment
                $this->command->info('Creating Import Scenario...');
                
                $inboundShipment = InboundShipment::create([
                    'company_id' => $company->id,
                    // 'supplier_id' => $intlSupplier->id, // Not in schema
                    'reference_number' => 'PO-IMP-2024-001',
                    'status' => 'arrived', // shipment_status -> status
                    'eta' => Carbon::now()->subDays(2),
                    'actual_arrival_date' => Carbon::now()->subDays(1), // arrival_date -> actual_arrival_date
                    'carrier_name' => 'Maersk Line', // carrier -> carrier_name
                    'vessel_flight_number' => 'Maersk Sentosa', // vessel_name -> vessel_flight_number
                    // 'bill_of_lading' => 'MAEU123456789', // Not in schema (reference_number used) or maybe add to notes
                    'origin_port' => 'Shanghai, CN', // origin_country -> origin_port
                    'destination_port' => 'IDJKT',
                    'shipment_number' => 'SHP-202602-0001', // Added required field
                ]);

                // Stock In
                $stockIn = StockIn::create([
                    'company_id' => $company->id,
                    'warehouse_id' => $warehouse->id,
                    'supplier_id' => $intlSupplier->id,
                    // 'inbound_shipment_id' => $inboundShipment->id, // Column does not exist
                    'transaction_code' => 'GR-2402-001', // Changed from reference
                    'status' => 'completed',
                    'date' => Carbon::now()->subDays(1), // Changed from received_at
                    'total' => 240000000,
                ]);
                
                // Stock In Details (100 valves)
                $stockIn->details()->create([
                    'product_id' => $valveProduct->id,
                    'quantity' => 100, // Changed from quantity_ordered/received
                    'purchase_price' => 2400000, // Changed from unit_cost (~150 USD)
                    'total' => 240000000,
                ]);

                // Supplier Payment (Letter of Credit)
                SupplierPayment::create([
                    'company_id' => $company->id,
                    'supplier_id' => $intlSupplier->id,
                    'stock_in_id' => $stockIn->id,
                    'amount_owed' => 15000.00, // 100 * 150 USD
                    'amount_paid' => 0,
                    'currency_code' => 'USD',
                    'payment_status' => 'unpaid',
                    'due_date' => Carbon::now()->addDays(30),
                    'payment_method' => 'letter_of_credit',
                    'lc_number' => 'LC-BK-2024-888',
                    'lc_issuing_bank' => 'Bank Mandiri',
                    'lc_expiry_date' => Carbon::now()->addMonths(3),
                    'payment_notes' => 'LC opened, awaiting documents.'
                ]);

                // Import Customs Declaration
                $cifValue = 240000000; // 100 * 2.400.000 IDR
                $bmRate = 0; // FTA rate
                $ppnRate = 11;
                $pphRate = 2.5;
                
                // Calculate duties
                $bmAmount = $cifValue * ($bmRate / 100);
                $baseVat = $cifValue + $bmAmount;
                $ppnAmount = $baseVat * ($ppnRate / 100);
                $pphAmount = $baseVat * ($pphRate / 100);
                $totalTax = $bmAmount + $ppnAmount + $pphAmount;

                CustomsDeclaration::create([
                    'company_id' => $company->id,
                    'declaration_number' => 'PIB-000123',
                    'declaration_date' => Carbon::now()->subDays(2),
                    'declaration_type' => 'import',
                    'customs_office' => 'KPU Tanjung Priok',
                    // 'inbound_shipment_id' => $inboundShipment->id, // Column does not exist yet
                    'declared_value' => $cifValue,
                    'currency_code' => 'IDR',
                    'duty_rate' => $bmRate,
                    'duty_amount' => $bmAmount,
                    'vat_rate' => $ppnRate,
                    'vat_amount' => $ppnAmount,
                    'pph_rate' => $pphRate,
                    'pph_amount' => $pphAmount,
                    'total_tax' => $totalTax, // total_tax_paid -> total_tax
                    'status' => 'released',
                    'notes' => 'Used ACFTA-E scheme for 0% BM.',
                    'fta_scheme' => 'ACFTA'
                ]);

                // 6. EXPORT FLOW: Outbound Shipment + Docs
                $this->command->info('Creating Export Scenario...');

                $so = SalesOrder::create([
                    'company_id' => $company->id,
                    'customer_id' => $intlCustomer->id,
                    'warehouse_id' => $warehouse->id,
                    'so_number' => 'SO-EXP-2024-005',
                    'order_date' => Carbon::now()->subDays(5),
                    'status' => 'confirmed',
                    'currency_code' => 'USD',
                    'total' => 25000.00, // total_amount -> total
                    'notes' => 'Payment Terms: T/T 50% Advance', // Moved to notes
                ]);

                $so->items()->create([
                    'product_id' => $coffeeProduct->id,
                    'quantity' => 2000, // 2 tons
                    'unit_price' => 12.50, // USD
                    'subtotal' => 25000.00,
                ]);

                $outboundShipment = OutboundShipment::create([
                    'company_id' => $company->id,
                    'sales_order_id' => $so->id,
                    'shipment_number' => 'SHP-EXP-2024-005',
                    'status' => 'draft', // processing -> draft (schema default options)
                    'shipment_date' => Carbon::now()->addDays(2), // scheduled_date -> shipment_date
                    'carrier_name' => 'Evergreen', // carrier -> carrier_name
                    // 'service_type' => 'FCL', // Not in schema
                    'bill_of_lading' => 'EGLV123456', // tracking_number -> bill_of_lading
                    // 'shipping_address' => $intlCustomer->address, // Not in schema
                    'notes' => 'Handle with care, keep dry. Ship to: ' . $intlCustomer->address, // shipping_notes -> notes
                ]);

                // Add Export Expenses
                $outboundShipment->expenses()->createMany([
                    [
                        // 'company_id' => $company->id, // Not in schema
                        'name' => 'Freight Cost', // expense_type -> name
                        'amount' => 1200.00,
                        'currency_code' => 'USD',
                        // 'vendor_name' => 'Evergreen Marine', // Not in schema
                        'notes' => 'Ocean Freight to US West Coast (Vendor: Evergreen Marine)', // combined description and vendor
                        'allocation_method' => 'volume', // Added default
                    ],
                    [
                        // 'company_id' => $company->id,
                        'name' => 'Handling Charges',
                        'amount' => 1500000,
                        'currency_code' => 'IDR',
                        // 'vendor_name' => 'Local Trucking Co',
                        'notes' => 'Trucking Warehouse to Port (Vendor: Local Trucking Co)',
                        'allocation_method' => 'quantity', // Added default
                    ]
                ]);

                 // Export Customs Declaration (PEB)
                 CustomsDeclaration::create([
                    'company_id' => $company->id,
                    'declaration_number' => 'PEB-000987',
                    'declaration_date' => Carbon::now(),
                    'declaration_type' => 'export',
                    'customs_office' => 'KPU Tanjung Priok',
                    'outbound_shipment_id' => $outboundShipment->id,
                    'declared_value' => 25000.00, // USD
                    'currency_code' => 'USD',
                    'duty_rate' => 0, // Export duty usually 0 for coffee
                    'duty_amount' => 0,
                    'vat_rate' => 0, // VAT exempt for export
                    'vat_amount' => 0,
                    'total_tax' => 0, // total_tax_paid -> total_tax
                    'status' => 'submitted',
                    'notes' => 'Export check complete.',
                ]);
            });
            $this->command->info('Phase 3 Pilot Data Seeded Successfully!');
        } catch (\Exception $e) {
            $this->command->error('Seeder Failed. Check log for details.');
            file_put_contents(storage_path('logs/seeder_error.txt'), $e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }

        $this->command->info('Phase 3 Pilot Data Seeded Successfully!');
    }
}
