<?php

namespace Src\Domain\CDEK\Entities;

use App\Traits\Makeable;
use JsonSerializable;

abstract class AbstractEntity implements JsonSerializable
{
    use Makeable;

    abstract public function jsonSerialize();
}
