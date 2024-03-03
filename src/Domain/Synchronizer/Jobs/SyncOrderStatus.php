<?php

namespace Src\Domain\Synchronizer\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
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
        $action->handle($this->order);
    }
}
