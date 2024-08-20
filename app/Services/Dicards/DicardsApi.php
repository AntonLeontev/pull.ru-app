<?php

namespace App\Services\Dicards;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class DicardsApi
{
    public static function getToken(): string
    {
        if (cache('dicards_token')) {
            return cache('dicards_token');
        }

        $token = Http::dicards()
            ->post('getToken', [
                'apiId' => config('services.dicards.api_id'),
                'apiKey' => config('services.dicards.api_key'),
            ])->json('token');

        cache(['dicards_token' => $token], now()->addMinutes(10));

        return cache('dicards_token');
    }

    public static function createCard(
        int|string $number,
        string $name,
        string $phone,
        string $birthday,
    ): Response {
        return Http::dicards()
            ->withToken(static::getToken())
            // ->dd()
            ->post('passes', [
                'template' => 'limmite',
                'serial_number' => $number,
                'name' => $name,
                'birthday' => $birthday,
                'phone' => $phone,
                'relevantDate' => now()->toISOString(),
                'values' => [
                    [
                        'label' => 'ДЕРЖАТЕЛЬ КАРТЫ',
                        'value' => $name,
                    ],
                    [
                        'label' => 'СКИДКА',
                        'value' => '0%',
                    ],
                ],
            ]);
    }

    public static function getCards()
    {
        return Http::dicards()
            ->withToken(static::getToken())
            ->get('passes');
    }

    public static function getCard(int|string $id)
    {
        return Http::dicards()
            ->withToken(static::getToken())
            ->get("passes/$id");
    }

    public static function getCardLink(int|string $id)
    {
        return Http::dicards()
            ->withToken(static::getToken())
            ->get("passes/$id/link");
    }
}
