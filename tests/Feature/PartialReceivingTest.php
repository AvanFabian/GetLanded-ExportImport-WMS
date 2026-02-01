<?php

namespace Tests\Feature;

use App\Models\InboundShipment;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetail;
use App\Models\StockIn;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PartialReceivingTest extends TestCase
{
    use RefreshDatabase;

    public function test_partial_receiving_updates_po_status()
    {
        // 1. Setup
        $user = User::factory()->create();
        $this->actingAs($user);

        $product = Product::factory()->create();
        $warehouse = Warehouse::factory()->create();
        $supplier = Supplier::factory()->create();

        // 2. Create PO (100 Units)
        $po = PurchaseOrder::create([
            'po_number' => 'PO-' . rand(1000, 9999),
            'warehouse_id' => $warehouse->id,
            'supplier_id' => $supplier->id,
            'status' => 'approved',
            'order_date' => now(),
            'company_id' => 1,
            'created_by' => $user->id
        ]);

        $detail = PurchaseOrderDetail::create([
            'purchase_order_id' => $po->id,
            'product_id' => $product->id,
            'quantity_ordered' => 100,
            'quantity_received' => 0,
            'unit_price' => 10,
            'subtotal' => 1000
        ]);

        // 3. Create Shipment
        $shipment = InboundShipment::create([
            'status' => 'arrived',
            'company_id' => 1,
            'created_by' => $user->id,
            'shipment_number' => 'SHP-TEST-001'
        ]);
        $po->update(['inbound_shipment_id' => $shipment->id]);

        // 4. Act: Receive 40 Units (Partial)
        // We simulate the form input structure: items[product_id] = qty
        $response = $this->post(route('inbound-shipments.receive', $shipment), [
            'items' => [
                $product->id => 40
            ]
        ]);

        $response->assertSessionHasNoErrors();

        // 5. Assert: Partial Status
        $po->refresh();
        $detail->refresh();

        $this->assertEquals(40, $detail->quantity_received, 'Detail received qty should be 40');
        $this->assertEquals('partially_received', $po->status, 'PO status should be partially_received');

        // 6. Act: Receive Remaining 60 Units
        // Note: In real world, this might be a NEW Shipment or the same one updated. 
        // For this test, we assume we are receiving the rest against the same shipment (or a new one linked to the same PO).
        // Let's create a NEW shipment for the remainder to be realistic (Split Shipment scenario).
        
        $shipment2 = InboundShipment::create([
            'status' => 'arrived',
            'company_id' => 1,
            'created_by' => $user->id,
            'shipment_number' => 'SHP-TEST-002'
        ]);
        // Re-link PO to new shipment? Or just process it? 
        // In our current simple logic, a PO links to ONE shipment. 
        // This is a constraint we might need to relax later, but for now, let's assume the user is receiving the *REST* against the same shipment 
        // (maybe it was 2 trucks for 1 shipment record).
        
        $response2 = $this->post(route('inbound-shipments.receive', $shipment), [
            'items' => [
                $product->id => 60
            ]
        ]);
        
        $response2->assertSessionHasNoErrors();

        // 7. Assert: Completed Status
        $po->refresh();
        $detail->refresh();

        $this->assertEquals(100, $detail->quantity_received, 'Detail received qty should be 100');
        $this->assertEquals('completed', $po->status, 'PO status should be completed');
    }
}
