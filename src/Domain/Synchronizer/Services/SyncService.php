<?php

namespace Src\Domain\Synchronizer\Services;

use App\Services\MoySklad\Entities\Image;
use App\Services\MoySklad\Entities\ProductFolder;
use App\Services\MoySklad\Entities\Unit;
use Illuminate\Database\Eloquent\Collection;
use Src\Domain\Synchronizer\Actions\SyncCategoriesFromInsales;
use Src\Domain\Synchronizer\Models\Category;

class SyncService
{
    public function __construct(public SyncCategoriesFromInsales $categorySync)
    {

    }

    public function actualInsalesCategories(array $request): Collection
    {
        $categories = $this->categoriesByInsalesIds($request[0]['collections_ids']);

        if (count($request[0]['collections_ids']) !== $categories->count()) {
            $this->categorySync->handle();

            $categories = $this->categoriesByInsalesIds($request[0]['collections_ids']);
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
    public function getImages(array $request): array
    {
        $result = [];

        foreach ($request[0]['images'] as $image) {
            $result[] = Image::make($image['filename'], $image['large_url']);

            if (count($result) === 10) {
                break;
            }
        }

        return $result;
    }

    public function getUnits(array $request): Unit
    {
        return Unit::fromInsalesUnit($request[0]['unit']);
    }
}
