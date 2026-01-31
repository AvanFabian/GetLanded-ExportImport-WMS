<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsappService
{
    /**
     * Send a WhatsApp message.
     *
     * @param string $to Phone number (international format, e.g., 628123456789)
     * @param string $message content
     * @return bool
     */
    public function send(string $to, string $message): bool
    {
        $driver = config('whatsapp.driver', 'console');

        try {
            if ($driver === 'webhook') {
                return $this->sendViaWebhook($to, $message);
            }
            
            if ($driver === 'fonnte') {
                return $this->sendViaFonnte($to, $message);
            }

            // Default: Log to console (useful for dev without a real gateway)
            Log::info("[WhatsApp] To: {$to} | Message: {$message}");
            return true;
        } catch (\Exception $e) {
            Log::error("[WhatsApp] Failed to send: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send via Generic Webhook (e.g., whatsapp-web.js wrapper)
     * Payload: { "number": "628...", "message": "..." }
     */
    protected function sendViaWebhook(string $to, string $message): bool
    {
        $url = config('whatsapp.api_url');
        $key = config('whatsapp.api_key');

        $response = Http::withHeaders([
            'Authorization' => $key,
            'Content-Type' => 'application/json',
        ])->post($url, [
            'number' => $to, // Adjust key based on your local gateway documentation
            'message' => $message,
        ]);

        return $response->successful();
    }
    
    /**
     * Send via Fonnte (Popular Indonesian unofficial gateway)
     */
    protected function sendViaFonnte(string $to, string $message): bool
    {
        $response = Http::withHeaders([
            'Authorization' => config('whatsapp.api_key'),
        ])->post('https://api.fonnte.com/send', [
            'target' => $to,
            'message' => $message,
        ]);
        
        return $response->successful();
    }
}
