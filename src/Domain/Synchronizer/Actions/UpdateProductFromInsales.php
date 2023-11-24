<?php

namespace Src\Domain\Synchronizer\Actions;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Ramsey\Collection\Set;
use Src\Domain\MoySklad\Entities\BuyPrice;
use Src\Domain\MoySklad\Entities\Characteristic;
use Src\Domain\MoySklad\Entities\Product as MoySkladProduct;
use Src\Domain\MoySklad\Entities\ProductFolder;
use Src\Domain\MoySklad\Entities\SalePrice;
use Src\Domain\MoySklad\Entities\Volume;
use Src\Domain\MoySklad\Services\MoySkladApi;
use Src\Domain\Synchronizer\Models\Option;
use Src\Domain\Synchronizer\Models\OptionValue;
use Src\Domain\Synchronizer\Models\Product;
use Src\Domain\Synchronizer\Models\Variant;
use Src\Domain\Synchronizer\Services\SyncService;

class UpdateProductFromInsales
{
    private Product $product;

    public function __construct(public SyncService $syncService, public SyncOptionsFromInsales $syncOptions)
    {
        $this->product = Product::where('insales_id', request()->json('0.id'))
            ->with('categories')
            ->first();
    }

    public function handle(): void
    {
        $categories = $this->syncService->actualInsalesCategories();

        $this->updateProduct($categories);

        $this->syncOptions();

        $this->syncVariants();
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
                'volume' => Volume::fromInsalesDimensions(request()->json('0.variants.0.dimensions')),
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

    private function syncVariants(): void
    {
        if (count(request()->json('0.variants')) <= 1) {
            return;
        }

        foreach (request()->json('0.variants') as $variant) {
            DB::transaction(function () use ($variant) {
                $dbVariant = Variant::updateOrCreate(
                    ['insales_id' => $variant['id']],
                    [
                        'name' => $variant['title'],
                        'product_id' => $this->product->id,
                    ]
                );

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

                if (is_null($dbVariant->moy_sklad_id)) {
                    $MSVariant = MoySkladApi::createVariant(
                        MoySkladProduct::make($this->product->moy_sklad_id),
                        $characteristics,
                        [
                            'salePrices' => [SalePrice::make($variant['price'])],
                            'buyPrice' => BuyPrice::make($variant['cost_price']),
                            'article' => (string) $variant['sku'],
                        ]
                    )->json();

                    $dbVariant->update(['moy_sklad_id' => $MSVariant['id']]);
                } else {
                    MoySkladApi::updateVariant(
                        $dbVariant->moy_sklad_id,
                        $characteristics,
                        [
                            'salePrices' => [SalePrice::make($variant['price'])],
                            'buyPrice' => BuyPrice::make($variant['cost_price']),
                            'article' => (string) $variant['sku'],
                        ]
                    );
                }
            });
        }
    }

    private function syncOptions()
    {
        $insalesOptionsIds = new Set('int');

        foreach (request()->json('0.variants') as $variant) {
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
}
