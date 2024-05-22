<?php

namespace Src\Domain\Synchronizer\Listeners;

use App\Services\Telegram\TelegramService;
use Src\Domain\Synchronizer\Events\AbstractEventForLogging;

class LogToTelegram
{
    public function handle(AbstractEventForLogging $event): void
    {
        TelegramService::log($event->getMessage());
    }
}
