<?php

namespace App\Services\Ip2location;

class Ip2LocationService
{
    public function __construct(public Ip2locationApi $api)
    {
    }

    public function location(string $ip): Ip2locationDTO
    {
        $response = $this->api->location($ip);

        return Ip2locationDTO::fromResponse($response);
    }
}
