<?php

namespace Src\Domain\CDEK\Entities;

class Weight extends AbstractEntity
{
    public function __construct(public ?int $grams)
    {
    }

    public function jsonSerialize(): int
    {
        return $this->grams;
    }

    public static function fromKilos(float|int|string|null $kilos): static
    {
        return new static((float) $kilos ?? 0 * 1000);
    }
}
