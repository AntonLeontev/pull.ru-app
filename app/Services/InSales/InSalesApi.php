<?php

namespace App\Services\InSales;

use Carbon\Carbon;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class InSalesApi
{
    public static function getWebhooks(): Response
    {
        return Http::inSales()
            ->get('admin/webhooks.json');
    }

    public static function createWebhook(
        string $address,
        string $topic,
        string $formatType = 'json',
        int $warehouseId = null,
        int $salesChannelId = null,
        int $batchSize = null,
    ): Response {
        return Http::inSales()
            ->post('admin/webhooks.json', [
                'webhook' => [
                    'address' => $address,
                    'topic' => $topic,
                    'format_type' => $formatType,
                    'warehouse_id' => $warehouseId,
                    'sales_channel_id' => $salesChannelId,
                    'batch_size' => $batchSize,
                ],
            ]);
    }

    public static function updateWebhook(
        int $id,
        string $address = null,
        string $topic = null,
        string $formatType = 'json',
    ): Response {
        $data = ['webhook' => []];

        if (! is_null($address)) {
            $data['webhook']['address'] = $address;
        }
        if (! is_null($topic)) {
            $data['webhook']['topic'] = $topic;
        }
        if (! is_null($formatType)) {
            $data['webhook']['formatType'] = $formatType;
        }

        return Http::inSales()
            ->put("admin/webhooks/$id.json", $data);
    }

    public static function deleteWebhook(int $id): Response
    {
        return Http::inSales()
            ->delete("admin/webhooks/$id.json");
    }

    public static function getCollections(): Response
    {
        return Http::inSales()
            ->get('admin/collections.json');
    }

    public static function getOptionNames(): Response
    {
        return Http::inSales()
            ->get('admin/option_names.json');
    }

    public static function getClients(): Response
    {
        return Http::inSales()
            ->get('admin/clients.json');
    }

    public static function getVariants(int $productId): Response
    {
        return Http::inSales()
            ->get("admin/products/$productId/variants.json");
    }

    public static function getVariant(int $productId, int $variantId): Response
    {
        return Http::inSales()
            ->get("admin/products/$productId/variants/$variantId.json");
    }

    public static function updateVariant(int $productId, int $variantId, array $data): Response
    {
        return Http::inSales()
            ->put("admin/products/$productId/variants/$variantId.json", $data);
    }

    public static function updateVariantsGroup(array $data): Response
    {
        return Http::inSales()
            ->put('/admin/products/variants_group_update.json', $data);
    }

    public static function getProduct(int $id): Response
    {
        return Http::inSales()
            ->get("/admin/products/$id.json");
    }

    public static function getProducts(
        int $fromId = null,
        int $perPage = 10,
        Carbon $updatedSince = null
    ): Response {
        return Http::inSales()
            ->get('/admin/products.json', [
                'with_deleted' => false,
                'from_id' => $fromId,
                'per_page' => $perPage,
                'updated_since' => $updatedSince?->format('Y-m-d H:i:s'),
            ]);
    }

    public static function getProductsCount(): Response
    {
        return Http::inSales()
            ->get('/admin/products/count.json');
    }

    public static function getImage(int $productId, int $imageId): Response
    {
        return Http::inSales()
            ->get("admin/products/$productId/images/$imageId.json");
    }

    public static function getOrder(int $id): Response
    {
        return Http::inSales()
            ->get("/admin/orders/{$id}.json");
    }

    public static function getCustomStatuses(): Response
    {
        return Http::inSales()
            ->get('/admin/custom_statuses.json');
    }
}
