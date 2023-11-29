<?php

namespace App\Services\MoySklad;

use App\Services\MoySklad\Entities\Product;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class MoySkladApi
{
    public static function getProducts(int $limit = 1000, int $offset = 0): Response
    {
        return Http::moySklad()
            ->get('entity/product', [
                'limit' => $limit,
                'offset' => $offset,
            ]);
    }

    public static function getProduct(string $id): Response
    {
        return Http::moySklad()
            ->get("entity/product/$id");
    }

    public static function updateProduct(string $id, array $params): Response
    {
        return Http::moySklad()
            ->put("entity/product/$id", $params);
    }

    public static function createProduct(
        string $name,
        array $params = [],
    ): Response {
        $data = [
            'name' => $name,
            ...$params,
        ];

        return Http::moySklad()
            ->post('entity/product', $data);
    }

    public static function createProductFolder(
        string $name,
        array $params = [],
    ): Response {
        $data = [
            'name' => $name,
            ...$params,
        ];

        return Http::moySklad()
            ->post('entity/productfolder', $data);
    }

    public static function updateProductFolder(
        string $id,
        array $params = [],
    ): Response {
        return Http::moySklad()
            ->put("entity/productfolder/$id", $params);
    }

    public static function getProductFolders(): Response
    {
        return Http::moySklad()
            ->get('entity/productfolder');
    }

    public static function priceTypeDefault(): Response
    {
        return Http::moySklad()
            ->get('context/companysettings/pricetype/default');
    }

    public static function getPriceTypes(): Response
    {
        return Http::moySklad()
            ->get('context/companysettings/pricetype');
    }

    public static function getUoms(): Response
    {
        return Http::moySklad()
            ->get('/entity/uom');
    }

    public static function getVariants(): Response
    {
        return Http::moySklad()
            ->get('/entity/variant');
    }

    /**
     * @param  App\Services\MoySklad\Entities\Product  $product
     * @param  App\Services\MoySklad\Entities\Characteristic[]  $characteristics
     */
    public static function createVariant(Product $product, array $characteristics, array $params = []): Response
    {
        return Http::moySklad()
            ->post('entity/variant', [
                'product' => $product,
                'characteristics' => $characteristics,
                ...$params,
            ]);
    }

    /**
     * @param  App\Services\MoySklad\Entities\Characteristic[]  $characteristics
     */
    public static function updateVariant(string $id, array $characteristics, array $params = []): Response
    {
        return Http::moySklad()
            ->put("entity/variant/$id", [
                'characteristics' => $characteristics,
                ...$params,
            ]);
    }

    public static function getCharacteristics(): Response
    {
        return Http::moySklad()
            ->get('entity/variant/metadata');
    }

    public static function createCharacteristic(string $name): Response
    {
        return Http::moySklad()
            ->post('entity/variant/metadata/characteristics', [
                'name' => $name,
            ]);
    }

    public static function getWebhooks(): Response
    {
        return Http::moySklad()
            ->get('entity/webhook');
    }

    public static function pceMeta(string $id): array
    {
        return [
            'meta' => [
                'href' => 'https://api.moysklad.ru/api/remap/1.2/entity/uom/'.config('services.moySklad.uom.pce'),
                'type' => 'uom',
            ],
        ];
    }
}
