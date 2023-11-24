<?php

namespace Src\Domain\MoySklad\Entities;

use App\Traits\Makeable;
use JsonSerializable;

class AbstractEntity implements JsonSerializable
{
    use Makeable;

    public function jsonSerialize(): mixed
    {
        return [];
    }
}
