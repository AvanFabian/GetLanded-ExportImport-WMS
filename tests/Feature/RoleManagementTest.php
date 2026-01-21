<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RoleManagementTest extends TestCase
{
    use RefreshDatabase;

    protected Company $companyA;
    protected Company $companyB;
    protected User $ownerA;
    protected User $userB;

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

        // Create owner for Company A with role.manage permission
        $this->ownerA = User::factory()->create([
            'company_id' => $this->companyA->id,
        ]);
        $adminRole = Role::where('name', 'admin')->first();
        $this->ownerA->assignRole($adminRole);

        // Create user for Company B with role.manage permission
        $this->userB = User::factory()->create([
            'company_id' => $this->companyB->id,
        ]);
        $this->userB->assignRole($adminRole);
    }

    /**
     * Test owner can create custom role with specific permissions.
     */
    public function test_owner_can_create_custom_role_with_specific_permissions(): void
    {
        $this->actingAs($this->ownerA);

        $response = $this->post(route('roles.store'), [
            'name' => 'warehouse_lead',
            'display_name' => 'Warehouse Lead',
            'description' => 'Leads warehouse operations',
            'permissions' => [
                'inventory.view',
                'stock.in.create',
                'stock.out.create',
                'batch.manage',
            ],
        ]);

        $response->assertRedirect(route('roles.index'));

        // Verify role was created
        $role = Role::where('name', 'warehouse_lead')
            ->where('company_id', $this->companyA->id)
            ->first();

        $this->assertNotNull($role);
        $this->assertEquals('Warehouse Lead', $role->display_name);
        $this->assertFalse($role->is_system);

        // Verify permissions were assigned
        $rolePermissions = $role->permissions()->pluck('name')->toArray();
        $this->assertContains('inventory.view', $rolePermissions);
        $this->assertContains('stock.in.create', $rolePermissions);
        $this->assertContains('batch.manage', $rolePermissions);
        $this->assertCount(4, $rolePermissions);
    }

    /**
     * Test role is only visible to the company that created it.
     */
    public function test_role_is_only_visible_to_the_company_that_created_it(): void
    {
        // Create custom role for Company A
        $customRole = Role::create([
            'company_id' => $this->companyA->id,
            'name' => 'custom_role_a',
            'display_name' => 'Custom Role A',
            'is_system' => false,
        ]);

        // User from Company B should not see Company A's custom role
        $this->actingAs($this->userB);

        $response = $this->get(route('roles.index'));
        $response->assertStatus(200);
        $response->assertDontSee('Custom Role A');
        $response->assertDontSee('custom_role_a');

        // User from Company A should see their custom role
        $this->actingAs($this->ownerA);

        $response = $this->get(route('roles.index'));
        $response->assertStatus(200);
        $response->assertSee('Custom Role A');
    }

    /**
     * Test role permissions are correctly enforced in policies.
     */
    public function test_role_permissions_are_correctly_enforced_in_policies(): void
    {
        // Create a user with limited permissions
        $limitedUser = User::factory()->create([
            'company_id' => $this->companyA->id,
        ]);

        // Create a custom role with only inventory.view
        $limitedRole = Role::create([
            'company_id' => $this->companyA->id,
            'name' => 'limited_viewer',
            'display_name' => 'Limited Viewer',
            'is_system' => false,
        ]);
        $limitedRole->syncPermissions(['inventory.view', 'report.view']);
        $limitedUser->assignRole($limitedRole);

        // Verify the user has the correct permissions
        $this->assertTrue($limitedUser->hasPermissionTo('inventory.view'));
        $this->assertTrue($limitedUser->hasPermissionTo('report.view'));
        $this->assertFalse($limitedUser->hasPermissionTo('stock.in.create'));
        $this->assertFalse($limitedUser->hasPermissionTo('transaction.approve'));
        $this->assertFalse($limitedUser->hasPermissionTo('role.manage'));

        // User should not be able to access role management
        $this->actingAs($limitedUser);
        $response = $this->get(route('roles.index'));
        $response->assertStatus(403);
    }

    /**
     * Test system roles cannot be edited.
     */
    public function test_system_roles_cannot_be_edited(): void
    {
        $this->actingAs($this->ownerA);

        $adminRole = Role::where('name', 'admin')->first();

        $response = $this->get(route('roles.edit', $adminRole));
        $response->assertRedirect(route('roles.index'));
        $response->assertSessionHas('error');
    }

    /**
     * Test system roles cannot be deleted.
     */
    public function test_system_roles_cannot_be_deleted(): void
    {
        $this->actingAs($this->ownerA);

        $adminRole = Role::where('name', 'admin')->first();

        $response = $this->delete(route('roles.destroy', $adminRole));
        $response->assertSessionHas('error');
        
        // Verify role still exists
        $this->assertNotNull(Role::find($adminRole->id));
    }

    /**
     * Test user cannot access another company's custom role.
     */
    public function test_user_cannot_access_another_companys_custom_role(): void
    {
        // Create custom role for Company A
        $customRole = Role::create([
            'company_id' => $this->companyA->id,
            'name' => 'custom_role_a',
            'display_name' => 'Custom Role A',
            'is_system' => false,
        ]);

        // User from Company B should not be able to edit Company A's role
        $this->actingAs($this->userB);

        $response = $this->get(route('roles.edit', $customRole));
        $response->assertStatus(404);

        $response = $this->put(route('roles.update', $customRole), [
            'display_name' => 'Hacked Role',
            'permissions' => ['inventory.view'],
        ]);
        $response->assertStatus(404);
    }

    /**
     * Test role with assigned users cannot be deleted.
     */
    public function test_role_with_assigned_users_cannot_be_deleted(): void
    {
        $this->actingAs($this->ownerA);

        // Create custom role
        $customRole = Role::create([
            'company_id' => $this->companyA->id,
            'name' => 'role_with_users',
            'display_name' => 'Role With Users',
            'is_system' => false,
        ]);
        $customRole->syncPermissions(['inventory.view']);

        // Assign role to a user
        $testUser = User::factory()->create(['company_id' => $this->companyA->id]);
        $testUser->assignRole($customRole);

        // Try to delete
        $response = $this->delete(route('roles.destroy', $customRole));
        $response->assertSessionHas('error');
        
        // Verify role still exists
        $this->assertNotNull(Role::find($customRole->id));
    }
}
