<?php

namespace Src\Domain\Synchronizer\Actions;

use App\Services\MoySklad\MoySkladApi;
use Src\Domain\Synchronizer\Jobs\VariantFromMoySkladToCdek;
use Src\Domain\Synchronizer\Jobs\VariantFromMoySkladToInsales;

class UpdateVariantFromMoySklad
{
    public function handle(array $request)
    {
        foreach (data_get($request, 'events') as $event) {
            $updatedFields = data_get($event, 'updatedFields');

            if (empty(array_intersect(config('services.moySklad.fields_to_update'), $updatedFields))) {
                return;
            }

            $variantId = str(data_get($event, 'meta.href'))->afterLast('/')->value();
            $variant = MoySkladApi::getVariant($variantId)->json();

            if (config('services.moySklad.enabled')) {
                dispatch(new VariantFromMoySkladToInsales($variant));
            }

            if (config('services.cdekff.enabled') && in_array('salePrices', $updatedFields)) {
                dispatch(new VariantFromMoySkladToCdek($variant));
            }
        }
    }
}
