<?php

namespace Src\Domain\Synchronizer\Jobs;

use App\Services\InSales\Exceptions\InsalesRateLimitException;
use App\Services\InSales\InsalesApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Src\Domain\Synchronizer\Enums\OrderStatus;
use Src\Domain\Synchronizer\Models\Order;

class SetPickPointOrderStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 5;

    /**
     * Create a new job instance.
     */
    public function __construct(public Order $order)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(InsalesApiService $service)
    {
        try {
            $service->updateOrderState($this->order->insales_id, OrderStatus::pickpoint);

            $this->order->update(['status' => OrderStatus::pickpoint]);
        } catch (InsalesRateLimitException $e) {
            return $this->release(300);
        }
    }
}
