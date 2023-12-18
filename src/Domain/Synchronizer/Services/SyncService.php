<?php

namespace Src\Domain\Synchronizer\Services;

use App\Services\MoySklad\Entities\Characteristic;
use App\Services\MoySklad\Entities\Image;
use App\Services\MoySklad\Entities\ProductFolder;
use App\Services\MoySklad\Entities\Unit;
use Illuminate\Database\Eloquent\Collection;
use Ramsey\Collection\Set;
use Src\Domain\Synchronizer\Actions\SyncCategoriesFromInsales;
use Src\Domain\Synchronizer\Actions\SyncOptionsFromInsales;
use Src\Domain\Synchronizer\Models\Category;
use Src\Domain\Synchronizer\Models\Option;
use Src\Domain\Synchronizer\Models\OptionValue;
use Src\Domain\Synchronizer\Models\Variant;

class SyncService
{
    public function __construct(public SyncCategoriesFromInsales $categorySync, public SyncOptionsFromInsales $syncOptions)
    {

    }

    public function actualInsalesCategories(array $product): Collection
    {
        $categories = $this->categoriesByInsalesIds($product['collections_ids']);

        if (count($product['collections_ids']) !== $categories->count()) {
            $this->categorySync->handle();

            $categories = $this->categoriesByInsalesIds($product['collections_ids']);
        }

        return $categories;
    }

    public function categoriesByInsalesIds(array $idsInInsales): Collection
    {
        return Category::whereIn('insales_id', $idsInInsales)
            ->with('children')
            ->get();
    }

    public function getMoySkladProductFolder(Collection $categories): ProductFolder
    {
        foreach ($categories as $category) {
            if ($category->children->isNotEmpty()) {
                continue;
            }

            $id = $category->moy_sklad_id;
            break;
        }

        return ProductFolder::make($id ?? $categories->last()->moy_sklad_id);
    }

    /**
     * @return Src\Domain\MoySklad\Entity\Image[]
     */
    public function getImages(array $product): array
    {
        $result = [];

        foreach ($product['images'] as $image) {
            $result[] = Image::make($image['filename'], $image['large_url']);

            if (count($result) === 10) {
                break;
            }
        }

        return $result;
    }

    public function getUnits(array $product): Unit
    {
        return Unit::fromInsalesUnit($product['unit']);
    }

    public function syncOptions(array $request)
    {
        $insalesOptionsIds = new Set('int');

        foreach (data_get($request, 'variants') as $variant) {
            foreach ($variant['option_values'] as $optionValue) {
                $insalesOptionsIds->add($optionValue['option_name_id']);
            }
        }

        if ($insalesOptionsIds->isEmpty()) {
            return;
        }

        $optionsCount = Option::whereIn('insales_id', $insalesOptionsIds->toArray())->count();

        if ($optionsCount !== $insalesOptionsIds->count()) {
            $this->syncOptions->handle();
        }
    }

    public function updateVariantOptions(array $variant, Variant $dbVariant): array
    {
        $characteristics = [];
        foreach ($variant['option_values'] as $optionValue) {
            $dbOption = Option::where('insales_id', $optionValue['option_name_id'])->first();

            OptionValue::updateOrCreate(
                ['insales_id' => $optionValue['id']],
                [
                    'value' => $optionValue['title'],
                    'option_id' => $dbOption->id,
                    'variant_id' => $dbVariant->id,
                ]
            );

            $characteristics[] = Characteristic::make($dbOption->moy_sklad_id, $optionValue['title']);
        }

        return $characteristics;
    }
}
