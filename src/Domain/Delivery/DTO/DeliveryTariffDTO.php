<?php

namespace Src\Domain\Delivery\DTO;

use App\Traits\Makeable;
use JsonSerializable;

readonly class DeliveryTariffDTO implements JsonSerializable
{
    use Makeable;

    public function __construct(
        public int $tariffId,
        public string $title,
        public float $price,
        public int $intervalMinDays = 0,
        public int $intervalMaxDays = 0,
        public ?string $company = null,
        public ?string $description = null,
    ) {}

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->tariffId,
            'title' => $this->title,
            'price' => $this->price,
            'interval' => [
                'min_days' => $this->intervalMinDays,
                'max_days' => $this->intervalMaxDays,
            ],
            'company' => $this->company,
            'description' => $this->description,
        ];
    }
}
