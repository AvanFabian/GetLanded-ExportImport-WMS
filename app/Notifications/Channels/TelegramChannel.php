<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use App\Services\TelegramService;

class TelegramChannel
{
    protected $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return void
     */
    public function send($notifiable, Notification $notification)
    {
        // Check if notification has 'toTelegram' method
        if (! method_exists($notification, 'toTelegram')) {
            return;
        }

        $message = $notification->toTelegram($notifiable);
        
        // Get Chat ID:
        // 1. From routeNotificationFor('telegram')
        // 2. From 'telegram_chat_id' attribute
        // 3. Fallback to config default
        $chatId = $notifiable->routeNotificationFor('telegram') 
            ?? $notifiable->telegram_chat_id 
            ?? config('telegram.default_chat_id');

        if (! $chatId) {
            return;
        }

        // Send message (assuming message is string or array with 'text')
        $text = is_array($message) ? ($message['text'] ?? '') : $message;
        $parseMode = is_array($message) ? ($message['parse_mode'] ?? 'Markdown') : 'Markdown';

        $this->telegramService->sendMessage($chatId, $text, $parseMode);
    }
}
