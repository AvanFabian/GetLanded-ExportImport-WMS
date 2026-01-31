<?php

namespace Tests\Feature;

use App\Models\Currency;
use App\Models\Supplier;
use App\Services\CurrencyService;
use App\Services\GeocodingService;
use App\Services\HolidayService;
use App\Services\TelegramService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class IntegrationTest extends TestCase
{
    use RefreshDatabase;
    
    protected $user;
    protected $company;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->company = \App\Models\Company::create([
            'name' => 'Test Company',
            'code' => 'TEST',
            'address' => 'Test Address',
            'email' => 'test@company.com',
            'phone' => '123456789'
        ]);

        $this->user = \App\Models\User::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $this->actingAs($this->user);
    }

    /** @test */
    public function frankfurter_currency_service_fetches_and_updates_rates()
    {
        $this->withoutExceptionHandling();
        
        // 1. Setup: Create USD currency
        Currency::create([
            'code' => 'USD',
            'name' => 'US Dollar',
            'symbol' => '$',
            'exchange_rate' => 15000,
            'is_base' => false,
        ]);

        // 2. Mock API Response
        Http::fake([
            'api.frankfurter.app/*' => Http::response([
                'amount' => 1.0,
                'base' => 'USD',
                'date' => '2025-01-31',
                'rates' => ['IDR' => 16000]
            ], 200),
        ]);

        // 3. Execute Service
        $service = new CurrencyService();
        $result = $service->fetchLatestRates();

        // 4. Verify
        $this->assertTrue($result);
        $this->assertDatabaseHas('currencies', [
            'code' => 'USD',
            'exchange_rate' => 16000, 
        ]);
    }
}
