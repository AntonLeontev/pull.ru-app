<?php

namespace App\Services\MoySklad\Entities;

readonly class CustomerOrder extends AbstractEntity
{
    public function __construct(public string $id) {}

    public function jsonSerialize(): array
    {
        return [
            'meta' => [
                'href' => "https://api.moysklad.ru/api/remap/1.2/entity/customerorder/{$this->id}",
                'metadataHref' => 'https://api.moysklad.ru/api/remap/1.2/entity/customerorder/metadata',
                'type' => 'customerorder',
                'mediaType' => 'application/json',
            ],
        ];
    }
}
