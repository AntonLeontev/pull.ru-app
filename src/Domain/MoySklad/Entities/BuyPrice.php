<?php

namespace Src\Domain\MoySklad\Entities;

use App\Traits\Makeable;
use JsonSerializable;

class BuyPrice implements JsonSerializable
{
    use Makeable;

    public readonly float $value;

    public function __construct(string|int|float $value)
    {
        $this->value = (float) ($value * 100);
    }

    public function jsonSerialize(): array
    {
        return [
            'value' => $this->value,
        ];
    }
}
