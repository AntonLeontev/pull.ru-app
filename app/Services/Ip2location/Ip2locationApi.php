<?php

namespace App\Services\Ip2location;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class Ip2locationApi
{
    public static function location(string $ip): Response
    {
        return Http::ip2location()->get('/?ip='.$ip);
    }
}
