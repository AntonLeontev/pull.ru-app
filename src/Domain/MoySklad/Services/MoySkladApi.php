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

    public static function createProductFolder(
        string $name,
        array $params = [],
        string $parentFolder = null,
    ): Response {
        $data = [
            'name' => $name,
            ...$params,
        ];

        if (! empty($parentFolder)) {
            $data['productFolder'] = self::productFolderMeta($parentFolder);
        }

        return Http::moySklad()
            ->post('entity/productfolder', $data);
    }

    public static function updateProductFolder(
        string $id,
        array $params = [],
        string $parentFolder = null,
    ): Response {
        $data = [
            ...$params,
        ];

        if (! empty($parentFolder)) {
            $data['productFolder'] = self::productFolderMeta($parentFolder);
        }

        return Http::moySklad()
            ->put("entity/productfolder/$id", $data);
    }

    public static function getProductFolders(): Response
    {
        return Http::moySklad()
            ->get('entity/productfolder');
    }

    private static function productFolderMeta(string $id): array
    {
        return [
            'meta' => [
                'href' => "https://api.moysklad.ru/api/remap/1.2/entity/productfolder/$id",
                'type' => 'productfolder',
            ],
        ];
    }
}
