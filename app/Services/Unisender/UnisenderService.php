<?php

namespace App\Services\Unisender;

class UnisenderService
{
    public function __construct(public UnisenderApi $api) {}

    public function subscribeFromFooterForm(string $email): void
    {
        $this->api->subscribe(1, ['email' => $email]);
    }
}
