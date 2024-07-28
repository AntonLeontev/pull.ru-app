<?php

namespace App\Services\InSales\Entities;

class ISImage
{
    public function __construct(
        public ?int $id = null,
        public ?int $productId = null,
        public ?int $position = null,
        public ?string $created_at = null,
        public ?bool $imageProcessing = null,
        public ?string $externalId = null,
        public ?string $title = null,
        public ?string $url = null,
        public ?string $originalUrl = null,
        public ?string $mediumUrl = null,
        public ?string $smallUrl = null,
        public ?string $thumbUrl = null,
        public ?string $compactUrl = null,
        public ?string $largeUrl = null,
        public ?string $filename = null,
    ) {}

    public static function fromRequest(array $data): static
    {
        return new static(
            $data['id'],
            $data['product_id'],
            $data['position'],
            $data['created_at'],
            $data['image_processing'],
            $data['external_id'],
            $data['title'],
            $data['url'],
            $data['original_url'],
            $data['medium_url'],
            $data['small_url'],
            $data['thumb_url'],
            $data['compact_url'],
            $data['large_url'],
            $data['filename'],
        );
    }
}
