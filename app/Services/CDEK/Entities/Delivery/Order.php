<?php

namespace App\Services\CDEK\Entities\Delivery;

use DomainException;
use JsonSerializable;
use Src\Domain\Synchronizer\Enums\OrderPaymentStatus;
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
        $isPaid = OrderPaymentStatus::from($order->financial_status) === OrderPaymentStatus::paid;

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
                $isPaid ? 0 : $orderLine->sale_price,
                $orderLine->sale_price,
                $orderLine->weight * 1000,
                $orderLine->quantity,
            );

            $items[] = $item;
        }

        $package = new Package($order->number, $items);

        $deliveryInfo = json_decode($order->comment, false);

        if ($deliveryInfo->deliveryType === 'office') {
            $deliveryPoint = $deliveryInfo->deliveryAddress->code;
            $location = null;
        } else {
            $deliveryPoint = null;

            $address = $deliveryInfo->deliveryAddress->formatted;

            if (! empty($deliveryInfo->deliveryAddress->apartment)) {
                $address .= ", кв. {$deliveryInfo->deliveryAddress->apartment}";
            }

            if (! empty($deliveryInfo->deliveryAddress->entrance)) {
                $address .= ", {$deliveryInfo->deliveryAddress->entrance} подъезд";
            }

            if (! empty($deliveryInfo->deliveryAddress->floor)) {
                $address .= ", {$deliveryInfo->deliveryAddress->floor} этаж";
            }

            if (! empty($deliveryInfo->deliveryAddress->intercom)) {
                $address .= ", домофон {$deliveryInfo->deliveryAddress->intercom}";
            }

            $location = new Location(
                $address,
                $deliveryInfo->deliveryAddress->position[0],
                $deliveryInfo->deliveryAddress->position[1],
            );
        }

        return new static(
            $order->number,
            $deliveryInfo->deliveryTariff->tariff_code,
            config('services.cdek.shipment_point'),
            $recipient,
            [$package],
            $isPaid ? 0 : $deliveryInfo->deliveryPrice,
            $deliveryPoint,
            $location,
        );
    }
}
