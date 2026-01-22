<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'code' => strtoupper($this->faker->unique()->bothify('???-#####')),
            'name' => $this->faker->words(3, true),
            'unit' => $this->faker->randomElement(['pcs', 'kg', 'box', 'carton']),
            'min_stock' => $this->faker->numberBetween(0, 100),
            'purchase_price' => $this->faker->randomFloat(2, 100, 10000),
            'selling_price' => $this->faker->randomFloat(2, 200, 15000),
            'status' => true,
        ];
    }
}
