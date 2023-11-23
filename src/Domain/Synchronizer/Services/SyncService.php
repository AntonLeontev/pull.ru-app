<?php

namespace Src\Domain\Synchronizer\Services;

use Illuminate\Database\Eloquent\Collection;
use Src\Domain\MoySklad\Entities\Image;
use Src\Domain\MoySklad\Entities\ProductFolder;
use Src\Domain\MoySklad\Services\MoySkladApi;
use Src\Domain\Synchronizer\Actions\SyncCategoriesFromInsales;
use Src\Domain\Synchronizer\Models\Category;

class SyncService
{
    public function __construct(public SyncCategoriesFromInsales $categorySync)
    {

    }

    public function actualInsalesCategories(): Collection
    {
        $categories = $this->categoriesByInsalesIds(request()->json('0.collections_ids'));

        if (count(request()->json('0.collections_ids')) !== $categories->count()) {
            $this->categorySync->handle();

            $categories = $this->categoriesByInsalesIds(request()->json('0.collections_ids'));
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

        return ProductFolder::make($id ?? $categories->first()->moy_sklad_id);
    }

    /**
     * @return Src\Domain\MoySklad\Entity\Image[]
     */
    public function getImages(): array
    {
        $result = [];

        foreach (request()->json('0.images') as $image) {
            $result[] = Image::make($image['filename'], $image['large_url']);

            if (count($result) === 10) {
                break;
            }
        }

        return $result;
    }

    public function getUnits(): ?array
    {
        return request()->json('0.unit') === 'pce'
            ? MoySkladApi::pceMeta(config('services.moySklad.uom.pce'))
            : null;
    }
}
