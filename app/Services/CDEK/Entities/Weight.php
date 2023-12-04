<?php

namespace App\Services\CDEK\Entities;

readonly class Weight extends AbstractEntity
{
    public function __construct(public ?int $grams)
    {
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize(): int
    {
        return $this->grams;
    }

    public static function fromKilos(float|int|string|null $kilos): static
    {
        return new static(($kilos ?? 0) * 1000);
    }
}
