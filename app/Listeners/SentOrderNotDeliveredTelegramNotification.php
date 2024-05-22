<?php

namespace App\Listeners;

use App\Events\OrderNotDelivered;
use App\Services\CDEK\CdekApi;
use App\Services\Telegram\TelegramService;
use Illuminate\Contracts\Queue\ShouldQueue;

class SentOrderNotDeliveredTelegramNotification implements ShouldQueue
{
    public $queue = 'high';

    public function handle(OrderNotDelivered $event): void
    {
        $response = CdekApi::getOrderByImNumber($event->order->number);

        $messages = collect(['Заказ '.$event->order->number.' не доставлен.']);
        $messages->push('Покупатель: '.$response->json('entity.recipient.name').' +'.$response->json('entity.recipient.phones.0.number'));
        $messages->push('Ссылка на заказ: https://limmite.ru/admin2/orders/'.$event->order->insales_id);

        TelegramService::notification($messages->join("\n"));
    }
}
