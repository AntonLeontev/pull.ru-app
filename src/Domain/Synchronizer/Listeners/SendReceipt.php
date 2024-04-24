<?php

namespace Src\Domain\Synchronizer\Listeners;

use Src\Domain\Synchronizer\Events\OrderDelivered;
use Src\Domain\Synchronizer\Events\OrderPartlyDelivered;
use Src\Domain\Synchronizer\Jobs\SendRecieptForDeliveredCdekOrder;
use Src\Domain\Synchronizer\Jobs\SendRecieptForPartlyDeliveredCdekOrder;

class SendReceipt
{
    public function handle(OrderDelivered|OrderPartlyDelivered $event): void
    {
        if ($event instanceof OrderDelivered) {
            dispatch(new SendRecieptForDeliveredCdekOrder($event->order));
        }

        if ($event instanceof OrderPartlyDelivered) {
            dispatch(new SendRecieptForPartlyDeliveredCdekOrder($event->order));
        }
    }
}
