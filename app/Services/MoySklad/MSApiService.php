<?php

namespace App\Services\MoySklad;

use App\Services\MoySklad\Entities\Counterparty;
use App\Services\MoySklad\Entities\CustomerOrder;
use App\Services\MoySklad\Entities\Organization;
use App\Services\MoySklad\Entities\PersonalDiscount;
use App\Services\MoySklad\Entities\Store;
use Carbon\Carbon;
use Src\Domain\Synchronizer\Models\Client;

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

    public function createCounterpartyFromClient(Client $client): object
    {
        return MoySkladApi::createIndividualCounterparty(
            trim($client->name.' '.$client->surname),
            $client->email,
            $client->phone,
            $client->discount_card,
            $client->discount_percent,
        )->object();
    }

    public function getPurchasesAmount(Client $client, ?Carbon $fromDate = null, ?Carbon $toDate = null): int|float
    {
        $filters = [
            "agent=https://api.moysklad.ru/api/remap/1.2/entity/counterparty/{$client->moy_sklad_id}",
        ];

        if (! is_null($fromDate)) {
            $filters[] = 'created>='.$fromDate->format('Y-m-d H:i:s');
        }

        if (! is_null($toDate)) {
            $filters[] = 'created<'.$toDate->format('Y-m-d H:i:s');
        }

        $demandsSum = MoySkladApi::getRetailDemands([
            'filter' => implode(';', $filters),
        ])
            ->collect('rows')
            ->sum('sum') / 100;

        $returnsSum = MoySkladApi::getRetailSalesReturns([
            'filter' => implode(';', $filters),
        ])
            ->collect('rows')
            ->sum('sum') / 100;

        $filters[] = 'state=https://api.moysklad.ru/api/remap/1.2/entity/customerorder/metadata/states/400c64b6-ad4b-11ee-0a80-0dfd005ae9c6';
        $ordersSum = MoySkladApi::getCustomerOrders([
            'filter' => implode(';', $filters),
            'expand' => 'state',
            'limit' => 100,
        ])
            ->collect('rows')
            ->sum('sum') / 100;

        return $demandsSum + $ordersSum - $returnsSum;
    }

    public function updateClientDiscount(Client $client, int $discountPercent): void
    {
        $discount = new PersonalDiscount(config('services.moySklad.personal_discount_id'));

        MoySkladApi::updateCounterparty($client->moy_sklad_id, [
            'discounts' => [
                [
                    'discount' => $discount,
                    'personalDiscount' => $discountPercent,
                ],
            ],
        ]);
    }
}
