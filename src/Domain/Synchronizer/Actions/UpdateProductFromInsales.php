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

class UpdateProductFromInsales
{
    private Product $product;

    public function __construct(public SyncService $syncService)
    {
        $this->product = Product::where('insales_id', request()->json('0.id'))
            ->with('categories')
            ->first();
    }

    public function handle(): void
    {
        $categories = $this->syncService->actualInsalesCategories();

        $this->updateProduct($categories);
    }

    private function updateProduct(Collection $categories): void
    {
        DB::transaction(function () use ($categories) {
            $productFolder = $this->updateProductFolder($categories);

            MoySkladApi::updateProduct($this->product->moy_sklad_id, [
                'name' => request()->json('0.title'),
                'description' => strip_tags(request()->json('0.description')),
                'salePrices' => [SalePrice::make(request()->json('0.variants.0.price'))],
                'buyPrice' => BuyPrice::make(request()->json('0.variants.0.cost_price')),
                // 'barcodes' => [['ean13' => request()->json('0.variants.0.barcode')]],
                'article' => (string) request()->json('0.variants.0.sku'),
                'weight' => (float) request()->json('0.variants.0.weight'),
                'uom' => $this->syncService->getUnits(),
                'images' => $this->syncService->getImages(),
                'productFolder' => $productFolder,
            ]);
        });
    }

    private function updateProductFolder(Collection $categories): ProductFolder
    {
        $this->product->categories()->sync($categories->pluck('id'));
        $this->product->load('categories');

        $moySkladProductCategory = MoySkladApi::getProduct($this->product->moy_sklad_id)->json('productFolder.meta.href');
        $moySkladCategoryId = str($moySkladProductCategory)->afterLast('/')->value();

        if (! in_array($moySkladCategoryId, $this->product->categories->pluck('moy_sklad_id')->toArray())) {
            $productFolder = $this->syncService->getMoySkladProductFolder($categories);
        } else {
            $productFolder = ProductFolder::make($moySkladCategoryId);
        }

        return $productFolder;
    }
}
