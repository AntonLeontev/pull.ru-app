<?php

namespace Src\Domain\CDEK\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Src\Domain\CDEK\Entities\Dimensions;
use Src\Domain\CDEK\Entities\Weight;

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

    public static function getLocalities(string $location): Response
    {
        return Http::cdek()
            ->get('locations/localities', [
                'filter' => [
                    [
                        'type' => 'ilike',       // Тип для получения конкретных данных(равно)
                        'field' => 'name',  //  Поле - поиск по названию
                        'value' => $location.'%',    // Значения - Значение сущности
                    ],
                    [
                        'type' => 'eq',       // Тип для получения конкретных данных(равно)
                        'field' => 'state',  //  Поле - поиск по состоянию
                        'value' => 'active',    // Значения - Значение сущности
                    ],
                    [
                        'type' => 'in',       // Тип для получения конкретных данных(равно)
                        'field' => 'type',  //  Поле - поиск по состоянию
                        'values' => ['город'],    // Значения - Значение сущности
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

    public static function getProducts(): Response
    {
        return Http::cdek()
            ->get('products/offer');
    }

    public static function createSimpleProduct(
        string $name,
        string|int|float $price = null,
        string $article = null,
        int $extId = null,
        string|int|float $purchasePrice = null,
        string $image = null,
        Weight $weight = null,
        Dimensions $dimensions = null,
    ): Response {
        return Http::cdek()
            ->post('products/offer', [
                'state' => 'normal',
                'type' => 'simple',
                'shop' => config('services.cdek.shop'),
                'name' => $name,
                'article' => $article,
                'price' => $price,
                'extId' => $extId,
                'purchasingPrice' => $purchasePrice,
                'image' => $image,
                'weight' => $weight,
                'dimensions' => $dimensions,
            ]);
    }

    public static function updateSimpleProduct(int $id, array $params = [])
    {
        $shop = config('services.cdek.shop');

        return Http::cdek()
            ->patch("products/offer/$shop/$id", $params);
    }
}
