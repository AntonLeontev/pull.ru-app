<?php

namespace Src\Domain\Synchronizer\Listeners;

use Src\Domain\Synchronizer\Events\OrderAcceptedAtPickPoint;
use Src\Domain\Synchronizer\Jobs\SetPickPointOrderStatus as JobsSetPickPointOrderStatus;

class SetPickPointOrderStatus
{
    public function handle(OrderAcceptedAtPickPoint $event): void
    {
        dispatch(new JobsSetPickPointOrderStatus($event->order));
    }
}
