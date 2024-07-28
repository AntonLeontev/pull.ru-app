<?php

namespace App\Services\CDEK\Entities;

readonly class PaymentState extends AbstractEntity
{
    public function __construct(public string $state) {}

    public function jsonSerialize(): string
    {
        return $this->state;
    }

    public static function fromInsales(string $financialStatus): static
    {
        return new static(self::convertFromInsales($financialStatus));
    }

    public static function convertFromInsales(string $status): string
    {
        return match ($status) {
            'pending' => 'not_paid',
            'paid' => 'paid',
        };
    }
}
