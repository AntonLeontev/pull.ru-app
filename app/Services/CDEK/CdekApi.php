<?php

namespace App\Services\CDEK;

use App\Services\CDEK\Entities\Delivery\Order;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class CdekApi
{
    public static function getToken()
    {
        return Http::baseUrl('https://api.cdek.ru/v2')
            ->asForm()
            ->post('oauth/token', [
                'grant_type' => 'client_credentials',
                'client_id' => config('services.cdek.login'),
                'client_secret' => config('services.cdek.password'),
            ]);
    }

    public static function cities(string $city)
    {
        return Http::cdek()
            ->get('location/cities', [
                'country_codes' => 'RU',
                'city' => $city,
            ]);
    }

    public static function regions(string $countryCode = 'RU')
    {
        return Http::cdek()
            ->get('location/regions', [
                'country_codes' => $countryCode,
            ]);
    }

    public static function deliverypoints(array $params = [])
    {
        return Http::cdek()
            ->get('deliverypoints', $params);
    }

    public static function calculate(string $city, int $weight, int $length, int $width, int $height)
    {
        return Http::cdek()
            ->post('calculator/tarifflist', [
                'additional_order_types' => 7,
                'from_location' => [
                    'code' => 44,
                ],
                'to_location' => [
                    'address' => $city,
                ],
                'packages' => [
                    'weight' => $weight,
                    'length' => $length,
                    'width' => $width,
                    'height' => $height,
                ],
            ]);
    }

    public static function createOrder(Order $order)
    {
        return Http::cdek()->post('/orders', $order);
    }

    public static function getOrders()
    {
        return Http::cdek()->get('orders');
    }

    public static function getOrder(string $id)
    {
        return Http::cdek()->get("orders/$id");
    }

    public static function getWebhooks(): Response
    {
        return Http::cdek()
            ->get('webhooks');
    }

    public static function getWebhook(string $uuid): Response
    {
        return Http::cdek()
            ->get('webhooks/'.$uuid);
    }

    public static function createWebhook(string $url, string $type, bool $retryable = false, bool $additional = false): Response
    {
        return Http::cdek()
            ->post('webhooks', [
                'url' => $url,
                'type' => $type,
                'retryable' => $retryable,
                'additional' => $additional,
            ]);
    }

    public static function deleteWebhook(string $id): Response
    {
        return Http::cdek()
            ->delete("webhooks/$id");
    }
}
