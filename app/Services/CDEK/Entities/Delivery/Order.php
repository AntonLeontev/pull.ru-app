<?php

namespace App\Services\CDEK\Entities\Delivery;

use DomainException;
use JsonSerializable;
use Src\Domain\Synchronizer\Models\Variant;

readonly class Order implements JsonSerializable
{
    public function __construct(
        public string $number,
        public int $tariffCode,
        public string $shipmentPoint,
        public Recipient $recipient,
        public array $packages,
        public float $deliveryCost = 0,
        public ?string $deliveryPoint = null,
        public ?Location $toLocation = null,
    ) {
    }

    public function jsonSerialize(): array
    {
        $data = [
            'number' => $this->number,
            'tariff_code' => $this->tariffCode,
            'shipment_point' => $this->shipmentPoint,
            'delivery_point' => $this->deliveryPoint,
            'delivery_recipient_cost' => [
                'value' => $this->deliveryCost,
            ],
            'recipient' => $this->recipient,
            'services' => [
                ['code' => 'TRYING_ON'],
                ['code' => 'PART_DELIV'],
            ],
            'packages' => $this->packages,
        ];

        if (! is_null($this->deliveryPoint)) {
            $data['delivery_point'] = $this->deliveryPoint;
        } elseif (! is_null($this->toLocation)) {
            $data['to_location'] = $this->toLocation;
        }

        return $data;
    }

    public static function fromInsalesOrderRequest(object $order): static
    {
        $recipient = new Recipient(
            $order->client->name,
            $order->client->email,
            $order->client->phone,
        );

        $items = [];

        foreach ($order->order_lines as $orderLine) {
            $variant = Variant::where('insales_id', $orderLine->variant_id)->first();

            if (is_null($variant)) {
                throw new DomainException("Ошибка при передаче нового заказа №$order->number в сдек. В базе не найдена модификация  c insales_id $orderLine->variant_id");
            }

            $item = new Item(
                $orderLine->title,
                $variant->id,
                $orderLine->sale_price,
                $orderLine->sale_price,
                $orderLine->weight * 100,
                $orderLine->quantity,
            );

            $items[] = $item;
        }

        $package = new Package($order->number, $items);

        if ($order->delivery_info->type === 'office') {
            $deliveryPoint = $order->delivery_info->address->code;
            $location = null;
        } else {
            $deliveryPoint = null;
            $location = new Location(
                "{$order->delivery_info->address->formatted}, {$order->delivery_info->address->apartment}",
                $order->delivery_info->address->position[0],
                $order->delivery_info->address->position[1],
            );
        }

        return new static(
            $order->number,
            $order->delivery_info->tariff_id,
            config('services.cdek.shipment_point'),
            $recipient,
            [$package],
            $order->delivery_info->price,
            $deliveryPoint,
            $location,
        );
    }
}
