<?php

namespace Database\Factories;

use App\Models\Claim;
use App\Models\Company;
use App\Models\SalesOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClaimFactory extends Factory
{
    protected $model = Claim::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'sales_order_id' => SalesOrder::factory(),
            'claim_type' => $this->faker->randomElement(['damage', 'shortage', 'delay']),
            'claimed_amount' => $this->faker->randomFloat(2, 100, 50000),
            'description' => $this->faker->paragraph(),
            'status' => 'open',
        ];
    }

    public function submitted(): static
    {
        return $this->state(fn () => ['status' => 'submitted']);
    }

    public function settled(float $amount = null): static
    {
        return $this->state(fn (array $attrs) => [
            'status' => 'settled',
            'settled_amount' => $amount ?? $attrs['claimed_amount'],
        ]);
    }
}
