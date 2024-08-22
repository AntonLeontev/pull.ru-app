<?php

namespace App\Services\MoySklad\Entities;

readonly class PersonalDiscount extends AbstractEntity
{
    public function __construct(public string $id) {}

    public function jsonSerialize(): array
    {
        return [
            'meta' => [
                'href' => "https://api.moysklad.ru/api/remap/1.2/entity/personaldiscount/{$this->id}",
                'type' => 'personaldiscount',
                'mediaType' => 'application/json',
            ],
        ];
    }
}
