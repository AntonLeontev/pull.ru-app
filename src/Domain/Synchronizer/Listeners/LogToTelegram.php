<?php

namespace Src\Domain\Synchronizer\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Src\Domain\Synchronizer\Events\AbstractEventForLogging;

class LogToTelegram implements ShouldQueue
{
    public function handle(AbstractEventForLogging $event): void
    {
        Log::channel('sync_log')->info($event->getMessage());
    }
}
