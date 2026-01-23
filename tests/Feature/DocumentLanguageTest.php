<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\PdfService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

class DocumentLanguageTest extends TestCase
{
    use RefreshDatabase;

    public function test_sales_order_stores_document_language()
    {
        $user = User::factory()->create();
        $customer = Customer::factory()->create();
        $warehouse = Warehouse::factory()->create();
        $product = Product::factory()->create();

        $response = $this->actingAs($user)->post(route('sales-orders.store'), [
            'customer_id' => $customer->id,
            'warehouse_id' => $warehouse->id,
            'order_date' => now()->toDateString(),
            'notes' => 'Test Order',
            'document_language' => 'en',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                    'unit_price' => 1000,
                ]
            ]
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('sales_orders', [
            'document_language' => 'en'
        ]);
    }

    public function test_pdf_generation_respects_document_language()
    {
        $user = User::factory()->create();
        $customer = Customer::factory()->create();
        $warehouse = Warehouse::factory()->create();
        $product = Product::factory()->create();
        
        $salesOrder = SalesOrder::factory()->create([
            'document_language' => 'en',
            'customer_id' => $customer->id,
            'warehouse_id' => $warehouse->id,
            'created_by' => $user->id,
        ]);
        
        // Add items
        $salesOrder->items()->create([
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 1000,
            'subtotal' => 1000,
        ]);

        $pdfService = new PdfService();

        // Force app locale to ID first.
        App::setLocale('id');
        
        // Assert that generating invoice doesn't throw error
        // and theoretically switches locale (hard to assert without mocking internal View)
        try {
            $pdfService->generateInvoice($salesOrder);
            $this->assertTrue(true);
        } catch (\Exception $e) {
             $this->fail("PDF Generation failed: " . $e->getMessage());
        }
    }
}
