<?php

namespace Src\Domain\Synchronizer\Actions;

use App\Services\CDEK\FullfillmentApi;
use Src\Domain\Synchronizer\Events\VariantFromMoySkladToCdekError;
use Src\Domain\Synchronizer\Events\VariantFromMoySkladToCdekSuccess;
use Src\Domain\Synchronizer\Models\Variant;

class VariantFromMoySkladToCdek
{
    public function handle(array $MSVariant): void
    {
        try {
            $variant = Variant::where('moy_sklad_id', $MSVariant['id'])
                ->with('product')
                ->first();

            FullfillmentApi::updateSimpleProduct($variant->cdek_id, [
                'price' => data_get($MSVariant, 'salePrices.0.value') / 100,
                'purchasingPrice' => data_get($MSVariant, 'buyPrice.value') / 100,
            ]);
        } catch (\Throwable $th) {
            event(new VariantFromMoySkladToCdekError($variant->name, $variant->id));

            throw $th;
        }

        event(new VariantFromMoySkladToCdekSuccess($variant->name, $variant->id));
    }
}
