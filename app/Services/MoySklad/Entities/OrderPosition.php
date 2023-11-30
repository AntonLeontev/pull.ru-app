<?php

namespace App\Services\MoySklad\Entities;

use App\Services\MoySklad\Entities\Product as MSProduct;
use Src\Domain\Synchronizer\Models\Product;

readonly class OrderPosition extends AbstractEntity
{
    public function __construct(
        public int $quantity,
        public float|int $price,
        public float|int $discount,
        public float|int $vat,
        public MSProduct $assortment,
    ) {
    }

    public function jsonSerialize(): array
    {
        return [
            'quantity' => $this->quantity,
            'price' => $this->price,
            'discount' => $this->discount,
            'vat' => $this->vat,
            'assortment' => $this->assortment,
        ];
    }

    public static function fromInsalesOrder(object $position): static
    {
        $product = Product::where('insales_id', $position->product_id)->first();
        $assortment = MSProduct::make($product->moy_sklad_id);

        return new static(
            $position->quantity,
            $position->sale_price * 100,
            $position->discounts_amount / $position->sale_price * 100,
            $position->vat === -1 ? 0 : $position->vat,
            $assortment
        );
    }
}
