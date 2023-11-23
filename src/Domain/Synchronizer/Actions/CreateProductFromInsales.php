<?php

namespace Src\Domain\Synchronizer\Actions;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Src\Domain\MoySklad\Entities\BuyPrice;
use Src\Domain\MoySklad\Entities\ProductFolder;
use Src\Domain\MoySklad\Entities\SalePrice;
use Src\Domain\MoySklad\Services\MoySkladApi;
use Src\Domain\Synchronizer\Models\Product;
use Src\Domain\Synchronizer\Services\SyncService;

class CreateProductFromInsales
{
    public function __construct(public SyncService $syncService)
    {
    }

    public function handle(): void
    {
        $categories = $this->syncService->actualInsalesCategories();

        DB::transaction(function () use ($categories) {
            $dbProduct = $this->createProductInDb($categories);

            $productFolder = $this->syncService->getMoySkladProductFolder($categories);

            $moySkladProduct = $this->createMoySkladProduct($productFolder);

            $dbProduct->update(['moy_sklad_id' => $moySkladProduct['id']]);
        });
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

    /**
     * @var array|null
     * @var string|null
     * @var Src\Domain\MoySklad\Entity\Image[]
     */
    private function createMoySkladProduct(?ProductFolder $productFolder): array
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
                'uom' => $this->syncService->getUnits(),
                'images' => $this->syncService->getImages(),
                'productFolder' => $productFolder,
            ],
        )->json();
    }
}
