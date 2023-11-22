<?php

namespace Src\Domain\Synchronizer\Actions;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Src\Domain\MoySklad\Services\MoySkladApi;
use Src\Domain\Synchronizer\Models\Category;
use Src\Domain\Synchronizer\Models\Product;

class CreateProductFromInsales
{
    public function __construct(public SyncCatogoriesFromInsales $categorySync)
    {
    }

    public function handle(): void
    {
        $categories = $this->getCategories();

        DB::transaction(function () use ($categories) {
            $dbProduct = $this->createProductInDb($categories);

            $productFolder = $this->getProductFolder($categories);

            $units = $this->getUnits();

            $moySkladProduct = $this->createMoySkladProduct($units, $productFolder);

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

    private function getProductFolder(Collection $categories): string
    {
        foreach ($categories as $category) {
            if ($category->children->isNotEmpty()) {
                continue;
            }

            $productFolder = $category->moy_sklad_id;
            break;
        }

        return $productFolder ?? $categories->first()->moy_sklad_id;
    }

    private function getUnits(): ?array
    {
        return request()->json('0.unit') === 'pce'
            ? MoySkladApi::pceMeta(config('services.moySklad.uom.pce'))
            : null;
    }

    private function createMoySkladProduct(?array $units, ?string $productFolder)
    {
        return MoySkladApi::createProduct(
            request()->json('0.title'),
            [
                'description' => strip_tags(request()->json('0.description')),
                'images' => [],
                'salePrices' => [
                    [
                        'value' => (float) request()->json('0.variants.0.price'),
                    ],
                ],
                'buyPrice' => [
                    'value' => (float) request()->json('0.variants.0.cost_price'),
                ],
                'barcodes' => [['ean13' => request()->json('0.variants.0.barcode')]],
                'article' => (string) request()->json('0.variants.0.sku'),
                'weight' => (float) request()->json('0.variants.0.weight'),
                'uom' => $units,
                'volume' => 0.43,
            ],
            $productFolder,
        )->json();
    }
}
