<?php

namespace App\Services\CDEK\Entities\Delivery;

use JsonSerializable;

readonly class Location implements JsonSerializable
{
    public function __construct(
        public string $address,
        public ?float $longitude = null,
        public ?float $latitude = null,
    ) {}

    public function jsonSerialize(): array
    {
        return [
            'address' => $this->address,
            'longitude' => $this->longitude,
            'latitude' => $this->latitude,
        ];
    }
}
