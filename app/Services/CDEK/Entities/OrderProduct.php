<?php

namespace App\Services\CDEK\Entities;

use App\Services\CDEK\Exceptions\EmptyVariantException;
use App\Services\CDEK\Exceptions\EmptyVariantInCdek;
use Src\Domain\Synchronizer\Models\Variant;

readonly class OrderProduct extends AbstractEntity
{
    public function __construct(
        public int $cdekId,
        public int $count,
        public int|float $price,
    ) {}

    public function jsonSerialize(): array
    {
        return [
            'productOffer' => $this->cdekId,
            'count' => $this->count,
            'price' => $this->price,
        ];
    }

    public static function fromInsales(int $insalesVariantId, int $count, int|float $price): static
    {
        $variant = Variant::where('insales_id', $insalesVariantId)->first();

        if (is_null($variant)) {
            throw new EmptyVariantException("Не найдена модификация по ID инсейлс. Синхронизируйте товары. insales id = $insalesVariantId");
        }

        if (is_null($variant->cdek_id)) {
            throw new EmptyVariantInCdek("Не найден ID модификации в СДЭК: insales id = $insalesVariantId");
        }

        return new static($variant->cdek_id, $count, $price);
    }
}
