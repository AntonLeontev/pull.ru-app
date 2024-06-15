<?php

namespace Src\Domain\Synchronizer\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Src\Domain\Synchronizer\Actions\SyncOrderStatusFromCdek;
use Src\Domain\Synchronizer\Models\Order;

class SyncOrderStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Order $order)
    {
    }

    public function handle(SyncOrderStatusFromCdek $action): void
    {
        try {
            $action->handle($this->order);
        } catch (\Throwable $th) {
            Log::channel('telegram')->warning('Job Error: Не удалось обновить статус заказа '.$this->order->number.'. '.$th->getMessage());
            $this->fail();
        }
    }
}
