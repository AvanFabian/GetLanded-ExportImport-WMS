<?php

namespace Database\Factories;

use App\Models\CustomsDeclaration;
use App\Models\Company;
use App\Models\OutboundShipment;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomsDeclarationFactory extends Factory
{
    protected $model = CustomsDeclaration::class;

    public function definition(): array
    {
        $declaredValue = $this->faker->randomFloat(2, 1000, 100000);
        $dutyRate = $this->faker->randomFloat(2, 0, 15);
        $dutyAmount = $declaredValue * ($dutyRate / 100);
        $vatRate = 11; // Indonesia PPN
        $vatAmount = ($declaredValue + $dutyAmount) * ($vatRate / 100);

        return [
            'company_id' => Company::factory(),
            'outbound_shipment_id' => OutboundShipment::factory(),
            'declaration_number' => 'CD-' . $this->faker->unique()->randomNumber(8),
            'declaration_type' => 'export',
            'declaration_date' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'customs_office' => $this->faker->randomElement(['KPU Tanjung Priok', 'KPPBC Tanjung Perak', 'KPPBC Belawan']),
            'hs_code' => $this->faker->numerify('####.##.##'),
            'declared_value' => $declaredValue,
            'currency_code' => 'USD',
            'duty_rate' => $dutyRate,
            'duty_amount' => $dutyAmount,
            'vat_rate' => $vatRate,
            'vat_amount' => $vatAmount,
            'excise_amount' => 0,
            'total_tax' => $dutyAmount + $vatAmount,
            'status' => 'draft',
        ];
    }

    public function cleared(): static
    {
        return $this->state(fn () => ['status' => 'cleared']);
    }
}
