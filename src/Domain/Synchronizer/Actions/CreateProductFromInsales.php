<?php

namespace Src\Domain\Synchronizer\Actions;

use App\Services\CDEK\Entities\Dimensions;
use App\Services\CDEK\Entities\Weight;
use App\Services\CDEK\FullfillmentApi;
use App\Services\MoySklad\Entities\BuyPrice;
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

    public function handle(array $request): void
    {
        $categories = $this->syncService->actualInsalesCategories($request);

        DB::transaction(function () use ($request, $categories) {
            $dbProduct = $this->createProductInDb($request, $categories);

            $productFolder = $this->syncService->getMoySkladProductFolder($categories);

            $moySkladProduct = $this->createMoySkladProduct($request, $productFolder);

            $dbProduct->update(['moy_sklad_id' => $moySkladProduct['id']]);

            event(new ProductCreatingSuccess($dbProduct->name));
        });
    }

    private function createProductInDb(array $request, Collection $categories): Product
    {
        $product = Product::create([
            'name' => $request[0]['title'],
            'insales_id' => $request[0]['id'],
        ]);

        $product->categories()->sync($categories->pluck('id'));

        $variant = data_get($request, '0.variants.0');

        $dbVariant = Variant::updateOrCreate(
            ['insales_id' => $variant['id']],
            [
                'name' => $variant['title'] ?? data_get($request, '0.title'),
                'product_id' => $product->id,
            ]
        );

        if (config('services.cdek.enabled')) {
            $cdekProduct = FullfillmentApi::createSimpleProduct(
                $product->name.' '.$dbVariant->name,
                $variant['price'],
                $variant['sku'],
                $dbVariant->id,
                $variant['cost_price'],
                data_get($request, '0.images.0.large_url'),
                Weight::fromKilos($variant['weight']),
                Dimensions::fromInsalesDimensions($variant['dimensions']),
            )->json();

            $dbVariant->update(['cdek_id' => $cdekProduct['id']]);
        }

        return $product;
    }

    /**
     * @var Src\Domain\MoySklad\Entity\Image[]
     */
    private function createMoySkladProduct(array $request, ?ProductFolder $productFolder): array
    {
        return MoySkladApi::createProduct(
            data_get($request, '0.title'),
            [
                'description' => strip_tags(data_get($request, '0.description')),
                'salePrices' => [SalePrice::make(data_get($request, '0.variants.0.price'))],
                'buyPrice' => BuyPrice::make(data_get($request, '0.variants.0.cost_price')),
                // 'barcodes' => [['ean13' => data_get($request, '0.variants.0.barcode')]],
                'article' => (string) data_get($request, '0.variants.0.sku'),
                'weight' => (float) data_get($request, '0.variants.0.weight'),
                'uom' => $this->syncService->getUnits($request),
                'images' => $this->syncService->getImages($request),
                'productFolder' => $productFolder,
            ],
        )->json();
    }
}
