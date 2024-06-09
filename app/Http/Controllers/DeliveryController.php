<?php

namespace App\Http\Controllers;

use App\Events\OrderNotDelivered;
use App\Services\InSales\InsalesApiService;
use App\Services\MoySklad\MoySkladApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Src\Domain\Delivery\Widget\Widget;
use Src\Domain\FinancialAccounting\Jobs\CreateOperationsInAccountingSystem;
use Src\Domain\Synchronizer\Enums\OrderStatus;
use Src\Domain\Synchronizer\Events\OrderAcceptedAtPickPoint;
use Src\Domain\Synchronizer\Events\OrderDelivered;
use Src\Domain\Synchronizer\Events\OrderTakenByCourier;
use Src\Domain\Synchronizer\Models\Order;

class DeliveryController extends Controller
{
    public function widget()
    {
        $service = new Widget();
        $data = $service->process($_GET, file_get_contents('php://input'));

        return response()->json($data);
    }

    public function orderStatus(Request $request)
    {
        if ($request->json('attributes.is_return')) {
            if (
                $request->json('attributes.code') === 'DELIVERED' ||
                $request->json('attributes.code') === 'NOT_DELIVERED'
            ) {
                dispatch(new CreateOperationsInAccountingSystem($request->json('uuid')));
            }

            return;
        }

        if ($request->json('attributes.code') === 'REMOVED') {
            return;
        }

        if (is_null($request->json('attributes.number'))) {
            return;
        }

        $order = Order::where('number', $request->json('attributes.number'))
            ->where('status', '!=', OrderStatus::canceled->value)
            ->first();

        if (is_null($order)) {
            Log::channel('telegram')->alert(sprintf(
                'При обновлении статуса не найден заказ %s. Новый статус: %s',
                $request->json('attributes.number'),
                $request->json('attributes.code'),
            ));

            return;
        }

        if ($request->json('attributes.code') === 'RECEIVED_AT_SHIPMENT_WAREHOUSE') {
            MoySkladApi::updateCustomerOrder($order->moy_sklad_id, ['state' => OrderStatus::dispatched->toMS()]);
            InsalesApiService::updateOrderState($order->insales_id, OrderStatus::dispatched);
            $order->update(['status' => OrderStatus::dispatched]);
        }

        if ($request->json('attributes.code') === 'ACCEPTED_AT_PICK_UP_POINT') {
            event(new OrderAcceptedAtPickPoint($order));
        }

        if ($request->json('attributes.code') === 'TAKEN_BY_COURIER') {
            event(new OrderTakenByCourier($order));
        }

        if ($request->json('attributes.code') === 'NOT_DELIVERED') {
            if ($order->status->level > OrderStatus::returning->level) {
                Log::channel('telegram')->info('Заказ '.$order->number.' уже возвращен, но мы повторно получили статус не доставлен из сдек');

                return;
            }

            MoySkladApi::updateCustomerOrder($order->moy_sklad_id, ['state' => OrderStatus::returning->toMS()]);
            InsalesApiService::updateOrderState($order->insales_id, OrderStatus::returning);
            $order->update(['status' => OrderStatus::returning]);

            event(new OrderNotDelivered($order));
            dispatch(new CreateOperationsInAccountingSystem($request->json('uuid')));
        }

        if ($request->json('attributes.code') === 'DELIVERED') {
            if ($order->status === OrderStatus::partlyDelivered || $order->status === OrderStatus::delivered) {
                Log::channel('telegram')->info('Заказ '.$order->number.' уже доставлен, но мы повторно получили статус доставлен из сдек');

                return;
            }

            if ($request->json('attributes.status_reason_code') == 20) {
                InsalesApiService::updateOrderState($order->insales_id, OrderStatus::partlyDelivered);
                $order->update(['status' => OrderStatus::partlyDelivered]);
                Log::channel('telegram')->info('Заказ '.$order->number.' частично доставлен');

                dispatch(new CreateOperationsInAccountingSystem($request->json('uuid')));

                return;
            }

            event(new OrderDelivered($order));
            MoySkladApi::updateCustomerOrder($order->moy_sklad_id, ['state' => OrderStatus::delivered->toMS()]);
            InsalesApiService::updateOrderState($order->insales_id, OrderStatus::delivered);
            $order->update(['status' => OrderStatus::delivered]);

            dispatch(new CreateOperationsInAccountingSystem($request->json('uuid')));
        }
    }
}
