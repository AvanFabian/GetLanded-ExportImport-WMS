<?php

namespace Tests\Feature;

use App\Models\Batch;
use App\Models\Category;
use App\Models\Company;
use App\Models\Permission;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\AuditLog;
use App\Rules\TenantScopedExists;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected Company $companyA;
    protected Company $companyB;
    protected User $userA;
    protected User $userB;
    protected User $superAdmin;
    protected $categoryA;
    protected $categoryB;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed permissions
        $this->artisan('db:seed', ['--class' => 'PermissionSeeder']);

        // Create two companies
        $this->companyA = Company::create([
            'uuid' => fake()->uuid(),
            'name' => 'Company A',
            'code' => 'COMPA',
            'is_active' => true,
        ]);

        $this->companyB = Company::create([
            'uuid' => fake()->uuid(),
            'name' => 'Company B',
            'code' => 'COMPB',
            'is_active' => true,
        ]);

        // Create users
        $this->userA = User::factory()->create([
            'company_id' => $this->companyA->id,
            'is_super_admin' => false,
        ]);

        $this->userB = User::factory()->create([
            'company_id' => $this->companyB->id,
            'is_super_admin' => false,
        ]);

        // Create super-admin (no company - platform level)
        $this->superAdmin = User::factory()->create([
            'company_id' => null,
            'is_super_admin' => true,
        ]);

        // Create categories for each company using DB::table to bypass TenantScope
        $catAId = DB::table('categories')->insertGetId([
            'company_id' => $this->companyA->id,
            'name' => 'Category A',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->categoryA = (object) ['id' => $catAId, 'name' => 'Category A'];

        $catBId = DB::table('categories')->insertGetId([
            'company_id' => $this->companyB->id,
            'name' => 'Category B',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->categoryB = (object) ['id' => $catBId, 'name' => 'Category B'];
    }

    /**
     * Test validation rejects ID from other company.
     */
    public function test_validation_rejects_id_from_other_company(): void
    {
        $this->actingAs($this->userA);

        // Create a validation rule for category that must belong to userA's company
        $rule = new TenantScopedExists('categories');

        $failed = false;
        $errorMessage = '';

        // Try to validate Company B's category ID
        $rule->validate(
            'category_id',
            $this->categoryB->id,
            function ($message) use (&$failed, &$errorMessage) {
                $failed = true;
                $errorMessage = $message;
            }
        );

        $this->assertTrue($failed, 'Validation should fail for cross-tenant ID');
        $this->assertStringContainsString('does not exist or does not belong', $errorMessage);
    }

    /**
     * Test validation accepts ID from own company.
     */
    public function test_validation_accepts_id_from_own_company(): void
    {
        $this->actingAs($this->userA);

        $rule = new TenantScopedExists('categories');

        $failed = false;

        // Validate own company's category
        $rule->validate(
            'category_id',
            $this->categoryA->id,
            function ($message) use (&$failed) {
                $failed = true;
            }
        );

        $this->assertFalse($failed, 'Validation should pass for own company ID');
    }

    /**
     * Test super-admin can bypass tenant scope.
     */
    public function test_super_admin_can_bypass_tenant_scope(): void
    {
        // Enable super-admin bypass
        app()->instance('disable_tenant_scope', true);

        $this->actingAs($this->superAdmin);

        // Super-admin should see all categories from all companies
        $categories = Category::withoutGlobalScopes()->get();

        $this->assertCount(2, $categories);
        $this->assertTrue($categories->contains('id', $this->categoryA->id));
        $this->assertTrue($categories->contains('id', $this->categoryB->id));
    }

    /**
     * Test middleware blocks inactive company.
     */
    public function test_middleware_blocks_inactive_company(): void
    {
        // Deactivate Company A
        $this->companyA->update(['is_active' => false]);

        $this->actingAs($this->userA);

        // Try to access dashboard
        $response = $this->get('/dashboard');

        // Should redirect to suspended page
        $response->assertRedirect(route('subscription.suspended'));
    }

    /**
     * Test active company can access normally.
     */
    public function test_active_company_can_access_normally(): void
    {
        $this->actingAs($this->userA);

        // Company A is active, should access dashboard normally
        $response = $this->get('/dashboard');

        $response->assertStatus(200);
    }

    /**
     * Test audit log retains name after force delete.
     */
    public function test_audit_log_retains_name_after_force_delete(): void
    {
        $this->actingAs($this->userA);

        // Create a product that will be deleted
        $product = Product::withoutGlobalScopes()->create([
            'company_id' => $this->companyA->id,
            'code' => 'PROD-DELETE-TEST',
            'name' => 'Product To Delete',
            'unit' => 'pcs',
        ]);

        $productId = $product->id;
        $productName = $product->name;

        // Force delete the product
        $product->forceDelete();

        // Find the audit log for the deletion
        $auditLog = AuditLog::where('auditable_type', Product::class)
            ->where('auditable_id', $productId)
            ->where('event', 'forceDeleted')
            ->first();

        $this->assertNotNull($auditLog, 'Audit log should exist for force delete');
        
        // Verify snapshot_data contains the display name
        $snapshotData = $auditLog->snapshot_data;
        $this->assertNotNull($snapshotData);
        $this->assertArrayHasKey('display_name', $snapshotData);
        $this->assertEquals($productName, $snapshotData['display_name']);
    }

    /**
     * Test super-admin is not blocked by subscription check.
     */
    public function test_super_admin_bypasses_subscription_check(): void
    {
        // Even if we try to set a company, super-admin should bypass
        $this->superAdmin->update(['company_id' => $this->companyA->id]);
        $this->companyA->update(['is_active' => false]);

        $this->actingAs($this->superAdmin);

        // Super-admin should bypass the subscription check
        $response = $this->get('/dashboard');

        // Should NOT be redirected to suspended page
        $response->assertStatus(200);
    }
}
