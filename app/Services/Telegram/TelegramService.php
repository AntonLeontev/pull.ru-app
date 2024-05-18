<?php

namespace App\Services\Telegram;

use Illuminate\Support\Facades\Http;

class TelegramService
{
    public static function sendMessage(string $text)
    {
        Http::telegram()
            ->post('/sendMessage', [
                'chat_id' => config('services.telegram.chat'),
                'text' => $text,
            ]);
    }
}
