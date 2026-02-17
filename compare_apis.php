<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;

function check($name, $url) {
    echo "Checking $name...\n";
    try {
        $response = Http::timeout(5)->withoutVerifying()->get($url);
        $data = $response->json();
        
        if ($name == 'Frankfurter') {
            $date = $data['date'] ?? 'N/A';
            $rate = $data['rates']['IDR'] ?? 'N/A';
            echo "Date: $date\nRate: $rate\n";
        } elseif ($name == 'OpenER') {
            $time = $data['time_last_update_utc'] ?? 'N/A';
            $rate = $data['rates']['IDR'] ?? 'N/A';
            echo "Time (UTC): $time\nRate: $rate\n";
        } elseif ($name == 'AwesomeAPI') {
            $item = $data['USDIDR'] ?? [];
            $date = $item['create_date'] ?? 'N/A';
            $rate = $item['bid'] ?? 'N/A';
            echo "Date: $date\nRate: $rate\n";
        }
    } catch (\Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
    echo "----------------\n";
}

check('Frankfurter', 'https://api.frankfurter.app/latest?from=USD&to=IDR');
check('OpenER', 'https://open.er-api.com/v6/latest/USD');
check('AwesomeAPI', 'https://economia.awesomeapi.com.br/json/last/USD-IDR');
