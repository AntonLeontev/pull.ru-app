<?php

namespace App\Services\InSales\Entities;

class ISProperty
{
    public function __construct(
        public ?int $id = null,
        public ?int $position = null,
        public ?bool $isHidden = null,
        public ?bool $isNavigational = null,
        public ?bool $backoffice = null,
        public ?string $permalink = null,
        public ?string $title = null,
    ) {
    }

    public static function fromRequest(array $data): static
    {
        return new static(
            $data['id'],
            $data['position'],
            $data['is_hidden'],
            $data['is_navigational'],
            $data['backoffice'],
            $data['permalink'],
            $data['title'],
        );
    }
}
