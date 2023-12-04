<?php

namespace App\Services\CDEK\Entities;

use App\Traits\Makeable;
use JsonSerializable;

abstract readonly class AbstractEntity implements JsonSerializable
{
    use Makeable;

    #[\ReturnTypeWillChange]
    abstract public function jsonSerialize();
}
