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
        if (is_null(cache('cdek_auth_token'))) {
            $response = CdekApi::getToken();
            cache(['cdek_auth_token' => $response->json('access_token')], $response->json('expires_in'));
        }

        return cache('cdek_auth_token');
    }
}

if (! function_exists('block_product')) {
    function block_product($id): void
    {
        cache(['blocked_products.'.$id => true]);
    }
}
