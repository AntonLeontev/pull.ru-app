<?php

namespace Src\Domain\Synchronizer\Actions;

use App\Services\MoySklad\MoySkladApi;
use Src\Domain\Synchronizer\Jobs\ProductFromMoySkladToCdek;
use Src\Domain\Synchronizer\Jobs\ProductFromMoySkladToInsales;
use Src\Domain\Synchronizer\Jobs\VariantFromMoySkladToCdek;
use Src\Domain\Synchronizer\Jobs\VariantFromMoySkladToInsales;
use Src\Domain\Synchronizer\Models\Product;

class UpdateProductFromMoySklad
{
    public function handle(array $request)
    {
        foreach (data_get($request, 'events') as $event) {
            $updatedFields = data_get($event, 'updatedFields');

            if (empty(array_intersect(config('services.moySklad.fields_to_update'), $updatedFields))) {
                return;
            }

            $productId = str(data_get($event, 'meta.href'))->afterLast('/')->value();
            $MSProduct = MoySkladApi::getProduct($productId)->json();

            $dbProduct = Product::where('moy_sklad_id', $productId)->first();

            if ($dbProduct->variants->count() > 1) {
                foreach ($dbProduct->variants as $variant) {
                    $MSVariant = MoySkladApi::getVariant($variant->moy_sklad_id)->json();

                    if (config('services.moySklad.enabled')) {
                        dispatch(new VariantFromMoySkladToInsales($MSVariant));
                    }

                    if (config('services.cdekff.enabled') && in_array('salePrices', $updatedFields)) {
                        dispatch(new VariantFromMoySkladToCdek($MSVariant));
                    }
                }

                return;
            }

            $dbVariant = $dbProduct->variants->first();

            if (config('services.moySklad.enabled')) {
                dispatch(new ProductFromMoySkladToInsales($dbVariant, $MSProduct));
            }

            if (config('services.cdekff.enabled') && in_array('salePrices', $updatedFields)) {
                dispatch(new ProductFromMoySkladToCdek($dbVariant, $MSProduct));
            }
        }
    }
}
