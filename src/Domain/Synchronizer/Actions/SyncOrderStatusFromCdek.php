<?php

namespace Src\Domain\Synchronizer\Actions;

use App\Services\CDEK\CdekApi;
use App\Services\CDEK\FullfillmentApi;
use App\Services\InSales\InSalesApi;
use App\Services\InSales\InsalesApiService;
use App\Services\MoySklad\MoySkladApi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Src\Domain\Synchronizer\Enums\OrderStatus;
use Src\Domain\Synchronizer\Exceptions\FullfillmentOrderNotCreated;
use Src\Domain\Synchronizer\Exceptions\FullfillmentOrderNotCreatedOnce;
use Src\Domain\Synchronizer\Jobs\SendRecieptForDeliveredCdekOrder;
use Src\Domain\Synchronizer\Models\Order;

class SyncOrderStatusFromCdek
{
    public function handle(Order $order): void
    {
        if ($order->fullfillment_id === null) {
            $cdekOrder = CdekApi::getOrder($order->cdek_id);
            $cdekNumber = $cdekOrder->json('entity.cdek_number');

            if (is_null($cdekNumber)) {
                $order->update(['is_error' => true]);

                Log::channel('telegram')->error("Заказ {$cdekOrder->json('entity.number')} не создан в Сдек", [
                    'errors' => $cdekOrder->json('requests.0.errors'),
                    'statuses' => $cdekOrder->json('entity.statuses'),
                ]);

                return;
            }

            $order->update(['fullfillment_id' => $cdekNumber]);
            InsalesApiService::updateCdekTraceInOrder($order->insales_id, (int) $cdekNumber);
        }

        try {
            $ffState = $this->tryGetState($order);
        } catch (FullfillmentOrderNotCreatedOnce $e) {
            return;
        } catch (FullfillmentOrderNotCreated $e) {
            Log::channel('telegram')->warning("Заказ {$order->number} оформлен {$order->created_at->format('d.m.y H:i')} но не добавлен в ФФ");

            return;
        }

        if ($ffState === 'pending_queued') {
            return;
        }

        if (in_array($ffState, ['pending_error', 'pending', 'partly_reserved'])) {
            Log::channel('telegram')->warning("Заказ №{$order->number}. Ошибки в ФФ");

            return;
        }

        $newStatus = OrderStatus::fromCdek($ffState);

        if ($newStatus === $order->status) {
            return;
        }

        if ($newStatus === OrderStatus::delivered) {
            dispatch(new SendRecieptForDeliveredCdekOrder($order));
        }

        DB::transaction(function () use ($order, $newStatus) {
            $this->setAppStatus($order, $newStatus);
            $this->setMSStatus($order, $newStatus);
            $this->setInsalesStatus($order, $newStatus);
        });
    }

    private function setAppStatus(Order $order, OrderStatus $newStatus): void
    {
        $order->update(['status' => $newStatus]);
    }

    private function setMSStatus(Order $order, OrderStatus $newStatus): void
    {
        MoySkladApi::updateCustomerOrder($order->moy_sklad_id, ['state' => $newStatus->toMS()]);
    }

    private function setInsalesStatus(Order $order, OrderStatus $newStatus): void
    {
        InSalesApi::updateOrderState($order->insales_id, $newStatus->toInsales());
    }

    private function tryGetState(Order $order): string
    {
        $response = FullfillmentApi::getOrderByExtId($order->fullfillment_id);

        if ($response->json('total_items') === 0) {
            $order->increment('tries');

            if ($order->tries > 1) {
                throw new FullfillmentOrderNotCreated();
            } else {
                throw new FullfillmentOrderNotCreatedOnce();
            }
        }

        return $response->json('_embedded.order.0.state');
    }
}
