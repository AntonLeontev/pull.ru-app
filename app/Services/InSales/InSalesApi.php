<?php

namespace App\Services\InSales;

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
}
