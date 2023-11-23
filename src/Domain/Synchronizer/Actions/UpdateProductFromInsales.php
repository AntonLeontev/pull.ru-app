<?php

namespace Src\Domain\Synchronizer\Actions;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Src\Domain\MoySklad\Entities\Image;
use Src\Domain\MoySklad\Entities\ProductFolder;
use Src\Domain\MoySklad\Services\MoySkladApi;
use Src\Domain\Synchronizer\Models\Category;
use Src\Domain\Synchronizer\Models\Product;

class UpdateProductFromInsales
{
    private Product $product;

    public function __construct(public SyncCategoriesFromInsales $categorySync)
    {
        $this->product = Product::where('insales_id', request()->json('0.id'))
            ->with('categories')
            ->first();
    }

    public function handle(): void
    {
        $categories = $this->getCategories();

        $this->updateProduct($categories);
    }

    private function getCategories(): Collection
    {
        $categories = $this->categoriesByInsalesIds(request()->json('0.collections_ids'));

        if (count(request()->json('0.collections_ids')) !== $categories->count()) {
            $this->categorySync->handle();

            $categories = $this->categoriesByInsalesIds(request()->json('0.collections_ids'));
        }

        return $categories;
    }

    private function categoriesByInsalesIds(array $idsInInsales): Collection
    {
        return Category::whereIn('insales_id', $idsInInsales)
            ->with('children')
            ->get();
    }

    private function updateProduct(Collection $categories): void
    {
        DB::transaction(function () use ($categories) {
            $productFolder = $this->updateProductFolder($categories);

            MoySkladApi::updateProduct($this->product->moy_sklad_id, [
                'description' => strip_tags(request()->json('0.description')),
                'salePrices' => [
                    [
                        'value' => (float) request()->json('0.variants.0.price'),
                    ],
                ],
                'buyPrice' => [
                    'value' => (float) request()->json('0.variants.0.cost_price'),
                ],
                // 'barcodes' => [['ean13' => request()->json('0.variants.0.barcode')]],
                'article' => (string) request()->json('0.variants.0.sku'),
                'weight' => (float) request()->json('0.variants.0.weight'),
                'uom' => $this->getUnits(),
                'images' => $this->getImages(),
                'productFolder' => $productFolder,
            ]);
        });
    }

    private function getProductFolder(Collection $categories): ProductFolder
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

    private function updateProductFolder(Collection $categories): ProductFolder
    {
        $this->product->categories()->sync($categories->pluck('id'));
        $this->product->load('categories');

        $moySkladProductCategory = MoySkladApi::getProduct($this->product->moy_sklad_id)->json('productFolder.meta.href');
        $moySkladCategoryId = str($moySkladProductCategory)->afterLast('/')->value();

        if (! in_array($moySkladCategoryId, $this->product->categories->pluck('moy_sklad_id')->toArray())) {
            $productFolder = $this->getProductFolder($categories);
        } else {
            $productFolder = ProductFolder::make($moySkladCategoryId);
        }

        return $productFolder;
    }

    /**
     * @return Src\Domain\MoySklad\Entity\Image[]
     */
    private function getImages(): array
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

    private function getUnits(): ?array
    {
        return request()->json('0.unit') === 'pce'
            ? MoySkladApi::pceMeta(config('services.moySklad.uom.pce'))
            : null;
    }
}
