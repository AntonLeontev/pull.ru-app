<?php

namespace App\Services\Unisender;

use App\Services\Unisender\Enums\Gender;

class UnisenderService
{
    public function __construct(public UnisenderApi $api) {}

    public function subscribeFromFooterForm(array $data): void
    {
        $gender = Gender::fromForm($data['sex']);
        $this->api->subscribe(1, ['email' => $data['email'], 'gender' => $gender->value]);
    }

    public function getContact(string $email): object
    {
        return $this->api->getContact($email)->object();
    }
}
