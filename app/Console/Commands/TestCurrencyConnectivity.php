<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Services\CurrencyService;

class TestCurrencyConnectivity extends Command
{
    protected $signature = 'test:currency';
    protected $description = 'Test connectivity to Currency API';

    public function handle()
    {
        $this->info('Testing connectivity to AwesomeAPI...');
        
        $url = 'https://economia.awesomeapi.com.br/json/last/USD-IDR';
        
        $this->info("Target URL: $url");
        
        try {
            $start = microtime(true);
            $response = Http::timeout(10)->withoutVerifying()->get($url);
            $duration = round((microtime(true) - $start) * 1000, 2);
            
            $this->info("Response received in {$duration}ms");
            $this->info("Status: " . $response->status());
            
            if ($response->successful()) {
                $this->info("Body Preview: " . substr($response->body(), 0, 200));
                $this->info("✅ Connectivity successful!");
            } else {
                $this->error("❌ API Error: " . $response->body());
            }
            
        } catch (\Exception $e) {
            $this->error("❌ Connection Failed: " . $e->getMessage());
            if (str_contains($e->getMessage(), 'cURL error')) {
                 $this->warn("This is likely a DNS or Firewall issue on the server.");
            }
        }
        
        $this->info('--- Testing CurrencyService Internal Logic ---');
        try {
            $service = new CurrencyService();
            $result = $service->fetchLatestRates();
            $this->info("Service Result: " . ($result ? 'Success' : 'Failure'));
        } catch (\Exception $e) {
            $this->error("Service Exception: " . $e->getMessage());
        }
    }
}
