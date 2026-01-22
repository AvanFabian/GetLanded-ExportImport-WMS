<?php

namespace Database\Factories;

use App\Models\WarehouseRack;
use App\Models\WarehouseZone;
use Illuminate\Database\Eloquent\Factories\Factory;

class WarehouseRackFactory extends Factory
{
    protected $model = WarehouseRack::class;

    public function definition(): array
    {
        return [
            'zone_id' => WarehouseZone::factory(),
            'code' => strtoupper($this->faker->unique()->lexify('RK-??')),
            'name' => 'Rack ' . $this->faker->randomLetter(),
            'levels' => $this->faker->numberBetween(1, 5),
            'is_active' => true,
        ];
    }
}
