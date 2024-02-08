<?php

namespace Src\Domain\Synchronizer\Actions;

use App\Services\MoySklad\MoySkladApi;
use Exception;
use Src\Domain\Synchronizer\Jobs\ProductFromMoySkladToCdek;
use Src\Domain\Synchronizer\Jobs\ProductFromMoySkladToInsales;
use Src\Domain\Synchronizer\Models\Product;

class UpdateProductFromMoySklad
{
    public function handle(array $request)
    {
        foreach (data_get($request, 'events') as $event) {
            $updatedFields = data_get($event, 'updatedFields');

            if (! in_array('salePrices', $updatedFields) && ! in_array('buyPrices', $updatedFields)) {
                return;
            }

            $productId = str(data_get($event, 'meta.href'))->afterLast('/')->value();
            $MSProduct = MoySkladApi::getProduct($productId)->json();

            $dbProduct = Product::where('moy_sklad_id', $productId)->first();

            if ($dbProduct->variants->count() > 1) {
                throw new Exception("У товара с id $dbProduct->id не должно быть модификаций");
            }

            $dbVariant = $dbProduct->variants->first();

            if (config('services.moySklad.enabled')) {
                dispatch(new ProductFromMoySkladToInsales($dbVariant, $MSProduct));
            }

            if (config('services.cdekff.enabled')) {
                dispatch(new ProductFromMoySkladToCdek($dbVariant, $MSProduct));
            }
        }
    }
}
