<?php

namespace Src\Domain\CDEK\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class FullfillmentApi
{
    public static function point(int $point): Response
    {
        return Http::cdek()
            ->get("delivery-services/service-points/$point");
    }

    public static function points(): Response
    {
        return Http::cdek()
            ->get('delivery-services/service-points');
    }

    public static function getSenders(): Response
    {
        return Http::cdek()
            ->get('delivery-services/senders');
    }

    public static function getLocalities(): Response
    {
        return Http::cdek()
            ->get('locations/localities', [
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

    public static function getShops(): Response
    {
        return Http::cdek()
            ->get('products/shops');
    }

    public static function getWarehouses(): Response
    {
        return Http::cdek()
            ->get('storage/warehouse');
    }
}
