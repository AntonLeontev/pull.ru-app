<?php

namespace Src\Domain\Synchronizer\Actions;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Src\Domain\MoySklad\Entities\BuyPrice;
use Src\Domain\MoySklad\Entities\Image;
use Src\Domain\MoySklad\Entities\ProductFolder;
use Src\Domain\MoySklad\Entities\SalePrice;
use Src\Domain\MoySklad\Services\MoySkladApi;
use Src\Domain\Synchronizer\Models\Category;
use Src\Domain\Synchronizer\Models\Product;

class CreateProductFromInsales
{
    public function __construct(public SyncCategoriesFromInsales $categorySync)
    {
    }

    public function handle(): void
    {
        $categories = $this->getCategories();

        DB::transaction(function () use ($categories) {
            $dbProduct = $this->createProductInDb($categories);

            $productFolder = $this->getProductFolder($categories);

            $units = $this->getUnits();

            $image = $this->getImages();

            $moySkladProduct = $this->createMoySkladProduct($units, $productFolder, $image);

            $dbProduct->update(['moy_sklad_id' => $moySkladProduct['id']]);
        });
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

    private function createProductInDb(Collection $categories): Product
    {
        $product = Product::create([
            'name' => request()->json('0.title'),
            'insales_id' => request()->json('0.id'),
        ]);

        $product->categories()->sync($categories->pluck('id'));

        return $product;
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

    private function getUnits(): ?array
    {
        return request()->json('0.unit') === 'pce'
            ? MoySkladApi::pceMeta(config('services.moySklad.uom.pce'))
            : null;
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

    /**
     * @var array|null
     * @var string|null
     * @var Src\Domain\MoySklad\Entity\Image[]
     */
    private function createMoySkladProduct(?array $units, ?ProductFolder $productFolder, array $images)
    {
        return MoySkladApi::createProduct(
            request()->json('0.title'),
            [
                'description' => strip_tags(request()->json('0.description')),
                'salePrices' => [SalePrice::make(request()->json('0.variants.0.price'))],
                'buyPrice' => BuyPrice::make(request()->json('0.variants.0.cost_price')),
                // 'barcodes' => [['ean13' => request()->json('0.variants.0.barcode')]],
                'article' => (string) request()->json('0.variants.0.sku'),
                'weight' => (float) request()->json('0.variants.0.weight'),
                'uom' => $units,
                'images' => $images,
                'productFolder' => $productFolder,
            ],
        )->json();
    }
}
