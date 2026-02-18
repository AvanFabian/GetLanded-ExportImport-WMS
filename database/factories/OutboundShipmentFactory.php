<?php

namespace Database\Factories;

use App\Models\OutboundShipment;
use App\Models\Company;
use App\Models\SalesOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

class OutboundShipmentFactory extends Factory
{
    protected $model = OutboundShipment::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'sales_order_id' => SalesOrder::factory(),
            'shipment_number' => 'SHP-' . date('Y') . '-' . str_pad($this->faker->unique()->randomNumber(5), 5, '0', STR_PAD_LEFT),
            'shipment_date' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'estimated_arrival' => $this->faker->dateTimeBetween('+7 days', '+45 days'),
            'carrier_name' => $this->faker->randomElement(['Maersk', 'MSC', 'CMA CGM', 'Hapag-Lloyd', 'ONE']),
            'vessel_name' => 'MV ' . $this->faker->word(),
            'port_of_loading' => $this->faker->randomElement(['Tanjung Priok', 'Tanjung Perak', 'Belawan']),
            'port_of_discharge' => $this->faker->randomElement(['Rotterdam', 'Hamburg', 'Singapore', 'Tokyo']),
            'destination_country' => $this->faker->country(),
            'incoterm' => $this->faker->randomElement(['FOB', 'CIF', 'CFR']),
            'freight_cost' => $this->faker->randomFloat(2, 500, 5000),
            'insurance_cost' => $this->faker->randomFloat(2, 100, 1000),
            'currency_code' => 'USD',
            'status' => 'draft',
        ];
    }

    public function shipped(): static
    {
        return $this->state(fn () => [
            'status' => 'shipped',
            'bill_of_lading' => 'BL-' . strtoupper($this->faker->bothify('??######')),
        ]);
    }
}
