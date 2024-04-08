<?php

namespace Src\Domain\Synchronizer\Listeners;

use Src\Domain\Synchronizer\Events\OrderDelivered;
use Src\Domain\Synchronizer\Jobs\SendRecieptForDeliveredCdekOrder;

class SendReceipt
{
    public function handle(OrderDelivered $event): void
    {
        dispatch(new SendRecieptForDeliveredCdekOrder($event->order));
    }
}
