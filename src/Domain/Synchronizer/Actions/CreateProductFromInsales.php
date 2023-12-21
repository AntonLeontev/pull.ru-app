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
use App\Services\MoySklad\MoySkladApi;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Src\Domain\Synchronizer\Events\ProductCreatingSuccess;
use Src\Domain\Synchronizer\Models\Product;
use Src\Domain\Synchronizer\Models\Variant;
use Src\Domain\Synchronizer\Services\SyncService;

class CreateProductFromInsales
{
    public function __construct(public SyncService $syncService)
    {
    }

    public function handle(array $request, bool $withBlocking = true): void
    {
        $categories = $this->syncService->actualInsalesCategories($request);

        DB::transaction(function () use ($request, $categories, $withBlocking) {
            $dbProduct = $this->createProductInDb($request, $categories);

            $productFolder = $this->syncService->getMoySkladProductFolder($categories);

            $moySkladProduct = $this->createMoySkladProduct($request, $productFolder);

            $dbProduct->update(['moy_sklad_id' => $moySkladProduct['id']]);

            $request['barcode'] = data_get($moySkladProduct, 'barcodes.0.ean13');

            $this->syncService->syncOptions($request);

            $this->createVariants($request, $dbProduct);

            if ($withBlocking) {
                //От инсейлс за созданием сразу идет запрос обновления.
                //Чтобы не запускать обновление с теми же данными, блочим этот товар
                cache(['blocked_products.'.$dbProduct->id => true]);
            }

            event(new ProductCreatingSuccess($dbProduct->name));
        });
    }

    private function createProductInDb(array $request, Collection $categories): Product
    {
        $product = Product::create([
            'name' => $request['title'],
            'insales_id' => $request['id'],
        ]);

        $product->categories()->sync($categories->pluck('id'));

        return $product;
    }

    /**
     * @var Src\Domain\MoySklad\Entity\Image[]
     */
    private function createMoySkladProduct(array $request, ?ProductFolder $productFolder): array
    {
        return MoySkladApi::createProduct(
            data_get($request, 'title'),
            [
                'description' => strip_tags(data_get($request, 'description')),
                'salePrices' => [SalePrice::make(data_get($request, 'variants.0.price'))],
                'buyPrice' => BuyPrice::make(data_get($request, 'variants.0.cost_price')),
                // 'barcodes' => [['ean13' => data_get($request, 'variants.0.barcode')]],
                'article' => (string) data_get($request, 'variants.0.sku'),
                'weight' => (float) data_get($request, 'variants.0.weight'),
                'uom' => $this->syncService->getUnits($request),
                'images' => $this->syncService->getImages($request),
                'productFolder' => $productFolder,
            ],
        )->json();
    }

    private function createVariants(array $request, Product $product)
    {
        $hasVariants = count(data_get($request, 'variants')) > 1;

        foreach (data_get($request, 'variants') as $variant) {
            DB::transaction(function () use ($request, $variant, $hasVariants, $product) {
                $dbVariant = Variant::updateOrCreate(
                    ['insales_id' => $variant['id']],
                    [
                        'name' => $variant['title'] ?? data_get($request, 'title'),
                        'product_id' => $product->id,
                        'ean13' => $request['barcode'],
                    ]
                );

                $characteristics = $this->syncService->updateVariantOptions($variant, $dbVariant);

                if (! empty($characteristics)) {
                    if (is_null($dbVariant->moy_sklad_id)) {
                        $MSVariant = MoySkladApi::createVariant(
                            MoySkladProduct::make($product->moy_sklad_id),
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
                }

                if (config('services.cdekff.enabled')) {
                    $name = $hasVariants ? $product->name.' '.$dbVariant->name : $product->name;

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
