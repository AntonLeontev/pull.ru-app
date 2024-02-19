<?php

namespace App\Services\CDEK\Entities;

use JsonSerializable;

readonly class MovementProduct implements JsonSerializable
{
    /**
     * @param  int  $productId идентификатор товара
     * @param  int  $shopId идентификатор магазина
     * @param  int  $movementId идентификатор перемещения
     * @param  string  $sku идентификатор товара для приемки (ean13 штрихкод)
     * @param  int  $quantity ожидаемое количество
     */
    public function __construct(
        public int $productId,
        public int $shopId,
        public int $movementId,
        public string $sku,
        public int $quantity,
    ) {
    }

    public function jsonSerialize(): array
    {
        return [
            'create' => [
                'productOffer' => [
                    'id' => $this->productId,
                    'shop' => $this->shopId,
                ],
                'shop' => $this->shopId,
                'document' => $this->movementId,
                'sku' => $this->sku,
                'quantityExpected' => $this->quantity,
            ],
        ];
    }
}
