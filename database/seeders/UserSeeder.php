<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run()
    {
        // 1. Create Default Company
        $companyId = DB::table('companies')->where('code', 'AVANDIGITAL')->value('id');
        if (!$companyId) {
            $companyId = DB::table('companies')->insertGetId([
                'uuid' => Str::uuid()->toString(),
                'code' => 'AVANDIGITAL',
                'name' => 'AvanDigital Demo',
                'base_currency_code' => 'IDR',
                'subscription_plan' => 'enterprise',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 2. Create Demo Owner User
        $demo = User::create([
            'name' => 'Demo Owner',
            'email' => 'demo@avandigital.id',
            'password' => Hash::make('demo1234'),
            'email_verified_at' => now(),
            'role' => 'admin', // Owner uses admin role context in this system
            'is_active' => true,
            'company_id' => $companyId,
            'locale' => 'id',
        ]);
        $demo->assignRole('admin');

        // 3. Create Standard Users
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@warehouse.test',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'role' => 'admin',
            'is_active' => true,
            'company_id' => $companyId,
            'locale' => 'en',
        ]);
        $admin->assignRole('admin');

        $manager = User::create([
            'name' => 'Manager User',
            'email' => 'manager@warehouse.test',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'role' => 'manager',
            'is_active' => true,
            'company_id' => $companyId,
            'locale' => 'id',
        ]);
        $manager->assignRole('manager');

        $staff = User::create([
            'name' => 'Staff User',
            'email' => 'staff@warehouse.test',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'role' => 'staff',
            'is_active' => true,
            'company_id' => $companyId,
            'locale' => 'id',
        ]);
        $staff->assignRole('staff');
    }
}