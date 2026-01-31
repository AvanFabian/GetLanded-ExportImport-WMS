<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeocodingService
{
    // Nominatim requires a User-Agent identifying the app
    protected string $userAgent = 'LandedOS/1.0 (internal@landedos.com)';
    protected string $baseUrl = 'https://nominatim.openstreetmap.org/search';

    /**
     * Convert an address string to Coordinates (Lat/Lng).
     * 
     * @param string $address
     * @return array|null ['lat' => 1.23, 'lon' => 4.56] or null
     */
    public function getCoordinates(string $address): ?array
    {
        try {
            $response = Http::withHeaders([
                'User-Agent' => $this->userAgent,
            ])->get($this->baseUrl, [
                'q' => $address,
                'format' => 'json',
                'limit' => 1,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if (!empty($data) && isset($data[0]['lat']) && isset($data[0]['lon'])) {
                    return [
                        'lat' => $data[0]['lat'],
                        'lon' => $data[0]['lon'],
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::warning("[Geocoding] Failed to geocode '{$address}': " . $e->getMessage());
        }

        return null;
    }
}
