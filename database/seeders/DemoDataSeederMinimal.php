<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DemoDataSeederMinimal extends Seeder
{
    public function run(): void
    {
        $this->command->info('Minimal Seeder Running');

        // Insert 1 category
        DB::table('categories')->insert([
            'company_id' => 1,
            'name' => 'Test Category',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        $this->command->info('Minimal Seeder Finished');
    }
}
