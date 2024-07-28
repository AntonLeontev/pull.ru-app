<?php

namespace App\Services\InSales\Entities;

use Illuminate\Support\Collection;

class ISVariant
{
    public function __construct(
        public ?int $id = null,
        public ?string $title = null,
        public ?int $productId = null,
        public ?string $sku = null,
        public ?string $barcode = null,
        public ?string $dimensions = null,
        public ?bool $available = null,
        public array $imageIds = [],
        public ?int $imageId = null,
        public ?string $weight = null,
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
        public ?int $quantity = null,
        public int|float|null $costPrice = null,
        public int|float|null $costPriceInSiteCurrency = null,
        public int|float|null $priceInSiteCurrency = null,
        public int|float|null $basePrice = null,
        public int|float|null $oldPrice = null,
        public int|float|null $price = null,
        public int|float|null $basePriceInSiteCurrency = null,
        public int|float|null $oldPriceInSiteCurrency = null,
        public array $prices = [],
        public array $pricesInSiteCurrency = [],
        public array $variantFieldValues = [],
        public ?Collection $optionValues = null,
    ) {}

    public static function fromRequest(array $data): static
    {
        $optionValues = collect();
        foreach ($data['option_values'] as $optionValue) {
            $optionValues->add(ISOptionValue::fromRequest($optionValue));
        }

        return new static(
            $data['id'],
            $data['title'],
            $data['product_id'],
            $data['sku'],
            $data['barcode'],
            $data['dimensions'],
            $data['available'],
            $data['image_ids'],
            $data['image_id'],
            $data['weight'],
            $data['created_at'],
            $data['updated_at'],
            $data['quantity'],
            $data['cost_price'],
            $data['cost_price_in_site_currency'],
            $data['price_in_site_currency'],
            $data['base_price'],
            $data['old_price'],
            $data['price'],
            $data['base_price_in_site_currency'],
            $data['old_price_in_site_currency'],
            $data['prices'],
            $data['prices_in_site_currency'],
            $data['variant_field_values'],
            $optionValues,
        );
    }
}
