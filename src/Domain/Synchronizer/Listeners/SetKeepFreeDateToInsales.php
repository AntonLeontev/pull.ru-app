<?php

namespace Src\Domain\Synchronizer\Listeners;

use Src\Domain\Synchronizer\Events\OrderAcceptedAtPickPoint;
use Src\Domain\Synchronizer\Events\OrderTakenByCourier;
use Src\Domain\Synchronizer\Jobs\SetKeepFreeDateToInsales as JobsSetKeepFreeDateToInsales;

class SetKeepFreeDateToInsales
{
    public function handle(OrderAcceptedAtPickPoint|OrderTakenByCourier $event): void
    {
        dispatch(new JobsSetKeepFreeDateToInsales($event->order));
    }
}
