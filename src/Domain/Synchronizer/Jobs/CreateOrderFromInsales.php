<?php

namespace Src\Domain\Synchronizer\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Src\Domain\Synchronizer\Actions\CreateOrderFromInsales as ActionsCreateOrderFromInsales;
use Src\Domain\Synchronizer\Enums\OrderStatus;
use Src\Domain\Synchronizer\Models\Order;

class CreateOrderFromInsales implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public array $request)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(ActionsCreateOrderFromInsales $createOrder): void
    {
        $order = Order::where('insales_id', $this->request['id'])->first();

        if ($order->status === OrderStatus::approved && $order->cdek_id !== null) {
            return;
        }

        $order->update(['status' => OrderStatus::approved]);

        $createOrder->handle($this->request);
    }
}
