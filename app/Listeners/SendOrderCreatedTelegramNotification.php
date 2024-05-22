<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Services\Telegram\TelegramService;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendOrderCreatedTelegramNotification implements ShouldQueue
{
    public $queue = 'high';

    public function handle(OrderCreated $event): void
    {
        $messages = collect(['Создан новый заказ', $event->order->number]);
        $messages->push('Ссылка на заказ: https://limmite.ru/admin2/orders/'.$event->order->insales_id);

        TelegramService::notification($messages->join("\n"));
    }
}
