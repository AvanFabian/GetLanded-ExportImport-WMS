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
     */
    protected string $apiUrl = 'https://api.exchangerate-api.com/v4/latest/USD';

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
            $response = Http::timeout(10)->get($this->apiUrl);

            if (!$response->successful()) {
                Log::critical('Currency API request failed - using cached rates', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return $this->useCachedRates();
            }

            $data = $response->json();
            
            if (!isset($data['conversion_rates']) && !isset($data['rates'])) {
                Log::critical('Invalid currency API response format - using cached rates', ['data' => $data]);
                return $this->useCachedRates();
            }

            $rates = $data['conversion_rates'] ?? $data['rates'];
            
            // Cache the raw rates for fallback
            Cache::forever(self::CACHE_KEY, [
                'rates' => $rates,
                'fetched_at' => now()->toIso8601String(),
            ]);

            // Update rates in database
            $this->updateDatabaseRates($rates);

            Log::info('Currency rates updated and cached successfully');
            return true;

        } catch (\Exception $e) {
            Log::critical('Currency rate fetch failed - using cached rates', [
                'error' => $e->getMessage(),
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
    protected function updateDatabaseRates(array $rates): void
    {
        foreach ($rates as $code => $rateToUsd) {
            $currency = Currency::where('code', $code)->first();
            
            if ($currency && !$currency->is_base) {
                // Convert rate to IDR base
                $idrRate = $rates['IDR'] ?? 15850; // Fallback
                
                if ($code === 'IDR') {
                    $currency->update(['exchange_rate' => 1.00000000]);
                } else {
                    $rateToIdr = $idrRate / $rateToUsd;
                    $currency->update([
                        'exchange_rate' => $rateToIdr,
                        'rate_updated_at' => now(),
                    ]);
                }
            }
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
