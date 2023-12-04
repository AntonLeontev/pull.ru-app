<?php

namespace App\Services\CDEK\Entities;

readonly class Client extends AbstractEntity
{
    public function __construct(
        public string $name,
        public string $email = '',
    ) {
    }

    public function jsonSerialize(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
        ];
    }
}
