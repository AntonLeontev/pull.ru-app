<?php

namespace App\Services\CDEK\Entities;

use App\Traits\Makeable;
use JsonSerializable;

abstract class AbstractEntity implements JsonSerializable
{
    use Makeable;

    #[\ReturnTypeWillChange]
    abstract public function jsonSerialize();
}
