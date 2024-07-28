<?php

namespace App\Services\CDEK;

use App\Services\CDEK\Entities\Dimensions;
use App\Services\CDEK\Entities\FullfilmentOrder;
use App\Services\CDEK\Entities\Weight;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
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

    public static function getProducts(int $page = 1): Response
    {
        $url = 'products/offer?';
        if ($page > 1) {
            $url .= "page=$page";
        }

        return Http::cdekff()
            ->get($url);
    }

    public static function createSimpleProduct(
        string $name,
        string|int|float|null $price = null,
        ?string $article = null,
        ?int $extId = null,
        string|int|float|null $purchasePrice = null,
        ?string $image = null,
        ?Weight $weight = null,
        ?Dimensions $dimensions = null,
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

    public static function getProduct(int $id): Response
    {
        $shop = config('services.cdekff.shop');

        return Http::cdekff()
            ->get("products/offer/$shop/$id");
    }

    public static function updateSimpleProduct(int $id, array $params = []): Response
    {
        $shop = config('services.cdekff.shop');

        return Http::cdekff()
            ->patch("products/offer/$shop/$id", $params);
    }

    public static function createOrder(array|FullfilmentOrder $data): Response
    {
        return Http::cdekff()
            ->post('products/order', $data);
    }

    public static function createMovement(int $warehouseId, ?int $extId = null): Response
    {
        return Http::cdekff()
            ->post('storage/movements/document', [
                'type' => 'products',
                'extId' => $extId,
                'warehouse' => $warehouseId,
            ]);
    }

    public static function getMovements(int $page = 1): Response
    {
        $url = 'storage/movements/document?';
        if ($page > 1) {
            $url .= "page=$page";
        }

        return Http::cdekff()
            ->get($url);
    }

    public static function getMovement(int $id): Response
    {
        return Http::cdekff()
            ->get("storage/movements/document/$id");
    }

    public static function deleteMovement(int $id): Response
    {
        return Http::cdekff()
            ->delete("storage/movements/document/$id");
    }

    public static function addProductsToMovement(array|Collection $products): Response
    {
        return Http::cdekff()
            ->post('storage/movements/document/item/bulk', $products);
    }

    public static function getMovementProducts(int $id, int $page = 1): Response
    {
        $url = "storage/movements/document/item?filter[0][type]=eq&filter[0][field]=document&filter[0][value]=$id";
        if ($page > 1) {
            $url .= "&page=$page";
        }

        return Http::cdekff()
            ->get($url);
    }

    public static function getOrders(): Response
    {
        return Http::cdekff()
            ->get('products/order');
    }

    public static function getOrderById(int $id): Response
    {
        return Http::cdekff()
            ->get('products/order/'.$id);
    }

    public static function getOrderByExtId(int $id): Response
    {
        return Http::cdekff()
            ->get("products/order?filter[0][type]=eq&filter[0][field]=extId&filter[0][value]=$id");
    }

    public static function getProductsByOrderId(int $id, int $page = 1): Response
    {
        return Http::cdekff()
            ->get("products/order/product?filter[0][type]=eq&filter[0][field]=order&filter[0][value]=$id&page=$page");
    }
}
