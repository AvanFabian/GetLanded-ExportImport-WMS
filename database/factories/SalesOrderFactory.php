<?php

namespace Database\Factories;

use App\Models\SalesOrder;
use App\Models\Customer;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

class SalesOrderFactory extends Factory
{
    protected $model = SalesOrder::class;

    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'warehouse_id' => Warehouse::factory(),
            'so_number' => 'SO-' . strtoupper($this->faker->unique()->bothify('??????##')),
            'order_date' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'delivery_date' => $this->faker->dateTimeBetween('now', '+30 days'),
            'total' => $this->faker->randomFloat(2, 1000, 100000),
            'subtotal' => $this->faker->randomFloat(2, 1000, 100000),
            'status' => 'draft',
            'payment_status' => 'unpaid',
        ];
    }

    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'confirmed',
        ]);
    }

    public function delivered(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'delivered',
        ]);
    }
}
