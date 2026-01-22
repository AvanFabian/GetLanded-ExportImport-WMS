<?php

namespace Database\Factories;

use App\Models\WarehouseBin;
use App\Models\WarehouseRack;
use Illuminate\Database\Eloquent\Factories\Factory;

class WarehouseBinFactory extends Factory
{
    protected $model = WarehouseBin::class;

    public function definition(): array
    {
        return [
            'rack_id' => WarehouseRack::factory(),
            'code' => strtoupper($this->faker->unique()->bothify('BN-##-##')),
            'level' => $this->faker->numberBetween(1, 5),
            'pick_priority' => $this->faker->randomElement(['high', 'medium', 'low']),
            'is_active' => true,
        ];
    }
}
