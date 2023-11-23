<?php

namespace Src\Domain\InSales\Services;

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

    public static function getCollections()
    {
        return Http::inSales()
            ->get('admin/collections.json');
    }

    public static function getOptionNames()
    {
        return Http::inSales()
            ->get('admin/option_names.json');
    }
}
