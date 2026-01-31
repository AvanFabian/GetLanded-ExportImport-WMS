<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class HolidayService
{
    protected string $baseUrl = 'https://date.nager.at/api/v3/PublicHolidays';

    /**
     * Get upcoming holidays for a specific country to warn about supply chain disruptions.
     * 
     * @param string $countryCode (e.g., 'CN' for China, 'ID' for Indonesia)
     * @return array
     */
    public function getUpcomingHolidays(string $countryCode): array
    {
        $year = now()->year;
        $cacheKey = "holidays:{$countryCode}:{$year}";

        return Cache::remember($cacheKey, 86400, function () use ($countryCode, $year) {
            try {
                $response = Http::timeout(5)->get("{$this->baseUrl}/{$year}/{$countryCode}");
                
                if ($response->successful()) {
                    // Filter: Only future holidays
                    return collect($response->json())
                        ->filter(function ($holiday) {
                            return now()->lte($holiday['date']);
                        })
                        ->take(3) // Only show next 3
                        ->values()
                        ->toArray();
                }
            } catch (\Exception $e) {
                Log::warning("[HolidayService] Failed to fetch for {$countryCode}: " . $e->getMessage());
            }

            return [];
        });
    }

    /**
     * Check if a "Critical Supplier Event" is coming up (e.g. CNY).
     * This is hardcoded logic to find massive disruptions.
     */
    public function getSupplyChainWarnings(): array
    {
        // Check China (World Factory) and Indonesia (Local)
        $holidays = array_merge(
            array_map(fn($h) => $h + ['country' => 'CN'], $this->getUpcomingHolidays('CN') ?? []),
            array_map(fn($h) => $h + ['country' => 'ID'], $this->getUpcomingHolidays('ID') ?? [])
        );

        // Sort by date
        usort($holidays, fn($a, $b) => strcmp($a['date'], $b['date']));

        return array_slice($holidays, 0, 3);
    }
}
