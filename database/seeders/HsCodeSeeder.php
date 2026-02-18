<?php

namespace Database\Seeders;

use App\Models\HsCode;
use Illuminate\Database\Seeder;

class HsCodeSeeder extends Seeder
{
    public function run(): void
    {
        $codes = [
            // Coffee
            ['code' => '0901.11.10', 'description' => 'Coffee, not roasted: Arabica WIB or Robusta OIB', 'bm_rate' => 5],
            ['code' => '0901.11.90', 'description' => 'Coffee, not roasted: Other', 'bm_rate' => 5],
            ['code' => '0901.21.10', 'description' => 'Coffee, roasted: Not decaffeinated, unground', 'bm_rate' => 20],
            ['code' => '0901.21.20', 'description' => 'Coffee, roasted: Not decaffeinated, ground', 'bm_rate' => 20],

            // Spices
            ['code' => '0904.11.10', 'description' => 'Pepper: Neither crushed nor ground: White', 'bm_rate' => 5],
            ['code' => '0904.11.20', 'description' => 'Pepper: Neither crushed nor ground: Black', 'bm_rate' => 5],
            ['code' => '0906.11.00', 'description' => 'Cinnamon (Cinnamomum zeylanicum Blume)', 'bm_rate' => 5],

            // Machinery parts (common imports)
            ['code' => '8481.80.50', 'description' => 'Taps, cocks and valves: For household plumbing', 'bm_rate' => 15],
            ['code' => '8483.40.30', 'description' => 'Gears and gearing: For machinery of 84.29 or 84.30', 'bm_rate' => 0], // Often 0 for heavy machinery parts
            
            // Electronics
            ['code' => '8517.62.21', 'description' => 'Machines for the reception, conversion and transmission or regeneration of voice, images or other data: Optical line terminal', 'bm_rate' => 0],
            ['code' => '8544.42.94', 'description' => 'Other electric conductors: Fitted with connectors: For a voltage not exceeding 80 V: Other', 'bm_rate' => 10],

            // Textiles
            ['code' => '6109.10.10', 'description' => 'T-shirts, singlets and other vests, knitted or crocheted: Of cotton: For men or boys', 'bm_rate' => 25],
        ];

        foreach ($codes as $code) {
            HsCode::updateOrCreate(['code' => $code['code']], $code);
        }
    }
}
