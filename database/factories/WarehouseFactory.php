<?php

namespace Database\Factories;

use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

class WarehouseFactory extends Factory
{
    protected $model = Warehouse::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company() . ' Warehouse',
            'code' => strtoupper($this->faker->unique()->lexify('WH-???')),
            'address' => $this->faker->address(),
            'city' => $this->faker->city(),
            'is_active' => true,
            'is_default' => false,
        ];
    }
}
