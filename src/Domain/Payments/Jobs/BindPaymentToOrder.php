<?php

namespace Src\Domain\Payments\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Src\Domain\Payments\Enums\OnlinePaymentStatus;
use Src\Domain\Payments\Models\OnlinePayment;
use Src\Domain\Synchronizer\Enums\OrderPaymentStatus;
use Src\Domain\Synchronizer\Enums\OrderPaymentType;
use Src\Domain\Synchronizer\Enums\OrderStatus;
use Src\Domain\Synchronizer\Jobs\SendPaidOrderToDelivery;
use Src\Domain\Synchronizer\Models\Order;

class BindPaymentToOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private array $request)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $request = objectize($this->request);

        $order = Order::query()
            ->where('number', $request->InvoiceId)
            ->where('payment_status', OrderPaymentStatus::pending)
            ->where('payment_type', OrderPaymentType::online)
            ->where('status', OrderStatus::init)
            ->first();

        if (is_null($order)) {
            if ($this->attempts() == 5) {
                Log::channel('telegram')->critical("Заказ $request->InvoiceId был оплачен, но так и не повился в БД. Прошло уже 10 минут");

                return;
            }

            $this->release(60 * 2);
        }

        OnlinePayment::create([
            'order_id' => $order?->id,
            'status' => OnlinePaymentStatus::from($request->Status),
            'transaction_id' => $request->TransactionId,
            'payment_amount' => $request->PaymentAmount,
            'user_email' => $request->Email,
        ]);

        $order->update(['payment_status' => OrderPaymentStatus::paid]);

        dispatch(new SendPaidOrderToDelivery($order));
    }
}
