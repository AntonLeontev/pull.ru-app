<?php

namespace Src\Domain\Synchronizer\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

abstract class AbstractEventForLogging
{
    use Dispatchable, SerializesModels;

    abstract public function getMessage(): string;
}
