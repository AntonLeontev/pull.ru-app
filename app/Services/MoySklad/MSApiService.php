<?php

namespace App\Services\MoySklad;

use App\Services\MoySklad\Entities\Counterparty;
use App\Services\MoySklad\Entities\CustomerOrder;
use App\Services\MoySklad\Entities\Organization;
use App\Services\MoySklad\Entities\Store;

class MSApiService
{
    public static function createDemandFromDeliveredOrder(
        Counterparty $counterparty,
        CustomerOrder $customerOrder,
        array $positions,
    ) {
        return MoySkladApi::createDemand([
            'organization' => Organization::make(config('services.moySklad.organization')),
            'store' => Store::make(config('services.moySklad.store')),
            'agent' => $counterparty,
            'customerOrder' => $customerOrder,
            'positions' => $positions,
        ]);
    }
}
