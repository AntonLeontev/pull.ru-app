<?php

namespace Src\Domain\Synchronizer\Jobs;

use App\Services\InSales\InSalesApi;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Src\Domain\Synchronizer\Actions\CreateOrderFromInsales;
use Src\Domain\Synchronizer\Enums\OrderPaymentStatus;
use Src\Domain\Synchronizer\Enums\OrderStatus;
use Src\Domain\Synchronizer\Models\Order;

class SendPaidOrderToDelivery implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 2;

    /**
     * Create a new job instance.
     */
    public function __construct(public Order $order)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(CreateOrderFromInsales $createAction)
    {
        InSalesApi::updateOrderPaymentState($this->order->insales_id, OrderPaymentStatus::paid->value);

        $insalesOrder = InSalesApi::getOrder($this->order->insales_id)->json();

        $createAction->handle($insalesOrder);

        InSalesApi::updateOrderState($this->order->insales_id, OrderStatus::dispatched->toInsales());
    }
}
