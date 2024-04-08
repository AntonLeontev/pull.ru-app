<?php

namespace Src\Domain\Synchronizer\Listeners;

use Src\Domain\Synchronizer\Events\OrderDelivered;
use Src\Domain\Synchronizer\Jobs\CreateDemandInMS as JobsCreateDemandInMS;

class CreateDemandInMS
{
    public function handle(OrderDelivered $event): void
    {
        dispatch(new JobsCreateDemandInMS($event->order));
    }
}
