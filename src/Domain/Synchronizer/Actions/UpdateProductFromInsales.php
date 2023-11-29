<?php

namespace Src\Domain\Synchronizer\Actions;

use App\Services\CDEK\Entities\Dimensions;
use App\Services\CDEK\Entities\Weight;
use App\Services\CDEK\FullfillmentApi;
use App\Services\MoySklad\Entities\BuyPrice;
use App\Services\MoySklad\Entities\Characteristic;
use App\Services\MoySklad\Entities\Product as MoySkladProduct;
use App\Services\MoySklad\Entities\ProductFolder;
use App\Services\MoySklad\Entities\SalePrice;
use App\Services\MoySklad\Entities\Volume;
use App\Services\MoySklad\MoySkladApi;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Ramsey\Collection\Set;
use Src\Domain\Synchronizer\Events\ProductUpdatingSuccess;
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
    }

    public function handle(array $request): void
    {
        $this->product = Product::where('insales_id', data_get($request, '0.id'))
            ->with('categories')
            ->first();

        $categories = $this->syncService->actualInsalesCategories($request);

        $this->updateProduct($request, $categories);

        $this->syncOptions($request);

        $this->syncVariants($request);

        event(new ProductUpdatingSuccess($this->product->name));
    }

    private function updateProduct(array $request, Collection $categories): void
    {
        DB::transaction(function () use ($request, $categories) {
            $productFolder = $this->updateProductFolder($categories);

            MoySkladApi::updateProduct($this->product->moy_sklad_id, [
                'name' => data_get($request, '0.title'),
                'description' => strip_tags(data_get($request, '0.description')),
                'salePrices' => [SalePrice::make(data_get($request, '0.variants.0.price'))],
                'buyPrice' => BuyPrice::make(data_get($request, '0.variants.0.cost_price')),
                // 'barcodes' => [['ean13' => data_get($request, '0.variants.0.barcode')]],
                'article' => (string) data_get($request, '0.variants.0.sku'),
                'weight' => (float) data_get($request, '0.variants.0.weight'),
                'volume' => Volume::fromInsalesDimensions(data_get($request, '0.variants.0.dimensions')),
                'uom' => $this->syncService->getUnits($request),
                'images' => $this->syncService->getImages($request),
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

    private function syncVariants(array $request): void
    {
        foreach (data_get($request, '0.variants') as $variant) {
            DB::transaction(function () use ($request, $variant) {
                $dbVariant = Variant::updateOrCreate(
                    ['insales_id' => $variant['id']],
                    [
                        'name' => $variant['title'] ?? data_get($request, '0.title'),
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

                if (is_null($dbVariant->cdek_id)) {
                    $cdekProduct = FullfillmentApi::createSimpleProduct(
                        $this->product->name.' '.$dbVariant->name,
                        $variant['price'],
                        $variant['sku'],
                        $dbVariant->id,
                        $variant['cost_price'],
                        data_get($request, '0.images.0.large_url'),
                        Weight::fromKilos($variant['weight']),
                        Dimensions::fromInsalesDimensions($variant['dimensions']),
                    )->json();

                    $dbVariant->update(['cdek_id' => $cdekProduct['id']]);
                } else {
                    FullfillmentApi::updateSimpleProduct($dbVariant->cdek_id, [
                        'name' => $this->product->name.' '.$dbVariant->name,
                        'article' => $variant['sku'],
                        'price' => $variant['price'],
                        'extId' => $dbVariant->id,
                        'purchasingPrice' => $variant['cost_price'],
                        'image' => data_get($request, '0.images.0.large_url'),
                        'weight' => Weight::fromKilos($variant['weight']),
                        'dimensions' => Dimensions::fromInsalesDimensions($variant['dimensions']),
                    ]);
                }

                if (empty($characteristics)) {
                    return;
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

    private function syncOptions(array $request)
    {
        $insalesOptionsIds = new Set('int');

        foreach (data_get($request, '0.variants') as $variant) {
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
