<?php

namespace App\Services\MoySklad\Entities;

use App\Traits\Makeable;
use JsonSerializable;

abstract readonly class AbstractEntity implements JsonSerializable
{
    use Makeable;

    abstract public function jsonSerialize(): mixed;
}
