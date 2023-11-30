<?php

namespace App\Services\MoySklad\Entities;

readonly class BuyPrice extends AbstractEntity
{
    public float $value;

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
