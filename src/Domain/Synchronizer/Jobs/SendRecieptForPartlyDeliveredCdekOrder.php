<?php

namespace Src\Domain\Synchronizer\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Src\Domain\Synchronizer\Actions\SendRecieptForPartlyDeliveredOrder;
use Src\Domain\Synchronizer\Enums\OrderPaymentStatus;
use Src\Domain\Synchronizer\Models\Order;

class SendRecieptForPartlyDeliveredCdekOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public Order $order) {}

    /**
     * Execute the job.
     */
    public function handle(SendRecieptForPartlyDeliveredOrder $partlyAction)
    {
        if ($this->order->payment_status === OrderPaymentStatus::paid) {
            //TODO send return reciept
            return;
        }

        $partlyAction->handle($this->order);
    }
}
