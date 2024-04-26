<?php

namespace Src\Domain\Synchronizer\Listeners;

use Src\Domain\Synchronizer\Events\OrderTakenByCourier;
use Src\Domain\Synchronizer\Jobs\SetCourierOrderStatus as JobsSetCourierOrderStatus;

class SetCourierOrderStatus
{
    public function handle(OrderTakenByCourier $event): void
    {
        dispatch(new JobsSetCourierOrderStatus($event->order));
    }
}
