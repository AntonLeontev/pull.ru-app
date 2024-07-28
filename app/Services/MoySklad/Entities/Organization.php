<?php

namespace App\Services\MoySklad\Entities;

readonly class Organization extends AbstractEntity
{
    public function __construct(public string $id) {}

    public function jsonSerialize(): mixed
    {
        return [
            'meta' => [
                'href' => "https://api.moysklad.ru/api/remap/1.2/entity/organization/{$this->id}",
                'type' => 'organization',
                'mediaType' => 'application/json',
            ],
        ];
    }
}
