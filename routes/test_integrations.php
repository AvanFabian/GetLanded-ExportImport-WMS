<?php

use Illuminate\Support\Facades\Route;
use App\Services\HolidayService;
use App\Services\CurrencyService;
use App\Services\GeocodingService;
use Illuminate\Support\Facades\DB;

// MANUAL TEST ROUTE FOR INTEGRATIONS
Route::get('/test-integrations', function () {
    $results = [];

    // 1. Test Holiday Service (Nager.Date)
    try {
        $holidayService = new HolidayService();
        $results['holidays_cn'] = $holidayService->getUpcomingHolidays('CN');
        $results['supply_chain_warnings'] = $holidayService->getSupplyChainWarnings();
    } catch (\Exception $e) {
        $results['holidays_error'] = $e->getMessage();
    }

    // 2. Test Currency Service (Frankfurter)
    try {
        $currencyService = new CurrencyService();
        // Force fetch
        $success = $currencyService->fetchLatestRates();
        $results['currency_sync_status'] = $success ? 'Success' : 'Failed';
        $results['usd_rate'] = \App\Models\Currency::where('code', 'USD')->value('exchange_rate');
    } catch (\Exception $e) {
        $results['currency_error'] = $e->getMessage();
    }

    // 3. Test Geocoding Service (Nominatim)
    try {
        $geoService = new GeocodingService();
        $results['geocoding_test'] = $geoService->getCoordinates("Jakarta, Indonesia");
    } catch (\Exception $e) {
        $results['geocoding_error'] = $e->getMessage();
    }

    return response()->json($results, 200, [], JSON_PRETTY_PRINT);
});
