<?php

namespace App\Services\MoySklad\Entities;

readonly class ProductFolder extends AbstractEntity
{
    public function __construct(public ?string $id) {}

    public function jsonSerialize(): ?array
    {
        if (is_null($this->id)) {
            return null;
        }

        return [
            'meta' => [
                'href' => "https://api.moysklad.ru/api/remap/1.2/entity/productfolder/{$this->id}",
                'type' => 'productfolder',
            ],
        ];
    }
}
