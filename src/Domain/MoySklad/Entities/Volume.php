<?php

namespace Src\Domain\MoySklad\Entities;

class Volume extends AbstractEntity
{
    public function __construct(public readonly ?float $volume)
    {
    }

    public function jsonSerialize(): float
    {
        return $this->volume;
    }

    public static function fromInsalesDimensions(?string $dimensions): static
    {
        if (is_null($dimensions)) {
            return new static(null);
        }

        $dimensions = preg_split('~x|х~', $dimensions);
        $volume = 0;

        foreach ($dimensions as $dimension) {
            if ($volume === 0) {
                $volume = $dimension;

                continue;
            }

            $volume *= $dimension;
        }

        // куб. см в куб метры
        return new static($volume / 1_000_000);
    }
}
