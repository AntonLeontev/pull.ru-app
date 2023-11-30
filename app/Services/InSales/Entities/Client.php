<?php

namespace App\Services\InSales\Entities;

use JsonSerializable;

readonly class Client implements JsonSerializable
{
    public function __construct(
        public int $id,
        public ?string $email = null,
        public ?string $name = null,
        public ?string $phone = null,
        public ?bool $registered = null,
        public ?string $surname = null,
        public ?string $middlename = null,
    ) {

    }

    public static function fromArray(array $clientData): static
    {
        return new static(
            $clientData['id'],
            $clientData['email'],
            $clientData['name'],
            $clientData['phone'],
            $clientData['registered'],
            $clientData['surname'],
            $clientData['middlename'],
        );
    }

    public static function fromObject(object $clientData): static
    {
        return new static(
            $clientData->id,
            $clientData->email,
            $clientData->name,
            $clientData->phone,
            $clientData->registered,
            $clientData->surname,
            $clientData->middlename,
        );
    }

    public function jsonSerialize(): mixed
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'name' => $this->name,
            'phone' => $this->phone,
            'registered' => $this->registered,
            'surname' => $this->surname,
            'middlename' => $this->middlename,
        ];
    }
}
