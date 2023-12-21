<?php

namespace Src\Domain\Synchronizer\Actions;

use App\Services\CDEK\Entities\Dimensions;
use App\Services\CDEK\Entities\Weight;
use App\Services\CDEK\FullfillmentApi;
use App\Services\InSales\InSalesApi;
use App\Services\MoySklad\Entities\BuyPrice;
use App\Services\MoySklad\Entities\Product as MoySkladProduct;
use App\Services\MoySklad\Entities\ProductFolder;
use App\Services\MoySklad\Entities\SalePrice;
use App\Services\MoySklad\Entities\Volume;
use App\Services\MoySklad\MoySkladApi;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Src\Domain\Synchronizer\Events\ProductUpdatingSuccess;
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
        $this->product = Product::where('insales_id', data_get($request, 'id'))
            ->with('categories')
            ->first();

        if (cache('blocked_products.'.$this->product->id)) {
            cache()->forget('blocked_products.'.$this->product->id);

            return;
        }

        $categories = $this->syncService->actualInsalesCategories($request);

        $this->updateProduct($request, $categories);

        $this->syncService->syncOptions($request);

        $this->syncVariants($request);

        event(new ProductUpdatingSuccess($this->product->name));
    }

    private function updateProduct(array $request, Collection $categories): void
    {
        DB::transaction(function () use ($request, $categories) {
            $productFolder = $this->updateProductFolder($categories);

            MoySkladApi::updateProduct($this->product->moy_sklad_id, [
                'name' => data_get($request, 'title'),
                'description' => strip_tags(data_get($request, 'description')),
                'salePrices' => [SalePrice::make(data_get($request, 'variants.0.price'))],
                'buyPrice' => BuyPrice::make(data_get($request, 'variants.0.cost_price')),
                // 'barcodes' => [['ean13' => data_get($request, 'variants.0.barcode')]],
                'article' => (string) data_get($request, 'variants.0.sku'),
                'weight' => (float) data_get($request, 'variants.0.weight'),
                'volume' => Volume::fromInsalesDimensions(data_get($request, 'variants.0.dimensions')),
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
        $hasVariants = count(data_get($request, 'variants')) > 1;

        foreach (data_get($request, 'variants') as $variant) {
            DB::transaction(function () use ($request, $variant, $hasVariants) {
                if ($hasVariants) {
                    $dbVariant = Variant::updateOrCreate(
                        [
                            'name' => $variant['title'],
                            'product_id' => $this->product->id,
                        ],
                        ['insales_id' => $variant['id']]
                    );
                } else {
                    $dbVariant = Variant::updateOrCreate(
                        ['product_id' => $this->product->id],
                        [
                            'insales_id' => $variant['id'],
                            'name' => $variant['title'] ?? data_get($request, 'title'),
                        ]
                    );
                }

                $characteristics = $this->syncService->updateVariantOptions($variant, $dbVariant);

                if (! empty($characteristics)) {
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

                        $dbVariant->update([
                            'moy_sklad_id' => $MSVariant['id'],
                            'ean13' => data_get($MSVariant, 'barcodes.0.ean13'),
                        ]);

                        $variant['barcode'] = data_get($MSVariant, 'barcodes.0.ean13');
                    } else {
                        $MSVariant = MoySkladApi::updateVariant(
                            $dbVariant->moy_sklad_id,
                            $characteristics,
                            [
                                'salePrices' => [SalePrice::make($variant['price'])],
                                'buyPrice' => BuyPrice::make($variant['cost_price']),
                                'article' => (string) $variant['sku'],
                            ]
                        );

                        $variant['barcode'] = data_get($MSVariant, 'barcodes.0.ean13');
                    }
                } else {
                    $variant['barcode'] = $dbVariant->ean13;
                }

                if (config('services.cdekff.enabled')) {
                    $name = $hasVariants ? $this->product->name.' '.$dbVariant->name : $this->product->name;

                    if ($variant['image_id']) {
                        try {
                            $image = InSalesApi::getImage($request['id'], $variant['image_id'])->json('large_url');
                        } catch (\Throwable $th) {
                            $image = data_get($request, 'images.0.large_url');
                        }
                    }

                    if (is_null($dbVariant->cdek_id)) {
                        $cdekProduct = FullfillmentApi::createSimpleProduct(
                            $name,
                            $variant['price'],
                            $variant['sku'],
                            $dbVariant->id,
                            $variant['cost_price'],
                            $image ?? data_get($request, 'images.0.large_url'),
                            Weight::fromKilos($variant['weight']),
                            Dimensions::fromInsalesDimensions($variant['dimensions']),
                            [$variant['barcode']]
                        )->json();

                        $dbVariant->update(['cdek_id' => $cdekProduct['id']]);
                    } else {
                        FullfillmentApi::updateSimpleProduct($dbVariant->cdek_id, [
                            'name' => $name,
                            'article' => $variant['sku'],
                            'price' => $variant['price'],
                            'extId' => $dbVariant->id,
                            'purchasingPrice' => $variant['cost_price'],
                            'image' => data_get($request, 'images.0.large_url'),
                            'weight' => Weight::fromKilos($variant['weight']),
                            'dimensions' => Dimensions::fromInsalesDimensions($variant['dimensions']),
                            'barcodes' => [$variant['barcode']],
                        ]);
                    }
                }
            });
        }
    }
}
