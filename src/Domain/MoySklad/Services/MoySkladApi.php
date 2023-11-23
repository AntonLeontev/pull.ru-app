<?php

namespace Src\Domain\MoySklad\Services;

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
        if (isset($params['salePrices'][0])) {
            $params['salePrices'][0]['priceType'] = self::priceTypeMeta(config('services.moySklad.default_price_type_id'));
            $params['salePrices'][0]['value'] *= 100;
        }

        if (isset($params['buyPrice'])) {
            $params['buyPrice']['value'] *= 100;
        }

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

        if (isset($data['salePrices'][0])) {
            $data['salePrices'][0]['priceType'] = self::priceTypeMeta(config('services.moySklad.default_price_type_id'));
            $data['salePrices'][0]['value'] *= 100;
        }

        if (isset($data['buyPrice'])) {
            $data['buyPrice']['value'] *= 100;
        }

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

    public static function priceTypeMeta(string $id): array
    {
        return [
            'meta' => [
                'href' => "https://api.moysklad.ru/api/remap/1.2/context/companysettings/pricetype/$id",
                'type' => 'pricetype',
            ],
        ];
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
