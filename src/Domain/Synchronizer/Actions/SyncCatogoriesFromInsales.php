<?php

namespace Src\Domain\Synchronizer\Actions;

use Illuminate\Support\Collection;
use Src\Domain\InSales\Services\InSalesApi;
use Src\Domain\MoySklad\Services\MoySkladApi;
use Src\Domain\Synchronizer\Models\Category;

class SyncCatogoriesFromInsales
{
    public function handle()
    {
        $categories = InSalesApi::getCollections()->json();
        $categories = collect($categories);

        $rootCategory = $categories->where('parent_id', null)->first();

        $this->recurciveSync($categories, $rootCategory, null);
    }

    private function recurciveSync(Collection $collection, array $category, ?Category $parent)
    {
        $dbCategory = $this->sync($category, $parent);

        $children = $collection->where('parent_id', $category['id']);

        foreach ($children as $child) {
            $this->recurciveSync($collection, $child, $dbCategory);
        }
    }

    private function sync(array $category, ?Category $parent): Category
    {
        $dbCategory = Category::updateOrCreate(
            ['insales_id' => $category['id']],
            [
                'name' => $category['title'],
                'is_hidden' => $category['is_hidden'],
                'parent_id' => $parent?->id,
            ]
        );

        if (is_null($dbCategory->moy_sklad_id)) {
            $moySkladId = MoySkladApi::createProductFolder($dbCategory->name, parentFolder: $parent?->moy_sklad_id)->json('id');

            $dbCategory->update(['moy_sklad_id' => $moySkladId]);
        } else {
            MoySkladApi::updateProductFolder($dbCategory->moy_sklad_id, ['name' => $dbCategory->name], $parent?->moy_sklad_id);
        }

        return $dbCategory;
    }
}
