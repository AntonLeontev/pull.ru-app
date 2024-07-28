<?php

namespace App\Services\InSales\Entities;

class ISOptionValue
{
    public function __construct(
        public ?int $id = null,
        public ?int $optionNameId = null,
        public ?int $position = null,
        public ?string $title = null,
        public ?string $imageUrl = null,
    ) {}

    public static function fromRequest(array $data): static
    {
        return new static(
            $data['id'],
            $data['option_name_id'],
            $data['position'],
            $data['title'],
            $data['image_url'],
        );
    }
}
