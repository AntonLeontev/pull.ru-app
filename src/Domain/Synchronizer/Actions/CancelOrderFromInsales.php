<?php

namespace Src\Domain\Synchronizer\Actions;

use App\Services\CDEK\CdekApi;
use App\Services\CDEK\FullfillmentApi;
use App\Services\InSales\InsalesApiService;
use Illuminate\Support\Facades\Log;
use Src\Domain\Synchronizer\Enums\OrderStatus;
use Src\Domain\Synchronizer\Models\Order;

class CancelOrderFromInsales
{
    public function __construct()
    {
    }

    public function handle(array $request): void
    {
        $request = objectize($request);

        $order = Order::where('insales_id', $request->id)->first();

        if ($order->status === OrderStatus::init) {
            InsalesApiService::updateOrderState($request->id, OrderStatus::canceled);

            $order->update(['status' => OrderStatus::canceled]);
        }

        if ($order->status === OrderStatus::approved) {
            $ffState = OrderStatus::fromFF(FullfillmentApi::getOrderByExtId($order->fullfillment_id)->json('_embedded.order.0.state'));

            if ($ffState === OrderStatus::assembled || $order->status === OrderStatus::assembling) {
                $this->cancelAssembled($order);

                return;
            }

            Log::channel('telegram')->notice('Отмена несобранного заказа: '.$order->number.' Нужно проконтролировать');
            CdekApi::cancelOrder($order->cdek_id);
            // Проверить в сдек. Если еще не собран, то удалить в логистике и отменить в ФФ
        }

        if ($order->status === OrderStatus::assembled || $order->status === OrderStatus::assembling) {
            $this->cancelAssembled($order);
        }

        if ($order->status->level() >= OrderStatus::dispatched->level()) {
            Log::channel('telegram')->notice('Отмена отправленного заказа: '.$order->number.' Нужно проконтролировать');
            CdekApi::cancelOrder($order->cdek_id);
        }
    }

    private function cancelAssembled(Order $order)
    {
        Log::channel('telegram')->notice('Отмена собранного заказа: '.$order->number.' Нужно написать письмо в CDEK');

        CdekApi::cancelOrder($order->cdek_id);
        InsalesApiService::updateOrderState($order->insales_id, OrderStatus::canceling);
    }
}
