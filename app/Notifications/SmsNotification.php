<?php

namespace App\Notifications;

use App\Support\SmsMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

abstract class SmsNotification extends Notification
{
    use Queueable;

    public function toSms(object $notifiable): SmsMessage
    {
        return (new SmsMessage)
            ->to($notifiable->phone)
            ->text('');

    }
}
