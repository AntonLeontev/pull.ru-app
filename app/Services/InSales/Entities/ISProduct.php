<?php

namespace App\Services\InSales\Entities;

use Illuminate\Support\Collection;

class ISProduct
{
    public function __construct(
        public ?int $id = null,
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
        public ?bool $isHidden = null,
        public ?bool $available = null,
        public ?bool $archived = null,
        public ?string $unit = null,
        public ?bool $ignoreDiscounts = null,
        public ?int $vat = null,
        public ?string $dimensions = null,
        public ?string $title = null,
        public ?string $shortDescription = null,
        public ?string $permalink = null,
        public ?string $htmlTitle = null,
        public ?string $metaKeywords = null,
        public ?string $metaDescription = null,
        public ?string $currencyCode = null,
        public array $collectionsIds = [],
        public ?string $description = null,
        public ?Collection $images = null,
        public ?Collection $optionNames = null,
        public ?Collection $properties = null,
        public ?Collection $characteristics = null,
        public array $productFieldValues = [],
        public ?Collection $variants = null,
        public array $productBundleComponents = [],
    ) {
    }

    public static function fromRequest(array $data): static
    {
        $images = collect();
        foreach ($data['images'] as $image) {
            $images[] = ISImage::fromRequest($image);
        }

        $optionNames = collect();
        foreach ($data['option_names'] as $optionName) {
            $optionNames[] = ISOptionName::fromRequest($optionName);
        }

        $properties = collect();
        foreach ($data['properties'] as $property) {
            $properties[] = ISProperty::fromRequest($property);
        }

        $characteristics = collect();
        foreach ($data['characteristics'] as $characteristic) {
            $characteristics[] = ISCharacteristic::fromRequest($characteristic);
        }

        $variants = collect();
        foreach ($data['variants'] as $variant) {
            $variants[] = ISVariant::fromRequest($variant);
        }

        return new static(
            $data['id'],
            $data['created_at'],
            $data['updated_at'],
            $data['is_hidden'],
            $data['available'],
            $data['archived'],
            $data['unit'],
            $data['ignore_discounts'],
            $data['vat'],
            $data['dimensions'],
            $data['title'],
            $data['short_description'],
            $data['permalink'],
            $data['html_title'],
            $data['meta_keywords'],
            $data['meta_description'],
            $data['currency_code'],
            $data['collections_ids'],
            $data['description'],
            $images,
            $optionNames,
            $properties,
            $characteristics,
            $data['product_field_values'],
            $variants,
            $data['product_bundle_components'],
        );
    }
}
