<?php

namespace Src\Domain\Synchronizer\Actions;

use App\Services\CDEK\FullfillmentApi;
use Src\Domain\Synchronizer\Events\VariantFromMoySkladToCdekError;
use Src\Domain\Synchronizer\Events\VariantFromMoySkladToCdekSuccess;
use Src\Domain\Synchronizer\Models\Variant;

class ProductFromMoySkladToCdek
{
    public function handle(Variant $dbVariant, array $MSProduct): void
    {
        try {
            FullfillmentApi::updateSimpleProduct($dbVariant->cdek_id, [
                'price' => data_get($MSProduct, 'salePrices.0.value') / 100,
                'purchasingPrice' => data_get($MSProduct, 'buyPrice.value') / 100,
            ]);
        } catch (\Throwable $th) {
            event(new VariantFromMoySkladToCdekError($dbVariant->name, $dbVariant->id));
            throw $th;
        }

        event(new VariantFromMoySkladToCdekSuccess($dbVariant->name, $dbVariant->id));
    }
}
