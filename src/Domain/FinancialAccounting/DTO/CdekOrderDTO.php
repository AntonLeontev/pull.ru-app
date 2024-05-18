<?php

namespace Src\Domain\FinancialAccounting\DTO;

use Illuminate\Http\Client\Response;

class CdekOrderDTO
{
    public function __construct(
        public string $uuid,
        public int $cdekNumber,
        public bool $isReturn,
        public bool $isReverse,
        public bool $isClientReturn,
        public ?bool $transactedPayment,
        public ?int $number,
        public ?float $recipientDeliveryCost,
        public ?float $paymentSum,
        public array $items,
        public float $totalExpence,
        public ?string $deliveryDate,
        public ?string $directOrderUuid = null,
        public ?string $returnOrderUuid = null,
    ) {
    }

    public static function fromResponse(Response $resposne): static
    {
        $directOrder = collect($resposne->json('related_entities'))->where('type', 'direct_order')->first();
        if ($directOrder) {
            $directOrderId = $directOrder['uuid'];
        }

        $returnOrder = collect($resposne->json('related_entities'))->where('type', 'return_order')->first();
        if ($returnOrder) {
            $returnOrderId = $returnOrder['uuid'];
        }

        return new static(
            uuid: $resposne->json('entity.uuid'),
            cdekNumber: $resposne->json('entity.cdek_number'),
            isReturn: $resposne->json('entity.is_return'),
            isReverse: $resposne->json('entity.is_reverse'),
            isClientReturn: $resposne->json('entity.is_client_return'),
            transactedPayment: $resposne->json('entity.transacted_payment'),
            number: $resposne->json('entity.number'),
            recipientDeliveryCost: $resposne->json('entity.delivery_recipient_cost.value'),
            paymentSum: $resposne->json('entity.delivery_detail.payment_sum'),
            items: $resposne->json('entity.packages.0.items'),
            totalExpence: $resposne->json('entity.delivery_detail.total_sum'),
            deliveryDate: $resposne->json('entity.delivery_date'),
            directOrderUuid: $directOrderId ?? null,
            returnOrderUuid: $returnOrderId ?? null,
        );
    }
}
