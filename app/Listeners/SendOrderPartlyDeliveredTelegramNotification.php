<?php

namespace App\Listeners;

use App\Services\Telegram\TelegramService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Src\Domain\Synchronizer\Events\OrderPartlyDelivered;

class SendOrderPartlyDeliveredTelegramNotification implements ShouldQueue
{
    public $queue = 'high';

    public function handle(OrderPartlyDelivered $event): void
    {
        TelegramService::notification('Частично доставлен заказ '.$event->order->number);
    }
}
