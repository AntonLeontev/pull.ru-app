<?php

namespace App\Services\CDEK;

use App\Services\CDEK\Entities\Dimensions;
use App\Services\CDEK\Entities\FullfilmentOrder;
use App\Services\CDEK\Entities\Weight;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class FullfillmentApi
{
    public static function point(int $point): Response
    {
        return Http::cdekff()
            ->get("delivery-services/service-points/$point");
    }

    public static function points(): Response
    {
        return Http::cdekff()
            ->get('delivery-services/service-points');
    }

    public static function pointByCode(string $code): Response
    {
        return Http::cdekff()
            ->get('delivery-services/service-points', [
                'filter' => [
                    [
                        'type' => 'eq',
                        'field' => 'extId',
                        'value' => $code,
                    ],
                ],
            ]);
    }

    public static function getSenders(): Response
    {
        return Http::cdekff()
            ->get('delivery-services/senders');
    }

    public static function getLocalities(string $location): Response
    {
        return Http::cdekff()
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
        return Http::cdekff()
            ->get('products/shops');
    }

    public static function getWarehouses(): Response
    {
        return Http::cdekff()
            ->get('storage/warehouse');
    }

    public static function getProducts(): Response
    {
        return Http::cdekff()
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
        array $barcodes = [],
    ): Response {
        return Http::cdekff()
            ->post('products/offer', [
                'state' => 'normal',
                'type' => 'simple',
                'shop' => config('services.cdekff.shop'),
                'name' => $name,
                'article' => $article,
                'price' => $price,
                'extId' => $extId,
                'purchasingPrice' => $purchasePrice,
                'image' => $image,
                'weight' => $weight,
                'dimensions' => $dimensions,
                'barcodes' => $barcodes,
            ]);
    }

    public static function updateSimpleProduct(int $id, array $params = []): Response
    {
        $shop = config('services.cdekff.shop');

        return Http::cdekff()
            ->patch("products/offer/$shop/$id", $params);
    }

    public static function createOrder(array|FullfilmentOrder $data)
    {
        return Http::cdekff()
            ->post('products/order', $data);
    }
}
