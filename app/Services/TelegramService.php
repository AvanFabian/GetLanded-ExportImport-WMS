<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    protected string $baseUrl = 'https://api.telegram.org/bot';

    /**
     * Send a message via Telegram Bot.
     *
     * @param string $chatId The recipient Chat ID
     * @param string $message The message content (supports Markdown/HTML if parsed_mode is set)
     * @param string $parseMode 'Markdown', 'HTML', or null
     * @return bool
     */
    public function sendMessage(string $chatId, string $message, string $parseMode = 'Markdown'): bool
    {
        $token = config('telegram.bot_token');
        
        if (empty($token)) {
            Log::warning('[Telegram] Bot token is not configured.');
            return false;
        }

        try {
            $url = "{$this->baseUrl}{$token}/sendMessage";

            $response = Http::post($url, [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => $parseMode,
            ]);

            if ($response->successful()) {
                Log::info("[Telegram] Message sent to {$chatId}");
                return true;
            } else {
                Log::error("[Telegram] Failed to send: " . $response->body());
                return false;
            }

        } catch (\Exception $e) {
            Log::error("[Telegram] Connection error: " . $e->getMessage());
            return false;
        }
    }
}
