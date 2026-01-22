<?php

namespace Database\Factories;

use App\Models\WarehouseZone;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

class WarehouseZoneFactory extends Factory
{
    protected $model = WarehouseZone::class;

    public function definition(): array
    {
        return [
            'warehouse_id' => Warehouse::factory(),
            'name' => 'Zone ' . $this->faker->randomLetter(),
            'code' => strtoupper($this->faker->unique()->lexify('???')),
            'type' => $this->faker->randomElement(['storage', 'receiving', 'shipping', 'quarantine', 'returns']),
            'is_active' => true,
        ];
    }
}
