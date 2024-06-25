<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Jobs\SetOrderLocation;

class GetLocationByIp
{
    public function handle(OrderCreated $event): void
    {
        dispatch(new SetOrderLocation($event->order));
    }
}
