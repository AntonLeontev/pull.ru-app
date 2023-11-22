<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Src\Domain\Categories\Models\Category;
use Src\Domain\InSales\Services\InSalesApi;
use Src\Domain\MoySklad\Services\MoySkladApi;

class SyncInSalesCategoriesToMoySklad extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:categories-sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Переносит категории из Инcейлс в Мой Склад';

    /**
     * Execute the console command.
     */
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
