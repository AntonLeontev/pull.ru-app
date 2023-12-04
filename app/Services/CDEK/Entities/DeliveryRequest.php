<?php

namespace App\Services\CDEK\Entities;

use App\Services\CDEK\FullfillmentApi;

readonly class DeliveryRequest extends AbstractEntity
{
    public function __construct(public int $rate, public int $price, public ?int $servicePointId = null)
    {
    }

    public function jsonSerialize(): array
    {
        $data = [
            'deliveryService' => 1,
            'rate' => $this->rate,
            'sender' => config('services.cdekff.senders.moscow'),
            'retailPrice' => $this->price,
        ];

        if (is_null($this->servicePointId)) {
            return $data;
        }

        $data['servicePoint'] = $this->servicePointId;

        return $data;
    }

    public static function fromInsales(int $rate, int $price, int|string $servicePoint = null): static
    {
        if (is_string($servicePoint) && ! is_numeric($servicePoint)) {
            $id = FullfillmentApi::pointByCode($servicePoint)->json('_embedded.servicePoints.0.id');

            return new static(self::toFullfillmentRate($rate), $price, $id);
        }

        return new static(self::toFullfillmentRate($rate), $price, $servicePoint);
    }

    public static function toFullfillmentRate(int $rate): int
    {
        return match ($rate) {
            483 => 38,
            482 => 39,
            485 => 437,
            62 => 44,
            122 => 436,
            136 => 48,
            137 => 49,
            368 => 384,
            233 => 58,
            234 => 59,
            378 => 385,
        };
    }
}
