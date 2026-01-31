<?php

return [
    /*
    |--------------------------------------------------------------------------
    | WhatsApp Unofficial API Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the connection to your local/hosted WhatsApp Gateway.
    | Recommended tools:
    | - whatsapp-web.js (Node.js)
    | - venom-bot
    | - Fonnte (Freemium)
    |
    */

    'driver' => env('WA_DRIVER', 'webhook'), // webhook, fonnte, console

    'api_url' => env('WA_API_URL', 'http://localhost:3000/send'),
    
    'api_key' => env('WA_API_KEY', ''),

    'sender_number' => env('WA_SENDER_NUMBER', ''), // Optional, for services that support multi-device
];
