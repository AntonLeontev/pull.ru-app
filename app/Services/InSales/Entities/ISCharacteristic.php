<?php

namespace App\Services\InSales\Entities;

class ISCharacteristic
{
    public function __construct(
        public ?int $id = null,
        public ?int $propertyId = null,
        public ?int $position = null,
        public ?string $title = null,
        public ?string $permalink = null,
    ) {
    }

    public static function fromRequest(array $data): static
    {
        return new static(
            $data['id'],
            $data['property_id'],
            $data['position'],
            $data['title'],
            $data['permalink'],
        );
    }
}
