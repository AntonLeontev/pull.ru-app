<?php

namespace App\Notifications\Channels;

use App\Notifications\SmsNotification;
use App\Services\Bytehand\BytehandService;

class Bytehand
{
    public function send($notifiable, SmsNotification $notification)
    {
        $message = $notification->toSms($notifiable);

        $service = app(BytehandService::class);

        $service->sendSms($message->to, $message->text);
    }
}
