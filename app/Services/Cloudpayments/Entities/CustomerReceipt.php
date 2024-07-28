<?php

namespace App\Services\Cloudpayments\Entities;

use App\Services\Cloudpayments\Enums\TaxationSystem;
use JsonSerializable;

class CustomerReceipt implements JsonSerializable
{
    public function __construct(
        public array $items,
        public Amounts $amounts,
        public ?TaxationSystem $taxationSystem = null,
        public ?string $email = null,
        public ?string $phone = null,
    ) {}

    public function jsonSerialize(): array
    {
        return [
            'Items' => $this->items,
            'TaxationSystem' => $this->taxationSystem,
            'Amounts' => $this->amounts,
            'Email' => $this->email,
            'Phone' => $this->phone,
        ];
    }
}
