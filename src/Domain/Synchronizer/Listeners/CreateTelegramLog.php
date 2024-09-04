<?php

namespace Src\Domain\Synchronizer\Listeners;

use App\Models\TelegramLog;
use Src\Domain\Synchronizer\Events\AbstractEventForLogging;

class CreateTelegramLog
{
    public function handle(AbstractEventForLogging $event): void
    {
        TelegramLog::create(['text' => $event->getMessage()]);
    }
}
