<?php

namespace Database\Factories;

use App\Models\SupplierPayment;
use App\Models\Company;
use App\Models\Supplier;
use App\Models\StockIn;
use Illuminate\Database\Eloquent\Factories\Factory;

class SupplierPaymentFactory extends Factory
{
    protected $model = SupplierPayment::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'stock_in_id' => StockIn::factory(),
            'supplier_id' => Supplier::factory(),
            'amount_owed' => $this->faker->randomFloat(2, 1000, 100000),
            'amount_paid' => 0,
            'due_date' => $this->faker->dateTimeBetween('now', '+60 days'),
            'payment_status' => 'unpaid',
        ];
    }

    public function partial(): static
    {
        return $this->state(fn (array $attrs) => [
            'amount_paid' => $attrs['amount_owed'] * 0.5,
            'payment_status' => 'partial',
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn (array $attrs) => [
            'amount_paid' => $attrs['amount_owed'],
            'payment_status' => 'paid',
        ]);
    }
}
