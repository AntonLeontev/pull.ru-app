<?php

namespace Src\Domain\Synchronizer\Actions;

use App\Services\InSales\InSalesApi;
use Src\Domain\Synchronizer\Events\VariantFromMoySkladToInsalesError;
use Src\Domain\Synchronizer\Events\VariantFromMoySkladToInsalesSuccess;
use Src\Domain\Synchronizer\Exceptions\SyncException;
use Src\Domain\Synchronizer\Models\Variant;

class VariantFromMoySkladToInsales
{
    public function handle(array $MSVariant): void
    {
        try {
            $variant = Variant::where('moy_sklad_id', $MSVariant['id'])
                ->with('product')
                ->first();

            if (is_null($variant)) {
                throw new SyncException("Ошибка при обновлении МС -> Insales. В базе не найдена модификация товара с moy_sklad_id {$MSVariant['id']}");
            }

            $name = $variant->product->name.' '.$variant->name;

            $prices = collect($MSVariant['salePrices']);
            $salePrice = $prices->first(fn ($el) => data_get($el, 'priceType.id') === config('services.moySklad.price_id'));
            $oldPrice = $prices->first(fn ($el) => data_get($el, 'priceType.id') === config('services.moySklad.old_price_id'));

            InSalesApi::updateVariant($variant->product->insales_id, $variant->insales_id, [
                'price' => $salePrice['value'] / 100,
                'old_price' => $oldPrice['value'] / 100,
            ]);

            cache(['blocked_products.'.$variant->product_id => true]);
        } catch (\Throwable $th) {
            event(new VariantFromMoySkladToInsalesError($name ?? $variant->name, $variant->id));
            throw $th;
        }

        event(new VariantFromMoySkladToInsalesSuccess($name, $variant->id));
    }
}
