<?php

namespace Src\Domain\Synchronizer\Actions;

use App\Services\InSales\InSalesApi;
use Src\Domain\Synchronizer\Events\VariantFromMoySkladToInsalesError;
use Src\Domain\Synchronizer\Events\VariantFromMoySkladToInsalesSuccess;
use Src\Domain\Synchronizer\Models\Variant;

class VariantFromMoySkladToInsales
{
    public function handle(array $MSVariant): void
    {
        try {
            $variant = Variant::where('moy_sklad_id', $MSVariant['id'])
                ->with('product')
                ->first();

            InSalesApi::updateVariant($variant->product->insales_id, $variant->insales_id, [
                'price' => data_get($MSVariant, 'salePrices.0.value') / 100,
            ]);

            cache(['blocked_products.'.$variant->product_id => true]);
        } catch (\Throwable $th) {
            event(new VariantFromMoySkladToInsalesError($variant->name, $variant->id));
            throw $th;
        }

        event(new VariantFromMoySkladToInsalesSuccess($variant->name, $variant->id));
    }
}
