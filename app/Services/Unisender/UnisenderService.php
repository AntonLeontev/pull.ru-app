<?php

namespace App\Services\Unisender;

use App\Services\Unisender\Enums\Gender;

class UnisenderService
{
    public function __construct(public UnisenderApi $api) {}

    public function subscribeFromFooterForm(string $email, Gender $gender): void
    {
        $this->api->subscribe(1, ['email' => $email, 'gender' => $gender->value]);
    }

    public function stylistConsultationSubscribe(string $email, Gender $gender): void
    {
        $this->api->subscribe(10, ['email' => $email, 'gender' => $gender->value]);
    }

    public function subscribeFromPopupRegister(string $email, string $phone, string $name): void
    {
        $this->api->subscribe(9, ['email' => $email, 'phone' => $phone, 'Name' => $name]);
    }

    public function subscribeFromCashbox(string $email, string $phone, string $name): void
    {
        $this->api->subscribe(12, ['email' => $email, 'phone' => $phone, 'Name' => $name]);
    }

    public function subscribeFromInsales(string $email, string $phone, string $name): void
    {
        $this->api->subscribe(11, ['email' => $email, 'phone' => $phone, 'Name' => $name]);
    }

    public function getContact(string $email): object
    {
        return $this->api->getContact($email)->object();
    }
}
