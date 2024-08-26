<?php

namespace Src\Domain\Synchronizer\Listeners;

use App\Services\MoySklad\MoySkladApi;
use Src\Domain\DiscountSystem\Jobs\SetDiscountByPurchases;
use Src\Domain\Synchronizer\Events\OrderDelivered;
use Src\Domain\Synchronizer\Models\Client;

class ScheduleDiscountUpdatingFromDeliveredOrder
{
    public function handle(OrderDelivered $event): void
    {
        $agentId = MoySkladApi::getCustomerOrder($event->order->moy_sklad_id, ['expand' => 'agent'])->json('agent.id');

        if ($agentId === config('services.moySklad.default_customer_id')) {
            return;
        }

        $client = Client::where('moy_sklad_id', $agentId)->first();
        if (is_null($client)) {
            telegram_log("Обновление скидки пользователя после покупки в ИМ не удалось: не найден пользователь с МС ID $agentId");

            return;
        }

        dispatch(new SetDiscountByPurchases($client));
    }
}
