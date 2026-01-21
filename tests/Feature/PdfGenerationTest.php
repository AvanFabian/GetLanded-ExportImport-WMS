<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\StockIn;
use App\Models\StockOut;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\PdfService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PdfGenerationTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;
    protected User $user;
    protected Warehouse $warehouse;

    protected function setUp(): void
    {
        parent::setUp();

        // Create company with bank details
        $this->company = Company::create([
            'name' => 'Test Export Company',
            'uuid' => \Illuminate\Support\Str::uuid(),
            'email' => 'test@company.com',
            'phone' => '+62 21 1234567',
            'address' => 'Jl. Test No. 123, Jakarta',
            'tax_id' => '01.234.567.8-901.234',
            'bank_name' => 'Bank Central Asia',
            'bank_account_number' => '1234567890',
            'bank_swift_code' => 'CENAIDJA',
            'invoice_terms' => 'Payment due within 30 days.',
            'is_active' => true,
            'base_currency_code' => 'IDR',
        ]);

        // Create user
        $this->user = User::factory()->create([
            'company_id' => $this->company->id,
            'name' => 'John Approver',
        ]);

        // Create warehouse using DB to avoid model hooks
        $warehouseId = DB::table('warehouses')->insertGetId([
            'name' => 'Main Warehouse',
            'code' => 'WH-' . uniqid(),
            'company_id' => $this->company->id,
            'is_active' => true,
            'is_default' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->warehouse = Warehouse::find($warehouseId);

        $this->actingAs($this->user);
    }

    /** @test */
    public function test_document_footer_contains_bank_info()
    {
        $stockIn = StockIn::create([
            'company_id' => $this->company->id,
            'warehouse_id' => $this->warehouse->id,
            'transaction_code' => 'STI-001',
            'date' => now(),
            'total' => 1000,
            'approved_by' => $this->user->id,
            'approved_at' => now(),
            'status' => 'APPROVED',
        ]);

        $html = view('pdf.warehouse-receipt', [
            'company' => $this->company,
            'stockIn' => $stockIn,
            'qrCode' => 'data:image/png;base64,test',
            'documentType' => 'Warehouse Receipt',
            'documentNumber' => $stockIn->transaction_code,
            'approver' => $this->user,
            'approvedAt' => $stockIn->approved_at,
            'isVoided' => false,
        ])->render();

        $this->assertStringContainsString('Bank Central Asia', $html);
        $this->assertStringContainsString('1234567890', $html);
        $this->assertStringContainsString('CENAIDJA', $html);
    }

    /** @test */
    public function test_signature_name_matches_approver_name()
    {
        $stockIn = StockIn::create([
            'company_id' => $this->company->id,
            'warehouse_id' => $this->warehouse->id,
            'transaction_code' => 'STI-002',
            'date' => now(),
            'total' => 500,
            'approved_by' => $this->user->id,
            'approved_at' => now(),
            'status' => 'APPROVED',
        ]);

        $html = view('pdf.warehouse-receipt', [
            'company' => $this->company,
            'stockIn' => $stockIn,
            'qrCode' => 'data:image/png;base64,test',
            'documentType' => 'Warehouse Receipt',
            'documentNumber' => $stockIn->transaction_code,
            'approver' => $this->user,
            'approvedAt' => $stockIn->approved_at,
            'isVoided' => false,
        ])->render();

        $this->assertStringContainsString('John Approver', $html);
        $this->assertStringContainsString('Authorized Signature', $html);
    }

    /** @test */
    public function test_packing_list_does_not_leak_pricing_data()
    {
        $productId = DB::table('products')->insertGetId([
            'company_id' => $this->company->id,
            'code' => 'COF-001',
            'name' => 'Arabica Coffee Beans',
            'hs_code' => '09011100',
            'purchase_price' => 250.00,
            'selling_price' => 300.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $stockOut = StockOut::create([
            'company_id' => $this->company->id,
            'warehouse_id' => $this->warehouse->id,
            'transaction_code' => 'STO-001',
            'date' => now(),
            'customer' => 'Test Customer',
            'total' => 5000.00,
            'approved_by' => $this->user->id,
            'approved_at' => now(),
            'status' => 'APPROVED',
        ]);

        DB::table('stock_out_details')->insert([
            'stock_out_id' => $stockOut->id,
            'product_id' => $productId,
            'quantity' => 100,
            'selling_price' => 50.00,
            'total' => 5000.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $stockOut->load(['warehouse', 'details.product']);

        $html = view('pdf.packing-list', [
            'company' => $this->company,
            'stockOut' => $stockOut,
            'qrCode' => 'data:image/png;base64,test',
            'documentType' => 'Packing List',
            'documentNumber' => $stockOut->transaction_code,
            'approver' => $this->user,
            'approvedAt' => $stockOut->approved_at,
            'isVoided' => false,
        ])->render();

        // Assert: No pricing data leaked
        $this->assertStringNotContainsString('$250', $html);
        $this->assertStringNotContainsString('$50', $html);
        $this->assertStringNotContainsString('$5000', $html);
        $this->assertStringNotContainsString('selling_price', strtolower($html));
        
        // Verify the document does contain expected data
        $this->assertStringContainsString('Arabica Coffee Beans', $html);
        $this->assertStringContainsString('09011100', $html);
    }

    /** @test */
    public function test_voided_document_shows_watermark()
    {
        $stockIn = StockIn::create([
            'company_id' => $this->company->id,
            'warehouse_id' => $this->warehouse->id,
            'transaction_code' => 'STI-003',
            'date' => now(),
            'total' => 1000,
            'status' => 'VOIDED',
        ]);

        $html = view('pdf.warehouse-receipt', [
            'company' => $this->company,
            'stockIn' => $stockIn,
            'qrCode' => 'data:image/png;base64,test',
            'documentType' => 'Warehouse Receipt',
            'documentNumber' => $stockIn->transaction_code,
            'approver' => null,
            'approvedAt' => null,
            'isVoided' => true,
        ])->render();

        $this->assertStringContainsString('void-watermark', $html);
        $this->assertStringContainsString('VOID', $html);
    }

    /** @test */
    public function test_commercial_invoice_shows_dual_currency()
    {
        $customerId = DB::table('customers')->insertGetId([
            'company_id' => $this->company->id,
            'name' => 'Export Customer',
            'address' => 'USA',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $productId = DB::table('products')->insertGetId([
            'company_id' => $this->company->id,
            'code' => 'GC-CODE',
            'name' => 'Green Coffee',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $salesOrder = SalesOrder::create([
            'company_id' => $this->company->id,
            'so_number' => 'SO-001',
            'customer_id' => $customerId,
            'warehouse_id' => $this->warehouse->id,
            'order_date' => now(),
            'currency_code' => 'USD',
            'exchange_rate_at_transaction' => 15500,
            'subtotal' => 1000,
            'tax' => 100,
            'total' => 1100,
            'status' => 'confirmed',
        ]);

        SalesOrderItem::create([
            'sales_order_id' => $salesOrder->id,
            'product_id' => $productId,
            'quantity' => 10,
            'unit_price' => 100,
            'subtotal' => 1000,
        ]);

        $salesOrder->load(['customer', 'items.product']);

        $html = view('pdf.commercial-invoice', [
            'company' => $this->company,
            'order' => $salesOrder,
            'qrCode' => 'data:image/png;base64,test',
            'documentType' => 'Commercial Invoice',
            'documentNumber' => $salesOrder->so_number,
            'approver' => null,
            'approvedAt' => null,
            'isVoided' => false,
        ])->render();

        $this->assertStringContainsString('USD', $html);
        $this->assertStringContainsString('IDR', $html);
    }

    /** @test */
    public function test_invoice_contains_terms_and_conditions()
    {
        $customerId = DB::table('customers')->insertGetId([
            'company_id' => $this->company->id,
            'name' => 'Test Customer',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $salesOrder = SalesOrder::create([
            'company_id' => $this->company->id,
            'so_number' => 'SO-002',
            'customer_id' => $customerId,
            'warehouse_id' => $this->warehouse->id,
            'order_date' => now(),
            'subtotal' => 500,
            'total' => 500,
            'status' => 'confirmed',
        ]);

        $salesOrder->load(['customer', 'items']);

        $html = view('pdf.commercial-invoice', [
            'company' => $this->company,
            'order' => $salesOrder,
            'qrCode' => 'data:image/png;base64,test',
            'documentType' => 'Commercial Invoice',
            'documentNumber' => $salesOrder->so_number,
            'approver' => null,
            'approvedAt' => null,
            'isVoided' => false,
        ])->render();

        $this->assertStringContainsString('Terms & Conditions', $html);
        $this->assertStringContainsString('Payment due within 30 days', $html);
    }
}
