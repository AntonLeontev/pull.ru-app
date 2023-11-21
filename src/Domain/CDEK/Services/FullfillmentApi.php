<?php

namespace Src\Domain\CDEK\Services;

use Illuminate\Support\Facades\Http;

class FullfillmentApi
{
    public static function test()
    {
        return Http::post('http://app.lk.reworker.ru/api/oauth/cdek', [
            'client_id' => 'reworker',
            'domain' => 'cdek',
            'grant_type' => 'password',
            'username' => 'z4fbcZcfzmiNGN2XRU95a30qlzbmh7As',
            'password' => 'TFterj9pzTOUBzsB4Bs4mx4tJ978Vl6x',
        ]);
    }

    public static function point(int $point)
    {
        return Http::cdek()
            ->get("api/delivery-services/service-points/$point");
    }

    public static function points()
    {
        return Http::cdek()
            ->get('api/delivery-services/service-points');
    }

    public static function getSenders()
    {
        return Http::cdek()
            ->get('/api/delivery-services/senders');
    }

    public static function getLocalities()
    {
        return Http::cdek()
            ->get('/api/locations/localities', [
                'filter' => [
                    [
                        'type' => 'eq',       // Тип для получения конкретных данных(равно)
                        'field' => 'name',  //  Поле - поиск по названию
                        'value' => 'Москва',    // Значения - Значение сущности
                    ],
                    [
                        'type' => 'eq',       // Тип для получения конкретных данных(равно)
                        'field' => 'state',  //  Поле - поиск по состоянию
                        'value' => 'active',    // Значения - Значение сущности
                    ],
                ],
            ]);
    }
}
