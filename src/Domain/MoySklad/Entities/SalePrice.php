<?php

namespace Src\Domain\MoySklad\Entities;

use App\Traits\Makeable;
use JsonSerializable;

class SalePrice implements JsonSerializable
{
    use Makeable;

    public readonly float $value;

    public readonly string $priceTypeId;

    public function __construct(string|int|float $value, string $priceTypeId = null)
    {
        $this->value = (float) ($value * 100);

        $this->priceTypeId = $priceTypeId ?? config('services.moySklad.default_price_type_id');
    }

    public function jsonSerialize(): array
    {
        return [
            'value' => $this->value,
            'priceType' => [
                'meta' => [
                    'href' => "https://api.moysklad.ru/api/remap/1.2/context/companysettings/pricetype/{$this->priceTypeId}",
                    'type' => 'pricetype',
                ],
            ],
        ];
    }
}
