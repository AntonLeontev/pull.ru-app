<?php

namespace App\Http\Controllers;

use App\Services\InSales\InSalesApi;
use App\Services\InSales\InsalesApiService;
use App\Services\MoySklad\MoySkladApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Src\Domain\Delivery\Widget\Widget;
use Src\Domain\Synchronizer\Enums\OrderStatus;
use Src\Domain\Synchronizer\Events\OrderDelivered;
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
            return;
        }

        $order = Order::where('number', $request->json('attributes.number'))->first();

        if (is_null($order)) {
            Log::channel('telegram')->alert('При обновлении статуса не найден заказ '.$request->json('attributes.number'));

            return;
        }

        if ($request->json('attributes.code') === 'DELIVERED') {
            if ($request->json('attributes.status_reason_code') == 20) {
                InSalesApi::updateOrderState($order->insales_id, OrderStatus::partlyDelivered->toInsales());
                $order->update(['status' => OrderStatus::partlyDelivered]);
                Log::channel('telegram')->info('Заказ '.$order->number.' частично доставлен');

                return;
            }

            event(new OrderDelivered($order));
            MoySkladApi::updateCustomerOrder($order->moy_sklad_id, ['state' => OrderStatus::delivered->toMS()]);
            InsalesApiService::updateOrderState($order->insales_id, OrderStatus::delivered->toInsales());
            $order->update(['status' => OrderStatus::delivered]);
        }
    }
}
