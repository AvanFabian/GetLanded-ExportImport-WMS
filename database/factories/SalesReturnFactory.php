<?php

namespace Database\Factories;

use App\Models\SalesReturn;
use App\Models\Company;
use App\Models\SalesOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

class SalesReturnFactory extends Factory
{
    protected $model = SalesReturn::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'sales_order_id' => SalesOrder::factory(),
            'return_number' => 'RET-' . strtoupper($this->faker->unique()->bothify('??????##')),
            'return_date' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'credit_amount' => $this->faker->randomFloat(2, 100, 10000),
            'reason' => $this->faker->sentence(),
            'status' => 'pending',
        ];
    }

    public function approved(): static
    {
        return $this->state(fn () => [
            'status' => 'approved',
            'approved_at' => now(),
        ]);
    }

    public function processed(): static
    {
        return $this->state(fn () => [
            'status' => 'processed',
            'approved_at' => now(),
        ]);
    }
}
