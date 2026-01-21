<?php

namespace Tests\Feature;

use App\Enums\TransactionStatus;
use App\Exceptions\SelfApprovalException;
use App\Models\Batch;
use App\Models\Company;
use App\Models\Product;
use App\Models\StockIn;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\Scopes\TenantScope;
use App\Services\StockTransactionFinalizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PhaseKEnterpriseTest extends TestCase
{
    use RefreshDatabase;

    protected Company $companyA;
    protected Company $companyB;
    protected User $userA;
    protected User $userB;
    protected User $approverA;
    protected Batch $batchA;
    protected Batch $batchB;

    protected function setUp(): void
    {
        parent::setUp();

        // Create two companies using DB::table to bypass any model events
        $companyAId = DB::table('companies')->insertGetId([
            'uuid' => fake()->uuid(),
            'name' => 'Company A',
            'code' => 'COMPA',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->companyA = Company::find($companyAId);

        $companyBId = DB::table('companies')->insertGetId([
            'uuid' => fake()->uuid(),
            'name' => 'Company B',
            'code' => 'COMPB',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->companyB = Company::find($companyBId);

        // Create users for each company
        $this->userA = User::factory()->create([
            'company_id' => $this->companyA->id,
            'role' => 'staff',
        ]);

        $this->approverA = User::factory()->create([
            'company_id' => $this->companyA->id,
            'role' => 'manager',
        ]);

        $this->userB = User::factory()->create([
            'company_id' => $this->companyB->id,
            'role' => 'admin',
        ]);

        // Create warehouses using DB::table to bypass TenantScope
        $warehouseAId = DB::table('warehouses')->insertGetId([
            'company_id' => $this->companyA->id,
            'name' => 'Warehouse A',
            'code' => 'WHA',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $warehouseBId = DB::table('warehouses')->insertGetId([
            'company_id' => $this->companyB->id,
            'name' => 'Warehouse B',
            'code' => 'WHB',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create products using DB::table
        $productAId = DB::table('products')->insertGetId([
            'company_id' => $this->companyA->id,
            'code' => 'PROD-A',
            'name' => 'Product A',
            'unit' => 'pcs',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $productBId = DB::table('products')->insertGetId([
            'company_id' => $this->companyB->id,
            'code' => 'PROD-B',
            'name' => 'Product B',
            'unit' => 'pcs',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create batches using DB::table
        $batchAId = DB::table('batches')->insertGetId([
            'company_id' => $this->companyA->id,
            'batch_number' => 'BATCH-A-001',
            'product_id' => $productAId,
            'status' => 'active',
            'cost_price' => 10000.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->batchA = Batch::withoutGlobalScope(TenantScope::class)->find($batchAId);

        $batchBId = DB::table('batches')->insertGetId([
            'company_id' => $this->companyB->id,
            'batch_number' => 'BATCH-B-001',
            'product_id' => $productBId,
            'status' => 'active',
            'cost_price' => 15000.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->batchB = Batch::withoutGlobalScope(TenantScope::class)->find($batchBId);
    }

    // =========================================
    // MULTI-TENANCY TESTS
    // =========================================

    /**
     * Test that TenantScope isolates data between companies.
     */
    public function test_global_tenant_scope_isolation(): void
    {
        // Act as User A
        $this->actingAs($this->userA);

        // User A should only see their company's batches
        $batches = Batch::all();
        
        $this->assertCount(1, $batches);
        $this->assertEquals($this->batchA->id, $batches->first()->id);
        $this->assertEquals('BATCH-A-001', $batches->first()->batch_number);
    }

    /**
     * Test that Company B user only sees their batches.
     */
    public function test_company_b_sees_own_batches(): void
    {
        $this->actingAs($this->userB);

        $batches = Batch::all();

        $this->assertCount(1, $batches);
        $this->assertEquals($this->batchB->id, $batches->first()->id);
        $this->assertEquals('BATCH-B-001', $batches->first()->batch_number);
    }

    // =========================================
    // MAKER-CHECKER TESTS
    // =========================================

    /**
     * Test that self-approval is prevented via reflection.
     */
    public function test_approver_cannot_self_approve(): void
    {
        $finalizer = new StockTransactionFinalizer();

        // Create reflection to test protected method directly
        $reflection = new \ReflectionClass($finalizer);
        $method = $reflection->getMethod('preventSelfApproval');
        $method->setAccessible(true);

        $this->expectException(SelfApprovalException::class);
        
        // Try to approve own transaction (same user ID for creator and approver)
        $method->invoke($finalizer, $this->userA->id, $this->userA->id);
    }

    /**
     * Test that different user can approve transaction.
     */
    public function test_different_user_can_approve(): void
    {
        $finalizer = new StockTransactionFinalizer();

        // Create reflection to test protected method
        $reflection = new \ReflectionClass($finalizer);
        $method = $reflection->getMethod('preventSelfApproval');
        $method->setAccessible(true);

        // Should not throw for different users
        try {
            $method->invoke($finalizer, $this->userA->id, $this->approverA->id);
            $this->assertTrue(true); // No exception = pass
        } catch (SelfApprovalException $e) {
            $this->fail('Should not throw exception for different users');
        }
    }

    // =========================================
    // TRANSACTION STATUS TESTS
    // =========================================

    /**
     * Test transaction status enum values.
     */
    public function test_transaction_status_enum_values(): void
    {
        $this->assertEquals('draft', TransactionStatus::DRAFT->value);
        $this->assertEquals('pending_approval', TransactionStatus::PENDING_APPROVAL->value);
        $this->assertEquals('completed', TransactionStatus::COMPLETED->value);
        $this->assertEquals('rejected', TransactionStatus::REJECTED->value);
    }

    /**
     * Test transaction status labels.
     */
    public function test_transaction_status_labels(): void
    {
        $this->assertEquals('Draft', TransactionStatus::DRAFT->label());
        $this->assertEquals('Pending Approval', TransactionStatus::PENDING_APPROVAL->label());
        $this->assertEquals('Completed', TransactionStatus::COMPLETED->label());
        $this->assertEquals('Rejected', TransactionStatus::REJECTED->label());
    }

    /**
     * Test canApprove only for pending status.
     */
    public function test_only_pending_can_be_approved(): void
    {
        $this->assertTrue(TransactionStatus::PENDING_APPROVAL->canApprove());
        $this->assertFalse(TransactionStatus::DRAFT->canApprove());
        $this->assertFalse(TransactionStatus::COMPLETED->canApprove());
        $this->assertFalse(TransactionStatus::REJECTED->canApprove());
    }

    /**
     * Test canEdit for various statuses.
     */
    public function test_can_edit_statuses(): void
    {
        $this->assertTrue(TransactionStatus::DRAFT->canEdit());
        $this->assertTrue(TransactionStatus::PENDING_APPROVAL->canEdit());
        $this->assertTrue(TransactionStatus::REJECTED->canEdit());
        $this->assertFalse(TransactionStatus::COMPLETED->canEdit());
    }
}
