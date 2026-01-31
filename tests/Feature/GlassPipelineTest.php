<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Company;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetail;
use App\Models\InboundShipment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GlassPipelineTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $company;
    protected $warehouse;
    protected $supplier;
    protected $productA;
    protected $productB;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->company = Company::factory()->create();
        $this->user = User::factory()->create(['company_id' => $this->company->id]);
        $this->warehouse = Warehouse::factory()->create(['company_id' => $this->company->id]);
        $this->supplier = Supplier::factory()->create(['company_id' => $this->company->id]);
        
        $this->productA = Product::factory()->create([
            'company_id' => $this->company->id,
            'name' => 'High Value Item',
            'sku' => 'SKU-HIGH'
        ]);
        
        $this->productB = Product::factory()->create([
            'company_id' => $this->company->id,
            'name' => 'Low Value Item',
            'sku' => 'SKU-LOW'
        ]);

        $this->actingAs($this->user);

        // Force Fix Schema for Test Interruption
        if (!\Illuminate\Support\Facades\Schema::hasColumn('stock_ins', 'warehouse_id')) {
            \Illuminate\Support\Facades\Schema::table('stock_ins', function ($table) {
                $table->foreignId('warehouse_id')->nullable();
                $table->string('status')->default('draft');
                $table->foreignId('approved_by')->nullable();
            });
        }
    }

    public function test_glass_pipeline_flow_with_landed_cost_allocation()
    {
        // 1. Create Purchase Order
        $po = PurchaseOrder::create([
            'po_number' => 'PO-TEST-001',
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'status' => 'approved',
            'order_date' => now(),
            'total_amount' => 11000, 
            'created_by' => $this->user->id,
            'company_id' => $this->company->id,
        ]);

        // Item A: 10 units @ $1000 = $10,000
        PurchaseOrderDetail::create([
            'purchase_order_id' => $po->id,
            'product_id' => $this->productA->id,
            'quantity_ordered' => 10,
            'quantity_received' => 0,
            'unit_price' => 1000,
            'subtotal' => 10000
        ]);

        // Item B: 10 units @ $100 = $1,000
        PurchaseOrderDetail::create([
            'purchase_order_id' => $po->id,
            'product_id' => $this->productB->id,
            'quantity_ordered' => 10,
            'quantity_received' => 0,
            'unit_price' => 100,
            'subtotal' => 1000
        ]);
        // Total Value = $11,000

        // 2. Create Inbound Shipment (The Bridge)
        $shipment = InboundShipment::create([
            'shipment_number' => 'SHP-TEST-001',
            'status' => 'on_water',
            'company_id' => $this->company->id,
        ]);
        
        // Link PO to Shipment
        $po->update(['inbound_shipment_id' => $shipment->id]);

        // 3. Add Expense (The Profit Brain)
        // Freight Cost: $1,100
        // Allocation Method: 'value'
        // Logic:
        //    Total PO Value = $11,000
        //    Expense = $1,100 (10% of value)
        //    Item A Allocation: $1000 * 10% = $100 per unit cost added
        //    Item B Allocation: $100 * 10% = $10 per unit cost added
        $shipment->expenses()->create([
            'name' => 'Ocean Freight',
            'amount' => 1100, 
            'allocation_method' => 'value',
            'currency_code' => 'USD'
        ]);

        // 4. Trigger Receive (Glass Pipeline Action)
        $response = $this->post(route('inbound-shipments.receive', $shipment));
        
        $response->assertStatus(302);
        $response->assertSessionHas('success');

        // 5. Verify Stock In Created
        $this->assertDatabaseHas('stock_ins', [
            'note' => 'Received from Shipment ' . $shipment->shipment_number
        ]);

        $stockIn = \App\Models\StockIn::latest()->first();

        // 6. Verify Cost Allocation (The Big Test)
        // Check Item A (High Value)
        $detailA = $stockIn->details()->where('product_id', $this->productA->id)->first();
        // Expected: $1000 (Purchase) + $100 (Freight) = $1100
        $this->assertEquals(10, $detailA->quantity);
        $this->assertEquals(100, $detailA->allocated_landed_cost, 'Item A allocated cost should be $100');
        $this->assertEquals(1100, $detailA->final_cost, 'Item A final cost should be $1100');

        // Check Item B (Low Value)
        $detailB = $stockIn->details()->where('product_id', $this->productB->id)->first();
        // Expected: $100 (Purchase) + $10 (Freight) = $110
        $this->assertEquals(10, $detailB->quantity);
        $this->assertEquals(10, $detailB->allocated_landed_cost, 'Item B allocated cost should be $10');
        $this->assertEquals(110, $detailB->final_cost, 'Item B final cost should be $110');
    }
}
