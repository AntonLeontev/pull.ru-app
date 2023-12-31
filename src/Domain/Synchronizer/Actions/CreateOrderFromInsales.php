<?php

namespace Src\Domain\Synchronizer\Actions;

use App\Services\CDEK\Entities\Order as CdekOrder;
use App\Services\CDEK\FullfillmentApi;
use App\Services\InSales\Entities\Client as InSalesClient;
use App\Services\MoySklad\Entities\Counterparty;
use App\Services\MoySklad\Entities\OrderPosition;
use App\Services\MoySklad\Entities\Organization;
use App\Services\MoySklad\Entities\Store;
use App\Services\MoySklad\MoySkladApi;
use Src\Domain\Synchronizer\Models\Client;
use Src\Domain\Synchronizer\Models\Order;

class CreateOrderFromInsales
{
    public function __construct(public ResolveDiscount $resolveDiscount)
    {
    }

    public function handle(array $request): void
    {
        $request = objectize($request);

        $this->resolveDiscount->handle($request);

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

        $address = $request->delivery_info->address->formatted ?? $request->delivery_info->address->city.' '.$request->delivery_info->address->address;

        $msOrder = MoySkladApi::createCustomerOrder(
            Organization::make(config('services.moySklad.organization')),
            $counterparty,
            [
                'name' => (string) $request->number,
                // 'vatEnabled' => false,
                'shipmentAddress' => $address,
                'positions' => $assortment,
                'store' => Store::make(config('services.moySklad.store')),
            ]
        )->json();

        $order->update(['moy_sklad_id' => $msOrder['id']]);

        $cdekOrder = CdekOrder::fromInsalesOrderRequest($request);

        $cdekOrder = FullfillmentApi::createOrder($cdekOrder)->json();

        $order->update(['cdek_id' => $cdekOrder['id']]);

        foreach ($request->order_lines as $line) {
            cache(['blocked_products.'.$line->product_id => true]);
        }
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
