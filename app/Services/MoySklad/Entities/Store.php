<?php

namespace App\Services\MoySklad\Entities;

readonly class Store extends AbstractEntity
{
    public function __construct(public string $id) {}

    public function jsonSerialize(): mixed
    {
        return [
            'meta' => [
                'href' => "https://api.moysklad.ru/api/remap/1.2/entity/store/{$this->id}",
                'type' => 'store',
                'mediaType' => 'application/json',
            ],
        ];
    }
}
