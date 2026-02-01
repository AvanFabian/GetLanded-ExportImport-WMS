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

class WeightedAverageCostTest extends TestCase
{
    use RefreshDatabase;

    public function test_wac_updates_correctly_on_receiving_shipment()
    {
        // 1. Setup User & Company
        $user = User::factory()->create();
        $this->actingAs($user);

        // 2. Create Product (Initial State)
        // Cost: $10, Stock: 0
        $product = Product::factory()->create([
            'purchase_price' => 10,
            'weighted_average_cost' => 10,
        ]);
        
        // Ensure 0 stock initially
        $product->warehouses()->sync([]);

        // 3. Create Shipment 1 (Receive 100 units @ Landed Cost $12)
        // Landed Cost = $10 Factory + $2 Freight
        $shipment = $this->createShipmentWithLandedCost($product, 100, 10, 200); // 200 expense / 100 units = $2 per unit

        // 4. Act: Receive Shipment 1
        $response = $this->post(route('inbound-shipments.receive', $shipment));
        $response->assertSessionHasNoErrors();
        
        // 5. Assert WAC = $12
        $product->refresh();
        // Calculation: ((0 * 10) + (100 * 12)) / 100 = 12
        $this->assertEquals(12.00, $product->weighted_average_cost, 'WAC should be 12 after first shipment');

        // 6. Create Shipment 2 (Receive 100 units @ Landed Cost $14)
        // Landed Cost = $10 Factory + $4 Freight
        $shipment2 = $this->createShipmentWithLandedCost($product, 100, 10, 400); // 400 expense / 100 units = $4 per unit
        
        // 7. Act: Receive Shipment 2
        $response = $this->post(route('inbound-shipments.receive', $shipment2));
        $response->assertSessionHasNoErrors();

        // 8. Assert WAC = $13
        $product->refresh();
        // Calculation: ((100 * 12) + (100 * 14)) / 200 = 2600 / 200 = 13
        $this->assertEquals(13.00, $product->weighted_average_cost, 'WAC should be 13 after second shipment');
    }
}
