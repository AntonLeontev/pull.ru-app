<?php

namespace App\Services\CDEK\Entities;

readonly class Dimensions extends AbstractEntity
{
    /**
     * @param  int|string|float|null  $x  in mm
     * @param  int|string|float|null  $y  in mm
     * @param  int|string|float|null  $z  in mm
     */
    public function __construct(
        public int|string|float|null $x = null,
        public int|string|float|null $y = null,
        public int|string|float|null $z = null,
    ) {}

    public function jsonSerialize(): ?array
    {
        if (is_null($this->x) || is_null($this->y) || is_null($this->z)) {
            return [
                'x' => 0,
                'y' => 0,
                'z' => 0,
            ];
        }

        return [
            'x' => (string) $this->x,
            'y' => (string) $this->y,
            'z' => (string) $this->z,
        ];
    }

    /**
     * @param  int|string|float  $x  in cm
     * @param  int|string|float  $y  in cm
     * @param  int|string|float  $z  in cm
     */
    public static function fromCentimetres(
        int|string|float $x,
        int|string|float $y,
        int|string|float $z,
    ): static {
        return new static(
            (float) $x * 10,
            (float) $y * 10,
            (float) $z * 10,
        );
    }

    public static function fromInsalesDimensions(?string $dimensions): static
    {
        if (is_null($dimensions)) {
            return new static;
        }

        $dimensions = preg_split('~x|х~', $dimensions);

        return static::fromCentimetres($dimensions[0], $dimensions[1], $dimensions[2]);
    }
}
