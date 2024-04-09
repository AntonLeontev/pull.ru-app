<?php

namespace App\Services\InSales;

use Illuminate\Http\Client\Response;

class InsalesApiService
{
    public static function updateCdekTraceInOrder(int $insalesOrderId, int $cdekTrace): Response
    {
        return InSalesApi::updateOrder($insalesOrderId, [
            'order' => [
                'fields_values_attributes' => [
                    [
                        'handle' => 'trace',
                        'value' => "https://www.cdek.ru/ru/tracking/?order_id=$cdekTrace",
                    ],
                ],
            ],
        ]);
    }

    public static function updateOrderState(int $id, string $permalink): Response
    {
        return InSalesApi::updateOrder($id, [
            'order' => [
                'custom_status_permalink' => $permalink,
            ],
        ]);
    }

    public static function updateOrderPaymentState(int $id, string $state): Response
    {
        return InSalesApi::updateOrder($id, [
            'order' => [
                'financial_status' => $state,
            ],
        ]);
    }
}
