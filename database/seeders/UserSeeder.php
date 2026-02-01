<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🔐 Creating Consolidated Demo Users for GetLanded...');

        // 1. Create or Get Default Company
        $companyId = DB::table('companies')->where('code', 'AVANDIGITAL')->value('id');
        if (!$companyId) {
            $companyId = DB::table('companies')->insertGetId([
                'uuid' => Str::uuid()->toString(),
                'code' => 'AVANDIGITAL',
                'name' => 'Avan Digital Demo',
                'base_currency_code' => 'IDR',
                'subscription_plan' => 'enterprise',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 2. Define standard demo users
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
            [
                'email' => 'viewer@avandigital.id',
                'name' => 'System Viewer',
                'role' => 'viewer',
                'locale' => 'en',
            ],
        ];

        foreach ($demoUsers as $userData) {
            // Check if role exists in DB (assigned via PermissionSeeder)
            if (!Role::where('name', $userData['role'])->exists()) {
                $this->command->error("❌ Role '{$userData['role']}' not found. Ensure PermissionSeeder is run first.");
                continue;
            }

            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => Hash::make('demo1234'),
                    'email_verified_at' => now(),
                    'company_id' => $companyId,
                    'is_active' => true,
                    'locale' => $userData['locale'],
                    'role' => $userData['role'], // For systems still using the string column
                ]
            );

            // Sync Spatie Role
            $user->syncRoles([$userData['role']]);

            $this->command->info("  ✅ Created: {$userData['name']} ({$userData['email']})");
        }

        // 3. Keep Legacy/Internal Dev Users (Optional - keeping for backward compatibility)
        User::firstOrCreate(
            ['email' => 'admin@warehouse.test'],
            [
                'name' => 'Dev Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'role' => 'admin',
                'is_active' => true,
                'company_id' => $companyId,
                'locale' => 'en',
            ]
        )->syncRoles(['admin']);

        $this->command->newLine();
        $this->command->info('🎉 User Seeding Complete!');
        $this->command->table(
            ['Name', 'Email', 'Password', 'Role'],
            array_map(fn($u) => [$u['name'], $u['email'], 'demo1234', $u['role']], $demoUsers)
        );
    }
}