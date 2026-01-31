<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Telegram Bot API Configuration
    |--------------------------------------------------------------------------
    |
    | Configure your Telegram Bot credentials here.
    | Get your token from @BotFather.
    |
    */

    'bot_token' => env('TELEGRAM_BOT_TOKEN', ''),
    
    // Default Chat ID for admin notifications (optional)
    'default_chat_id' => env('TELEGRAM_CHAT_ID', ''), 
];
