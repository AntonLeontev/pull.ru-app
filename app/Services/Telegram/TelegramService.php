<?php

namespace App\Services\Telegram;

use Illuminate\Support\Facades\Http;

class TelegramService
{
    public static function sendMessage(int $chatId, string $text, ?int $messageThreadId = null): void
    {
        Http::telegram()
            ->post('/sendMessage', [
                'chat_id' => $chatId,
                'text' => $text,
                'message_thread_id' => $messageThreadId,
                'link_preview_options' => [
                    'is_disabled' => true,
                ],
            ]);
    }

    public static function notification(string $text)
    {
        static::sendMessage(
            config('services.telegram.limmite_notifications.chat'),
            $text,
            config('services.telegram.limmite_notifications.thread')
        );
    }

    public static function log(string $text)
    {
        static::sendMessage(
            config('services.telegram.limmite_logs.chat'),
            $text,
            config('services.telegram.limmite_logs.thread')
        );
    }
}
