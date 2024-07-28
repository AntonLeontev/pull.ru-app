<?php

namespace App\Services\Tinkoff\Entities;

use App\Services\Tinkoff\Enums\PaymentObject;
use JsonSerializable;

readonly class ReceiptItem implements JsonSerializable
{
    public function __construct(
        public string $name,
        public int $price,
        public int $quantity,
        public int $amount,
        public PaymentObject $paymentObject,
    ) {}

    public function jsonSerialize(): array
    {
        return [
            'Name' => $this->name,
            'Price' => $this->price,
            'Quantity' => $this->quantity,
            'Amount' => $this->amount,
            'Tax' => config('services.tinkoff.tax'),
            'PaymentObject' => $this->paymentObject->value,
        ];
    }
}
