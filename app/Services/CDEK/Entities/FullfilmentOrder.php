<?php

namespace App\Services\CDEK\Entities;

readonly class FullfilmentOrder extends AbstractEntity
{
    public function __construct(
        public Client $client,
        public string $phone,
        public PaymentState $paymentState,
        public array $products,
        public DeliveryRequest $deliveryRequest,
        public ?Address $address = null,
        public int|string|null $externalId = null,
        public ?string $comment = null,
    ) {}

    public static function fromInsalesOrderRequest(object $request): static
    {
        $client = new Client($request->client->name, $request->client->email);
        $phone = $request->client->phone;
        $externalId = $request->id;
        $paymentState = PaymentState::fromInsales($request->financial_status);
        $products = [];

        foreach ($request->order_lines as $product) {
            $products[] = OrderProduct::fromInsales($product->variant_id, $product->quantity, $product->sale_price);
        }

        if ($request->delivery_info->type === 'office') {
            $deliveryRequest = DeliveryRequest::fromInsales(
                $request->delivery_info->tariff_id,
                $request->delivery_info->price,
                $request->delivery_info->address->code
            );

            $address = null;
        } else {
            $deliveryRequest = DeliveryRequest::fromInsales(
                $request->delivery_info->tariff_id,
                $request->delivery_info->price
            );

            $address = new Address(
                $request->delivery_info->address->city,
                $request->delivery_info->address->formatted,
                $request->delivery_info->address->entrance ?? '',
                $request->delivery_info->address->floor ?? '',
                $request->delivery_info->address->apartment ?? '',
                $request->delivery_info->address->intercom ?? '',
            );
        }

        return new static(
            $client,
            $phone,
            $paymentState,
            $products,
            $deliveryRequest,
            $address,
            $externalId
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'profile' => $this->client,
            'phone' => $this->phone,
            'shop' => config('services.cdekff.shop'),
            'extId' => $this->externalId,
            'paymentState' => $this->paymentState,
            'orderProducts' => $this->products,
            'eav' => [
                'order-reserve-warehouse' => config('services.cdekff.warehouse'),
            ],
            'deliveryRequest' => $this->deliveryRequest,
            'address' => $this->address,
            'comment' => $this->comment,
        ];
    }
}
