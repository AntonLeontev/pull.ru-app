<?php

namespace Src\Domain\Synchronizer\Actions;

use App\Services\InSales\InSalesApi;
use Src\Domain\Synchronizer\Events\VariantFromMoySkladToInsalesError;
use Src\Domain\Synchronizer\Events\VariantFromMoySkladToInsalesSuccess;
use Src\Domain\Synchronizer\Models\Variant;

class ProductFromMoySkladToInsales
{
    public function handle(Variant $dbVariant, array $MSProduct): void
    {
        try {
            InSalesApi::updateVariant($dbVariant->product->insales_id, $dbVariant->insales_id, [
                'price' => data_get($MSProduct, 'salePrices.0.value') / 100,
            ]);
        } catch (\Throwable $th) {
            event(new VariantFromMoySkladToInsalesError($dbVariant->name, $dbVariant->id));
            throw $th;
        }

        event(new VariantFromMoySkladToInsalesSuccess($dbVariant->name, $dbVariant->id));
    }
}
