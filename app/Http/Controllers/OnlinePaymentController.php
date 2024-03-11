<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Src\Domain\Payments\Enums\OnlinePaymentStatus;
use Src\Domain\Payments\Models\OnlinePayment;
use Src\Domain\Synchronizer\Enums\OrderPaymentStatus;
use Src\Domain\Synchronizer\Enums\OrderPaymentType;
use Src\Domain\Synchronizer\Enums\OrderStatus;
use Src\Domain\Synchronizer\Jobs\SendOrderToDelivery;
use Src\Domain\Synchronizer\Models\Order;

class OnlinePaymentController extends Controller
{
    public function cloudpaymentsPay(Request $request)
    {
        $order = Order::query()
            ->where('number', $request->json('InvoiceId'))
            ->where('payment_status', OrderPaymentStatus::pending)
            ->where('payment_type', OrderPaymentType::online)
            ->where('status', OrderStatus::init)
            ->first();

        OnlinePayment::create([
            'order_id' => $order?->id,
            'status' => OnlinePaymentStatus::from($request->Status),
            'transaction_id' => $request->TransactionId,
            'payment_amount' => $request->PaymentAmount,
            'user_email' => $request->Email,
        ]);

        if (is_null($order)) {
            Log::channel('telegram')->alert("Пришла онлайн оплата заказа {$request->json('InvoiceId')}, но его нет в базе");

            return response()->json(['code' => 0]);
        }

        $order->update(['payment_status' => OrderPaymentStatus::paid]);

        dispatch(new SendOrderToDelivery($order));

        return response()->json(['code' => 0]);
    }
}
