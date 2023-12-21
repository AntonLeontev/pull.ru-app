<?php

namespace App\Services\CDEK;

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
}
