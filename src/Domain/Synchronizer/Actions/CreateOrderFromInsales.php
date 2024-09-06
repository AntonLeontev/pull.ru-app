<?php

namespace Src\Domain\Synchronizer\Actions;

use App\Services\CDEK\CdekApi;
use App\Services\CDEK\Entities\Delivery\Order as DeliveryOrder;
use App\Services\InSales\Entities\Client as InSalesClient;
use App\Services\MoySklad\Entities\Counterparty;
use App\Services\MoySklad\Entities\OrderPosition;
use App\Services\MoySklad\Entities\Organization;
use App\Services\MoySklad\Entities\Store;
use App\Services\MoySklad\MoySkladApi;
use Src\Domain\Synchronizer\Enums\OrderPaymentType;
use Src\Domain\Synchronizer\Jobs\CreateDicardsCard;
use Src\Domain\Synchronizer\Models\Client;
use Src\Domain\Synchronizer\Models\Order;

class CreateOrderFromInsales
{
    public function __construct(public ResolveDiscount $resolveDiscount) {}

    public function handle(array $request): void
    {
        $request = objectize($request);

        $this->resolveDiscount->handle($request);

        $client = $this->getClientFromInsales(InSalesClient::fromObject($request->client));

        if (is_null($client->discount_card) && $client->is_registered) {
			$number = next_discount_card_number();
			$client->update(['discount_card' => $number]);
            dispatch(new CreateDicardsCard($client));
        }

        if (is_null($client->moy_sklad_id) && $client->is_registered) {
            $msClient = MoySkladApi::createIndividualCounterparty($client->name, $client->email, $client->phone)->json();

            $client->update(['moy_sklad_id' => $msClient['id']]);

            $counterparty = Counterparty::make($client->moy_sklad_id);
        }

        if (! is_null($client->moy_sklad_id)) {
            $counterparty = Counterparty::make($client->moy_sklad_id);
        }

        if (! $client->is_registered) {
            $counterparty = Counterparty::make(config('services.moySklad.default_customer_id'));
        }

        $order = Order::firstOrCreate(
            ['insales_id' => $request->id],
            ['payment_type' => OrderPaymentType::fromInsales($request->payment_gateway_id)]
        );

        if (is_null($order->moy_sklad_id)) {
            $assortment = [];
            foreach ($request->order_lines as $insalesProduct) {
                $assortment[] = OrderPosition::fromInsalesOrder($insalesProduct);
            }

            $deliveryInfo = get_delivery_info($request);

            $address = $deliveryInfo->deliveryAddress->formatted ?? $deliveryInfo->deliveryAddress->city.' '.$deliveryInfo->deliveryAddress->address;

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
        }

        if (is_null($order->cdek_id)) {
            $cdekOrder = DeliveryOrder::fromInsalesOrderRequest($request);

            $cdekOrderId = CdekApi::createOrder($cdekOrder)->json('entity.uuid');

            $order->update(['cdek_id' => $cdekOrderId]);
        }

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
