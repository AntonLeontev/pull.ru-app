<?php

use App\Services\CDEK\CdekApi;

if (! function_exists('objectize')) {
    function objectize(array $input): object
    {
        return json_decode(json_encode($input));
    }
}

if (! function_exists('cdek_token')) {
    function cdek_auth_token(): string
    {
        $response = CdekApi::getToken();

        if (is_null(cache('cdek_auth_token'))) {
            cache(['cdek_auth_token' => $response->json('access_token')], $response->json('expires_in'));
        }

        return cache('cdek_auth_token');
    }
}
