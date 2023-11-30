<?php

namespace App\Services\MoySklad\Entities;

readonly class Counterparty extends AbstractEntity
{
    public function __construct(public string $id)
    {
    }

    public function jsonSerialize(): array
    {
        return [
            'meta' => [
                'href' => "https://api.moysklad.ru/api/remap/1.2/entity/counterparty/{$this->id}",
                'type' => 'counterparty',
                'mediaType' => 'application/json',
            ],
        ];
    }
}
