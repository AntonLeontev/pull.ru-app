<?php

namespace App\Listeners;

use App\Services\Telegram\TelegramService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Src\Domain\Synchronizer\Events\OrderDelivered;

class SendOrderDeliveredTelegramNotification implements ShouldQueue
{
    public $queue = 'high';

    public function handle(OrderDelivered $event): void
    {
        TelegramService::notification('Доставлен заказ '.$event->order->number);
    }
}
