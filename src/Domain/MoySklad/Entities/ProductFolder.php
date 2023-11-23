<?php

namespace Src\Domain\MoySklad\Entities;

use App\Traits\Makeable;
use JsonSerializable;

class ProductFolder implements JsonSerializable
{
    use Makeable;

    public function __construct(public readonly ?string $id)
    {
    }

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
