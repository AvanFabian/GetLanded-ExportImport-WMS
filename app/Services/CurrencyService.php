<?php

namespace App\Services;

use App\Models\Currency;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * CurrencyService
 * 
 * Handles currency conversion and exchange rate synchronization.
 * Base currency approach: IDR = 1.00000000
 */
class CurrencyService
{
    /**
     * The API endpoint for exchange rates.
     * using AwesomeAPI (Free, Real-Time, 30s updates)
     */
    protected string $apiUrl = 'https://economia.awesomeapi.com.br/json/last/';

    /**
     * Cache key for last known good rates.
     */
    protected const CACHE_KEY = 'currency:last_known_rates';

    /**
     * Fetch latest exchange rates from external API.
     * On success, caches rates. On failure, uses cached rates if available.
     * 
     * @return bool True if rates were updated successfully
     */
    public function fetchLatestRates(): bool
    {
        try {
            // Get all active non-base currencies
            $currencies = Currency::where('is_base', false)->get();
            
            if ($currencies->isEmpty()) {
                Log::warning('No currencies to sync.');
                return false;
            }

            // We use USD as a bridge for all currencies to ensure availability
            // Logic: 
            // 1. Fetch USD-IDR (Base)
            // 2. Fetch USD-Foreign (for others)
            // 3. Foreign-IDR = (USD-IDR) / (USD-Foreign)
            
            $apiPairs = [];
            $apiPairs[] = 'USD-IDR'; // Essential base pair
            
            foreach ($currencies as $currency) {
                if ($currency->code !== 'USD') {
                    $apiPairs[] = "USD-{$currency->code}";
                }
            }

            // Remove duplicates and implode
            $url = $this->apiUrl . implode(',', array_unique($apiPairs));
            
            $response = Http::timeout(10)->withoutVerifying()->get($url);
            
            if ($response->successful()) {
                $data = $response->json();
                $rates = [];
                $successCount = 0;
                $updateTime = now();
                
                // Get USD-IDR rate first
                $usdIdrKey = 'USDIDR';
                if (!isset($data[$usdIdrKey]) || !isset($data[$usdIdrKey]['bid'])) {
                    Log::error('AwesomeAPI did not return USD-IDR rate');
                    return $this->useCachedRates();
                }
                
                $usdIdrRate = (float) $data[$usdIdrKey]['bid'];
                
                // Set USD rate
                if ($currencies->where('code', 'USD')->isNotEmpty()) {
                    $rates['USD'] = $usdIdrRate;
                    $successCount++;
                     // Use USD timestamp as reference
                    if (isset($data[$usdIdrKey]['create_date'])) {
                        $updateTime = $data[$usdIdrKey]['create_date'];
                    }
                }

                // Calculate others
                foreach ($currencies as $currency) {
                    if ($currency->code === 'USD') continue;
                    
                    $key = "USD{$currency->code}";
                    
                    if (isset($data[$key]) && isset($data[$key]['bid'])) {
                        $usdForeignRate = (float) $data[$key]['bid'];
                        
                        // Cross rate calculation: (USD-IDR) / (USD-Foreign)
                        if ($usdForeignRate > 0) {
                            $calculatedRate = $usdIdrRate / $usdForeignRate;
                            $rates[$currency->code] = $calculatedRate;
                            $successCount++;
                        }
                    }
                }

                if ($successCount > 0) {
                    // Cache the rates
                    Cache::forever(self::CACHE_KEY, [
                        'rates' => $rates,
                        'fetched_at' => now()->toIso8601String(),
                        'provider_update' => $updateTime
                    ]);

                    // Update rates in database
                    $this->updateDatabaseRates($rates);

                    Log::info("Currency rates updated successfully for {$successCount} currencies using AwesomeAPI (Cross-Rates)");
                    return true;
                }
            }
            
            Log::error('Currency API failure', [
                'provider' => 'AwesomeAPI',
                'status' => $response->status(),
                'body' => $response->body(),
                'url' => $url
            ]);
            return $this->useCachedRates();

        } catch (\Exception $e) {
            Log::critical('Currency rate fetch global failure', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->useCachedRates();
        }
    }

    /**
     * Use cached rates when API is unavailable.
     */
    protected function useCachedRates(): bool
    {
        $cached = Cache::get(self::CACHE_KEY);
        
        if (!$cached || !isset($cached['rates'])) {
            Log::error('No cached currency rates available - system may use stale data');
            return false;
        }

        Log::warning('Using cached currency rates', [
            'fetched_at' => $cached['fetched_at'] ?? 'unknown',
        ]);

        $this->updateDatabaseRates($cached['rates']);
        return true; // Partial success - using cached data
    }

    /**
     * Update database with rate data.
     */
    /**
     * Update database with rate data.
     * Rates array key is Currency Code (e.g. USD), value is rate to IDR (e.g. 15950)
     */
    protected function updateDatabaseRates(array $rates): void
    {
        foreach ($rates as $code => $rateToIdr) {
            // Skip IDR itself or if rate is invalid
            if ($code === 'IDR' || $rateToIdr <= 0) {
                continue;
            }

            Currency::where('code', $code)->update([
                'exchange_rate' => $rateToIdr,
                'rate_updated_at' => now(),
            ]);
        }
    }

    /**
     * Convert an amount between currencies using current rates.
     * 
     * @param float $amount Amount to convert
     * @param string $from Source currency code
     * @param string $to Target currency code
     * @return float Converted amount
     */
    public function convert(float $amount, string $from, string $to): float
    {
        if ($from === $to) {
            return $amount;
        }

        $fromCurrency = Currency::findByCode($from);
        $toCurrency = Currency::findByCode($to);

        if (!$fromCurrency || !$toCurrency) {
            throw new \InvalidArgumentException("Invalid currency code: {$from} or {$to}");
        }

        // Convert to base (IDR) first, then to target
        // From currency rate = IDR per 1 unit of from currency
        $amountInBase = $amount * $fromCurrency->exchange_rate;
        
        // To currency rate = IDR per 1 unit of to currency
        $result = $amountInBase / $toCurrency->exchange_rate;

        return round($result, 2);
    }

    /**
     * Convert using a specific historical rate.
     * 
     * @param float $amount Amount to convert
     * @param string $from Source currency code
     * @param string $to Target currency code
     * @param float $historicalRate The locked exchange rate at transaction time
     * @return float Converted amount
     */
    public function convertUsingRate(float $amount, string $from, string $to, float $historicalRate): float
    {
        if ($from === $to) {
            return $amount;
        }

        // Historical rate is stored as "IDR per 1 unit of transaction currency"
        if ($from === 'IDR') {
            // Converting IDR to foreign currency
            return round($amount / $historicalRate, 2);
        } else {
            // Converting foreign currency to IDR
            return round($amount * $historicalRate, 2);
        }
    }

    /**
     * Get current exchange rate for a currency.
     * 
     * @param string $code Currency code
     * @return float Exchange rate (IDR per 1 unit)
     */
    public function getRate(string $code): float
    {
        $currency = Currency::findByCode($code);
        
        if (!$currency) {
            throw new \InvalidArgumentException("Currency not found: {$code}");
        }

        return (float) $currency->exchange_rate;
    }

    /**
     * Get all currencies with their rates.
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllRates()
    {
        return Currency::orderBy('is_base', 'desc')
            ->orderBy('code')
            ->get();
    }
}
