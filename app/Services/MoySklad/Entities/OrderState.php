<?php

namespace App\Services\MoySklad\Entities;

use App\Traits\Makeable;

readonly class OrderState extends AbstractEntity
{
    use Makeable;

    public function __construct(public string $id) {}

    public function jsonSerialize(): array
    {
        return [
            'meta' => [
                'href' => "https://api.moysklad.ru/api/remap/1.2/entity/customerorder/metadata/states/{$this->id}",
                'type' => 'state',
                'mediaType' => 'application/json',
            ],
        ];
    }
}
