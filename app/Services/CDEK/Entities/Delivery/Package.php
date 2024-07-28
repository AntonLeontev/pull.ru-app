<?php

namespace App\Services\CDEK\Entities\Delivery;

use JsonSerializable;

readonly class Package implements JsonSerializable
{
    /**
     * @param  float  $weight  Вес (в граммах)
     */
    public function __construct(
        public string $number,
        public array $items = [],
        public int $weight = 0,
        public ?int $length = null,
        public ?int $width = null,
        public ?int $height = null,
    ) {}

    public function addItem(Item $item): void
    {
        $this->items[] = $item;
    }

    public function countWeight(): int
    {
        return collect($this->items)
            ->reduce(fn ($carry, Item $item) => $carry + $item->weight, 0);
    }

    public function jsonSerialize(): array
    {
        return [
            'number' => $this->number,
            'weight' => $this->weight > 0 ? $this->weight : $this->countWeight(),
            'items' => $this->items,
            'length' => $this->length,
            'width' => $this->width,
            'height' => $this->height,
        ];
    }
}
