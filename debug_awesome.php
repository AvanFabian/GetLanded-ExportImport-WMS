<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;
use App\Models\Currency;

try {
    echo "Getting currencies...\n";
    $currencies = Currency::where('is_base', false)->get();
    
    // Construct pairs: USD-IDR (Essential) AND USD-Foreign (for Cross Rate)
    // We want to calculate Foreign->IDR.
    // Spec: 1 Foreign = (1 USD -> IDR) / (1 USD -> Foreign)
    
    $pairs = [];
    $pairs[] = "USD-IDR"; 
    
    foreach ($currencies as $currency) {
        if ($currency->code !== 'USD') {
             $pairs[] = "USD-{$currency->code}";
        }
    }
    
    $strPairs = implode(',', $pairs);
    
    echo "Pairs: $strPairs\n";
    $url = "https://economia.awesomeapi.com.br/json/last/" . $strPairs;
    echo "URL: $url\n";
    
    echo "Sending request...\n";
    $response = Http::timeout(10)->withoutVerifying()->get($url);
    
    echo "Status: " . $response->status() . "\n";
    
    if ($response->successful()) {
        $data = $response->json();
        
        // Get USD-IDR Base
        $usdIdr = (float) $data['USDIDR']['bid'];
        echo "USD-IDR Rate: $usdIdr\n";
        
        foreach ($currencies as $currency) {
            if ($currency->code === 'USD') {
                echo "USD: $usdIdr\n";
                continue;
            }
            
            $key = "USD{$currency->code}";
            if (isset($data[$key])) {
                $usdForeign = (float) $data[$key]['bid'];
                $crossRate = $usdIdr / $usdForeign;
                echo "{$currency->code}: (USD-{$currency->code} = $usdForeign) => Cross Rate: $crossRate\n";
            } else {
                echo "{$currency->code}: Pair $key NOT FOUND\n";
            }
        }
    } else {
        echo "Body: " . substr($response->body(), 0, 500) . "...\n";
    }

} catch (\Exception $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n";
}
