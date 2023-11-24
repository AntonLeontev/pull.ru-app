<?php

namespace Src\Domain\MoySklad\Entities;

class BuyPrice extends AbstractEntity
{
    public readonly float $value;

    public function __construct(string|int|float|null $value)
    {
        $this->value = (float) (($value ?? 0) * 100);
    }

    public function jsonSerialize(): array
    {
        return [
            'value' => $this->value,
        ];
    }
}
