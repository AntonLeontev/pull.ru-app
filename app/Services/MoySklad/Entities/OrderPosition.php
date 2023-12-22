<?php

namespace App\Services\MoySklad\Entities;

use App\Services\MoySklad\Entities\Product as MSProduct;
use App\Services\MoySklad\Entities\Variant as MSVariant;
use Src\Domain\Synchronizer\Models\Product;
use Src\Domain\Synchronizer\Models\Variant;

readonly class OrderPosition extends AbstractEntity
{
    public function __construct(
        public int $quantity,
        public float|int $price,
        public float|int $discount,
        public float|int $vat,
        public MSProduct|MSVariant $assortment,
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
        $product = Product::where('insales_id', $position->product_id)
            ->withCount('variants')
            ->first();

        if ($product->variants_count > 1) {
            $variant = Variant::where('insales_id', $position->variant_id)->first();

            $assortment = MSVariant::make($variant->moy_sklad_id);
        } else {
            $assortment = MSProduct::make($product->moy_sklad_id);
        }

        return new static(
            $position->quantity,
            $position->sale_price * 100,
            0,
            (int) $position->vat === -1 ? 0 : $position->vat,
            $assortment
        );
    }
}
