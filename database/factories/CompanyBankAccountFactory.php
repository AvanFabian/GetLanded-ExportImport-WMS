<?php

namespace Database\Factories;

use App\Models\CompanyBankAccount;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class CompanyBankAccountFactory extends Factory
{
    protected $model = CompanyBankAccount::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'account_name' => $this->faker->company() . ' Account',
            'bank_name' => $this->faker->randomElement(['BCA', 'Mandiri', 'BNI', 'BRI', 'CIMB']),
            'account_number' => $this->faker->numerify('##########'),
            'swift_code' => strtoupper($this->faker->lexify('????????')),
            'currency_code' => 'IDR',
            'is_default' => true,
        ];
    }
}
