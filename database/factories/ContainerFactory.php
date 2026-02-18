<?php

namespace Database\Factories;

use App\Models\Container;
use App\Models\Company;
use App\Models\OutboundShipment;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContainerFactory extends Factory
{
    protected $model = Container::class;

    public function definition(): array
    {
        $type = $this->faker->randomElement(array_keys(Container::TYPES));
        $specs = Container::TYPES[$type];

        return [
            'company_id' => Company::factory(),
            'outbound_shipment_id' => OutboundShipment::factory(),
            'container_number' => strtoupper($this->faker->bothify('????#######')),
            'container_type' => $type,
            'max_weight_kg' => $specs['max_weight'],
            'max_volume_cbm' => $specs['max_volume'],
            'status' => 'empty',
            'used_weight_kg' => 0,
            'used_volume_cbm' => 0,
        ];
    }

    public function loading(): static
    {
        return $this->state(fn (array $attrs) => [
            'status' => 'loading',
            'used_weight_kg' => $attrs['max_weight_kg'] * 0.3,
            'used_volume_cbm' => $attrs['max_volume_cbm'] * 0.3,
        ]);
    }

    public function sealed(): static
    {
        return $this->state(fn (array $attrs) => [
            'status' => 'sealed',
            'seal_number' => 'SEAL-' . $this->faker->unique()->randomNumber(8),
            'used_weight_kg' => $attrs['max_weight_kg'] * 0.8,
            'used_volume_cbm' => $attrs['max_volume_cbm'] * 0.7,
        ]);
    }
}
