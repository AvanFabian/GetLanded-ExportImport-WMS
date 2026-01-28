<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * DemoUserSeeder
 * 
 * Creates demo users for the AgroWMS application.
 * All users are connected to the AVANDIGITAL company (ID: 1).
 * 
 * Usage:
 *   php artisan db:seed --class=DemoUserSeeder
 * 
 * Demo Credentials:
 *   - Owner:   owner@avandigital.id   / demo1234 (admin)
 *   - Manager: manager@avandigital.id / demo1234 (manager)
 *   - Staff:   staff@avandigital.id   / demo1234 (staff)
 */
class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🔐 Creating Demo Users for AgroWMS...');

        // Get the AVANDIGITAL company ID
        $companyId = DB::table('companies')
            ->where('code', 'AVANDIGITAL')
            ->value('id');

        if (!$companyId) {
            $this->command->error('❌ AVANDIGITAL company not found. Please run UserSeeder first.');
            return;
        }

        // Verify roles exist (from PermissionSeeder)
        $requiredRoles = ['admin', 'manager', 'staff'];
        foreach ($requiredRoles as $roleName) {
            if (!Role::where('name', $roleName)->exists()) {
                $this->command->error("❌ Role '{$roleName}' not found. Please run PermissionSeeder first.");
                return;
            }
        }

        // Define demo users
        $demoUsers = [
            [
                'email' => 'owner@avandigital.id',
                'name' => 'Avan Digital Owner',
                'role' => 'admin',
                'locale' => 'id',
            ],
            [
                'email' => 'manager@avandigital.id',
                'name' => 'Warehouse Manager',
                'role' => 'manager',
                'locale' => 'id',
            ],
            [
                'email' => 'staff@avandigital.id',
                'name' => 'Warehouse Staff',
                'role' => 'staff',
                'locale' => 'id',
            ],
        ];

        foreach ($demoUsers as $userData) {
            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => Hash::make('demo1234'),
                    'email_verified_at' => now(),
                    'company_id' => $companyId,
                    'is_active' => true,
                    'locale' => $userData['locale'],
                    'role' => $userData['role'], // Legacy column if exists
                ]
            );

            // Assign Spatie role (sync to prevent duplicates)
            $user->syncRoles([$userData['role']]);

            $this->command->info("  ✅ {$userData['name']} ({$userData['email']}) → {$userData['role']}");
        }

        $this->command->newLine();
        $this->command->info('🎉 Demo users created successfully!');
        $this->command->table(
            ['Email', 'Password', 'Role'],
            [
                ['owner@avandigital.id', 'demo1234', 'admin'],
                ['manager@avandigital.id', 'demo1234', 'manager'],
                ['staff@avandigital.id', 'demo1234', 'staff'],
            ]
        );
    }
}
