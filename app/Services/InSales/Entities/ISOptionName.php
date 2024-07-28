<?php

namespace App\Services\InSales\Entities;

class ISOptionName
{
    public function __construct(
        public ?int $id = null,
        public ?int $position = null,
        public ?bool $navigational = null,
        public ?string $title = null,
    ) {}

    public static function fromRequest(array $data): static
    {
        return new static(
            $data['id'],
            $data['position'],
            $data['navigational'],
            $data['title'],
        );
    }
}
