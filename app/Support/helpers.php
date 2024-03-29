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

if (! function_exists('get_delivery_info')) {
    function get_delivery_info(object $order): object
    {
        $fields = collect($order->fields_values);

        $deliveryField = $fields->first(fn ($el) => $el->handle === 'delivery_data');

        return json_decode(str_replace('&quot;', '"', $deliveryField->value));
    }
}

if (! function_exists('organization_by_brand_id')) {
    function organization_by_brand_id(int $brandId): array
    {
        $brands = collect(config('brands.brands'));
        $organizations = collect(config('brands.organizations'));

        $brand = $brands->first(fn ($el) => $el['id'] == $brandId);

        return $organizations->first(fn ($el) => $el['id'] == $brand['organization_id']);
    }
}
