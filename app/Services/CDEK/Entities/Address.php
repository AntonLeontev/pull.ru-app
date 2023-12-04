<?php

namespace App\Services\CDEK\Entities;

readonly class Address extends AbstractEntity
{
    public function __construct(
        public string $city,
        public string $formatted,
        public int|string $entrance,
        public int|string $floor,
        public int|string $apartment,
        public int|string $intercom,
    ) {
    }

    public function jsonSerialize(): array
    {
        return [
            'locality' => [
                'name' => $this->city,
                'country' => 28,
            ],
            'notFormal' => $this->full(),
        ];
    }

    public function full(): string
    {
        $result = $this->formatted;

        if (! empty($this->apartment)) {
            $result = $result.', кв. '.$this->apartment;
        }

        if (! empty($this->entrance)) {
            $result = $result.', подъезд '.$this->entrance;
        }

        if (! empty($this->floor)) {
            $result = $result.', этаж '.$this->floor;
        }

        if (! empty($this->intercom)) {
            $result = $result.', домофон '.$this->intercom;
        }

        return $result;
    }
}
