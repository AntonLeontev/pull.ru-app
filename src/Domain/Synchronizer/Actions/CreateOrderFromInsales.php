<?php

namespace Src\Domain\Synchronizer\Actions;

use App\Services\CDEK\Entities\Order as CdekOrder;
use App\Services\CDEK\FullfillmentApi;
use App\Services\InSales\Entities\Client as InSalesClient;
use App\Services\MoySklad\Entities\Counterparty;
use App\Services\MoySklad\Entities\OrderPosition;
use App\Services\MoySklad\Entities\Organization;
use App\Services\MoySklad\MoySkladApi;
use Src\Domain\Synchronizer\Models\Client;
use Src\Domain\Synchronizer\Models\Order;

class CreateOrderFromInsales
{
    public function handle(array $request): void
    {
        $request = objectize($request);

        $client = $this->getClientFromInsales(InSalesClient::fromObject($request->client));

        if (is_null($client->moy_sklad_id)) {
            $msClient = MoySkladApi::createIndividualCounterparty($client->name, $client->email, $client->phone)->json();

            $client->update(['moy_sklad_id' => $msClient['id']]);
        }

        $counterparty = Counterparty::make($client->moy_sklad_id);

        $order = Order::create(['insales_id' => $request->id]);

        $assortment = [];
        foreach ($request->order_lines as $insalesProduct) {
            $assortment[] = OrderPosition::fromInsalesOrder($insalesProduct);
        }

        $msOrder = MoySkladApi::createCustomerOrder(
            Organization::make(config('services.moySklad.organization')),
            $counterparty,
            [
                'name' => (string) $request->number,
                // 'vatEnabled' => false,
                'shipmentAddress' => $request->delivery_info->address->formatted,
                'positions' => $assortment,
            ]
        )->json();

        $order->update(['moy_sklad_id' => $msOrder['id']]);

        $cdekOrder = CdekOrder::fromInsalesOrderRequest($request);

        $cdekOrder = FullfillmentApi::createOrder($cdekOrder)->json();

        $order->update(['cdek_id' => $cdekOrder['id']]);
    }

    public function getClientFromInsales(InSalesClient $client): Client
    {
        return Client::firstOrCreate(
            ['insales_id' => $client->id],
            [
                'name' => $client->name,
                'phone' => $client->phone,
                'email' => $client->email,
                'is_registered' => $client->registered,
            ]
        );
    }
}
