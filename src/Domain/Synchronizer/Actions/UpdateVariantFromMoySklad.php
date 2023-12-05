<?php

namespace Src\Domain\Synchronizer\Actions;

use App\Services\MoySklad\MoySkladApi;
use Src\Domain\Synchronizer\Jobs\VariantFromMoySkladToCdek;
use Src\Domain\Synchronizer\Jobs\VariantFromMoySkladToInsales;

class UpdateVariantFromMoySklad
{
    public function handle(array $request)
    {
        $updatedFields = data_get($request, 'events.0.updatedFields');

        if (! in_array('salePrices', $updatedFields) && ! in_array('buyPrices', $updatedFields)) {
            return;
        }

        $variantId = str(data_get($request, 'events.0.meta.href'))->afterLast('/')->value();
        $variant = MoySkladApi::getVariant($variantId)->json();

        dispatch(new VariantFromMoySkladToInsales($variant));

        dispatch(new VariantFromMoySkladToCdek($variant));
    }
}
