<?php

namespace Src\Domain\Synchronizer\Listeners;

use Illuminate\Support\Facades\Log;
use Src\Domain\Synchronizer\Events\AbstractEventForLogging;

class LogToTelegram
{
    public function handle(AbstractEventForLogging $event): void
    {
        Log::channel('sync_log')->info($event->getMessage());
    }
}
