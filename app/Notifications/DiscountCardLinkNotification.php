<?php

namespace App\Notifications;

use App\Notifications\Channels\Bytehand;
use App\Support\SmsMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class DiscountCardLinkNotification extends SmsNotification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public string $link) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [Bytehand::class];
    }

    /**
     * Get the sms representation of the notification.
     */
    public function toSms(object $notifiable): SmsMessage
    {
        return (new SmsMessage)
            ->to($notifiable->phone)
            ->text("Ссылка для получения скидочной карты: $this->link");
    }

    /**
     * Determine which queues should be used for each notification channel.
     *
     * @return array<string, string>
     */
    public function viaQueues(): array
    {
        return [
            Bytehand::class => 'high',
        ];
    }
}
