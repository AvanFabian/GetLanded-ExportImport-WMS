<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\Company;
use App\Models\Customer;
use App\Models\SalesOrder;
use App\Models\CompanyBankAccount;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'customer_id' => Customer::factory(),
            'payment_date' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'amount' => $this->faker->randomFloat(2, 100, 50000),
            'bank_fees' => 0,
            'currency_code' => 'IDR',
            'exchange_rate' => 1,
            'base_currency_amount' => fn (array $attrs) => $attrs['amount'] * ($attrs['exchange_rate'] ?? 1),
            'payment_method' => 'bank_transfer',
            'reference' => fn () => 'PAY-' . strtoupper($this->faker->unique()->bothify('??????##')),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }

    public function forOrder(SalesOrder $order): static
    {
        return $this->state(fn () => [
            'company_id' => $order->company_id,
            'sales_order_id' => $order->id,
            'customer_id' => $order->customer_id,
        ]);
    }

    public function usd(): static
    {
        return $this->state(fn () => [
            'currency_code' => 'USD',
            'exchange_rate' => 15850,
        ]);
    }

    public function withBankFees(float $fees = 25.00): static
    {
        return $this->state(fn () => [
            'bank_fees' => $fees,
        ]);
    }

    public function deposit(): static
    {
        return $this->state(fn () => [
            'sales_order_id' => null,
        ]);
    }
}
