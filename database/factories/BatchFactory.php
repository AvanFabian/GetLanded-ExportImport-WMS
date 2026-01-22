<?php

namespace Database\Factories;

use App\Models\Batch;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class BatchFactory extends Factory
{
    protected $model = Batch::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'batch_number' => 'BATCH-' . strtoupper($this->faker->unique()->bothify('??###')),
            'manufacture_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'expiry_date' => $this->faker->dateTimeBetween('+6 months', '+2 years'),
            'cost_price' => $this->faker->randomFloat(2, 100, 10000),
            'status' => 'active',
            'is_quarantined' => false,
        ];
    }

    public function quarantined(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_quarantined' => true,
            'quarantine_reason' => 'Quality check failed',
        ]);
    }
}
