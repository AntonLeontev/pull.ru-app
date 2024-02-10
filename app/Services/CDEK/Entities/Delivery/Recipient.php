<?php

namespace App\Services\CDEK\Entities\Delivery;

use JsonSerializable;

readonly class Recipient implements JsonSerializable
{
    public function __construct(
        public string $name,
        public string $email,
        public string $phone,
    ) {
    }

    public function jsonSerialize(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'phones' => [
                ['number' => $this->phone],
            ],
        ];
    }
}
